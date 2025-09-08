<?php

namespace App\Models\Reports\SystemLogs;

use App\Core\Database;
use PDO;

class SystemLogsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    private function buildWhereClause($filters) {
        $conditions = [];
        $params = [];

        if (!empty($filters['level'])) {
            $conditions[] = "d.status = :level";
            $params[':level'] = $filters['level'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "d.opened_by = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(d.reason LIKE :search OR d.notes LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(d.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(d.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        
        return ['sql' => $whereSql, 'params' => $params];
    }

    public function getLogs($filters, $limit, $offset)
    {
        $queryBuilder = $this->buildWhereClause($filters);
        
        $sql = "SELECT d.id, d.status as level, d.reason as message, d.notes as context, d.created_at, u.username, u.id as user_id
                FROM discussions d
                LEFT JOIN users u ON d.opened_by = u.id" . $queryBuilder['sql'];

        $sql .= " ORDER BY d.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryBuilder['params'] as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLogsCount($filters)
    {
        $queryBuilder = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(d.id) FROM discussions d" . $queryBuilder['sql'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryBuilder['params']);
        return (int)$stmt->fetchColumn();
    }

    public function getFilterOptions()
    {
        $users = $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT DISTINCT status FROM discussions ORDER BY status ASC")->fetchAll(PDO::FETCH_COLUMN);
        return [
            'users' => $users,
            'levels' => $levels
        ];
    }
} 