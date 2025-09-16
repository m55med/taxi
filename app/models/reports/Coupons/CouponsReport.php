<?php

namespace App\Models\Reports\Coupons;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class CouponsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetches summary statistics for coupons.
     * @return array
     */
    public function getCouponStats(): array
    {
        $sql = "SELECT
                    COUNT(*) as total_coupons,
                    SUM(CASE WHEN c.is_used = 1 THEN 1 ELSE 0 END) as used_coupons,
                    SUM(CASE WHEN c.is_used = 0 THEN 1 ELSE 0 END) as unused_coupons
                FROM coupons c";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        if ($result) {

            return convert_dates_for_display($result, ['created_at', 'updated_at']);

        }


        return $result;
    }

    /**
     * Fetches detailed information for all coupons with filtering.
     * @param array $filters
     * @return array
     */
    public function getCouponsDetails($filters = [], $limit = 25, $offset = 0): array
    {
        $sql = "SELECT 
                    c.id, c.code, c.value, c.is_used, c.created_at, c.used_at,
                    t.id as ticket_id,
                    t.ticket_number,
                    u.name as used_by_name,
                    co.name as country_name
                FROM 
                    coupons c
                LEFT JOIN 
                    tickets t ON c.used_in_ticket = t.id
                LEFT JOIN 
                    users u ON c.used_by = u.id
                LEFT JOIN 
                    countries co ON c.country_id = co.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'used') {
                $conditions[] = "c.is_used = 1";
            } elseif ($filters['status'] === 'unused') {
                $conditions[] = "c.is_used = 0";
            }
        }
        
        if (!empty($filters['code'])) {
            $conditions[] = "c.code LIKE :code";
            $params[':code'] = '%' . $filters['code'] . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters, including new limit and offset
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    /**
     * Gets the total count of coupons based on filters for pagination.
     * @param array $filters
     * @return int
     */
    public function getTotalCouponsCount($filters = []): int
    {
        $sql = "SELECT COUNT(c.id) FROM coupons c";
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'used') {
                $conditions[] = "c.is_used = 1";
            } elseif ($filters['status'] === 'unused') {
                $conditions[] = "c.is_used = 0";
            }
        }
        
        if (!empty($filters['code'])) {
            $conditions[] = "c.code LIKE :code";
            $params[':code'] = '%' . $filters['code'] . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
} 