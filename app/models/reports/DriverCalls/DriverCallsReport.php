<?php

namespace App\Models\Reports\DriverCalls;

use App\Core\Database;
use PDO;

class DriverCallsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCallsReport($filters)
    {
        $sql = "SELECT dc.*, d.name as driver_name, u.username as user_name, c.name as country_name, ct.name as car_type_name
                FROM driver_calls dc
                JOIN drivers d ON dc.driver_id = d.id
                JOIN users u ON dc.call_by = u.id
                LEFT JOIN countries c ON d.country_id = c.id
                LEFT JOIN car_types ct ON d.car_type_id = ct.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['country_id'])) {
            $conditions[] = "d.country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }
        if (!empty($filters['car_type_id'])) {
            $conditions[] = "d.car_type_id = :car_type_id";
            $params[':car_type_id'] = $filters['car_type_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "dc.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY dc.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For filters dropdown
        $countries = $this->db->query("SELECT id, name FROM countries")->fetchAll(PDO::FETCH_ASSOC);
        $car_types = $this->db->query("SELECT id, name FROM car_types")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'calls' => $calls,
            'countries' => $countries,
            'car_types' => $car_types,
        ];
    }
} 