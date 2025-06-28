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

    public function getLogs($filters)
    {
        $sql = "SELECT d.id, d.status as level, d.reason as message, d.notes as context, d.created_at, u.username 
                FROM discussions d
                LEFT JOIN users u ON d.opened_by = u.id";

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

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = $this->db->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT DISTINCT status FROM discussions")->fetchAll(PDO::FETCH_COLUMN);


        return [
            'logs' => $logs,
            'users' => $users,
            'levels' => $levels
        ];
    }
} 