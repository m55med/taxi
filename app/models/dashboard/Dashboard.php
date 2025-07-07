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

    public function getDashboardData($startDate, $endDate)
    {
        $data = [];

        // Add end date time to include the whole day
        $endDate = $endDate . ' 23:59:59';

        // User Stats
        $data['user_stats'] = $this->getUserStats();

        // Driver Stats
        $data['driver_stats'] = $this->getDriverStats();

        // Ticket Stats
        $data['ticket_stats'] = $this->getTicketStats($startDate, $endDate);
        
        // Call Stats
        $data['call_stats'] = $this->getCallStats($startDate, $endDate);

        // Coupon Stats
        $data['coupon_stats'] = $this->getCouponStats($startDate, $endDate);

        // Quick Info
        $data['quick_info'] = $this->getQuickInfo();

        // Rankings
        $data['rankings'] = $this->getRankings($startDate, $endDate);

        // Chart Data
        $data['chart_data'] = $this->getChartData($startDate, $endDate);

        // Add other stats calls here later

        return $data;
    }

    private function getUserStats()
    {
        $stats = [];
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['status_breakdown'] = $this->db->query("SELECT status, COUNT(*) as count FROM users GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['online'] = $this->db->query("SELECT COUNT(*) FROM users WHERE is_online = 1")->fetchColumn();
        return $stats;
    }

    private function getDriverStats()
    {
        $stats = [];
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
        $stats['app_status_breakdown'] = $this->db->query("SELECT app_status, COUNT(*) as count FROM drivers GROUP BY app_status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['main_system_status_breakdown'] = $this->db->query("SELECT main_system_status, COUNT(*) as count FROM drivers GROUP BY main_system_status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['on_hold'] = $this->db->query("SELECT COUNT(*) FROM drivers WHERE hold = 1")->fetchColumn();
        return $stats;
    }

    private function getTicketStats($startDate, $endDate)
    {
        $stats = [];
        $baseQuery = "FROM ticket_details WHERE created_at BETWEEN :startDate AND :endDate";

        $totalStmt = $this->db->prepare("SELECT COUNT(*) $baseQuery");
        $totalStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['total'] = $totalStmt->fetchColumn();

        $vipStmt = $this->db->prepare("SELECT is_vip, COUNT(*) as count $baseQuery GROUP BY is_vip");
        $vipStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['vip_breakdown'] = $vipStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        // Ensure keys exist, 0 for normal, 1 for VIP
        $stats['vip_breakdown'][0] = $stats['vip_breakdown'][0] ?? 0;
        $stats['vip_breakdown'][1] = $stats['vip_breakdown'][1] ?? 0;

        return $stats;
    }

    private function getCallStats($startDate, $endDate)
    {
        $stats = [];
        
        // Outgoing calls from driver_calls
        $outgoingStmt = $this->db->prepare("SELECT COUNT(*) FROM driver_calls WHERE created_at BETWEEN :startDate AND :endDate");
        $outgoingStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['outgoing'] = $outgoingStmt->fetchColumn();
        
        // Incoming calls from incoming_calls
        $incomingStmt = $this->db->prepare("SELECT COUNT(*) FROM incoming_calls WHERE call_started_at BETWEEN :startDate AND :endDate");
        $incomingStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['incoming'] = $incomingStmt->fetchColumn();
        
        $stats['total'] = $stats['outgoing'] + $stats['incoming'];
        
        return $stats;
    }

    private function getCouponStats($startDate, $endDate)
    {
        $stats = [];
        $baseQuery = "FROM coupons WHERE created_at BETWEEN :startDate AND :endDate";

        $usedStmt = $this->db->prepare("SELECT COUNT(*) $baseQuery AND is_used = 1");
        $usedStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['used'] = $usedStmt->fetchColumn();

        $unusedStmt = $this->db->prepare("SELECT COUNT(*) $baseQuery AND is_used = 0");
        $unusedStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $stats['unused'] = $unusedStmt->fetchColumn();

        return $stats;
    }

    private function getQuickInfo()
    {
        $info = [];
        $info['countries'] = $this->db->query("SELECT name FROM countries ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
        $info['car_types'] = $this->db->query("SELECT name FROM car_types ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
        $info['platforms'] = $this->db->query("SELECT name FROM platforms ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
        return $info;
    }

    private function getRankings($startDate, $endDate)
    {
        $rankings = [];
        $params = [':startDate' => $startDate, ':endDate' => $endDate, ':startDate2' => $startDate, ':endDate2' => $endDate];

        // Top Employees (by ticket edits + outgoing calls)
        $employeeQuery = "
            SELECT u.username, 
                   (SELECT COUNT(*) FROM ticket_details WHERE edited_by = u.id AND created_at BETWEEN :startDate AND :endDate) +
                   (SELECT COUNT(*) FROM driver_calls WHERE call_by = u.id AND created_at BETWEEN :startDate2 AND :endDate2) AS activity_score
            FROM users u
            WHERE u.role_id != 1
            GROUP BY u.id
            HAVING activity_score > 0
            ORDER BY activity_score DESC
            LIMIT 5
        ";
        $employeeStmt = $this->db->prepare($employeeQuery);
        $employeeStmt->execute([':startDate' => $startDate, ':endDate' => $endDate, ':startDate2' => $startDate, ':endDate2' => $endDate]);
        $rankings['top_employees'] = $employeeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Top Teams
        $teamQuery = "
            SELECT t.name, 
                   (SELECT COUNT(*) FROM ticket_details td JOIN team_members tm ON td.edited_by = tm.user_id WHERE tm.team_id = t.id AND td.created_at BETWEEN :startDate AND :endDate) +
                   (SELECT COUNT(*) FROM driver_calls dc JOIN team_members tm ON dc.call_by = tm.user_id WHERE tm.team_id = t.id AND dc.created_at BETWEEN :startDate2 AND :endDate2) AS team_score
            FROM teams t
            GROUP BY t.id
            HAVING team_score > 0
            ORDER BY team_score DESC
            LIMIT 5
        ";
        $teamStmt = $this->db->prepare($teamQuery);
        $teamStmt->execute([':startDate' => $startDate, ':endDate' => $endDate, ':startDate2' => $startDate, ':endDate2' => $endDate]);
        $rankings['top_teams'] = $teamStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Top Marketers (by referral visits)
        $marketerQuery = "
            SELECT u.username, COUNT(rv.id) AS visit_count
            FROM users u
            JOIN referral_visits rv ON u.id = rv.affiliate_user_id
            WHERE rv.visit_recorded_at BETWEEN :startDate AND :endDate
            GROUP BY u.id
            HAVING visit_count > 0
            ORDER BY visit_count DESC
            LIMIT 5
        ";
        $marketerStmt = $this->db->prepare($marketerQuery);
        $marketerStmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
        $rankings['top_marketers'] = $marketerStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $rankings;
    }

    private function getChartData($startDate, $endDate)
    {
        $charts = [];
        $params = [':startDate' => $startDate, ':endDate' => $endDate];

        // Driver Status Breakdown
        $charts['driver_status'] = $this->db->query("SELECT app_status, COUNT(*) as count FROM drivers GROUP BY app_status")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Ticket Type Breakdown
        $ticketQuery = "SELECT CASE WHEN is_vip = 1 THEN 'VIP' ELSE 'Normal' END as type, COUNT(*) as count FROM ticket_details WHERE created_at BETWEEN :startDate AND :endDate GROUP BY is_vip";
        $ticketStmt = $this->db->prepare($ticketQuery);
        $ticketStmt->execute($params);
        $charts['ticket_types'] = $ticketStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // User Status Breakdown
        $charts['user_status'] = $this->db->query("SELECT status, COUNT(*) as count FROM users GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

        return $charts;
    }

    // Previous chart data methods will be refactored and integrated later
    // For now, they are removed to simplify the model.
} 