<?php

namespace App\Models\Reports\TicketCoupons;

use App\Core\Database;
use PDO;

class TicketCouponsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTicketCoupons($filters)
    {
        $sql = "SELECT 
                    tc.id,
                    t.ticket_number,
                    c.code as coupon_code,
                    u.username as created_by_user,
                    tc.created_at
                FROM ticket_coupons tc
                JOIN tickets t ON tc.ticket_id = t.id
                JOIN coupons c ON tc.coupon_id = c.id
                JOIN users u ON t.created_by = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['coupon_id'])) {
            $conditions[] = "tc.coupon_id = :coupon_id";
            $params[':coupon_id'] = $filters['coupon_id'];
        }
        if (!empty($filters['ticket_id'])) {
            $conditions[] = "tc.ticket_id = :ticket_id";
            $params[':ticket_id'] = $filters['ticket_id'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY tc.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ticket_coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $coupons = $this->db->query("SELECT id, code FROM coupons ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
        $tickets = $this->db->query("SELECT id, ticket_number FROM tickets ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'ticket_coupons' => $ticket_coupons,
            'coupons' => $coupons,
            'tickets' => $tickets
        ];
    }
} 