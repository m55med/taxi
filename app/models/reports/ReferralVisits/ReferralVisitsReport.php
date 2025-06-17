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

    public function getVisits($filters)
    {
        $sql = "SELECT 
                    rv.id,
                    rv.referral_code,
                    u.username as referrer_name,
                    rv.ip_address,
                    rv.user_agent,
                    rv.created_at
                FROM referral_visits rv
                LEFT JOIN referrals r ON rv.referral_code = r.referral_code
                LEFT JOIN users u ON r.user_id = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['referral_code'])) {
            $conditions[] = "rv.referral_code = :referral_code";
            $params[':referral_code'] = $filters['referral_code'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "rv.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "rv.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY rv.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 