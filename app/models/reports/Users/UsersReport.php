<?php

namespace App\Models\Reports\Users;

use App\Core\Database;
use App\Services\PointsService;
use PDO;

class UsersReport
{
    private $db;
    private $pointsService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pointsService = new PointsService();
    }

    private function getAllActivitiesForUsers(array $userIds, $dateFrom, $dateTo)
    {
        if (empty($userIds)) {
            return [];
        }

        $userIdsPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $params = [];
        
        $ticketsQuery = "
            SELECT 
                'Ticket' as activity_type, td.id as activity_id, td.created_at as activity_date, 
                td.edited_by as user_id, td.is_vip, p.name as platform_name
            FROM ticket_details td
            JOIN platforms p ON td.platform_id = p.id
            WHERE td.edited_by IN ({$userIdsPlaceholders})
        ";
        $ticketParams = $userIds;
        if ($dateFrom) { $ticketsQuery .= " AND td.created_at >= ?"; $ticketParams[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo)   { $ticketsQuery .= " AND td.created_at <= ?"; $ticketParams[] = $dateTo . ' 23:59:59'; }
        $params = array_merge($params, $ticketParams);

        $outgoingCallsQuery = "
            SELECT 
                'Outgoing Call' as activity_type, dc.id as activity_id, dc.created_at as activity_date, 
                dc.call_by as user_id, NULL as is_vip, NULL as platform_name
            FROM driver_calls dc
            WHERE dc.call_by IN ({$userIdsPlaceholders})
        ";
        $outgoingParams = $userIds;
        if ($dateFrom) { $outgoingCallsQuery .= " AND dc.created_at >= ?"; $outgoingParams[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo)   { $outgoingCallsQuery .= " AND dc.created_at <= ?"; $outgoingParams[] = $dateTo . ' 23:59:59'; }
        $params = array_merge($params, $outgoingParams);
        
        $incomingCallsQuery = "
            SELECT 
                'Incoming Call' as activity_type, ic.id as activity_id, ic.call_started_at as activity_date, 
                ic.call_received_by as user_id, NULL as is_vip, NULL as platform_name
            FROM incoming_calls ic
            WHERE ic.call_received_by IN ({$userIdsPlaceholders})
        ";
        $incomingParams = $userIds;
        if ($dateFrom) { $incomingCallsQuery .= " AND ic.call_started_at >= ?"; $incomingParams[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo)   { $incomingCallsQuery .= " AND ic.call_started_at <= ?"; $incomingParams[] = $dateTo . ' 23:59:59'; }
        $params = array_merge($params, $incomingParams);

        $sql = $ticketsQuery . " UNION ALL " . $outgoingCallsQuery . " UNION ALL " . $incomingCallsQuery;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function getUsersReportWithPoints($filters)
    {
        $baseUsers = $this->getUsersReport($filters);
        $users = $baseUsers['users'];
        $userIds = array_column($users, 'id');

        if (empty($userIds)) {
            return [
                'users' => [],
                'summary_stats' => [
                    'normal_tickets' => 0, 'vip_tickets' => 0, 'incoming_calls' => 0, 'outgoing_calls' => 0,
                    'total_points' => 0, 'total_quality_score' => 0, 'total_reviews' => 0,
                ]
            ];
        }

        $activities = $this->getAllActivitiesForUsers($userIds, $filters['date_from'] ?? null, $filters['date_to'] ?? null);

        $userStats = [];
        foreach ($userIds as $id) {
            $userStats[$id] = [
                'normal_tickets' => 0, 'vip_tickets' => 0, 'incoming_calls' => 0, 'outgoing_calls' => 0,
                'total_points' => 0
            ];
        }
        
        foreach ($activities as $activity) {
            if(!isset($userStats[$activity->user_id])) continue;

            $this->pointsService->calculateForActivity($activity);
            $userStats[$activity->user_id]['total_points'] += $activity->points;

            switch ($activity->activity_type) {
                case 'Ticket':
                    $platformName = strtolower(str_replace('_', ' ', $activity->platform_name ?? ''));
                    if ($platformName !== 'incoming call' && $platformName !== 'incoming calls') {
                        if ($activity->is_vip) {
                            $userStats[$activity->user_id]['vip_tickets']++;
                        } else {
                            $userStats[$activity->user_id]['normal_tickets']++;
                        }
                    }
                    break;
                case 'Incoming Call':
                    $userStats[$activity->user_id]['incoming_calls']++;
                    break;
                case 'Outgoing Call':
                    $userStats[$activity->user_id]['outgoing_calls']++;
                    break;
            }
        }
        
        $qualityScores = $this->getQualityScores($userIds, $filters);
        
        foreach ($users as &$user) {
            $userId = $user['id'];
            if(isset($userStats[$userId])) {
                $user = array_merge($user, $userStats[$userId]);
            }

            $user['quality_score'] = $qualityScores[$userId]['quality_score'] ?? 0;
            $user['total_reviews'] = $qualityScores[$userId]['total_reviews'] ?? 0;
        }
        unset($user);

        $summaryStats = [
             'normal_tickets' => array_sum(array_column($userStats, 'normal_tickets')),
             'vip_tickets' => array_sum(array_column($userStats, 'vip_tickets')),
             'incoming_calls' => array_sum(array_column($userStats, 'incoming_calls')),
             'outgoing_calls' => array_sum(array_column($userStats, 'outgoing_calls')),
             'total_points' => array_sum(array_column($userStats, 'total_points')),
             'total_quality_score' => array_sum(array_column($qualityScores, 'quality_score')),
             'total_reviews' => array_sum(array_column($qualityScores, 'total_reviews')),
        ];

        return [
            'users' => $users,
            'summary_stats' => $summaryStats
        ];
    }
    
    public function getUsersReport($filters)
    {
        $mainConditions = [];
        $mainParams = [];

        if (!empty($filters['role_id'])) {
            $mainConditions[] = 'u.role_id = :role_id';
            $mainParams[':role_id'] = $filters['role_id'];
        }
        if (!empty($filters['status'])) {
            $mainConditions[] = 'u.status = :status';
            $mainParams[':status'] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $mainConditions[] = 'u.id = :user_id';
            $mainParams[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['team_id'])) {
            $mainConditions[] = "u.id IN (SELECT user_id FROM team_members WHERE team_id = :team_id)";
            $mainParams[':team_id'] = $filters['team_id'];
        }
        
        $mainWhereClause = !empty($mainConditions) ? 'WHERE ' . implode(' AND ', $mainConditions) : '';
        
        $sql = "SELECT 
                    u.id, u.username, u.email, u.status, u.is_online,
                    r.name as role_name,
                    t.id as team_id,
                    t.name as team_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                {$mainWhereClause}
                GROUP BY u.id
                ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($mainParams);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['users' => $users];
    }

    public function getAllUsersForFilter()
    {
        $stmt = $this->db->prepare("SELECT id, username FROM users ORDER BY username ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getQualityScores(array $userIds, array $filters)
    {
        if (empty($userIds)) {
            return [];
        }

        $dateConditionsSql = "";
        $dateParams = [];

        if (!empty($filters['date_from'])) {
            $dateConditionsSql .= " AND r.reviewed_at >= ?";
            $dateParams[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $dateConditionsSql .= " AND r.reviewed_at <= ?";
            $dateParams[] = $filters['date_to'] . ' 23:59:59';
        }

        $userIdsPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

        $sql = "
            SELECT
                user_id,
                AVG(rating) AS quality_score,
                COUNT(rating) AS total_reviews
            FROM (
                SELECT 
                    td.edited_by AS user_id, 
                    r.rating,
                    r.reviewed_at
                FROM reviews r
                JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type = 'ticket_detail'
                WHERE td.edited_by IN ({$userIdsPlaceholders}) {$dateConditionsSql}

                UNION ALL

                SELECT 
                    dc.call_by AS user_id, 
                    r.rating,
                    r.reviewed_at
                FROM reviews r
                JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type = 'driver_call'
                WHERE dc.call_by IN ({$userIdsPlaceholders}) {$dateConditionsSql}
            ) AS all_reviews
            GROUP BY user_id
        ";

        $params = array_merge($userIds, $dateParams, $userIds, $dateParams);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scores = [];
        foreach ($results as $row) {
            $scores[$row['user_id']] = $row;
        }
        return $scores;
    }
    
    private function getBonusesForUsers($userIds, $from, $to)
    {
         if (empty($userIds)) return [];
     
         $sql = "SELECT user_id, bonus_percent, bonus_year, bonus_month 
                 FROM user_monthly_bonus 
                 WHERE user_id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")";
         
         $params = $userIds;
  
          if ($from) {
             $sql .= " AND STR_TO_DATE(CONCAT(bonus_year, '-', bonus_month, '-01'), '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')";
             $params[] = date('Y-m-01', strtotime($from));
          }
          if ($to) {
             $sql .= " AND STR_TO_DATE(CONCAT(bonus_year, '-', bonus_month, '-01'), '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')";
             $params[] = date('Y-m-t', strtotime($to));
          }
  
         $stmt = $this->db->prepare($sql);
         $stmt->execute($params);
 
         $bonuses = [];
         foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
             $bonuses[$row['user_id']][$row['bonus_year']][$row['bonus_month']] = $row['bonus_percent'];
         }
         return $bonuses;
    }
}
