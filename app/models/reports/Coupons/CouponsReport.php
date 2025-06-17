<?php

namespace App\Models\Reports\Coupons;

use App\Core\Database;
use PDO;

class CouponsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCoupons($filters)
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM assignments a WHERE a.coupon_id = c.id) as usage_count
                FROM coupons c";

        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "c.status = :status";
            $params[':status'] = $filters['status'];
        }
        if ($filters['is_active'] !== '') {
            $conditions[] = "c.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 