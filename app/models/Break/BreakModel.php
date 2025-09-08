<?php

namespace App\Models\Break;

use App\Core\Database;
use PDO;

class BreakModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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

        $sql = "INSERT INTO breaks (user_id, start_time) VALUES (:user_id, UTC_TIMESTAMP())";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([':user_id' => $userId])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Stop an ongoing break.
     */
    public function stop($breakId)
    {
        $sql = "UPDATE breaks 
                SET end_time = UTC_TIMESTAMP(), 
                    duration_seconds = TIMESTAMPDIFF(SECOND, start_time, UTC_TIMESTAMP()) 
                WHERE id = :id AND end_time IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $breakId]);
    }
    
    /**
     * Get the current ongoing break for a user.
     */
    public function getOngoingBreak($userId)
    {
        $sql = "SELECT id, user_id, start_time, end_time, duration_seconds, created_at, updated_at 
                FROM breaks 
                WHERE user_id = :user_id AND end_time IS NULL 
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
                    SUM(b.duration_seconds) as total_duration_seconds,
                    SEC_TO_TIME(SUM(b.duration_seconds)) as total_duration_formatted,
                    COUNT(b.id) as total_breaks
                FROM breaks b
                JOIN users u ON b.user_id = u.id
                WHERE b.duration_seconds IS NOT NULL";

        $params = [];

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

        $sql .= " GROUP BY u.id, u.name ORDER BY total_duration_seconds DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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

    private function formatSeconds($seconds) {
        if ($seconds === null || $seconds == 0) return '00:00:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
