<?php

namespace App\Models\Reports\MyActivity;

use App\Core\Database;
use PDO;

class MyActivityReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getReportData($userId, $filters)
    {
        $calls = $this->getCallsData($userId, $filters);
        $tickets = $this->getTicketsData($userId, $filters);

        $summary = [
            'total_calls' => count($calls),
            'total_tickets' => count($tickets),
        ];

        return [
            'summary' => $summary,
            'calls' => $calls,
            'tickets' => $tickets,
            'discussions' => $this->getDiscussionsData($userId, $filters),
            'coupons' => $this->getCouponsData($userId, $filters),
            'referral_visits' => $this->getReferralVisitsData($userId, $filters),
        ];
    }

    private function getCallsData($userId, $filters)
    {
        $sql = "SELECT dc.id as call_id, d.name as driver_name, dc.call_status, dc.notes, dc.next_call_at, dc.created_at 
                FROM driver_calls dc
                JOIN drivers d ON dc.driver_id = d.id
                WHERE dc.call_by = :user_id";
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(dc.created_at) >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(dc.created_at) <= :date_to";
        }

        $sql .= " ORDER BY dc.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if (!empty($filters['date_from'])) {
            $stmt->bindValue(':date_from', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $stmt->bindValue(':date_to', $filters['date_to']);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTicketsData($userId, $filters)
    {
        $sql = "SELECT t.ticket_number, t.is_vip, p.name as platform_name, cat.name as category_name, sub.name as subcategory_name, cod.name as code_name, t.created_at
                FROM tickets t
                LEFT JOIN platforms p ON t.platform_id = p.id
                LEFT JOIN ticket_categories cat ON t.category_id = cat.id
                LEFT JOIN ticket_subcategories sub ON t.subcategory_id = sub.id
                LEFT JOIN ticket_codes cod ON t.code_id = cod.id
                WHERE t.created_by = :user_id";

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(t.created_at) >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(t.created_at) <= :date_to";
        }
        
        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if (!empty($filters['date_from'])) {
            $stmt->bindValue(':date_from', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $stmt->bindValue(':date_to', $filters['date_to']);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getDiscussionsData($userId, $filters) { return []; }
    private function getCouponsData($userId, $filters) { return []; }
    private function getReferralVisitsData($userId, $filters) { return []; }
} 