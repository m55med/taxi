<?php

namespace App\Models\Reports\Users;

use App\Core\Database;
use PDO;

class UsersReport
{
    private $db;
    private $cache = [];
    private $cacheTime = 300; // 5 minutes cache

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getCacheKey($method, $params)
    {
        return md5($method . serialize($params));
    }

    private function getCached($key)
    {
        if (isset($this->cache[$key])) {
            $cached = $this->cache[$key];
            if (time() - $cached['time'] < $this->cacheTime) {
                return $cached['data'];
            }
            unset($this->cache[$key]);
        }
        return null;
    }

    private function setCache($key, $data)
    {
        $this->cache[$key] = [
            'data' => $data,
            'time' => time()
        ];

        // Limit cache size
        if (count($this->cache) > 50) {
            array_shift($this->cache);
        }
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
        $params = array_merge($params ?? [], $ticketParams);

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

    private function getOptimizedUserStats(array $userIds, array $filters)
    {
        if (empty($userIds)) {
            return [];
        }
    
        // Try cache first
        $cacheKey = $this->getCacheKey('optimized_user_stats', [$userIds, $filters]);
        $cached = $this->getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    
        // placeholders لليوزرز
        $userIdsPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $userParams = $userIds;
    
        // conditions للتاريخ
        $dateConditions = "";
        $dateParams = [];
        if (!empty($filters['date_from'])) {
            $dateConditions .= " AND activities.activity_date >= ?";
            $dateParams[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $dateConditions .= " AND activities.activity_date <= ?";
            $dateParams[] = $filters['date_to'] . ' 23:59:59';
        }
    
        // الاستعلام
        $sql = "
            SELECT
                user_id,
                SUM(CASE WHEN activity_type = 'Ticket' 
                          AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls') 
                          AND is_vip = 1 THEN 1 ELSE 0 END) as vip_tickets,
                SUM(CASE WHEN activity_type = 'Ticket' 
                          AND LOWER(REPLACE(platform_name, '_', ' ')) NOT IN ('incoming call', 'incoming calls') 
                          AND is_vip = 0 THEN 1 ELSE 0 END) as normal_tickets,
                SUM(CASE WHEN activity_type = 'Incoming Call' THEN 1 ELSE 0 END) as incoming_calls,
                SUM(CASE WHEN activity_type = 'Outgoing Call' THEN 1 ELSE 0 END) as outgoing_calls,
                SUM(COALESCE(points, 0)) as total_points
            FROM (
                -- Tickets
                SELECT
                    td.edited_by as user_id,
                    'Ticket' as activity_type,
                    td.created_at as activity_date,
                    td.is_vip,
                    p.name as platform_name,
                    COALESCE(tcp.points, 0) as points
                FROM ticket_details td
                JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN ticket_code_points tcp 
                    ON tcp.code_id = td.code_id
                   AND tcp.is_vip = td.is_vip
                   AND tcp.valid_from <= td.created_at
                   AND (tcp.valid_to >= td.created_at OR tcp.valid_to IS NULL)
                WHERE td.edited_by IN ({$userIdsPlaceholders})
    
                UNION ALL
    
                -- Outgoing Calls
                SELECT
                    dc.call_by as user_id,
                    'Outgoing Call' as activity_type,
                    dc.created_at as activity_date,
                    NULL as is_vip,
                    NULL as platform_name,
                    COALESCE(cp.points, 0) as points
                FROM driver_calls dc
                LEFT JOIN call_points cp 
                    ON cp.call_type = 'outgoing'
                   AND cp.valid_from <= dc.created_at
                   AND (cp.valid_to >= dc.created_at OR cp.valid_to IS NULL)
                WHERE dc.call_by IN ({$userIdsPlaceholders})
    
                UNION ALL
    
                -- Incoming Calls
                SELECT
                    ic.call_received_by as user_id,
                    'Incoming Call' as activity_type,
                    ic.call_started_at as activity_date,
                    NULL as is_vip,
                    NULL as platform_name,
                    COALESCE(cp.points, 0) as points
                FROM incoming_calls ic
                LEFT JOIN call_points cp 
                    ON cp.call_type = 'incoming'
                   AND cp.valid_from <= ic.call_started_at
                   AND (cp.valid_to >= ic.call_started_at OR cp.valid_to IS NULL)
                WHERE ic.call_received_by IN ({$userIdsPlaceholders})
            ) activities
            WHERE 1=1 {$dateConditions}
            GROUP BY user_id
        ";
    
        // params = userIds × 3 + dateParams
        $finalParams = array_merge($userParams, $userParams, $userParams, $dateParams);
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute($finalParams);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Convert to associative array keyed by user_id
        $userStats = [];
        foreach ($results as $row) {
            $userStats[$row['user_id']] = [
                'normal_tickets' => (int)$row['normal_tickets'],
                'vip_tickets' => (int)$row['vip_tickets'],
                'incoming_calls' => (int)$row['incoming_calls'],
                'outgoing_calls' => (int)$row['outgoing_calls'],
                'total_points' => (float)$row['total_points']
            ];
        }
    
        // Cache the results
        $this->setCache($cacheKey, $userStats);
    
        return $userStats;
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
                'total_points' => 0, 'avg_quality_score' => 0, 'total_reviews' => 0,
            ]
        ];
    }

