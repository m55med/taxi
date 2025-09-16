<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use PDOException;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class DocumentType
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_types ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create($name)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO document_types (name) VALUES (:name)");
            $stmt->bindParam(':name', $name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM document_types WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findByName($name)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_types WHERE name = :name");
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            if ($result) {

                return convert_dates_for_display($result, ['created_at', 'updated_at']);

            }


            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $name)
    {
        try {
            $stmt = $this->db->prepare("UPDATE document_types SET name = :name WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}