<?php

namespace App\Models\Reports\AdminDashboard;

use App\Core\Database;
use PDO;

class AdminDashboardReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getSystemOverview()
    {
        $data = [];

        // Users Overview
        $stmt = $this->db->query("SELECT COUNT(*) as total_users, SUM(is_online) as online_users FROM users");
        $user_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['total_users'] = $user_stats['total_users'] ?? 0;
        $data['online_users'] = $user_stats['online_users'] ?? 0;

        // Drivers Overview
        $stmt = $this->db->query("SELECT COUNT(*) as total_drivers, 
                                        SUM(CASE WHEN app_status = 'active' THEN 1 ELSE 0 END) as active_drivers 
                                 FROM drivers");
        $driver_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['total_drivers'] = $driver_stats['total_drivers'] ?? 0;
        $data['active_drivers'] = $driver_stats['active_drivers'] ?? 0;

        // Calls Overview
        $stmt = $this->db->query("SELECT COUNT(*) as total_calls FROM driver_calls");
        $data['total_calls'] = $stmt->fetchColumn() ?? 0;

        // Tickets Overview
        $stmt = $this->db->query("SELECT COUNT(*) as total_tickets FROM tickets");
        $data['total_tickets'] = $stmt->fetchColumn() ?? 0;
        
        return $data;
    }
} 