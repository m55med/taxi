<?php

namespace App\Models\Reports\DriverAssignments;

use App\Core\Database;
use PDO;

class DriverAssignmentsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAssignments($filters)
    {
        $sql = "SELECT
                    da.id,
                    da.created_at,
                    d.name as driver_name,
                    from_user.username as from_user_name,
                    to_user.username as to_user_name,
                    da.note,
                    da.is_seen
                FROM
                    driver_assignments da
                JOIN drivers d ON da.driver_id = d.id
                JOIN users from_user ON da.from_user_id = from_user.id
                JOIN users to_user ON da.to_user_id = to_user.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['driver_id'])) {
            $conditions[] = "da.driver_id = :driver_id";
            $params[':driver_id'] = $filters['driver_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "da.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "da.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY da.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For filters dropdown
        $drivers = $this->db->query("SELECT id, name FROM drivers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'assignments' => $assignments,
            'drivers' => $drivers,
        ];
    }
} 