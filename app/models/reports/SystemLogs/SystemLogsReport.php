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
        $sql = "SELECT l.id, l.level, l.message, l.context, l.created_at, u.username 
                FROM logs l
                LEFT JOIN users u ON l.user_id = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['level'])) {
            $conditions[] = "l.level = :level";
            $params[':level'] = $filters['level'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "l.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY l.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = $this->db->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $levels = $this->db->query("SELECT DISTINCT level FROM logs")->fetchAll(PDO::FETCH_COLUMN);


        return [
            'logs' => $logs,
            'users' => $users,
            'levels' => $levels
        ];
    }
} 