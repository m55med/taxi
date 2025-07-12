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
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM reviews r
                    JOIN tickets t ON r.reviewable_id = t.id AND r.reviewable_type = 'ticket'
                    JOIN users reviewer ON r.reviewed_by = reviewer.id
                    JOIN users agent ON t.created_by = agent.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['reviewer_id'])) $conditions[] = "r.reviewed_by = :reviewer_id";
        if (!empty($filters['agent_id'])) $conditions[] = "t.created_by = :agent_id";
        if (!empty($filters['rating_from'])) $conditions[] = "r.rating >= :rating_from";
        if (!empty($filters['rating_to'])) $conditions[] = "r.rating <= :rating_to";
        if (!empty($filters['date_from'])) $conditions[] = "DATE(r.reviewed_at) >= :date_from";
        if (!empty($filters['date_to'])) $conditions[] = "DATE(r.reviewed_at) <= :date_to";

        foreach ($filters as $key => $value) {
            if (!empty($value)) $params[":$key"] = $value;
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getReviews($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT r.id, r.rating, r.review_notes, r.reviewed_at,
                       t.id as ticket_id, t.ticket_number,
                       reviewer.id as reviewer_id, reviewer.username as reviewer_name,
                       agent.id as agent_id, agent.username as agent_name
                " . $queryParts['base'] . $queryParts['where']
                . " ORDER BY r.reviewed_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) $stmt->bindParam($key, $val);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getReviewsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(r.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        return [
            'reviewers' => $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN reviews r ON u.id = r.reviewed_by WHERE r.reviewable_type = 'ticket' ORDER BY u.username ASC")->fetchAll(PDO::FETCH_ASSOC),
            'agents' => $this->db->query("SELECT DISTINCT u.id, u.username FROM users u JOIN tickets t ON u.id = t.created_by ORDER BY u.username ASC")->fetchAll(PDO::FETCH_ASSOC),
        ];
    }
} 