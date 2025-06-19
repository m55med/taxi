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
                    rv.visit_recorded_at,
                    u.username as affiliate_user_name,
                    rv.ip_address,
                    rv.user_agent,
                    rv.registration_status,
                    d.name as registered_driver_name
                FROM referral_visits rv
                LEFT JOIN users u ON rv.affiliate_user_id = u.id
                LEFT JOIN drivers d ON rv.registered_driver_id = d.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['affiliate_name'])) {
            $conditions[] = "u.username LIKE :affiliate_name";
            $params[':affiliate_name'] = '%' . $filters['affiliate_name'] . '%';
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

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY rv.visit_recorded_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 