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

            // Assignments summary
            $assignmentsSummarySql = "SELECT COUNT(*) as assignments_count FROM driver_assignments WHERE from_user_id IN ($placeholders) {$summaryDateConditions}";
            $assignmentsSummaryStmt = $this->db->prepare($assignmentsSummarySql);
            $assignmentsSummaryStmt->execute(array_merge($userIds, $summaryDateParams));
            $assignmentsSummary = $assignmentsSummaryStmt->fetch(PDO::FETCH_ASSOC);

            // Combine all summaries
            $summaryStats = array_merge($summaryStats, (array)$callsSummary, (array)$assignmentsSummary);
            
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
    
    public function getUsersReportWithPoints($filters)
    {
        // Get base user data using the existing filtered query
        $reportData = $this->getUsersReport($filters);
        $users = $reportData['users'];
        $summaryStats = $reportData['summary_stats'];
    
        if (empty($users)) {
            return [
                'users' => [],
                'summary_stats' => $summaryStats,
            ];
        }
    
        $userIds = array_column($users, 'id');
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
    
        // 1. Get base points from tickets and calls, aggregated by user and month
        $monthlyBasePoints = [];
        $tickets = $this->getTicketsForUsers($userIds, $from, $to);
        foreach ($tickets as $ticket) {
            $userId = $ticket['created_by'];
            $year = $ticket['year'];
            $month = $ticket['month'];
            $points = (float)($ticket['points'] ?? 0);
            if (!isset($monthlyBasePoints[$userId][$year][$month])) {
                $monthlyBasePoints[$userId][$year][$month] = 0;
            }
            $monthlyBasePoints[$userId][$year][$month] += $points;
        }
    
        $calls = $this->getCallsForUsers($userIds, $from, $to);
        foreach ($calls as $call) {
            $userId = $call['call_by'];
            $year = $call['year'];
            $month = $call['month'];
            $points = (float)($call['points'] ?? 0);
            if (!isset($monthlyBasePoints[$userId][$year][$month])) {
                $monthlyBasePoints[$userId][$year][$month] = 0;
            }
            $monthlyBasePoints[$userId][$year][$month] += $points;
        }
    
        // 2. Get monthly bonuses
        $monthlyBonuses = $this->getBonusesForUsers($userIds, $from, $to);
    
        // 3. Calculate final points for each user
        $totalPoints = 0;
        foreach ($users as &$user) {
            $userId = $user['id'];
            $userBasePoints = 0;
            $userBonusAmount = 0;
            $userBonusReasons = [];
    
            if (isset($monthlyBasePoints[$userId])) {
                foreach ($monthlyBasePoints[$userId] as $year => $months) {
                    foreach ($months as $month => $basePoints) {
                        $userBasePoints += $basePoints;
                        $bonusPercent = 0;
                        if (isset($monthlyBonuses[$userId][$year][$month])) {
                            $bonusInfo = $monthlyBonuses[$userId][$year][$month];
                            $bonusPercent = (float)($bonusInfo['bonus_percent'] ?? 0);
                            if (!empty($bonusInfo['reason'])) {
                                $userBonusReasons[] = "{$bonusInfo['reason']} ({$bonusPercent}%)";
                            }
                        }
                        $userBonusAmount += $basePoints * ($bonusPercent / 100);
                    }
                }
            }
    
            $finalPoints = $userBasePoints + $userBonusAmount;
            $user['points_details'] = [
                'total_base_points' => $userBasePoints,
                'total_bonus_amount' => $userBonusAmount,
                'final_total_points' => $finalPoints,
                'bonus_reasons' => $userBonusReasons
            ];
            $totalPoints += $finalPoints;
        }
        unset($user);
    
        $summaryStats['total_points'] = $totalPoints;

        return [
            'users' => $users,
            'summary_stats' => $summaryStats
        ];
    }

    private function getTicketsForUsers($userIds, $from, $to)
    {
        if (empty($userIds)) return [];

        // Base query for tickets
        $ticketsSql = "
            SELECT 
                t.created_by, 
                YEAR(t.created_at) as year,
                MONTH(t.created_at) as month,
                COALESCE((
                    SELECT tcp.points 
                    FROM ticket_code_points tcp
                    WHERE tcp.code_id = t.code_id 
                      AND tcp.is_vip = t.is_vip
                      AND DATE(t.created_at) >= tcp.valid_from 
                      AND (tcp.valid_to IS NULL OR DATE(t.created_at) <= tcp.valid_to)
                    ORDER BY tcp.valid_from DESC
                    LIMIT 1
                ), 0) as points
            FROM tickets t
        ";

        // Conditions
        $ticketsConditions = ["t.created_by IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")"];
        $ticketsParams = $userIds;

        if ($from) {
            $ticketsConditions[] = "t.created_at >= ?";
            $ticketsParams[] = $from . ' 00:00:00';
        }
        if ($to) {
            $ticketsConditions[] = "t.created_at <= ?";
            $ticketsParams[] = $to . ' 23:59:59';
        }

        $ticketsSql .= " WHERE " . implode(" AND ", $ticketsConditions);
        
        $stmt = $this->db->prepare($ticketsSql);
        $stmt->execute($ticketsParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCallsForUsers($userIds, $from, $to)
    {
        if (empty($userIds)) return [];

        $callsSql = "
            SELECT 
                dc.call_by, 
                YEAR(dc.created_at) as year,
                MONTH(dc.created_at) as month,
                COALESCE((
                    SELECT cp.points 
                    FROM call_points cp
                    WHERE DATE(dc.created_at) >= cp.valid_from 
                      AND (cp.valid_to IS NULL OR DATE(dc.created_at) <= cp.valid_to)
                    ORDER BY cp.valid_from DESC
                    LIMIT 1
                ), 0) as points
            FROM driver_calls dc
        ";

        $callsConditions = ["dc.call_by IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")"];
        $callsParams = $userIds;

        if ($from) {
            $callsConditions[] = "dc.created_at >= ?";
            $callsParams[] = $from . ' 00:00:00';
        }
        if ($to) {
            $callsConditions[] = "dc.created_at <= ?";
            $callsParams[] = $to . ' 23:59:59';
        }

        $callsSql .= " WHERE " . implode(" AND ", $callsConditions);

        $stmt = $this->db->prepare($callsSql);
        $stmt->execute($callsParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getBonusesForUsers($userIds, $from, $to)
    {
        if (empty($userIds)) return [];

        $bonusSql = "SELECT user_id, bonus_percent, bonus_year, bonus_month, reason FROM user_monthly_bonus";
        
        $bonusConditions = ["user_id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")"];
        $bonusParams = $userIds;

        if ($from) {
            $bonusConditions[] = "LAST_DAY(STR_TO_DATE(CONCAT(bonus_year, '-', bonus_month, '-01'), '%Y-%m-%d')) >= ?";
            $bonusParams[] = $from;
        }
        if ($to) {
            $bonusConditions[] = "STR_TO_DATE(CONCAT(bonus_year, '-', bonus_month, '-01'), '%Y-%m-%d') <= ?";
            $bonusParams[] = $to;
        }

        $bonusSql .= " WHERE " . implode(" AND ", $bonusConditions);
        
        $stmt = $this->db->prepare($bonusSql);
        $stmt->execute($bonusParams);
        $bonuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Re-index the array for easy lookup
        $indexedBonuses = [];
        foreach ($bonuses as $bonus) {
            $indexedBonuses[$bonus['user_id']][$bonus['bonus_year']][$bonus['bonus_month']] = $bonus;
        }

        return $indexedBonuses;
    }
}
