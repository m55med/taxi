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
            $stmt = $this->db->query("SELECT * FROM ticket_subcategories ORDER BY name ASC");
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
} 