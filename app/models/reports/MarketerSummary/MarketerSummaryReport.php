<?php

namespace App\Models\Reports\MarketerSummary;

use App\Core\Database;
use PDO;

class MarketerSummaryReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getMarketers() {
        return $this->db->query("SELECT id, username FROM users WHERE role = 'marketing'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary($filters)
    {
        $sql = "SELECT 
                    u.username as marketer_name,
                    COUNT(DISTINCT rv.id) as total_visits,
                    COUNT(DISTINCT d.id) as total_registrations
                FROM users u
                LEFT JOIN referrals r ON u.id = r.user_id
                LEFT JOIN referral_visits rv ON r.referral_code = rv.referral_code
                LEFT JOIN drivers d ON r.id = d.referral_id
                WHERE u.role = 'marketing'";

        $params = [];

        if (!empty($filters['marketer_id'])) {
            $sql .= " AND u.id = :marketer_id";
            $params[':marketer_id'] = $filters['marketer_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND rv.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND rv.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " GROUP BY u.id, u.username ORDER BY total_registrations DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 