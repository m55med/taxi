<?php

namespace App\Models\Reports\Users;

use App\Core\Database;
use PDO;

class UsersReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getUsersReport($filters)
    {
        $params = [];
        $conditions = [];

        // Base query
        $sql = "SELECT 
                    u.id, 
                    u.username, 
                    u.email, 
                    u.status,
                    u.is_online,
                    r.name as role_name,
                    t.name as team_name,
                    COALESCE(cs.total_calls, 0) as total_calls,
                    COALESCE(cs.answered, 0) as answered,
                    COALESCE(cs.no_answer, 0) as no_answer,
                    COALESCE(cs.busy, 0) as busy,
                    COALESCE(cs_today.total_calls, 0) as today_total,
                    COALESCE(cs_today.answered, 0) as today_answered,
                    COALESCE(cs_today.no_answer, 0) as today_no_answer,
                    COALESCE(cs_today.busy, 0) as today_busy
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN (
                    SELECT 
                        call_by,
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                        SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                        SUM(CASE WHEN call_status = 'busy' THEN 1 ELSE 0 END) as busy
                    FROM driver_calls
                    GROUP BY call_by
                ) cs ON u.id = cs.call_by
                LEFT JOIN (
                    SELECT
                        call_by,
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                        SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                        SUM(CASE WHEN call_status = 'busy' THEN 1 ELSE 0 END) as busy
                    FROM driver_calls
                    WHERE DATE(created_at) = CURDATE()
                    GROUP BY call_by
                ) cs_today ON u.id = cs_today.call_by
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id";
        
        // Apply filters
        if (!empty($filters['role_id'])) {
            $conditions[] = 'u.role_id = :role_id';
            $params[':role_id'] = $filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['team_id'])) {
            $conditions[] = 't.id = :team_id';
            $params[':team_id'] = $filters['team_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(u.created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(u.created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY u.created_at DESC';

        // Execute user data query
        $stmt = $this->db->query($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process results to add rates
        foreach ($users as &$user) {
            $total = (int)$user['total_calls'];
            $user['call_stats'] = [
                'total_calls' => $total,
                'answered' => (int)$user['answered'],
                'no_answer' => (int)$user['no_answer'],
                'busy' => (int)$user['busy'],
                'answered_rate' => $total > 0 ? ((int)$user['answered'] / $total) * 100 : 0,
                'no_answer_rate' => $total > 0 ? ((int)$user['no_answer'] / $total) * 100 : 0,
                'busy_rate' => $total > 0 ? ((int)$user['busy'] / $total) * 100 : 0,
                'today_total' => (int)$user['today_total'],
                'today_answered' => (int)$user['today_answered'],
                'today_no_answer' => (int)$user['today_no_answer'],
                'today_busy' => (int)$user['today_busy'],
            ];
            // Unset raw stats
            unset($user['total_calls'], $user['answered'], $user['no_answer'], $user['busy']);
            unset($user['today_total'], $user['today_answered'], $user['today_no_answer'], $user['today_busy']);
        }
        unset($user);

        // Summary statistics query
        $summarySql = "SELECT 
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                        SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                        SUM(CASE WHEN call_status = 'busy' THEN 1 ELSE 0 END) as busy
                       FROM driver_calls";
        
        // Re-use date filters if they exist
        $summaryParams = [];
        $summaryConditions = [];
        if (!empty($filters['date_from'])) {
            $summaryConditions[] = 'DATE(created_at) >= :date_from';
            $summaryParams[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $summaryConditions[] = 'DATE(created_at) <= :date_to';
            $summaryParams[':date_to'] = $filters['date_to'];
        }

        if (!empty($summaryConditions)) {
            $summarySql .= ' WHERE ' . implode(' AND ', $summaryConditions);
        }

        $summaryStmt = $this->db->query($summarySql);
        foreach ($summaryParams as $key => $value) {
            $summaryStmt->bindValue($key, $value);
        }
        $summaryStmt->execute();
        $summaryStats = $summaryStmt->fetch(PDO::FETCH_ASSOC);

        $totalCalls = (int)($summaryStats['total_calls'] ?? 0);
        $summaryStats['answered_rate'] = $totalCalls > 0 ? ((int)($summaryStats['answered'] ?? 0) / $totalCalls) * 100 : 0;
        $summaryStats['no_answer_rate'] = $totalCalls > 0 ? ((int)($summaryStats['no_answer'] ?? 0) / $totalCalls) * 100 : 0;
        $summaryStats['busy_rate'] = $totalCalls > 0 ? ((int)($summaryStats['busy'] ?? 0) / $totalCalls) * 100 : 0;
        
        return [
            'users' => $users,
            'summary_stats' => $summaryStats
        ];
    }
} 