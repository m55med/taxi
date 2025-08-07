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
            $data['leaderboards'] = $this->getLeaderboards($dateFrom, $dateTo);
            $data['user_stats'] = $this->getUserStats(); // User stats are not time-dependent
            $data['ticket_stats'] = $this->getTicketStats(null, $dateFrom, $dateTo);
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats(null, $dateFrom, $dateTo);
            $data['call_stats'] = $this->getCallStats(null, $dateFrom, $dateTo);
            $data['call_ratio'] = $this->getCallRatio(null, $dateFrom, $dateTo);
            $data['daily_trends'] = $this->getDailyTrends($dateFrom, $dateTo);
            if (in_array($role, ['admin', 'developer', 'marketer'])) {
                $data['marketer_stats'] = $this->getMarketerStats(null, $dateFrom, $dateTo);
            }
        } else {
            // Non-privileged users get their own stats
            $data['ticket_stats'] = $this->getTicketStats($userId, $dateFrom, $dateTo);
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats($userId, $dateFrom, $dateTo);
            $data['call_stats'] = $this->getCallStats($userId, $dateFrom, $dateTo);
            $data['call_ratio'] = $this->getCallRatio($userId, $dateFrom, $dateTo);
            if ($role === 'marketer') {
                $data['marketer_stats'] = $this->getMarketerStats($userId, $dateFrom, $dateTo);
            }
        }

        return $data;
    }

    private function getDailyTrends($dateFrom, $dateTo)
    {
        $sql = "
        WITH RECURSIVE all_dates(a_date) AS (
            SELECT :date_from
            UNION ALL
            SELECT a_date + INTERVAL 1 DAY
            FROM all_dates
            WHERE a_date < :date_to
        ),
        tickets AS (
            SELECT DATE(created_at) as t_date, COUNT(id) as ticket_count
            FROM ticket_details
            WHERE DATE(created_at) BETWEEN :date_from_tickets AND :date_to_tickets
            GROUP BY t_date
        ),
        calls AS (
             SELECT DATE(created_at) as c_date, COUNT(id) as call_count
             FROM driver_calls
             WHERE DATE(created_at) BETWEEN :date_from_calls AND :date_to_calls
             GROUP BY c_date
        )
        SELECT 
            ad.a_date as action_date,
            COALESCE(t.ticket_count, 0) as tickets,
            COALESCE(c.call_count, 0) as calls
        FROM all_dates ad
        LEFT JOIN tickets t ON ad.a_date = t.t_date
        LEFT JOIN calls c ON ad.a_date = c.c_date
        ORDER BY ad.a_date ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $params = [
                ':date_from' => $dateFrom,
                ':date_to' => $dateTo,
                ':date_from_tickets' => $dateFrom,
                ':date_to_tickets' => $dateTo,
                ':date_from_calls' => $dateFrom,
                ':date_to_calls' => $dateTo,
            ];
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in getDailyTrends: " . $e->getMessage());
            return [];
        }
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
        $defaults = ['reviews' => 0, 'discussions' => 0];
        $stats = [];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];
        $userWhereReviews = '';
        $userWhereDiscussions = '';

        if ($userId) {
            $userWhereReviews = " AND td.edited_by = :user_id";
            $userWhereDiscussions = " AND opened_by = :user_id";
            $params[':user_id'] = $userId;
        }

        $reviewsQuery = "SELECT COUNT(r.id) FROM reviews r JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type = 'TicketDetail' WHERE DATE(r.reviewed_at) BETWEEN :date_from AND :date_to" . $userWhereReviews;
        $stmt_reviews = $this->db->prepare($reviewsQuery);
        $stmt_reviews->execute($params);
        $stats['reviews'] = (int) $stmt_reviews->fetchColumn();

        if (!$userId) {
            unset($params[':user_id']);
        }

        $discussionsQuery = "SELECT COUNT(*) FROM discussions WHERE DATE(created_at) BETWEEN :date_from AND :date_to" . $userWhereDiscussions;
        $stmt_discussions = $this->db->prepare($discussionsQuery);
        $stmt_discussions->execute($params);
        $stats['discussions'] = (int) $stmt_discussions->fetchColumn();
        
        return array_merge($defaults, $stats);
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

    private function getLeaderboards($dateFrom, $dateTo)
    {
        $leaderboards = [
            'tickets' => [],
            'outgoing_calls' => [],
            'incoming_calls' => [],
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

        return $leaderboards;
    }
}