    // Get all data in optimized queries
    $userStats = $this->getOptimizedUserStats($userIds, $filters);
    $qualityScores = $this->getQualityScores($userIds, $filters);

    // Fetch delegations for the given month
    $delegations = [];
    if (!empty($filters['date_from'])) {
        $reportMonth = date('n', strtotime($filters['date_from']));
        $reportYear = date('Y', strtotime($filters['date_from']));
        $delegations = $this->getDelegationsForUsers($userIds, $reportMonth, $reportYear);
    }

    foreach ($users as &$user) {
        $userId = $user['id'];

        // Merge stats with defaults
        $user = array_merge($user, $userStats[$userId] ?? [
            'normal_tickets' => 0, 'vip_tickets' => 0, 'incoming_calls' => 0, 'outgoing_calls' => 0,
            'total_points' => 0
        ]);

        $user['quality_score'] = $qualityScores[$userId]['quality_score'] ?? 0;
        $user['total_reviews'] = $qualityScores[$userId]['total_reviews'] ?? 0;

        // Apply delegation bonus if exists
        $user['delegation_applied'] = false;
        if (isset($delegations[$userId])) {
            $delegation = $delegations[$userId];
            $original_points = $user['total_points'];
            $bonus_percentage = $delegation['percentage'];
            $bonus_amount = ($original_points * $bonus_percentage) / 100;
            $new_total_points = $original_points + $bonus_amount;

            $user['total_points'] = $new_total_points;
            $user['delegation_applied'] = true;
            $user['delegation_details'] = [
                'original_points' => $original_points,
                'percentage' => $bonus_percentage,
                'bonus_amount' => $bonus_amount,
                'reason' => $delegation['reason']
            ];
        }
    }
    unset($user);

    // ✅ Calculate summary stats properly
    $summaryStats = [
        'normal_tickets' => array_sum(array_column($userStats, 'normal_tickets')),
        'vip_tickets' => array_sum(array_column($userStats, 'vip_tickets')),
        'incoming_calls' => array_sum(array_column($userStats, 'incoming_calls')),
        'outgoing_calls' => array_sum(array_column($userStats, 'outgoing_calls')),
        'total_points' => array_sum(array_column($users, 'total_points')),
        'total_reviews' => array_sum(array_column($qualityScores, 'total_reviews')),
        'avg_quality_score' => 0,
    ];

    // ✅ fix Avg Quality = weighted average
    $weightedSum = 0;
    $totalReviews = 0;
    foreach ($qualityScores as $row) {
        $weightedSum += ($row['quality_score'] * $row['total_reviews']);
        $totalReviews += $row['total_reviews'];
    }
    if ($totalReviews > 0) {
        $summaryStats['avg_quality_score'] = round($weightedSum / $totalReviews, 2);
    }

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
    
        // ✅ استثناء الأدوار باستخدام named parameters فقط
        $excludedRoles = ['developer', 'marketer', 'VIP'];
        $excludedPlaceholders = [];
        foreach ($excludedRoles as $idx => $roleName) {
            $paramName = ":excluded_role_$idx";
            $excludedPlaceholders[] = $paramName;
            $mainParams[$paramName] = $roleName;
        }
        $mainConditions[] = "r.name NOT IN (" . implode(',', $excludedPlaceholders) . ")";
    
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
    
    

