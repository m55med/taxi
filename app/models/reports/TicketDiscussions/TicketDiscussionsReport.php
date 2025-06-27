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
                    d.id,
                    t.ticket_number,
                    d.reason,
                    d.notes,
                    d.status,
                    u.username as opened_by_user,
                    d.created_at
                FROM discussions d
                JOIN tickets t ON d.discussable_id = t.id
                JOIN users u ON d.opened_by = u.id";

        $conditions = ["d.discussable_type = 'App\\\\Models\\\\Tickets\\\\Ticket'"];
        $params = [];

        if (!empty($filters['ticket_id'])) {
            $conditions[] = "d.discussable_id = :ticket_id";
            $params[':ticket_id'] = $filters['ticket_id'];
        }
        if (!empty($filters['opened_by'])) {
            $conditions[] = "d.opened_by = :opened_by";
            $params[':opened_by'] = $filters['opened_by'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "d.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usersSql = "SELECT DISTINCT u.id, u.username 
                     FROM users u 
                     JOIN discussions d ON u.id = d.opened_by 
                     WHERE d.discussable_type = 'App\\\\Models\\\\Tickets\\\\Ticket'";
        $users = $this->db->query($usersSql)->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'discussions' => $discussions,
            'users' => $users
        ];
    }
} 