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
            $stmt = $this->db->query("
                SELECT 
                    tm.id, 
                    u.username as user_name, 
                    t.name as team_name 
                FROM team_members tm
                JOIN users u ON tm.user_id = u.id
                JOIN teams t ON tm.team_id = t.id
                ORDER BY t.name, u.username ASC
            ");
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
} 