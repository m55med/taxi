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

    public function getAllUsersForFilter()
    {
        $sql = "SELECT id, username FROM users ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersReport($filters)
    {
        $params = [];
        $conditions = [];

        $date_conditions_sql = '';
        if (!empty($filters['date_from'])) {
            $date_conditions_sql .= ' AND dc.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $date_conditions_sql .= ' AND dc.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        $tickets_date_conditions_sql = '';
        if (!empty($filters['date_from'])) {
            $tickets_date_conditions_sql .= ' AND t.created_at >= :date_from_tickets';
            $params[':date_from_tickets'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $tickets_date_conditions_sql .= ' AND t.created_at <= :date_to_tickets';
            $params[':date_to_tickets'] = $filters['date_to'] . ' 23:59:59';
        }
        
        $assignments_date_conditions_sql = '';
        if (!empty($filters['date_from'])) {
            $assignments_date_conditions_sql .= ' AND da.created_at >= :date_from_assignments';
            $params[':date_from_assignments'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $assignments_date_conditions_sql .= ' AND da.created_at <= :date_to_assignments';
            $params[':date_to_assignments'] = $filters['date_to'] . ' 23:59:59';
        }

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
                    COALESCE(cs_today.busy, 0) as today_busy,
                    COALESCE(ts.normal_tickets, 0) as normal_tickets,
                    COALESCE(ts.vip_tickets, 0) as vip_tickets,
                    COALESCE(asg.assignments_count, 0) as assignments_count
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN (
                    SELECT 
                        dc.call_by,
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN dc.call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                        SUM(CASE WHEN dc.call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                        SUM(CASE WHEN dc.call_status = 'busy' THEN 1 ELSE 0 END) as busy
                    FROM driver_calls dc
                    WHERE 1=1 {$date_conditions_sql}
                    GROUP BY dc.call_by
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
                LEFT JOIN (
                    SELECT
                        t.created_by,
                        SUM(CASE WHEN t.is_vip = 0 THEN 1 ELSE 0 END) as normal_tickets,
                        SUM(CASE WHEN t.is_vip = 1 THEN 1 ELSE 0 END) as vip_tickets
                    FROM tickets t
                    WHERE 1=1 {$tickets_date_conditions_sql}
                    GROUP BY t.created_by
                ) ts ON u.id = ts.created_by
                LEFT JOIN (
                    SELECT
                        da.from_user_id,
                        COUNT(*) as assignments_count
                    FROM driver_assignments da
                    WHERE 1=1 {$assignments_date_conditions_sql}
                    GROUP BY da.from_user_id
                ) asg ON u.id = asg.from_user_id
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
        
        if (!empty($filters['user_id'])) {
            $conditions[] = 'u.id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY u.created_at DESC';

        // Execute user data query
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
        $userIds = array_column($users, 'id');
        $summaryStats = [
            'total_calls' => 0,
            'answered' => 0,
            'no_answer' => 0,
            'busy' => 0,
            'answered_rate' => 0,
            'no_answer_rate' => 0,
            'busy_rate' => 0,
            'normal_tickets' => 0,
            'vip_tickets' => 0,
            'assignments_count' => 0
        ];
        
        if (!empty($userIds)) {
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';

            $summaryDateParams = [];
            $summaryDateConditions = "";
            if (!empty($filters['date_from'])) {
                $summaryDateConditions .= " AND created_at >= ?";
                $summaryDateParams[] = $filters['date_from'] . " 00:00:00";
            }
            if (!empty($filters['date_to'])) {
                $summaryDateConditions .= " AND created_at <= ?";
                $summaryDateParams[] = $filters['date_to'] . " 23:59:59";
            }

            // Calls summary
            $callsSummarySql = "SELECT 
                                    COUNT(*) as total_calls,
                                    SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                                    SUM(CASE WHEN call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                                    SUM(CASE WHEN call_status = 'busy' THEN 1 ELSE 0 END) as busy
                                FROM driver_calls WHERE call_by IN ($placeholders) {$summaryDateConditions}";
            $callsSummaryStmt = $this->db->prepare($callsSummarySql);
            $callsSummaryStmt->execute(array_merge($userIds, $summaryDateParams));
            $callsSummary = $callsSummaryStmt->fetch(PDO::FETCH_ASSOC);

            // Tickets summary
            $ticketsSummarySql = "SELECT
                                    SUM(CASE WHEN is_vip = 0 THEN 1 ELSE 0 END) as normal_tickets,
                                    SUM(CASE WHEN is_vip = 1 THEN 1 ELSE 0 END) as vip_tickets
                                  FROM tickets WHERE created_by IN ($placeholders) {$summaryDateConditions}";
            $ticketsSummaryStmt = $this->db->prepare($ticketsSummarySql);
            $ticketsSummaryStmt->execute(array_merge($userIds, $summaryDateParams));
            $ticketsSummary = $ticketsSummaryStmt->fetch(PDO::FETCH_ASSOC);

            // Assignments summary
            $assignmentsSummarySql = "SELECT COUNT(*) as assignments_count FROM driver_assignments WHERE from_user_id IN ($placeholders) {$summaryDateConditions}";
            $assignmentsSummaryStmt = $this->db->prepare($assignmentsSummarySql);
            $assignmentsSummaryStmt->execute(array_merge($userIds, $summaryDateParams));
            $assignmentsSummary = $assignmentsSummaryStmt->fetch(PDO::FETCH_ASSOC);

            // Combine all summaries
            $summaryStats = array_merge($summaryStats, (array)$callsSummary, (array)$ticketsSummary, (array)$assignmentsSummary);
            
            $totalCalls = (int)($summaryStats['total_calls'] ?? 0);
            $summaryStats['answered_rate'] = $totalCalls > 0 ? ((int)($summaryStats['answered'] ?? 0) / $totalCalls) * 100 : 0;
            $summaryStats['no_answer_rate'] = $totalCalls > 0 ? ((int)($summaryStats['no_answer'] ?? 0) / $totalCalls) * 100 : 0;
            $summaryStats['busy_rate'] = $totalCalls > 0 ? ((int)($summaryStats['busy'] ?? 0) / $totalCalls) * 100 : 0;
        }
        
        return [
            'users' => $users,
            'summary_stats' => $summaryStats
        ];
    }
} 