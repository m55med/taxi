<?php

namespace App\Models\Reports\DriverCalls;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class DriverCallsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM driver_calls dc
                    JOIN drivers d ON dc.driver_id = d.id
                    JOIN users u ON dc.call_by = u.id
                    LEFT JOIN countries c ON d.country_id = c.id
                    LEFT JOIN car_types ct ON d.car_type_id = ct.id
                    LEFT JOIN team_members tm ON u.id = tm.user_id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['driver_id'])) {
            $conditions[] = "dc.driver_id = :driver_id";
            $params[':driver_id'] = $filters['driver_id'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "dc.call_by = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['team_id'])) {
            $conditions[] = "tm.team_id = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['call_status'])) {
            $conditions[] = "dc.call_status = :call_status";
            $params[':call_status'] = $filters['call_status'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "dc.notes LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(dc.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(dc.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getCalls($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        
        $sql = "SELECT dc.id, dc.created_at, dc.call_status, dc.notes,
                       d.id as driver_id, d.name as driver_name,
                       u.id as user_id, u.username as user_name,
                       c.name as country_name,
                       ct.name as car_type_name
                " . $queryParts['base'] 
                . $queryParts['where']
                . " ORDER BY dc.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    public function getCallsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(dc.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        return [
            'drivers' => $this->db->query("SELECT id, name FROM drivers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'users' => $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC),
            'teams' => $this->db->query("SELECT id, name FROM teams ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'statuses' => $this->db->query("SELECT DISTINCT call_status FROM driver_calls ORDER BY call_status ASC")->fetchAll(PDO::FETCH_COLUMN)
        ];
    }
} 