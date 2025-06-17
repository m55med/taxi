<?php

namespace App\Models\Reports\TicketDiscussions;

use App\Core\Database;
use PDO;

class TicketDiscussionsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDiscussions($filters)
    {
        $sql = "SELECT 
                    td.id,
                    t.ticket_number,
                    td.reason,
                    td.notes,
                    td.status,
                    u.username as opened_by_user,
                    td.created_at
                FROM ticket_discussions td
                JOIN tickets t ON td.ticket_id = t.id
                JOIN users u ON td.opened_by = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['ticket_id'])) {
            $conditions[] = "td.ticket_id = :ticket_id";
            $params[':ticket_id'] = $filters['ticket_id'];
        }
        if (!empty($filters['opened_by'])) {
            $conditions[] = "td.opened_by = :opened_by";
            $params[':opened_by'] = $filters['opened_by'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "td.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY td.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN ticket_discussions td ON u.id = td.opened_by")->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'discussions' => $discussions,
            'users' => $users
        ];
    }
} 