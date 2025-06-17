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
                SELECT t.id, t.name, u.username as team_leader_name 
                FROM teams t
                JOIN users u ON t.team_leader_id = u.id
                ORDER BY t.name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
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