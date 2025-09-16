<?php

namespace App\Models\Dashboard;

use App\Core\Database;
use PDO;

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDashboardData($user, $dateFrom = null, $dateTo = null)
    {
        $data = [];
        $role = $user['role_name'];
        $userId = $user['id'];
        $isPrivileged = in_array($role, ['admin', 'developer', 'quality_manager', 'Team_leader']);

        // Set default date range if not provided
        $dateTo = $dateTo && strtotime($dateTo) ? $dateTo : date('Y-m-d');
        $dateFrom = $dateFrom && strtotime($dateFrom) ? $dateFrom : date('Y-m-d', strtotime('-30 days', strtotime($dateTo)));
        
        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;
        
        $data['user_role'] = $role;

        if ($isPrivileged) {
            // Privileged users get all stats
            $data['driver_stats'] = ($role === 'admin') ? $this->getDriverStats() : $this->getDriverStats($dateFrom, $dateTo);
            $data['leaderboards'] = $this->getLeaderboards($dateFrom, $dateTo, null);
            $data['user_stats'] = $this->getUserStats(); // User stats are not time-dependent
            $data['ticket_stats'] = $this->getTicketStats(null, $dateFrom, $dateTo);
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats(null, $dateFrom, $dateTo);
            $data['call_stats'] = $this->getCallStats(null, $dateFrom, $dateTo);
            $data['call_ratio'] = $this->getCallRatio(null, $dateFrom, $dateTo);
            $data['daily_trends'] = $this->getDailyTrends($dateFrom, $dateTo);
            $data['top_reviews'] = $this->getTopReviews(null, 10);
            if (in_array($role, ['admin', 'developer', 'marketer'])) {
                $data['marketer_stats'] = $this->getMarketerStats(null, $dateFrom, $dateTo);
            }
        } else {
            // Non-privileged users get their own stats only
            $data['ticket_stats'] = $this->getTicketStats($userId, $dateFrom, $dateTo);
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats($userId, $dateFrom, $dateTo);
            $data['call_stats'] = $this->getCallStats($userId, $dateFrom, $dateTo);
            $data['call_ratio'] = $this->getCallRatio($userId, $dateFrom, $dateTo);
            $data['top_reviews'] = $this->getTopReviews($userId, 10);
            
            // Don't show leaderboards for non-privileged users
            $data['leaderboards'] = [
                'tickets' => [],
                'outgoing_calls' => [],
                'incoming_calls' => [],
                'reviews' => []
            ];
            
            if ($role === 'marketer') {
                $data['marketer_stats'] = $this->getMarketerStats($userId, $dateFrom, $dateTo);
            }
        }

        return $data;
    }

    private function getDailyTrends($dateFrom, $dateTo)
    {
        // Create a simple date range using PHP
        $dates = [];
        $currentDate = strtotime($dateFrom);
        $endDate = strtotime($dateTo);

        while ($currentDate <= $endDate) {
            $dates[] = date('Y-m-d', $currentDate);
            $currentDate = strtotime('+1 day', $currentDate);
        }

        $results = [];

        foreach ($dates as $date) {
            try {
                // Get tickets for this date
                $stmt = $this->db->prepare("SELECT COUNT(id) as count FROM ticket_details WHERE DATE(created_at) = ?");
                $stmt->execute([$date]);
                $ticketCount = (int) $stmt->fetchColumn();

                // Get calls for this date
                $stmt = $this->db->prepare("SELECT COUNT(id) as count FROM driver_calls WHERE DATE(created_at) = ?");
                $stmt->execute([$date]);
                $callCount = (int) $stmt->fetchColumn();

                // Get reviews for this date
                $stmt = $this->db->prepare("SELECT COUNT(id) as count FROM reviews WHERE DATE(reviewed_at) = ?");
                $stmt->execute([$date]);
                $reviewCount = (int) $stmt->fetchColumn();

                $results[] = [
                    'action_date' => $date,
                    'tickets' => $ticketCount,
                    'calls' => $callCount,
                    'reviews' => $reviewCount
                ];
            } catch (\PDOException $e) {
                error_log("Error getting data for date $date: " . $e->getMessage());
                // Add empty data for this date
                $results[] = [
                    'action_date' => $date,
                    'tickets' => 0,
                    'calls' => 0,
                    'reviews' => 0
                ];
            }
        }

        return $results;
    }

    private function getUserStats()
    {
        $query = "
        SELECT
                COUNT(id) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'banned' THEN 1 ELSE 0 END) AS banned,
                SUM(is_online) AS online
        FROM users
        ";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in getUserStats: " . $e->getMessage());
            $result = false;
        }

        return [
            'total'    => (int)($result['total'] ?? 0),
            'active'   => (int)($result['active'] ?? 0),
            'pending'  => (int)($result['pending'] ?? 0),
            'banned'   => (int)($result['banned'] ?? 0),
            'online'   => (int)($result['online'] ?? 0),
        ];
    }

    private function getDriverStats($dateFrom = null, $dateTo = null)
    {
        $defaults = [
            'total' => 0, 'active' => 0, 'inactive' => 0, 'missing_documents' => 0,
            'more_than_10_trips' => 0, 'less_than_10_trips' => 0
        ];
        
        $params = [];
        $whereClause = '';
        if ($dateFrom && $dateTo) {
            $whereClause = 'WHERE DATE(created_at) BETWEEN :date_from AND :date_to';
            $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        }

        $statsQuery = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN app_status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN app_status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(has_missing_documents) as missing_documents
            FROM drivers
            {$whereClause}
        ";
        $stmt = $this->db->prepare($statsQuery);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $tripWhereClause = '';
        if ($dateFrom && $dateTo) {
            $tripWhereClause = 'WHERE DATE(d.created_at) BETWEEN :date_from AND :date_to';
        }

        $tripQuery = "
            SELECT 
                SUM(CASE WHEN da.has_many_trips = 1 THEN 1 ELSE 0 END) as more_than_10_trips,
                SUM(CASE WHEN da.has_many_trips = 0 THEN 1 ELSE 0 END) as less_than_10_trips
            FROM driver_attributes da
            JOIN drivers d ON da.driver_id = d.id
            {$tripWhereClause}
        ";
        $stmt = $this->db->prepare($tripQuery);
        $stmt->execute($params);
        $tripCounts = $stmt->fetch(PDO::FETCH_ASSOC);

        $results = array_merge(
            is_array($stats) ? $stats : [],
            is_array($tripCounts) ? $tripCounts : []
        );

        return array_merge($defaults, $results);
    }

    private function getTicketStats($userId, $dateFrom, $dateTo)
    {
        $stats = ['total_tickets' => 0, 'total_details' => 0, 'vip_details' => 0, 'normal_details' => 0];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $userWhere = '';

        if ($userId) {
            $userWhere = ' AND created_by = :user_id';
            $params[':user_id'] = $userId;
        }

        $ticketQuery = "SELECT COUNT(*) FROM tickets WHERE DATE(created_at) BETWEEN :date_from AND :date_to" . $userWhere;
        $stmt = $this->db->prepare($ticketQuery);
        $stmt->execute($params);
        $stats['total_tickets'] = (int) $stmt->fetchColumn();

        if ($userId) {
            $userWhere = ' AND edited_by = :user_id';
        } else {
             unset($params[':user_id']);
        }

        $detailQuery = "SELECT COUNT(*) FROM ticket_details WHERE DATE(created_at) BETWEEN :date_from AND :date_to" . $userWhere;
        $stmt = $this->db->prepare($detailQuery);
        $stmt->execute($params);
        $stats['total_details'] = (int) $stmt->fetchColumn();
        
        $vipQuery = "SELECT is_vip, COUNT(*) as count FROM ticket_details WHERE DATE(created_at) BETWEEN :date_from AND :date_to" . $userWhere . " GROUP BY is_vip";
        $stmt = $this->db->prepare($vipQuery);
        $stmt->execute($params);
        $vipCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $vipData = is_array($vipCounts) ? $vipCounts : [];
        $stats['vip_details'] = $vipData[1] ?? 0;
        $stats['normal_details'] = $vipData[0] ?? 0;

        return $stats;
    }

    private function getReviewDiscussionStats($userId, $dateFrom, $dateTo)
    {
        $defaults = ['reviews' => '0%', 'discussions' => 0, 'individual_reviews' => []];
        $stats = [];
    
        // Get user role to determine filtering logic
        $role = $userId ? $this->getUserRole($userId) : 'admin'; // Default to admin if no userId
        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer', 'Quality'];
    
        // === REVIEWS COUNT ===
        $reviewsParams = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $whereClauses = [];
    
        if ($role === 'agent' && $userId) {
            $whereClauses[] = "(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)";
            $reviewsParams[':agent_review_user_id_ticket'] = $userId;
            $reviewsParams[':agent_review_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles) && $userId) {
            return $defaults;
        }
    
        $whereSql = count($whereClauses) > 0 ? " AND " . implode(' AND ', $whereClauses) : "";
    
        $reviewsQuery = "
            SELECT ROUND(AVG(r.rating)) as average_rating
            FROM reviews r
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
            WHERE DATE(r.reviewed_at) BETWEEN :date_from AND :date_to{$whereSql}
        ";
    
        try {
            $stmt_reviews = $this->db->prepare($reviewsQuery);
            $stmt_reviews->execute($reviewsParams);
            $avgRating = $stmt_reviews->fetchColumn();
            $stats['reviews'] = $avgRating ? $avgRating . '%' : '0%';
        } catch (\PDOException $e) {
            error_log("Error in getReviewDiscussionStats (reviews): " . $e->getMessage());
            $stats['reviews'] = '0%';
        }
    
        // === INDIVIDUAL REVIEWS ===
        if ($userId && $role === 'agent') {
            $individualReviewsQuery = "
                SELECT r.review_notes, CONCAT(ROUND(r.rating), '%') as rating
                FROM reviews r
                LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
                WHERE DATE(r.reviewed_at) BETWEEN :date_from AND :date_to
                AND (td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)
                ORDER BY r.reviewed_at DESC
                LIMIT 5
            ";
            try {
                $stmt_individual_reviews = $this->db->prepare($individualReviewsQuery);
                $stmt_individual_reviews->execute($reviewsParams);
                $stats['individual_reviews'] = $stmt_individual_reviews->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log("Error in getReviewDiscussionStats (individual_reviews): " . $e->getMessage());
                $stats['individual_reviews'] = [];
            }
        }
    
        // === DISCUSSIONS COUNT ===
        $discussionsParams = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $userWhereDiscussions = '';
    
        if ($userId && !in_array($role, $highAccessRoles)) {
            $userWhereDiscussions = " AND opened_by = :user_id";
            $discussionsParams[':user_id'] = $userId;
        }
    
        $discussionsQuery = "SELECT COUNT(*) FROM discussions WHERE DATE(created_at) BETWEEN :date_from AND :date_to{$userWhereDiscussions}";
        
        try {
            $stmt_discussions = $this->db->prepare($discussionsQuery);
            $stmt_discussions->execute($discussionsParams);
            $stats['discussions'] = (int) $stmt_discussions->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error in getReviewDiscussionStats (discussions): " . $e->getMessage());
            $stats['discussions'] = 0;
        }
    
        return array_merge($defaults, $stats);
    }
    
    private function getUserRole($userId)
    {
        if (!$userId) return 'guest';

        try {
            $stmt = $this->db->prepare("
                SELECT r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['role_name'] : 'guest';
        } catch (\PDOException $e) {
            error_log("Error getting user role: " . $e->getMessage());
            return 'guest';
        }
    }

    private function getMarketerStats($userId, $dateFrom, $dateTo)
    {
        $defaults = ['total_marketers' => 0, 'visits' => 0, 'registrations' => 0, 'top_countries' => []];
        $stats = [];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $userWhere = '';

        if ($userId) {
            $userWhere = " AND affiliate_user_id = :user_id";
            $params[':user_id'] = $userId;

            $visitsQuery = "SELECT COUNT(*) FROM referral_visits WHERE visit_date BETWEEN :date_from AND :date_to" . $userWhere;
            $stmt_visits = $this->db->prepare($visitsQuery);
            $stmt_visits->execute($params);
            $stats['visits'] = (int) $stmt_visits->fetchColumn();

            $regsQuery = "SELECT COUNT(*) FROM referral_visits WHERE registration_status = 'successful' AND visit_date BETWEEN :date_from AND :date_to" . $userWhere;
            $stmt_regs = $this->db->prepare($regsQuery);
            $stmt_regs->execute($params);
            $stats['registrations'] = (int) $stmt_regs->fetchColumn();
            
            $countriesQuery = "SELECT country, COUNT(*) as count FROM referral_visits WHERE country IS NOT NULL AND visit_date BETWEEN :date_from AND :date_to" . $userWhere . " GROUP BY country ORDER BY count DESC LIMIT 3";
            $stmt_countries = $this->db->prepare($countriesQuery);
            $stmt_countries->execute($params);
            $stats['top_countries'] = $stmt_countries->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } else {
            $stats['total_marketers'] = (int) $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'marketer'")->fetchColumn();
            
            $visitsQuery = "SELECT COUNT(*) FROM referral_visits WHERE visit_date BETWEEN :date_from AND :date_to";
            $stmt_visits = $this->db->prepare($visitsQuery);
            $stmt_visits->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $stats['visits'] = (int) $stmt_visits->fetchColumn();

            $regsQuery = "SELECT COUNT(*) FROM referral_visits WHERE registration_status = 'successful' AND visit_date BETWEEN :date_from AND :date_to";
            $stmt_regs = $this->db->prepare($regsQuery);
            $stmt_regs->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $stats['registrations'] = (int) $stmt_regs->fetchColumn();

            $countriesQuery = "SELECT country, COUNT(*) as count FROM referral_visits WHERE country IS NOT NULL AND visit_date BETWEEN :date_from AND :date_to GROUP BY country ORDER BY count DESC LIMIT 3";
            $stmt_countries = $this->db->prepare($countriesQuery);
            $stmt_countries->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $stats['top_countries'] = $stmt_countries->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        }
        
        return array_merge($defaults, $stats);
    }

    private function getCallStats($userId, $dateFrom, $dateTo)
    {
        $stats = ['incoming' => 0, 'outgoing' => 0];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $userWhereIncoming = '';
        $userWhereOutgoing = '';

        if ($userId) {
            $userWhereIncoming = ' AND call_received_by = :user_id';
            $userWhereOutgoing = ' AND call_by = :user_id';
            $params[':user_id'] = $userId;
        }

        $incomingQuery = "SELECT COUNT(*) FROM incoming_calls WHERE DATE(call_started_at) BETWEEN :date_from AND :date_to" . $userWhereIncoming;
        $stmt_incoming = $this->db->prepare($incomingQuery);
        $stmt_incoming->execute($params);
        $stats['incoming'] = (int) $stmt_incoming->fetchColumn();
        
        if (!$userId) {
            unset($params[':user_id']);
        }

        $outgoingQuery = "SELECT COUNT(*) FROM driver_calls WHERE DATE(created_at) BETWEEN :date_from AND :date_to" . $userWhereOutgoing;
        $stmt_outgoing = $this->db->prepare($outgoingQuery);
        $stmt_outgoing->execute($params);
        $stats['outgoing'] = (int) $stmt_outgoing->fetchColumn();
    
        return $stats;
    }
    
    private function getCallRatio($userId, $dateFrom, $dateTo)
    {
        $callStats = $this->getCallStats($userId, $dateFrom, $dateTo);
        $totalCalls = ($callStats['incoming'] ?? 0) + ($callStats['outgoing'] ?? 0);

        if ($totalCalls === 0) {
            return ['incoming' => 0, 'outgoing' => 0];
        }

        return [
            'incoming' => round(($callStats['incoming'] / $totalCalls) * 100, 2),
            'outgoing' => round(($callStats['outgoing'] / $totalCalls) * 100, 2),
        ];
    }

    private function getLeaderboards($dateFrom, $dateTo, $userId = null)
    {
        $leaderboards = [
            'tickets' => [],
            'outgoing_calls' => [],
            'incoming_calls' => [],
            'reviews' => [],
        ];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        $ticketsQuery = "
            SELECT u.name, COUNT(td.id) as count 
            FROM ticket_details td 
            JOIN users u ON td.edited_by = u.id 
            WHERE DATE(td.created_at) BETWEEN :date_from AND :date_to
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ";
        $stmt = $this->db->prepare($ticketsQuery);
        $stmt->execute($params);
        $leaderboards['tickets'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $outgoingQuery = "
            SELECT u.name, COUNT(dc.id) as count 
            FROM driver_calls dc 
            JOIN users u ON dc.call_by = u.id 
            WHERE DATE(dc.created_at) BETWEEN :date_from AND :date_to
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ";
        $stmt = $this->db->prepare($outgoingQuery);
        $stmt->execute($params);
        $leaderboards['outgoing_calls'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $incomingQuery = "
            SELECT u.name, COUNT(ic.id) as count 
            FROM incoming_calls ic 
            JOIN users u ON ic.call_received_by = u.id 
            WHERE DATE(ic.call_started_at) BETWEEN :date_from AND :date_to
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ";
        $stmt = $this->db->prepare($incomingQuery);
        $stmt->execute($params);
        $leaderboards['incoming_calls'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Reviews leaderboard with same filtering logic
        $role = $userId ? $this->getUserRole($userId) : 'admin'; // Default to admin if no userId
        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer', 'Quality'];

        if ($role === 'agent' && $userId) {
            // For agents, only show reviews for actions they performed
            $reviewsWhereClauses = ["(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)"];
            $reviewsParams = array_merge($params, [
                ':agent_review_user_id_ticket' => $userId,
                ':agent_review_user_id_call' => $userId
            ]);
        } elseif (!in_array($role, $highAccessRoles) && $userId) {
            // For other non-admin roles, return empty leaderboard
            $leaderboards['reviews'] = [];
            return $leaderboards;
        } else {
            // For admin/privileged users, show all reviews
            $reviewsWhereClauses = [];
            $reviewsParams = $params;
        }

        $reviewsWhereSql = count($reviewsWhereClauses) > 0 ? " AND " . implode(' AND ', $reviewsWhereClauses) : "";

        // Reviews leaderboard - أفضل الموظفين من حيث متوسط التقييمات المستلمة
        $reviewsQuery = "
            SELECT
                u.id as user_id,
                u.name,
                COUNT(r.id) as total_reviews,
                ROUND(AVG(r.rating), 1) as average_rating
            FROM reviews r
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
            JOIN users u ON (
                CASE
                    WHEN r.reviewable_type LIKE '%TicketDetail' THEN td.edited_by
                    WHEN r.reviewable_type LIKE '%DriverCall' THEN dc.call_by
                    ELSE NULL
                END
            ) = u.id
            WHERE DATE(r.reviewed_at) BETWEEN :date_from AND :date_to{$reviewsWhereSql}
            GROUP BY u.id, u.name
            ORDER BY average_rating DESC, total_reviews DESC
            LIMIT 10
        ";
        
        try {
            $stmt = $this->db->prepare($reviewsQuery);
            $stmt->execute($reviewsParams);
            $leaderboards['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Error in getLeaderboards (reviews): " . $e->getMessage());
            $leaderboards['reviews'] = [];
        }

        return $leaderboards;
    }

    private function getTopReviews($userId = null, $limit = 10)
    {
        $role = $userId ? $this->getUserRole($userId) : 'admin'; // Default to admin if no userId
        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer', 'Quality'];

        // Use the same filtering logic as QualityModel
        $whereClauses = [];
        $params = [];

        if ($role === 'agent' && $userId) {
            $whereClauses[] = "(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)";
            $params[':agent_review_user_id_ticket'] = $userId;
            $params[':agent_review_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles) && $userId) {
            // For other non-admin roles, return empty array
            return [];
        }

        // Build the WHERE clause
        $whereSql = count($whereClauses) > 0 ? " WHERE " . implode(' AND ', $whereClauses) : "";


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        $query = "
            SELECT r.rating, r.reviewed_at, u.name as reviewer_name
            FROM reviews r
            JOIN users u ON r.reviewed_by = u.id
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
            {$whereSql}
            ORDER BY r.reviewed_at DESC
            LIMIT :limit
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (\PDOException $e) {
            error_log("Error in getTopReviews: " . $e->getMessage());
            return [];
        }
    }
}
