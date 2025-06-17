<?php

namespace App\Models\Reports\TeamLeaderboard;

use App\Core\Database;
use PDO;

class TeamLeaderboardReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getLeaderboard()
    {
        $sql = "SELECT
                    t.name as team_name,
                    COUNT(DISTINCT tm.user_id) as member_count,
                    SUM((
                        SELECT COUNT(DISTINCT dc.id) * 2 FROM driver_calls dc WHERE dc.call_by = tm.user_id
                    ) + (
                        SELECT COUNT(DISTINCT ti.id) * 3 FROM tickets ti WHERE ti.created_by = tm.user_id
                    ) + (
                        SELECT COUNT(DISTINCT tr.id) * 1 FROM ticket_reviews tr WHERE tr.reviewed_by = tm.user_id
                    )) as total_team_score
                FROM
                    teams t
                JOIN team_members tm ON t.id = tm.team_id
                GROUP BY
                    t.id, t.name
                ORDER BY
                    total_team_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 