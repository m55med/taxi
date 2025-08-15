<?php

namespace App\Models\Referral;

use App\Core\Database;
use PDO;

class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllVisits($filters = [])
    {
        $sql = "SELECT 
                    rv.*, 
                    u.username as affiliate_name,
                    d.name as driver_name
                FROM referral_visits rv
                LEFT JOIN users u ON rv.affiliate_user_id = u.id
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id";
        
        $where = [];
        $params = [];

        if (!empty($filters['marketer_id'])) {
            $where[] = "rv.affiliate_user_id = :marketer_id";
            $params[':marketer_id'] = $filters['marketer_id'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY rv.visit_recorded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisitsForMarketer($user_id, $filters = [])
    {
        $sql = "SELECT 
                    rv.*,
                    u.username as affiliate_name,
                    d.name as driver_name
                FROM referral_visits rv
                LEFT JOIN users u ON rv.affiliate_user_id = u.id
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id";

        $params = [];
        $where = [];

        // This handles marketer's own dashboard, and admin viewing a specific marketer's details page
        if (!empty($user_id)) {
            $where[] = "rv.affiliate_user_id = :user_id";
            $params[':user_id'] = $user_id;
        } 
        // This handles the admin's filter on the main dashboard
        elseif (!empty($filters['marketer_id'])) {
            $where[] = "rv.affiliate_user_id = :marketer_id";
            $params[':marketer_id'] = $filters['marketer_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY rv.visit_recorded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDashboardStats($filters = []) {
        $base_query = "FROM referral_visits v LEFT JOIN users u ON v.affiliate_user_id = u.id";
        $where_clauses = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where_clauses[] = "DATE(v.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where_clauses[] = "DATE(v.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['marketer_id'])) {
            $where_clauses[] = "v.affiliate_user_id = :marketer_id";
            $params[':marketer_id'] = $filters['marketer_id'];
        }

        $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

        // --- Main Stats ---
        $query = "SELECT
                    COUNT(v.id) as total_visits,
                    SUM(CASE WHEN v.registration_status = 'successful' THEN 1 ELSE 0 END) as total_registrations
                  $base_query $where_sql";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stats || $stats['total_visits'] === null) {
            return [
                'total_visits' => 0,
                'total_registrations' => 0,
                'conversion_rate' => 0,
                'top_referers' => [],
                'top_countries' => [],
                'top_device_types' => [],
                'top_browsers' => [],
                'top_os' => []
            ];
        }
        
        $stats['total_registrations'] = $stats['total_registrations'] ?? 0;
        $stats['conversion_rate'] = ($stats['total_visits'] > 0) ? ($stats['total_registrations'] / $stats['total_visits']) * 100 : 0;

        $limit_clause = "ORDER BY count DESC LIMIT 5";
        
        // --- Top Referers ---
        $where_referers = $where_clauses;
        $where_referers[] = "v.referer_url IS NOT NULL";
        $where_referers[] = "v.referer_url != ''";
        $where_sql_referers = count($where_referers) > 0 ? "WHERE " . implode(' AND ', $where_referers) : "";
        $query_referers = "SELECT v.referer_url, COUNT(v.id) as count $base_query $where_sql_referers GROUP BY v.referer_url $limit_clause";
        $stmt_referers = $this->db->prepare($query_referers);
        $stmt_referers->execute($params);
        $stats['top_referers'] = $stmt_referers->fetchAll(PDO::FETCH_ASSOC);

        // --- Top Countries ---
        $where_countries = $where_clauses;
        $where_countries[] = "v.country IS NOT NULL";
        $where_countries[] = "v.country != ''";
        $where_sql_countries = count($where_countries) > 0 ? "WHERE " . implode(' AND ', $where_countries) : "";
        $query_countries = "SELECT v.country, COUNT(v.id) as count $base_query $where_sql_countries GROUP BY v.country $limit_clause";
        $stmt_countries = $this->db->prepare($query_countries);
        $stmt_countries->execute($params);
        $stats['top_countries'] = $stmt_countries->fetchAll(PDO::FETCH_ASSOC);

        // --- Top Devices ---
        $where_devices = $where_clauses;
        $where_devices[] = "v.device_type IS NOT NULL";
        $where_devices[] = "v.device_type != 'Unknown'";
        $where_sql_devices = count($where_devices) > 0 ? "WHERE " . implode(' AND ', $where_devices) : "";
        $query_devices = "SELECT v.device_type, COUNT(v.id) as count $base_query $where_sql_devices GROUP BY v.device_type $limit_clause";
        $stmt_devices = $this->db->prepare($query_devices);
        $stmt_devices->execute($params);
        $stats['top_device_types'] = $stmt_devices->fetchAll(PDO::FETCH_ASSOC);

        // --- Top Browsers ---
        $where_browsers = $where_clauses;
        $where_browsers[] = "v.browser_name IS NOT NULL";
        $where_browsers[] = "v.browser_name != 'Unknown'";
        $where_sql_browsers = count($where_browsers) > 0 ? "WHERE " . implode(' AND ', $where_browsers) : "";
        $query_browsers = "SELECT v.browser_name, COUNT(v.id) as count $base_query $where_sql_browsers GROUP BY v.browser_name $limit_clause";
        $stmt_browsers = $this->db->prepare($query_browsers);
        $stmt_browsers->execute($params);
        $stats['top_browsers'] = $stmt_browsers->fetchAll(PDO::FETCH_ASSOC);

        // --- Top OS ---
        $where_os = $where_clauses;
        $where_os[] = "v.operating_system IS NOT NULL";
        $where_os[] = "v.operating_system != 'Unknown'";
        $where_sql_os = count($where_os) > 0 ? "WHERE " . implode(' AND ', $where_os) : "";
        $query_os = "SELECT v.operating_system, COUNT(v.id) as count $base_query $where_sql_os GROUP BY v.operating_system $limit_clause";
        $stmt_os = $this->db->prepare($query_os);
        $stmt_os->execute($params);
        $stats['top_os'] = $stmt_os->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
    
    public function getMarketers()
    {
        $sql = "SELECT id, username FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer') ORDER BY username";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 