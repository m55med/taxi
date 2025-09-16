<?php

namespace App\Models\Reports\TicketsSummary;

use App\Core\Database;
use PDO;

class TicketsSummaryReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getSummary($filters = [])
    {
        $whereClause = $this->buildWhereClause($filters);
        
        $summary = [
            'by_status' => $this->getSummaryByStatus($whereClause['sql'], $whereClause['params']),
            'by_category' => $this->getSummaryByCategory($whereClause['sql'], $whereClause['params']),
            'by_platform' => $this->getSummaryByPlatform($whereClause['sql'], $whereClause['params']),
            'by_vip_status' => $this->getSummaryByVipStatus($whereClause['sql'], $whereClause['params']),
        ];

        return $summary;
    }

    private function buildWhereClause($filters)
    {
        $conditions = [];
        $params = [];
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(td.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(td.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        return [
            'sql' => count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "",
            'params' => $params
        ];
    }
    
    // As there's no status on tickets, we'll use a proxy: a ticket is "Closed" if it has a review.
    private function getSummaryByStatus($where, $params) {
        $sql = "SELECT CASE WHEN r.id IS NOT NULL THEN 'Closed' ELSE 'Open' END as status, COUNT(DISTINCT t.id) as count
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                LEFT JOIN reviews r ON r.reviewable_id = t.id AND r.reviewable_type = 'ticket'
                {$where}
                GROUP BY status";

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        return $this->db->prepare($sql)->execute($params)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    private function getSummaryByCategory($where, $params) {
        $sql = "SELECT tc.name, COUNT(td.id) as count 
                FROM ticket_details td
                JOIN ticket_categories tc ON td.category_id = tc.id 
                {$where}
                GROUP BY tc.name ORDER BY count DESC";
        return $this->db->prepare($sql)->execute($params)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    
    private function getSummaryByPlatform($where, $params) {
        $sql = "SELECT p.name, COUNT(td.id) as count 
                FROM ticket_details td
                JOIN platforms p ON td.platform_id = p.id 
                {$where}
                GROUP BY p.name ORDER BY count DESC";
        return $this->db->prepare($sql)->execute($params)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    
    private function getSummaryByVipStatus($where, $params) {
        $sql = "SELECT CASE WHEN td.is_vip = 1 THEN 'VIP' ELSE 'Standard' END as vip_status, COUNT(td.id) as count 
                FROM ticket_details td
                {$where}
                GROUP BY vip_status";
        return $this->db->prepare($sql)->execute($params)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
} 