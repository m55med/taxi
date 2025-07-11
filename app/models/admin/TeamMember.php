<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;

class TeamMember
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    tm.id, 
                    u.username as user_name, 
                    t.name as team_name 
                FROM team_members tm
                JOIN users u ON tm.user_id = u.id
                JOIN teams t ON tm.team_id = t.id
                ORDER BY t.name, u.username ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($user_id, $team_id)
    {
        try {
            // Check if the user is already in the team
            $checkStmt = $this->db->prepare("SELECT id FROM team_members WHERE user_id = :user_id AND team_id = :team_id");
            $checkStmt->execute([':user_id' => $user_id, ':team_id' => $team_id]);
            if ($checkStmt->fetch()) {
                // Member already exists in the team
                return false;
            }
            
            $stmt = $this->db->prepare("INSERT INTO team_members (user_id, team_id) VALUES (:user_id, :team_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM team_members WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isUserInAnyTeam($userId)
    {
        $sql = "SELECT COUNT(*) FROM team_members WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Assigns a user to a team.
     * If the user is already in a team, it moves them.
     * If not, it adds them.
     *
     * @param int $userId
     * @param int $teamId
     * @return bool
     */
    public function assignUserToTeam(int $userId, int $teamId): bool
    {
        if ($this->isUserInAnyTeam($userId)) {
            // User is in a team, so UPDATE (move) them
            $sql = "UPDATE team_members SET team_id = :team_id, joined_at = NOW() WHERE user_id = :user_id";
        } else {
            // User is not in a team, so INSERT (add) them
            $sql = "INSERT INTO team_members (user_id, team_id) VALUES (:user_id, :team_id)";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log error
            error_log("Error in assignUserToTeam: " . $e->getMessage());
            return false;
        }
    }

    public function getUnassignedUsers()
    {
        try {
            // This query selects users who are not in the team_members table.
            // It also excludes roles that typically shouldn't be in teams like 'admin' or 'developer'.
            $stmt = $this->db->prepare("
                SELECT u.id, u.username
                FROM users u
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE tm.user_id IS NULL 
                  AND u.status = 'active'
                  AND r.name NOT IN ('admin', 'developer')
                ORDER BY u.username ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error if needed
            error_log("Error fetching unassigned users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the current team ID for a given user.
     *
     * @param int $userId The ID of the user.
     * @return int|null The team ID, or null if the user is not in a team.
     */
    public static function getCurrentTeamIdForUser(int $userId): ?int
    {
        $db = Database::getInstance();
        $sql = "SELECT team_id FROM team_members WHERE user_id = :user_id ORDER BY joined_at DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['team_id'] : null;
    }
} 