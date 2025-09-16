<?php

namespace App\Models\Reports\TicketCoupons;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class TicketCouponsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM ticket_coupons tc
                    JOIN tickets t ON tc.ticket_id = t.id
                    JOIN coupons c ON tc.coupon_id = c.id
                    JOIN users u ON t.created_by = u.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['user_id'])) $conditions[] = "t.created_by = :user_id";
        if (!empty($filters['search'])) $conditions[] = "(t.ticket_number LIKE :search OR c.code LIKE :search)";
        if (!empty($filters['date_from'])) $conditions[] = "DATE(tc.created_at) >= :date_from";
        if (!empty($filters['date_to'])) $conditions[] = "DATE(tc.created_at) <= :date_to";

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key === 'search') $params[":$key"] = '%' . $value . '%';
                else $params[":$key"] = $value;
            }
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getTicketCoupons($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT tc.id, t.ticket_number, c.code as coupon_code, u.username as created_by_user,
                       tc.created_at, t.id as ticket_id, c.id as coupon_id, u.id as user_id
                " . $queryParts['base'] . $queryParts['where']
                . " ORDER BY tc.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) $stmt->bindParam($key, $val);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    public function getTicketCouponsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(tc.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        return [
            'users' => $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
} 