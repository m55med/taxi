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
                    d.name as driver_name
                FROM referral_visits rv
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id
                WHERE rv.affiliate_user_id = :user_id";

        $params = [':user_id' => $user_id];
        $where = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(rv.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
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
        
        $query_referers = "SELECT referer_url, COUNT(v.id) as count $base_query $where_sql AND referer_url IS NOT NULL AND referer_url != '' GROUP BY referer_url $limit_clause";
        $stmt_referers = $this->db->prepare($query_referers);
        $stmt_referers->execute($params);
        $stats['top_referers'] = $stmt_referers->fetchAll(PDO::FETCH_ASSOC);
        
        $query_countries = "SELECT country, COUNT(v.id) as count $base_query $where_sql AND country IS NOT NULL AND country != '' GROUP BY country $limit_clause";
        $stmt_countries = $this->db->prepare($query_countries);
        $stmt_countries->execute($params);
        $stats['top_countries'] = $stmt_countries->fetchAll(PDO::FETCH_ASSOC);

        $query_devices = "SELECT device_type, COUNT(v.id) as count $base_query $where_sql AND device_type IS NOT NULL AND device_type != 'Unknown' GROUP BY device_type $limit_clause";
        $stmt_devices = $this->db->prepare($query_devices);
        $stmt_devices->execute($params);
        $stats['top_device_types'] = $stmt_devices->fetchAll(PDO::FETCH_ASSOC);

        $query_browsers = "SELECT browser_name, COUNT(v.id) as count $base_query $where_sql AND browser_name IS NOT NULL AND browser_name != 'Unknown' GROUP BY browser_name $limit_clause";
        $stmt_browsers = $this->db->prepare($query_browsers);
        $stmt_browsers->execute($params);
        $stats['top_browsers'] = $stmt_browsers->fetchAll(PDO::FETCH_ASSOC);

        $query_os = "SELECT operating_system, COUNT(v.id) as count $base_query $where_sql AND operating_system IS NOT NULL AND operating_system != 'Unknown' GROUP BY operating_system $limit_clause";
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