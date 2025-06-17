<?php

namespace App\Models\Reports\TicketReviews;

use App\Core\Database;
use PDO;

class TicketReviewsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getReviews($filters)
    {
        $sql = "SELECT 
                    tr.id,
                    tr.review_result,
                    tr.review_notes,
                    t.ticket_number,
                    reviewer.username as reviewer_name,
                    agent.username as agent_name,
                    tr.reviewed_at
                FROM ticket_reviews tr
                JOIN tickets t ON tr.ticket_id = t.id
                JOIN users reviewer ON tr.reviewed_by = reviewer.id
                JOIN users agent ON t.created_by = agent.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['reviewer_id'])) {
            $conditions[] = "tr.reviewed_by = :reviewer_id";
            $params[':reviewer_id'] = $filters['reviewer_id'];
        }
        if (!empty($filters['agent_id'])) {
            $conditions[] = "t.created_by = :agent_id";
            $params[':agent_id'] = $filters['agent_id'];
        }
        if (!empty($filters['review_result'])) {
            $conditions[] = "tr.review_result = :review_result";
            $params[':review_result'] = $filters['review_result'];
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY tr.reviewed_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviewers = $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN ticket_reviews tr ON u.id = tr.reviewed_by")->fetchAll(PDO::FETCH_ASSOC);
        $agents = $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN tickets t ON u.id = t.created_by WHERE u.role_id = (SELECT id from roles where name = 'agent')")->fetchAll(PDO::FETCH_ASSOC);


        return [
            'reviews' => $reviews,
            'reviewers' => $reviewers,
            'agents' => $agents
        ];
    }
} 