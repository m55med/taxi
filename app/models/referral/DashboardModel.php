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
            $where[] = "(rv.affiliate_user_id = :user_id OR d.added_by = :user_id_driver)";
            $params[':user_id'] = $user_id;
            $params[':user_id_driver'] = $user_id;
        }
        // This handles the admin's filter on the main dashboard
        elseif (!empty($filters['marketer_id'])) {
            $where[] = "(rv.affiliate_user_id = :marketer_id_1 OR d.added_by = :marketer_id_2)";
            $params[':marketer_id_1'] = $filters['marketer_id'];
            $params[':marketer_id_2'] = $filters['marketer_id'];
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
        $base_query = "FROM referral_visits v LEFT JOIN users u ON v.affiliate_user_id = u.id LEFT JOIN drivers d ON v.registered_driver_id = d.id";
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
            $where_clauses[] = "(v.affiliate_user_id = :marketer_id_1 OR d.added_by = :marketer_id_2)";
            $params[':marketer_id_1'] = $filters['marketer_id'];
            $params[':marketer_id_2'] = $filters['marketer_id'];
        }

        $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

        // --- Main Stats ---
        $query = "SELECT
                    COUNT(v.id) as total_visits,
                    SUM(CASE WHEN v.registration_status = 'successful' THEN 1 ELSE 0 END) as total_registrations,
                    SUM(CASE WHEN v.registered_driver_id IS NOT NULL THEN 1 ELSE 0 END) as total_driver_visits,
                    SUM(CASE WHEN v.registration_status = 'successful' AND v.registered_driver_id IS NOT NULL THEN 1 ELSE 0 END) as total_driver_registrations
                  $base_query $where_sql";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stats || $stats['total_visits'] === null) {
            return [
                'total_visits' => 0,
                'total_registrations' => 0,
                'total_driver_visits' => 0,
                'total_driver_registrations' => 0,
                'conversion_rate' => 0,
                'top_referers' => [],
                'top_countries' => [],
                'top_device_types' => [],
                'top_browsers' => [],
                'top_os' => []
            ];
        }
        
        $stats['total_registrations'] = $stats['total_registrations'] ?? 0;
        $stats['total_driver_visits'] = $stats['total_driver_visits'] ?? 0;
        $stats['total_driver_registrations'] = $stats['total_driver_registrations'] ?? 0;
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

        if (!empty($filters['marketer_id'])) {
            $driver_stats = $this->getDriverStatsForMarketer($filters['marketer_id']);
            $stats['total_driver_visits'] = $driver_stats['total_driver_visits'];
            $stats['total_driver_registrations'] = $driver_stats['total_driver_registrations'];
        }
        return $stats;
    }

    private function getDriverStatsForMarketer($marketer_id) {
        $sql = "SELECT
                    COUNT(rv.id) as total_driver_visits,
                    SUM(CASE WHEN rv.registration_status = 'successful' THEN 1 ELSE 0 END) as total_driver_registrations
                FROM drivers d
                LEFT JOIN referral_visits rv ON d.id = rv.registered_driver_id
                WHERE d.added_by = :marketer_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':marketer_id' => $marketer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getMarketers()
    {
        $sql = "SELECT id, username FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer') ORDER BY username";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRestaurantReferralStats($filters = []) {
        // 1. Get visit stats from restaurant_referral_visits
        $visit_base_query = "FROM restaurant_referral_visits v";
        $visit_where_clauses = [];
        $visit_params = [];

        if (!empty($filters['start_date'])) {
            $visit_where_clauses[] = "DATE(v.visit_recorded_at) >= :start_date";
            $visit_params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $visit_where_clauses[] = "DATE(v.visit_recorded_at) <= :end_date";
            $visit_params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['marketer_id'])) {
            $visit_where_clauses[] = "v.affiliate_user_id = :marketer_id";
            $visit_params[':marketer_id'] = $filters['marketer_id'];
        }

        $visit_where_sql = !empty($visit_where_clauses) ? "WHERE " . implode(' AND ', $visit_where_clauses) : "";
        $query_visits = "SELECT COUNT(v.id) as total_visits $visit_base_query $visit_where_sql";
        
        $stmt_visits = $this->db->prepare($query_visits);
        $stmt_visits->execute($visit_params);
        $total_visits = (int) $stmt_visits->fetchColumn();

        // 2. Get registration stats from the actual restaurants table
        $reg_base_query = "FROM restaurants r";
        $reg_where_clauses = [];
        $reg_params = [];

        if (!empty($filters['start_date'])) {
            $reg_where_clauses[] = "DATE(r.created_at) >= :start_date";
            $reg_params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $reg_where_clauses[] = "DATE(r.created_at) <= :end_date";
            $reg_params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['marketer_id'])) {
            $reg_where_clauses[] = "r.referred_by_user_id = :marketer_id";
            $reg_params[':marketer_id'] = $filters['marketer_id'];
        } else {
            // For admin dashboard without filter, count all referred restaurants
            $reg_where_clauses[] = "r.referred_by_user_id IS NOT NULL";
        }

        $reg_where_sql = !empty($reg_where_clauses) ? "WHERE " . implode(' AND ', $reg_where_clauses) : "";
        $query_regs = "SELECT COUNT(r.id) $reg_base_query $reg_where_sql";

        $stmt_regs = $this->db->prepare($query_regs);
        $stmt_regs->execute($reg_params);
        $total_registrations = (int) $stmt_regs->fetchColumn();
        
        // 3. Combine and return stats
        return [
            'total_visits' => $total_visits,
            'total_registrations' => $total_registrations,
            'conversion_rate' => ($total_visits > 0) ? (($total_registrations / $total_visits) * 100) : 0
        ];
    }

    /**
     * Fetches detailed statistics for each marketer.
     * This version is more robust and correctly handles marketers with no visits.
     *
     * @return array An array of marketers with their performance stats.
     */
    public function getMarketersPerformance()
    {
        $sql = "
            SELECT 
                u.id,
                u.username,
                COALESCE(vs.total_visits, 0) as total_visits,
                COALESCE(vs.total_registrations, 0) as total_registrations,
                IF(COALESCE(vs.total_visits, 0) > 0, (COALESCE(vs.total_registrations, 0) / vs.total_visits) * 100, 0) AS conversion_rate,
                (SELECT COUNT(r.id) FROM restaurants r WHERE r.referred_by_user_id = u.id) as total_restaurants
            FROM 
                users u
            LEFT JOIN (
                SELECT
                    affiliate_user_id,
                    COUNT(id) AS total_visits,
                    SUM(CASE WHEN registration_status = 'successful' THEN 1 ELSE 0 END) AS total_registrations
                FROM referral_visits
                GROUP BY affiliate_user_id
            ) AS vs ON u.id = vs.affiliate_user_id
            WHERE 
                u.role_id = (SELECT id FROM roles WHERE name = 'marketer')
            GROUP BY 
                u.id, u.username
            ORDER BY 
                total_registrations DESC, total_visits DESC;
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryStats()
    {
        $sql = "
            SELECT 
                (SELECT COUNT(id) FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer')) as total_marketers,
                (SELECT COUNT(id) FROM referral_visits) as total_visits,
                (SELECT COUNT(id) FROM referral_visits WHERE registration_status = 'successful') as total_registrations
        ";
        $stmt = $this->db->query($sql);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Prevent errors if the query fails or returns no rows
        if (!$summary) {
            $summary = ['total_marketers' => 0, 'total_visits' => 0, 'total_registrations' => 0];
        }

        // Ensure the conversion rate is calculated safely.
        $summary['conversion_rate'] = ($summary['total_visits'] > 0) ? ($summary['total_registrations'] / $summary['total_visits']) * 100 : 0;
        return $summary;
    }

    public function getVisits($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
    
        $baseQuery = "FROM referral_visits rv
                      LEFT JOIN users u ON rv.affiliate_user_id = u.id
                      LEFT JOIN drivers d ON rv.registered_driver_id = d.id";
    
        $whereClauses = [];
        $params = [];
    
        if (!empty($filters['marketer_id'])) {
            $whereClauses[] = "(rv.affiliate_user_id = :marketer_id_1 OR d.added_by = :marketer_id_2)";
            $params[':marketer_id_1'] = $filters['marketer_id'];
            $params[':marketer_id_2'] = $filters['marketer_id'];
        }
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(rv.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(rv.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['registration_status'])) {
            $whereClauses[] = "rv.registration_status = :registration_status";
            $params[':registration_status'] = $filters['registration_status'];
        }
        if (!empty($filters['device_type'])) {
            $whereClauses[] = "rv.device_type = :device_type";
            $params[':device_type'] = $filters['device_type'];
        }
    
        $whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);
    
        $sql = "SELECT rv.*, u.username as affiliate_name, d.name as driver_name
                $baseQuery
                $whereSql
                ORDER BY rv.visit_recorded_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        // Bind parameters
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalVisits($filters = []) {
        $baseQuery = "FROM referral_visits rv LEFT JOIN drivers d ON rv.registered_driver_id = d.id";
        $whereClauses = [];
        $params = [];
    
        if (!empty($filters['marketer_id'])) {
            $whereClauses[] = "(rv.affiliate_user_id = :marketer_id_1 OR d.added_by = :marketer_id_2)";
            $params[':marketer_id_1'] = $filters['marketer_id'];
            $params[':marketer_id_2'] = $filters['marketer_id'];
        }
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(rv.visit_recorded_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(rv.visit_recorded_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['registration_status'])) {
            $whereClauses[] = "rv.registration_status = :registration_status";
            $params[':registration_status'] = $filters['registration_status'];
        }
        if (!empty($filters['device_type'])) {
            $whereClauses[] = "rv.device_type = :device_type";
            $params[':device_type'] = $filters['device_type'];
        }
    
        $whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);
    
        $sql = "SELECT COUNT(rv.id) $baseQuery $whereSql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
} 