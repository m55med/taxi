<?php

namespace App\Models\Reports\TicketRework;

use App\Core\Database;
use PDO;

class TicketReworkReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAgents() {
        return $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN tickets t ON u.id = t.assigned_to")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReworks($filters)
    {
        // This query assumes a `reopened_count` column exists on the `tickets` table.
        $sql = "SELECT 
                    t.id,
                    t.ticket_number,
                    agent.username as agent_name,
                    t.reopened_count,
                    t.closed_at
                FROM tickets t
                JOIN users agent ON t.assigned_to = agent.id
                WHERE t.reopened_count > 0";

        $params = [];

        if (!empty($filters['agent_id'])) {
            $sql .= " AND t.assigned_to = :agent_id";
            $params[':agent_id'] = $filters['agent_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.closed_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.closed_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY t.reopened_count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 