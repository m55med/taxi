<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;

class TicketCode
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT tc.id, tc.name, ts.name as subcategory_name FROM ticket_codes tc JOIN ticket_subcategories ts ON tc.subcategory_id = ts.id ORDER BY ts.name, tc.name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($name, $subcategory_id)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO ticket_codes (name, subcategory_id) VALUES (:name, :subcategory_id)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ticket_codes WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
} 