<?php
namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;

class Coupon {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Get a paginated and filtered list of coupons.
     */
    public function getCoupons($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, 
                       cnt.name as country_name, 
                       u_used.username as used_by_username,
                       u_held.username as held_by_username,
                       t.ticket_number
                FROM coupons c
                LEFT JOIN countries cnt ON c.country_id = cnt.id
                LEFT JOIN users u_used ON c.used_by = u_used.id
                LEFT JOIN users u_held ON c.held_by = u_held.id
                LEFT JOIN tickets t ON c.used_in_ticket = t.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND c.code LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $sql .= " AND c.is_used = :is_used";
            $params[':is_used'] = $filters['is_used'];
        }
        if (!empty($filters['country_id'])) {
            $sql .= " AND c.country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt_paginated = $this->pdo->prepare($sql);
        $stmt_paginated->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt_paginated->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => &$val) {
            $stmt_paginated->bindParam($key, $val);
        }
        $stmt_paginated->execute();
        $results = $stmt_paginated->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $count_sql = "SELECT COUNT(id) FROM coupons WHERE 1=1";
         if (!empty($filters['search'])) {
            $count_sql .= " AND code LIKE :search";
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $count_sql .= " AND is_used = :is_used";
        }
        if (!empty($filters['country_id'])) {
            $count_sql .= " AND country_id = :country_id";
        }

        $stmt_count = $this->pdo->prepare($count_sql);
        if(!empty($params)){
             foreach ($params as $key => &$val) {
                $stmt_count->bindParam($key, $val);
            }
        }
       
        $stmt_count->execute();
        $total_records = (int) $stmt_count->fetchColumn();

        return [
            'data' => $results,
            'total' => $total_records,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total_records / $limit)
        ];
    }

    /**
     * Find a coupon by its ID.
     */
    public function findCouponById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if a coupon code already exists.
     */
    public function isCodeExists($code) {
        $stmt = $this->pdo->prepare("SELECT id FROM coupons WHERE code = :code");
        $stmt->execute([':code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Add multiple coupons in a batch.
     * Returns stats about the operation.
     */
    public function addBulkCoupons($codes, $value, $country_id) {
        $stats = ['added' => 0, 'skipped' => 0, 'skipped_codes' => []];
        
        $sql = "INSERT INTO coupons (code, value, country_id) VALUES (:code, :value, :country_id)";
        $stmt = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();
        try {
            foreach ($codes as $code) {
                if (empty($code)) continue;

                if ($this->isCodeExists($code)) {
                    $stats['skipped']++;
                    $stats['skipped_codes'][] = $code;
                } else {
                    $stmt->execute([
                        ':code' => $code,
                        ':value' => $value,
                        ':country_id' => $country_id ? $country_id : null
                    ]);
                    $stats['added']++;
                }
            }
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // In a real app, you'd log this error
            return false;
        }

        return $stats;
    }

    /**
     * Update a coupon's data.
     */
    public function updateCoupon($id, $data) {
        // A coupon can't be updated if it is used.
        $coupon = $this->findCouponById($id);
        if ($coupon && $coupon['is_used']) {
            return false;
        }

        $sql = "UPDATE coupons SET code = :code, value = :value, country_id = :country_id WHERE id = :id AND is_used = 0";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':code' => $data['code'],
            ':value' => $data['value'],
            ':country_id' => $data['country_id'] ? $data['country_id'] : null
        ]);
    }

    /**
     * Delete a coupon.
     */
    public function deleteCoupon($id) {
        // A coupon can't be deleted if it is used.
        $coupon = $this->findCouponById($id);
        if ($coupon && $coupon['is_used']) {
            return false;
        }

        $sql = "DELETE FROM coupons WHERE id = :id AND is_used = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Get a list of all countries for dropdowns.
     */
    public function getCountries() {
        $stmt = $this->pdo->prepare("SELECT id, name FROM countries ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics about coupons based on filters.
     */
    public function getCouponStats($filters = []) {
        $base_sql = "SELECT 
                        COUNT(id) as total,
                        SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used,
                        SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as unused
                     FROM coupons";
        
        $where_clauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where_clauses[] = "code LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $where_clauses[] = "is_used = :is_used";
            $params[':is_used'] = $filters['is_used'];
        }
        if (!empty($filters['country_id'])) {
            $where_clauses[] = "country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }

        if (!empty($where_clauses)) {
            $base_sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $stmt = $this->pdo->prepare($base_sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure stats are always integers
        return [
            'total' => (int)($stats['total'] ?? 0),
            'used' => (int)($stats['used'] ?? 0),
            'unused' => (int)($stats['unused'] ?? 0),
        ];
    }
} 