<?php

namespace App\Models\Reports\EmployeeActivityScore;

use App\Core\Database;
use PDO;

class EmployeeActivityScoreReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getTeams() {
        return $this->db->query("SELECT id, name FROM teams")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivityScores($filters)
    {
        $sql = "SELECT
                    u.id as user_id,
                    u.username,
                    t.name as team_name,
                    COUNT(DISTINCT dc.id) as calls_made,
                    COUNT(DISTINCT ti.id) as tickets_created,
                    COUNT(DISTINCT tr.id) as tickets_reviewed,
                    (COUNT(DISTINCT dc.id) * 2) + (COUNT(DISTINCT ti.id) * 3) + (COUNT(DISTINCT tr.id) * 1) as activity_score
                FROM
                    users u
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN teams t ON tm.team_id = t.id
                LEFT JOIN driver_calls dc ON u.id = dc.call_by
                LEFT JOIN tickets ti ON u.id = ti.created_by
                LEFT JOIN ticket_reviews tr ON u.id = tr.reviewed_by";

        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['team_id'])) {
            $conditions[] = "t.id = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "u.created_at >= :date_from"; // Assuming activity is tied to user creation for simplicity
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "u.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " WHERE " . implode(" AND ", $conditions);
        $sql .= " GROUP BY u.id, u.username, t.name ORDER BY activity_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 