<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;

class Team
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
                SELECT t.id, t.name, t.team_leader_id, u.username as team_leader_name 
                FROM teams t
                JOIN users u ON t.team_leader_id = u.id
                ORDER BY t.name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllTeamLeaders()
    {
        try {
            // This query joins users with user_roles and roles tables 
            // to find users who are designated as 'Team Leader'.
            $stmt = $this->db->query("
                SELECT u.id, u.username
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE r.role_name = 'Team Leader'
                ORDER BY u.username ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error and return empty array on failure
            error_log("Error fetching team leaders: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findByName($name)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM teams WHERE name = :name");
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findByLeaderId($leaderId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM teams WHERE team_leader_id = :leader_id");
            $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function create($name, $team_leader_id)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO teams (name, team_leader_id) VALUES (:name, :team_leader_id)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':team_leader_id', $team_leader_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $name, $team_leader_id)
    {
        try {
            $sql_parts = [];
            $params = [];

            if ($name !== null) {
                $sql_parts[] = "name = :name";
                $params[':name'] = $name;
            }

            if ($team_leader_id !== null) {
                $sql_parts[] = "team_leader_id = :team_leader_id";
                $params[':team_leader_id'] = $team_leader_id;
            }

            if (empty($sql_parts)) {
                return true; // No changes to be made
            }

            $sql = "UPDATE teams SET " . implode(', ', $sql_parts) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log($e->getMessage()); // Log error
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM teams WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
} 