    private function getDelegationsForUsers(array $userIds, int $month, int $year): array
    {
        if (empty($userIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $sql = "SELECT 
                    ud.user_id,
                    ud.reason,
                    dt.percentage
                FROM user_delegations ud
                JOIN delegation_types dt ON ud.delegation_type_id = dt.id
                WHERE ud.user_id IN ({$placeholders})
                  AND ud.applicable_month = ?
                  AND ud.applicable_year = ?";

        $params = array_merge($userIds, [$month, $year]);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $delegations = [];
        foreach ($results as $row) {
            $delegations[$row['user_id']] = $row;
        }

        return $delegations;
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

        // Try cache first
        $cacheKey = $this->getCacheKey('quality_scores', [$userIds, $filters]);
        $cached = $this->getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
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
                JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                WHERE td.edited_by IN ({$userIdsPlaceholders}) {$dateConditionsSql}

                UNION ALL

                SELECT
                    dc.call_by AS user_id,
                    r.rating,
                    r.reviewed_at
                FROM reviews r
                JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
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

        // Cache the results
        $this->setCache($cacheKey, $scores);

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

    public function getUsersReportWithPointsPaginated($filters)
    {
        $baseUsers = $this->getUsersReport($filters);
        $allUsers = $baseUsers['users'];
        $userIds = array_column($allUsers, 'id');

        if (empty($userIds)) {
            return [
                'users' => [],
                'summary_stats' => [
                    'normal_tickets' => 0, 'vip_tickets' => 0, 'incoming_calls' => 0, 'outgoing_calls' => 0,
                    'total_points' => 0, 'total_quality_score' => 0, 'total_reviews' => 0,
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $filters['per_page'],
                    'total' => 0,
                    'total_pages' => 0,
                    'has_next' => false,
                    'has_prev' => false
                ]
            ];
        }

        // Get paginated users
        $totalUsers = count($allUsers);
        $perPage = $filters['per_page'];
        $currentPage = $filters['page'];
        $offset = ($currentPage - 1) * $perPage;

        $paginatedUsers = array_slice($allUsers, $offset, $perPage);
        $paginatedUserIds = array_column($paginatedUsers, 'id');

        // Get data only for paginated users
        $userStats = $this->getOptimizedUserStats($paginatedUserIds, $filters);
        $qualityScores = $this->getQualityScores($paginatedUserIds, $filters);

        // Fetch delegations for the given month
        $delegations = [];
        if (!empty($filters['date_from'])) {
            $reportMonth = date('n', strtotime($filters['date_from']));
            $reportYear = date('Y', strtotime($filters['date_from']));
            $delegations = $this->getDelegationsForUsers($paginatedUserIds, $reportMonth, $reportYear);
        }

        foreach ($paginatedUsers as &$user) {
            $userId = $user['id'];

            // Merge stats with defaults
            $user = array_merge($user, $userStats[$userId] ?? [
                'normal_tickets' => 0, 'vip_tickets' => 0, 'incoming_calls' => 0, 'outgoing_calls' => 0,
                'total_points' => 0
            ]);

            $user['quality_score'] = $qualityScores[$userId]['quality_score'] ?? 0;
            $user['total_reviews'] = $qualityScores[$userId]['total_reviews'] ?? 0;

            // Apply delegation bonus if exists
            $user['delegation_applied'] = false;
            if (isset($delegations[$userId])) {
                $delegation = $delegations[$userId];
                $original_points = $user['total_points'];
                $bonus_percentage = $delegation['percentage'];
                $bonus_amount = ($original_points * $bonus_percentage) / 100;
                $new_total_points = $original_points + $bonus_amount;

                $user['total_points'] = $new_total_points;
                $user['delegation_applied'] = true;
                $user['delegation_details'] = [
                    'original_points' => $original_points,
                    'percentage' => $bonus_percentage,
                    'bonus_amount' => $bonus_amount,
                    'reason' => $delegation['reason']
                ];
            }
        }
        unset($user);

        // Get summary stats for ALL users (not just paginated)
        $allUserStats = $this->getOptimizedUserStats($userIds, $filters);
        $allQualityScores = $this->getQualityScores($userIds, $filters);

        $summaryStats = [
            'normal_tickets' => array_sum(array_column($allUserStats, 'normal_tickets')),
            'vip_tickets' => array_sum(array_column($allUserStats, 'vip_tickets')),
            'incoming_calls' => array_sum(array_column($allUserStats, 'incoming_calls')),
            'outgoing_calls' => array_sum(array_column($allUserStats, 'outgoing_calls')),
            'total_points' => array_sum(array_column($allUserStats, 'total_points')),
            'total_quality_score' => array_sum(array_column($allQualityScores, 'quality_score')),
            'total_reviews' => array_sum(array_column($allQualityScores, 'total_reviews')),
        ];

        $totalPages = ceil($totalUsers / $perPage);

        return [
            'users' => $paginatedUsers,
            'summary_stats' => $summaryStats,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalUsers,
                'total_pages' => $totalPages,
                'has_next' => $currentPage < $totalPages,
                'has_prev' => $currentPage > 1
            ]
        ];
    }
}
