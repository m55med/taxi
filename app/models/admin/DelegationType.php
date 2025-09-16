<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class DelegationType
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $query = "SELECT * FROM delegation_types ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (\PDOException $e) {
            // In a real application, log the error
            return [];
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT * FROM delegation_types WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            if ($result) {

                return convert_dates_for_display($result, ['created_at', 'updated_at']);

            }


            return $result;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function create($name, $percentage)
    {
        try {
            $query = "INSERT INTO delegation_types (name, percentage) VALUES (:name, :percentage)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':percentage', $percentage);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function update($id, $name, $percentage)
    {
        try {
            $query = "UPDATE delegation_types SET name = :name, percentage = :percentage WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':percentage', $percentage);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $query = "DELETE FROM delegation_types WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
} 