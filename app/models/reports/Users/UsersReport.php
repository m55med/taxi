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

    private function getIncomingCallPlatformId() {
        // Cache the ID to avoid multiple queries
        static $platformId = null;
        if ($platformId === null) {
            $stmt = $this->db->prepare("SELECT id FROM platforms WHERE name = 'Incoming Call'");
            $stmt->execute();
            $platformId = $stmt->fetchColumn() ?: -1; // Return -1 or another invalid ID if not found
        }
        return $platformId;
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
        $mainParams = [];
        $mainConditions = [];

        $outgoingCallSubParams = [];
        $outgoingCallSubConditions = "1=1";

        $incomingCallSubParams = [];
        $incomingCallSubConditions = "1=1";

        $assignmentSubParams = [];
        $assignmentSubConditions = "1=1";

        // Handle date filters for all relevant subqueries
        if (!empty($filters['date_from'])) {
            $dateFrom = $filters['date_from'] . ' 00:00:00';
            
            $outgoingCallSubConditions .= " AND dc.created_at >= :date_from_outgoing";
            $outgoingCallSubParams[':date_from_outgoing'] = $dateFrom;

            $incomingCallSubConditions .= " AND ic.call_started_at >= :date_from_incoming";
            $incomingCallSubParams[':date_from_incoming'] = $dateFrom;

            $assignmentSubConditions .= " AND da.created_at >= :date_from_assign";
            $assignmentSubParams[':date_from_assign'] = $dateFrom;
        }
        if (!empty($filters['date_to'])) {
            $dateTo = $filters['date_to'] . ' 23:59:59';

            $outgoingCallSubConditions .= " AND dc.created_at <= :date_to_outgoing";
            $outgoingCallSubParams[':date_to_outgoing'] = $dateTo;
            
            $incomingCallSubConditions .= " AND ic.call_started_at <= :date_to_incoming";
            $incomingCallSubParams[':date_to_incoming'] = $dateTo;

            $assignmentSubConditions .= " AND da.created_at <= :date_to_assign";
            $assignmentSubParams[':date_to_assign'] = $dateTo;
        }

        // Handle main query filters
        if (!empty($filters['role_id'])) {
            $mainConditions[] = 'u.role_id = :role_id';
            $mainParams[':role_id'] = $filters['role_id'];
        }
        if (!empty($filters['status'])) {
            $mainConditions[] = 'u.status = :status';
            $mainParams[':status'] = $filters['status'];
        }
        if (!empty($filters['team_id'])) {
            $mainConditions[] = 't.id = :team_id';
            $mainParams[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['user_id'])) {
            $mainConditions[] = 'u.id = :user_id';
            $mainParams[':user_id'] = $filters['user_id'];
        }

        // Construct the main WHERE clause
        $mainWhereClause = !empty($mainConditions) ? 'WHERE ' . implode(' AND ', $mainConditions) : '';

        // Base query with placeholders for subquery conditions
        $sql = "SELECT 
                    u.id, u.username, u.email, u.status, u.is_online,
                    r.name as role_name, t.name as team_name,
                    COALESCE(outgoing_cs.total_calls, 0) as total_outgoing_calls,
                    COALESCE(outgoing_cs.answered, 0) as answered_outgoing,
                    COALESCE(outgoing_cs.no_answer, 0) as no_answer_outgoing,
                    COALESCE(outgoing_cs.busy, 0) as busy_outgoing,
                    COALESCE(incoming_cs.total_calls, 0) as total_incoming_calls,
                    COALESCE(cs_today.total_calls, 0) as today_total,
                    COALESCE(cs_today.answered, 0) as today_answered,
                    COALESCE(asg.assignments_count, 0) as assignments_count
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN (
                    SELECT dc.call_by, COUNT(*) as total_calls,
                           SUM(CASE WHEN dc.call_status = 'answered' THEN 1 ELSE 0 END) as answered,
                           SUM(CASE WHEN dc.call_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                           SUM(CASE WHEN dc.call_status = 'busy' THEN 1 ELSE 0 END) as busy
                    FROM driver_calls dc
                    WHERE {$outgoingCallSubConditions}
                    GROUP BY dc.call_by
                ) outgoing_cs ON u.id = outgoing_cs.call_by
                LEFT JOIN (
                    SELECT ic.call_received_by, COUNT(*) as total_calls
                    FROM incoming_calls ic
                    WHERE {$incomingCallSubConditions}
                    GROUP BY ic.call_received_by
                ) incoming_cs ON u.id = incoming_cs.call_received_by
                LEFT JOIN (
                    SELECT call_by, COUNT(*) as total_calls, SUM(CASE WHEN call_status = 'answered' THEN 1 ELSE 0 END) as answered
                    FROM driver_calls
                    WHERE DATE(created_at) = CURDATE()
                    GROUP BY call_by
                ) cs_today ON u.id = cs_today.call_by
                LEFT JOIN (
                    SELECT da.from_user_id, COUNT(*) as assignments_count
                    FROM driver_assignments da
                    WHERE {$assignmentSubConditions}
                    GROUP BY da.from_user_id
                ) asg ON u.id = asg.from_user_id
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                {$mainWhereClause}
                ORDER BY u.created_at DESC";

        // Combine all parameters
        $finalParams = array_merge($mainParams, $outgoingCallSubParams, $incomingCallSubParams, $assignmentSubParams);

        // Execute user data query
        $stmt = $this->db->prepare($sql);
        $stmt->execute($finalParams);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process results to add rates
        foreach ($users as &$user) {
            $total_outgoing = (int)$user['total_outgoing_calls'];
            $user['call_stats'] = [
                'total_outgoing_calls' => $total_outgoing,
                'answered_outgoing' => (int)$user['answered_outgoing'],
                'no_answer_outgoing' => (int)$user['no_answer_outgoing'],
                'busy_outgoing' => (int)$user['busy_outgoing'],
                'answered_rate' => $total_outgoing > 0 ? ((int)$user['answered_outgoing'] / $total_outgoing) * 100 : 0,
                'no_answer_rate' => $total_outgoing > 0 ? ((int)$user['no_answer_outgoing'] / $total_outgoing) * 100 : 0,
                'busy_rate' => $total_outgoing > 0 ? ((int)$user['busy_outgoing'] / $total_outgoing) * 100 : 0,
                'total_incoming_calls' => (int)$user['total_incoming_calls'],
                'today_total' => (int)$user['today_total'],
                'today_answered' => (int)$user['today_answered'],
            ];
            // Unset raw stats
            unset($user['total_outgoing_calls'], $user['answered_outgoing'], $user['no_answer_outgoing'], $user['busy_outgoing'], $user['total_incoming_calls']);
            unset($user['today_total'], $user['today_answered']);
        }
        unset($user);
        
        // No need for separate summary queries. We can calculate from the filtered $users array.
        $summaryStats = [
            'total_outgoing_calls' => array_sum(array_column(array_column($users, 'call_stats'), 'total_outgoing_calls')),
            'answered_outgoing' => array_sum(array_column(array_column($users, 'call_stats'), 'answered_outgoing')),
            'no_answer_outgoing' => array_sum(array_column(array_column($users, 'call_stats'), 'no_answer_outgoing')),
            'busy_outgoing' => array_sum(array_column(array_column($users, 'call_stats'), 'busy_outgoing')),
            'total_incoming_calls' => array_sum(array_column(array_column($users, 'call_stats'), 'total_incoming_calls')),
            'assignments_count' => array_sum(array_column($users, 'assignments_count'))
        ];
        
        $totalOutgoingCalls = (int)($summaryStats['total_outgoing_calls'] ?? 0);
        $summaryStats['answered_rate'] = $totalOutgoingCalls > 0 ? (($summaryStats['answered_outgoing'] ?? 0) / $totalOutgoingCalls) * 100 : 0;
        $summaryStats['no_answer_rate'] = $totalOutgoingCalls > 0 ? (($summaryStats['no_answer_outgoing'] ?? 0) / $totalOutgoingCalls) * 100 : 0;
        $summaryStats['busy_rate'] = $totalOutgoingCalls > 0 ? (($summaryStats['busy_outgoing'] ?? 0) / $totalOutgoingCalls) * 100 : 0;

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
            $summaryStats['normal_tickets'] = 0;
            $summaryStats['vip_tickets'] = 0;
            $summaryStats['total_points'] = 0;
            return [
                'users' => [],
                'summary_stats' => $summaryStats,
            ];
        }
    
        $userIds = array_column($users, 'id');
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
    
        // Initialize ticket and call counts for all users
        $userStats = array_fill_keys($userIds, [
            'incoming_calls' => 0, 
            'other_tickets' => 0, 
            'vip_tickets' => 0, // Keep this for VIP tickets that are NOT incoming calls
        ]);

        // 1. Get tickets and aggregate counts
        $incomingCallPlatformId = $this->getIncomingCallPlatformId();
        $tickets = $this->getTicketsForUsers($userIds, $from, $to, $incomingCallPlatformId);
        
        foreach ($tickets as $ticket) {
            $userId = $ticket['created_by'];
            if (isset($userStats[$userId])) {
                if ($ticket['platform_id'] == $incomingCallPlatformId) {
                    $userStats[$userId]['incoming_calls']++;
                } else {
                    // This is a regular ticket (not from an incoming call)
                    if ($ticket['is_vip']) {
                        $userStats[$userId]['vip_tickets']++;
                    } else {
                        $userStats[$userId]['other_tickets']++;
                    }
                }
            }
        }
        
        // 1. Get base points from tickets and calls, aggregated by user and month
        $monthlyBasePoints = [];
        // Calculate points only for tickets that are NOT from incoming calls
        $ticketPointsData = $this->getTicketPointsForUsers($userIds, $from, $to, $incomingCallPlatformId);
        foreach ($ticketPointsData as $ticket) {
            $userId = $ticket['created_by'];
            $year = $ticket['year'];
            $month = $ticket['month'];
            $points = $ticket['points'] ?? 0;
            $monthlyBasePoints[$userId][$year][$month] = ($monthlyBasePoints[$userId][$year][$month] ?? 0) + $points;
        }

        $calls = $this->getCallsForUsers($userIds, $from, $to);
        foreach ($calls as $call) {
            $userId = $call['call_by'];
            $year = $call['year'];
            $month = $call['month'];
            $points = (float)($call['points'] ?? 0);
            $monthlyBasePoints[$userId][$year][$month] = ($monthlyBasePoints[$userId][$year][$month] ?? 0) + $points;
        }

        // 2. Get bonuses for users
        $bonuses = $this->getBonusesForUsers($userIds, $from, $to);
        $userBonuses = [];
        foreach ($bonuses as $bonus) {
            $userBonuses[$bonus['user_id']][] = $bonus;
        }

        // 3. Calculate final points for each user and add ticket counts
        foreach ($users as &$user) {
            $userId = $user['id'];
            
            // Add ticket counts to user object
            $user['incoming_calls'] = $userStats[$userId]['incoming_calls'] ?? 0;
            $user['normal_tickets'] = $userStats[$userId]['other_tickets']; // Rename for consistency in the view
            $user['vip_tickets'] = $userStats[$userId]['vip_tickets'];
            $user['outgoing_calls'] = $user['call_stats']['total_outgoing_calls']; // This is from driver_calls

            $totalBasePoints = 0;
            if (isset($monthlyBasePoints[$userId])) {
                foreach ($monthlyBasePoints[$userId] as $year => $months) {
                    foreach ($months as $month => $basePoints) {
                        $totalBasePoints += $basePoints;
                    }
                }
            }

            $userBonusAmount = 0;
            $userBonusReasons = [];
            if (isset($userBonuses[$userId])) {
                foreach ($userBonuses[$userId] as $bonus) {
                    $bonusPercent = (float)($bonus['bonus_percent'] ?? 0);
                    if (!empty($bonus['reason'])) {
                        $userBonusReasons[] = "{$bonus['reason']} ({$bonusPercent}%)";
                    }
                    $userBonusAmount += $totalBasePoints * ($bonusPercent / 100);
                }
            }

            $finalPoints = $totalBasePoints + $userBonusAmount;
            $pointsDetails = [
                'total_base_points' => $totalBasePoints,
                'total_bonus_amount' => $userBonusAmount,
                'final_total_points' => $finalPoints,
                'bonus_reasons' => $userBonusReasons
            ];

            $user['points_details'] = $pointsDetails;
        }
        unset($user);

        // Update summary statistics with ticket counts and total points
        $summaryStats['incoming_calls'] = array_sum(array_column($userStats, 'incoming_calls'));
        $summaryStats['normal_tickets'] = array_sum(array_column($userStats, 'other_tickets'));
        $summaryStats['vip_tickets'] = array_sum(array_column($userStats, 'vip_tickets'));
        $summaryStats['outgoing_calls'] = $summaryStats['total_outgoing_calls']; // from driver_calls

        $summaryStats['total_points'] = array_sum(array_map(function($u) {
            return $u['points_details']['final_total_points'] ?? 0;
        }, $users));

        return [
            'users' => $users,
            'summary_stats' => $summaryStats
        ];
    }

    private function getTicketPointsForUsers($userIds, $from, $to, $incomingCallPlatformId)
    {
        $sql = "SELECT 
                    t.created_by,
                    YEAR(td.created_at) as year,
                    MONTH(td.created_at) as month,
                    SUM(COALESCE(tcp.points, 0)) as points
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                LEFT JOIN ticket_code_points tcp ON td.code_id = tcp.code_id AND td.is_vip = tcp.is_vip
                    AND DATE(td.created_at) >= tcp.valid_from 
                    AND (DATE(td.created_at) <= tcp.valid_to OR tcp.valid_to IS NULL)
                WHERE t.created_by IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")";

        $params = $userIds;

        // Exclude tickets from the 'Incoming Call' platform
        if ($incomingCallPlatformId > 0) {
            $sql .= " AND td.platform_id != ?";
            $params[] = $incomingCallPlatformId;
        }

        if ($from) {
            $sql .= " AND td.created_at >= ?";
            $params[] = $from . ' 00:00:00';
        }
        if ($to) {
            $sql .= " AND td.created_at <= ?";
            $params[] = $to . ' 23:59:59';
        }

        $sql .= " GROUP BY t.created_by, year, month";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTicketsForUsers($userIds, $from, $to, $incomingCallPlatformId)
    {
        $sql = "SELECT 
                    t.created_by,
                    td.platform_id,
                    td.is_vip
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                WHERE t.created_by IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")";
        
        $params = $userIds;

        // Correctly apply the incoming call platform exclusion for counting
        if ($from) {
            $sql .= " AND td.created_at >= ?";
            $params[] = $from . ' 00:00:00';
        }
        if ($to) {
            $sql .= " AND td.created_at <= ?";
            $params[] = $to . ' 23:59:59';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
