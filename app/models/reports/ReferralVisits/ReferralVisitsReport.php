<?php

namespace App\Models\Reports\ReferralVisits;

use App\Core\Database;
use PDO;

class ReferralVisitsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function buildFilterConditions($filters, &$conditions, &$params)
    {
        if (!empty($filters['affiliate_id'])) {
            $conditions[] = "u.id = :affiliate_id";
            $params[':affiliate_id'] = $filters['affiliate_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(rv.visit_recorded_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(rv.visit_recorded_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['registration_status'])) {
            $conditions[] = "rv.registration_status = :registration_status";
            $params[':registration_status'] = $filters['registration_status'];
        }
    }

    public function getVisits($filters, $page = 1, $perPage = 25, $export = false)
    {
        // First, get the total count of records matching the filters
        $countSql = "SELECT COUNT(*) 
                     FROM referral_visits rv
                     LEFT JOIN users u ON rv.affiliate_user_id = u.id
                     LEFT JOIN drivers d ON rv.registered_driver_id = d.id";

        $sql = "SELECT 
                    rv.id,
                    rv.visit_recorded_at,
                    u.username as affiliate_user_name,
                    rv.ip_address,
                    rv.user_agent,
                    rv.registration_status,
                    d.id as registered_driver_id,
                    d.name as registered_driver_name
                FROM referral_visits rv
                LEFT JOIN users u ON rv.affiliate_user_id = u.id
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id";

        $conditions = [];
        $params = [];
        $this->buildFilterConditions($filters, $conditions, $params);

        if (count($conditions) > 0) {
            $whereClause = " WHERE " . implode(" AND ", $conditions);
            $sql .= $whereClause;
            $countSql .= $whereClause;
        }
        
        // Get total records
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        $sql .= " ORDER BY rv.visit_recorded_at DESC";

        if (!$export) {
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT :perPage OFFSET :offset";
            // Do not add to $params, as they are bound separately with type info
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!$export) {
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'total' => $totalRecords,
            'page' => $page,
            'perPage' => $perPage
        ];
    }

    public function getAffiliateMarketers()
    {
        // Assuming 'marketer' is a role name in the `roles` table
        $sql = "SELECT u.id, u.username 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'marketer'
                ORDER BY u.username ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryStats($filters)
    {
        $stats = [];
        $baseSql = "FROM referral_visits rv LEFT JOIN users u ON rv.affiliate_user_id = u.id";

        // 1. Total Visits (respects all filters)
        $totalConditions = [];
        $totalParams = [];
        $this->buildFilterConditions($filters, $totalConditions, $totalParams);
        $totalWhere = empty($totalConditions) ? "" : "WHERE " . implode(" AND ", $totalConditions);
        $totalSql = "SELECT COUNT(*) $baseSql $totalWhere";
        $totalStmt = $this->db->prepare($totalSql);
        $totalStmt->execute($totalParams);
        $stats['total_visits'] = $totalStmt->fetchColumn();

        // 2. Successful Registrations (ignores registration_status filter)
        $successfulFilters = $filters;
        unset($successfulFilters['registration_status']);
        $successfulConditions = [];
        $successfulParams = [];
        $this->buildFilterConditions($successfulFilters, $successfulConditions, $successfulParams);
        $successfulConditions[] = "rv.registration_status = 'successful'";
        $successfulWhere = "WHERE " . implode(" AND ", $successfulConditions);
        $successfulSql = "SELECT COUNT(*) $baseSql $successfulWhere";
        $successfulStmt = $this->db->prepare($successfulSql);
        $successfulStmt->execute($successfulParams);
        $stats['successful_registrations'] = $successfulStmt->fetchColumn();

        // 3. Unique Affiliates (respects all filters)
        $uniqueSql = "SELECT COUNT(DISTINCT rv.affiliate_user_id) $baseSql $totalWhere";
        $uniqueStmt = $this->db->prepare($uniqueSql);
        $uniqueStmt->execute($totalParams);
        $stats['unique_affiliates'] = $uniqueStmt->fetchColumn();

        return $stats;
    }
} 