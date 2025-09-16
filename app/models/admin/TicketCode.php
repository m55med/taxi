<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

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
            $stmt = $this->db->prepare("SELECT tc.id, tc.name, tc.subcategory_id, ts.name as subcategory_name FROM ticket_codes tc JOIN ticket_subcategories ts ON tc.subcategory_id = ts.id ORDER BY ts.name, tc.name ASC");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
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

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM ticket_codes WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            if ($result) {

                return convert_dates_for_display($result, ['created_at', 'updated_at']);

            }


            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update($id, $name, $subcategory_id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE ticket_codes SET name = :name, subcategory_id = :subcategory_id WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
} 