<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

class Team extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTeamLeaders()
    {
        // Team leaders are users who are listed as a team_leader_id in the 'teams' table.
        $sql = "SELECT DISTINCT u.id, u.username 
                FROM users u
                JOIN teams t ON u.id = t.team_leader_id 
                ORDER BY u.username ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds the team leader for a given team member.
     *
     * @param int $userId The ID of the team member.
     * @return int|null The ID of the team leader, or null if not found.
     */
    public function findLeaderByMemberId($userId)
    {
        $sql = "SELECT t.team_leader_id
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['team_leader_id'] : null;
    }
} 