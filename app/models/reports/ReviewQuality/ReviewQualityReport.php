<?php

namespace App\Models\Reports\ReviewQuality;

use App\Core\Database;
use PDO;

class ReviewQualityReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAgents() {
        return $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN tickets t ON u.id = t.assigned_to")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQualitySummary($filters)
    {
        $sql = "SELECT 
                    agent.username as agent_name,
                    COUNT(tr.id) as total_reviews,
                    AVG(tr.rating) as average_rating,
                    SUM(CASE WHEN td.type = 'objection' THEN 1 ELSE 0 END) as objection_count
                FROM ticket_reviews tr
                JOIN tickets t ON tr.ticket_id = t.id
                JOIN users agent ON t.assigned_to = agent.id
                LEFT JOIN ticket_discussions td ON t.id = td.ticket_id AND td.type = 'objection'";

        $conditions = [];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $conditions[] = "t.assigned_to = :agent_id";
            $params[':agent_id'] = $filters['agent_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "tr.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "tr.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY agent.id, agent.username ORDER BY average_rating DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 