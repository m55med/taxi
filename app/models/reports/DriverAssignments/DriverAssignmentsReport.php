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
    
    private function buildWhereClause($filters) {
        $conditions = [];
        $params = [];

        if (!empty($filters['driver_id'])) {
            $conditions[] = "da.driver_id = :driver_id";
            $params[':driver_id'] = $filters['driver_id'];
        }
        if (!empty($filters['from_user_id'])) {
            $conditions[] = "da.from_user_id = :from_user_id";
            $params[':from_user_id'] = $filters['from_user_id'];
        }
        if (!empty($filters['to_user_id'])) {
            $conditions[] = "da.to_user_id = :to_user_id";
            $params[':to_user_id'] = $filters['to_user_id'];
        }
        if (isset($filters['is_seen']) && $filters['is_seen'] !== '') {
            $conditions[] = "da.is_seen = :is_seen";
            $params[':is_seen'] = $filters['is_seen'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "da.note LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(da.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(da.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        return [
            'sql' => count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "",
            'params' => $params
        ];
    }

    public function getAssignments($filters, $limit, $offset)
    {
        $queryBuilder = $this->buildWhereClause($filters);
        
        $sql = "SELECT
                    da.id, da.created_at, da.note, da.is_seen,
                    d.id as driver_id, d.name as driver_name,
                    from_user.id as from_user_id, from_user.username as from_user_name,
                    to_user.id as to_user_id, to_user.username as to_user_name
                FROM driver_assignments da
                JOIN drivers d ON da.driver_id = d.id
                JOIN users from_user ON da.from_user_id = from_user.id
                JOIN users to_user ON da.to_user_id = to_user.id"
                . $queryBuilder['sql']
                . " ORDER BY da.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryBuilder['params'] as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAssignmentsCount($filters)
    {
        $queryBuilder = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(da.id) FROM driver_assignments da" . $queryBuilder['sql'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryBuilder['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        $drivers = $this->db->query("SELECT id, name FROM drivers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $users = $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
        return [
            'drivers' => $drivers,
            'users' => $users
        ];
    }
} 