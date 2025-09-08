<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;

class TicketSubCategory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM ticket_subcategories ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($name, $category_id)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO ticket_subcategories (name, category_id) VALUES (:name, :category_id)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':category_id', $category_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ticket_subcategories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM ticket_subcategories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update($id, $name, $category_id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE ticket_subcategories SET name = :name, category_id = :category_id WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
} 