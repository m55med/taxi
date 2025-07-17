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

    public function getDashboardData($user)
    {
        $data = [];
        $role = $user['role_name'];
        $userId = $user['id'];

        $isPrivileged = in_array($role, ['admin', 'developer', 'quality_manager', 'Team_leader']);

        // Stats visible to all or based on role
        $data['driver_stats'] = $this->getDriverStats();
        $data['leaderboards'] = $this->getLeaderboards();

        if ($isPrivileged) {
            $data['user_stats'] = $this->getUserStats();
            $data['ticket_stats'] = $this->getTicketStats();
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats();
            $data['call_stats'] = $this->getCallStats();
            $data['call_ratio'] = $this->getCallRatio();
            $data['daily_trends'] = $this->getDailyTrends(); // Add trends for privileged users
        } else {
            $data['ticket_stats'] = $this->getTicketStats($userId);
            $data['review_discussion_stats'] = $this->getReviewDiscussionStats($userId);
            $data['call_stats'] = $this->getCallStats($userId);
            $data['call_ratio'] = $this->getCallRatio($userId);
        }

        if (in_array($role, ['admin', 'developer', 'marketer'])) {
            $data['marketer_stats'] = ($role === 'marketer') ? $this->getMarketerStats($userId) : $this->getMarketerStats();
        }

        $data['user_role'] = $role; // Pass role to view for conditional rendering

        return $data;
    }

    private function getDailyTrends()
    {
        $days = 15;
        $date_start = date('Y-m-d', strtotime("-{$days} days"));

        $sql = "
        WITH all_dates AS (
            -- This part generates a list of dates for the last 15 days
            SELECT a.Date as a_date
            FROM (
                SELECT CURDATE() - INTERVAL (a.a + (10 * b.a)) DAY as Date
                FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
            ) a
            WHERE a.Date BETWEEN :date_start AND CURDATE()
        ),
        tickets AS (
            SELECT DATE(created_at) as t_date, COUNT(id) as ticket_count
            FROM ticket_details
            WHERE DATE(created_at) BETWEEN :date_start AND CURDATE()
            GROUP BY t_date
        ),
        calls AS (
             SELECT DATE(created_at) as c_date, COUNT(id) as call_count
             FROM driver_calls
             WHERE DATE(created_at) BETWEEN :date_start AND CURDATE()
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

        return $this->db->query($sql, [':date_start' => $date_start])->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserStats()
    {
        $stats = $this->db->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'banned' THEN 1 ELSE 0 END) as banned
        FROM users
    ")->fetch(PDO::FETCH_ASSOC);

        // ✅ تحقق إذا لم تكن مصفوفة، عيّن مصفوفة فاضية
        $stats = is_array($stats) ? $stats : [];

        // أضف إحصائية الأونلاين
        $stats['online'] = $this->db->query("SELECT COUNT(*) FROM users WHERE is_online = 1")->fetchColumn();

        return $stats;
    }


    private function getDriverStats()
    {
        $stats = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN app_status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN app_status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(has_missing_documents) as missing_documents
            FROM drivers
        ")->fetch(PDO::FETCH_ASSOC);

        $tripCounts = $this->db->query("
            SELECT 
                SUM(CASE WHEN has_many_trips = 1 THEN 1 ELSE 0 END) as more_than_10_trips,
                SUM(CASE WHEN has_many_trips = 0 THEN 1 ELSE 0 END) as less_than_10_trips
            FROM driver_attributes
        ")->fetch(PDO::FETCH_ASSOC);

        // Ensure that the variables are arrays before merging to prevent errors
        $stats = is_array($stats) ? $stats : [];
        $tripCounts = is_array($tripCounts) ? $tripCounts : [];

        return array_merge($stats, $tripCounts);
    }

    private function getTicketStats($userId = null)
    {
        $baseQueryTickets = "SELECT COUNT(*) FROM tickets";
        $baseQueryDetails = "SELECT COUNT(*) FROM ticket_details";
        $baseQueryVip = "SELECT is_vip, COUNT(*) as count FROM ticket_details";

        if ($userId) {
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d 23:59:59');

            $stats['total_tickets'] = $this->db->query($baseQueryTickets . " WHERE created_by = ? AND created_at BETWEEN ? AND ?", [$userId, $today_start, $today_end])->fetchColumn() ?: 0;
            $stats['total_details'] = $this->db->query($baseQueryDetails . " WHERE edited_by = ? AND created_at BETWEEN ? AND ?", [$userId, $today_start, $today_end])->fetchColumn() ?: 0;
            $vipCounts = $this->db->query($baseQueryVip . " WHERE edited_by = ? AND created_at BETWEEN ? AND ? GROUP BY is_vip", [$userId, $today_start, $today_end])->fetchAll(PDO::FETCH_KEY_PAIR);
        } else {
            $stats['total_tickets'] = $this->db->query($baseQueryTickets)->fetchColumn() ?: 0;
            $stats['total_details'] = $this->db->query($baseQueryDetails)->fetchColumn() ?: 0;
            $vipCounts = $this->db->query($baseQueryVip . " GROUP BY is_vip")->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        $stats['vip_details'] = $vipCounts[1] ?? 0;
        $stats['normal_details'] = $vipCounts[0] ?? 0;

        return $stats;
    }

    private function getReviewDiscussionStats($userId = null)
    {
        if ($userId) {
            $stats['reviews'] = $this->db->query("SELECT COUNT(r.id) FROM reviews r JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type = 'TicketDetail' WHERE td.edited_by = ?", [$userId])->fetchColumn();
            $stats['discussions'] = $this->db->query("SELECT COUNT(*) FROM discussions WHERE opened_by = ?", [$userId])->fetchColumn();
        } else {
            $stats['reviews'] = $this->db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
            $stats['discussions'] = $this->db->query("SELECT COUNT(*) FROM discussions")->fetchColumn();
        }
        return $stats;
    }

    private function getMarketerStats($userId = null)
    {
        if ($userId) {
            $stats['visits'] = $this->db->query("SELECT COUNT(*) FROM referral_visits WHERE affiliate_user_id = ?", [$userId])->fetchColumn();
            $stats['registrations'] = $this->db->query("SELECT COUNT(*) FROM referral_visits WHERE affiliate_user_id = ? AND registration_status = 'successful'", [$userId])->fetchColumn();
            $stats['top_countries'] = $this->db->query("SELECT country, COUNT(*) as count FROM referral_visits WHERE affiliate_user_id = ? AND country IS NOT NULL GROUP BY country ORDER BY count DESC LIMIT 3", [$userId])->fetchAll(PDO::FETCH_KEY_PAIR);
        } else {
            $stats['total_marketers'] = $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'marketer'")->fetchColumn();
            $stats['visits'] = $this->db->query("SELECT COUNT(*) FROM referral_visits")->fetchColumn();
            $stats['registrations'] = $this->db->query("SELECT COUNT(*) FROM referral_visits WHERE registration_status = 'successful'")->fetchColumn();
            $stats['top_countries'] = $this->db->query("SELECT country, COUNT(*) as count FROM referral_visits WHERE country IS NOT NULL GROUP BY country ORDER BY count DESC LIMIT 3")->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        return $stats;
    }

    private function getCallStats($userId = null)
    {
        if ($userId) {
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d 23:59:59');
    
            $stats['incoming'] = $this->db->query(
                "SELECT COUNT(*) FROM incoming_calls WHERE call_received_by = ? AND call_started_at BETWEEN ? AND ?",
                [$userId, $today_start, $today_end]
            )->fetchColumn() ?: 0;
    
            $stats['outgoing'] = $this->db->query(
                "SELECT COUNT(*) FROM driver_calls WHERE call_by = ? AND created_at BETWEEN ? AND ?",
                [$userId, $today_start, $today_end]
            )->fetchColumn() ?: 0;
        } else {
            $stats['incoming'] = $this->db->query("SELECT COUNT(*) FROM incoming_calls")->fetchColumn() ?: 0;
            $stats['outgoing'] = $this->db->query("SELECT COUNT(*) FROM driver_calls")->fetchColumn() ?: 0;
        }
    
        return $stats;
    }
    

    private function getCallRatio($userId = null)
    {
        $stats = $this->getCallStats($userId);
        $total = $stats['incoming'] + $stats['outgoing'];
        if ($total === 0) {
            return ['incoming' => 0, 'outgoing' => 0];
        }
        return [
            'incoming' => round(($stats['incoming'] / $total) * 100, 2),
            'outgoing' => round(($stats['outgoing'] / $total) * 100, 2),
        ];
    }

    private function getLeaderboards()
    {
        $leaderboards['tickets'] = $this->db->query("
            SELECT u.name, COUNT(td.id) as count 
            FROM ticket_details td 
            JOIN users u ON td.edited_by = u.id 
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        $leaderboards['outgoing_calls'] = $this->db->query("
            SELECT u.name, COUNT(dc.id) as count 
            FROM driver_calls dc 
            JOIN users u ON dc.call_by = u.id 
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        $leaderboards['incoming_calls'] = $this->db->query("
            SELECT u.name, COUNT(ic.id) as count 
            FROM incoming_calls ic 
            JOIN users u ON ic.call_received_by = u.id 
            GROUP BY u.id, u.name 
            ORDER BY count DESC 
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        return $leaderboards;
    }
}