<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class Discussion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetches all discussions relevant to a specific user.
     * - Admins/Developers/QM see all discussions.
     * - Team Leaders see discussions on their tickets or opened by them.
     * - Agents see discussions they opened or were involved in.
     */
    public function getDiscussionsForUser($userId, $role)
    {
        $sql = "
            SELECT 
                d.id as discussion_id,
                d.reason,
                d.status,
                d.created_at,
                t.id as ticket_id,
                t.ticket_number,
                opener.username as opener_username,
                (SELECT COUNT(*) FROM ticket_discussion_objections WHERE discussion_id = d.id) as replies_count,
                (SELECT MAX(created_at) FROM ticket_discussion_objections WHERE discussion_id = d.id) as last_reply_at
            FROM ticket_discussions d
            JOIN tickets t ON d.ticket_id = t.id
            JOIN users opener ON d.opened_by = opener.id
        ";

        $params = [];

        // Tailor the query based on user role
        if (!in_array($role, ['admin', 'developer', 'quality_manager'])) {
            $sql .= " LEFT JOIN team_members tm ON t.created_by = tm.user_id";
            $sql .= " LEFT JOIN teams team ON tm.team_id = team.id";
            
            if ($role === 'Team_leader') {
                // Team leaders see discussions on their team's tickets or ones they opened
                $sql .= " WHERE (team.team_leader_id = :user_id OR d.opened_by = :user_id)";
            } else { // For agents or other roles
                // Agents see discussions they opened or are on their tickets
                $sql .= " WHERE (t.created_by = :user_id OR d.opened_by = :user_id)";
            }
             $params[':user_id'] = $userId;
        }

        $sql .= " GROUP BY d.id ORDER BY d.status ASC, d.updated_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDiscussionsForUser: " . $e->getMessage());
            return [];
        }
    }
} 