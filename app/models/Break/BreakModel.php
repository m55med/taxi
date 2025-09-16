<?php

namespace App\Models\Break;

use App\Core\Database;
use PDO;
use App\Models\Break\BreakStorage;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class BreakModel
{
    private $db;
    private $breakStorage;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->breakStorage = new BreakStorage();
    }

    /**
     * Start a new break for a user and return the new break's ID.
     */
    public function start($userId)
    {
        // Check if there is already an ongoing break for the user
        $ongoing = $this->getOngoingBreak($userId);
        if ($ongoing) {
            return false; // Or handle as an error, e.g., return the existing break ID
        }

        $utcTimestamp = DateTimeHelper::getCurrentUTC();
        $sql = "INSERT INTO breaks (user_id, start_time, is_active) VALUES (:user_id, :start_time, 1)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([':user_id' => $userId, ':start_time' => $utcTimestamp])) {
            $breakId = $this->db->lastInsertId();

            // Get user info for JSON storage
            $userInfo = $this->getUserInfo($userId);
            if ($userInfo) {
                $breakData = (object) [
                    'id' => $breakId,
                    'user_id' => $userId,
                    'user_name' => $userInfo->name,
                    'team_name' => $userInfo->team_name,
                    'start_time' => date('Y-m-d H:i:s')
                ];
                $this->breakStorage->addActiveBreak($breakData);
            }

            return $breakId;
        }
        return false;
    }

    /**
     * Stop an ongoing break.
     */
    public function stop($breakId)
    {
        $utcTimestamp = DateTimeHelper::getCurrentUTC();
        $sql = "UPDATE breaks
                SET end_time = :end_time,
                    duration_seconds = TIMESTAMPDIFF(SECOND, start_time, :end_time_calc),
                    is_active = 0
                WHERE id = :id AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $breakId,
            ':end_time' => $utcTimestamp,
            ':end_time_calc' => $utcTimestamp
        ]);

        // Remove from JSON storage
        if ($result) {
            $this->breakStorage->removeActiveBreak($breakId);
        }

        return $result;
    }
    
    /**
     * Get the current ongoing break for a user.
     */
    public function getOngoingBreak($userId)
    {
        $sql = "SELECT id, user_id, start_time, end_time, duration_seconds, created_at, updated_at
                FROM breaks
                WHERE user_id = :user_id AND is_active = 1
                ORDER BY start_time DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $break = $stmt->fetch(PDO::FETCH_OBJ);

        if ($break && $break->start_time) {
            try {
                // Create a DateTime object from the database time, assuming it's UTC
                $dateTime = new \DateTime($break->start_time, new \DateTimeZone('UTC'));
                // Format it to ISO8601 with 'Z' for JavaScript
                $break->start_time = $dateTime->format(DATE_ISO8601);
            } catch (\Exception $e) {
                error_log("Error formatting start_time to ISO8601 UTC: " . $e->getMessage());
                // Fallback to original if error occurs
                // $break->start_time remains as fetched from DB
            }
        }

        return $break;
    }
    

    /**
     * Get all breaks for a specific user with filtering.
     */
    public function getBreaksForUser($userId, $filters = [])
    {
        $sql = "SELECT *, SEC_TO_TIME(duration_seconds) as duration_formatted 
                FROM breaks 
                WHERE user_id = :user_id";

        $params = [':user_id' => $userId];

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(start_time) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(start_time) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        $sql .= " ORDER BY start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get a summary of breaks for all users with filtering.
     */
    public function getBreaksSummary($filters = [])
    {
        $sql = "SELECT
                    u.id as user_id,
                    u.name as user_name,
                    t.name as team_name,
                    tm.team_id,
                    SUM(b.duration_seconds) as total_duration_seconds,
                    SEC_TO_TIME(SUM(b.duration_seconds)) as total_duration_formatted,
                    COUNT(b.id) as total_breaks,
                    ROUND(SUM(b.duration_seconds) / 60, 0) as total_minutes,
                    MAX(CASE WHEN b.is_active = 1 THEN 1 ELSE 0 END) as currently_on_break,
                    MAX(CASE WHEN b.is_active = 1 THEN b.start_time END) as current_break_start,
                    MAX(CASE WHEN b.is_active = 1 THEN TIMESTAMPDIFF(MINUTE, b.start_time, UTC_TIMESTAMP()) END) as current_break_minutes
                FROM breaks b
                JOIN users u ON b.user_id = u.id
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                WHERE b.end_time IS NOT NULL";

        $params = [];

        // Filter by specific user if provided
        if (!empty($filters['user_id'])) {
            $sql .= " AND u.id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        // Filter by team if provided
        if (!empty($filters['team_id'])) {
            $sql .= " AND tm.team_id = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND u.name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(b.start_time) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(b.start_time) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        // Handle sorting
        $sortBy = $filters['sort_by'] ?? 'total_duration_seconds';
        $sortOrder = strtoupper($filters['sort_order'] ?? 'DESC');

        // Validate sort parameters
        $allowedSortFields = ['total_duration_seconds', 'total_breaks', 'user_name'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'total_duration_seconds';
        }
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        // Map sort fields to actual column names
        $sortFieldMap = [
            'total_duration_seconds' => 'total_duration_seconds',
            'total_breaks' => 'total_breaks',
            'user_name' => 'u.name'
        ];

        $actualSortField = $sortFieldMap[$sortBy] ?? 'total_duration_seconds';

        $sql .= " GROUP BY u.id, u.name, t.name, tm.team_id ORDER BY {$actualSortField} {$sortOrder}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Get current break details for users currently on break
        $currentBreaksSql = "
            SELECT
                b.user_id,
                b.start_time,
                TIMESTAMPDIFF(MINUTE, b.start_time, UTC_TIMESTAMP()) as minutes_elapsed
            FROM breaks b
            WHERE b.is_active = 1
        ";

        $stmt2 = $this->db->prepare($currentBreaksSql);
        $stmt2->execute();
        $currentBreaks = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Create a map of user_id to current break info
        $currentBreakMap = [];
        foreach ($currentBreaks as $break) {
            $currentBreakMap[$break['user_id']] = [
                'start_time' => $break['start_time'],
                'minutes_elapsed' => $break['minutes_elapsed']
            ];
        }

        // Add current break info to results
        foreach ($results as $result) {
            if (isset($currentBreakMap[$result->user_id])) {
                $result->current_break_info = $currentBreakMap[$result->user_id];
            } else {
                $result->current_break_info = null;
            }
        }

        return $results;
    }

    /**
     * Get overall summary statistics with filtering.
     */
    public function getOverallSummaryStats($filters = [])
    {
        $sql = "SELECT 
                    COUNT(DISTINCT b.user_id) as total_users,
                    COUNT(b.id) as total_breaks,
                    SUM(b.duration_seconds) as total_duration_seconds
                FROM breaks b
                JOIN users u ON b.user_id = u.id
                WHERE b.duration_seconds IS NOT NULL";

        $params = [];

        // Filter by specific user if provided
        if (!empty($filters['user_id'])) {
            $sql .= " AND u.id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND u.name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(b.start_time) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(b.start_time) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if ($result) {
            $result->total_duration_formatted = $this->formatSeconds($result->total_duration_seconds);
        }
        
        return $result;
    }

    /**
     * Get summary stats for a single user with filtering.
     */
    public function getUserSummaryStats($userId, $filters = [])
    {
        $sql = "SELECT 
                    COUNT(id) as total_breaks,
                    SUM(duration_seconds) as total_duration_seconds
                FROM breaks
                WHERE user_id = :user_id AND duration_seconds IS NOT NULL";

        $params = [':user_id' => $userId];

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(start_time) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(start_time) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if ($result) {
            $result->total_duration_formatted = $this->formatSeconds($result->total_duration_seconds);
        }
        
        return $result;
    }

    /**
     * Get all teams for filtering
     */
    public function getAllTeams()
    {
        $sql = "SELECT id, name FROM teams ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get team name by ID
     */
    public function getTeamNameById($teamId)
    {
        if (empty($teamId)) {
            return null;
        }

        $sql = "SELECT name FROM teams WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $teamId]);
        $team = $stmt->fetch(PDO::FETCH_OBJ);

        return $team ? $team->name : null;
    }

    /**
     * Get current ongoing breaks for all users (from database using is_active)
     */
    public function getCurrentOngoingBreaks()
    {
        $utcTimestamp = DateTimeHelper::getCurrentUTC();
        $sql = "SELECT
                    b.id,
                    b.user_id,
                    b.start_time,
                    u.name as user_name,
                    t.name as team_name,
                    TIMESTAMPDIFF(MINUTE, b.start_time, :current_time) as minutes_elapsed
                FROM breaks b
                JOIN users u ON b.user_id = u.id
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                WHERE b.is_active = 1
                ORDER BY b.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':current_time' => $utcTimestamp]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get count of users currently on break
     */
    public function getCurrentBreakCount()
    {
        $sql = "SELECT COUNT(*) as count FROM breaks WHERE is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count ?? 0;
    }

    /**
     * Get user info for JSON storage
     */
    private function getUserInfo($userId)
    {
        $sql = "SELECT
                    u.name,
                    t.name as team_name
                FROM users u
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                WHERE u.id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    private function formatSeconds($seconds) {
        if ($seconds === null || $seconds == 0) return '00:00:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
