<?php

namespace App\Models\Document;

use App\Core\Database;
use PDO;
use PDOException;

class Document
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDriverDocuments($driverId)
    {
        try {
            $sql = "
                SELECT 
                    ddr.document_type_id,
                    dt.name,
                    ddr.status,
                    ddr.note,
                    ddr.updated_at,
                    u.name as updated_by_name
                FROM driver_documents_required ddr
                INNER JOIN document_types dt ON ddr.document_type_id = dt.id
                LEFT JOIN users u ON ddr.updated_by = u.id
                WHERE ddr.driver_id = :driver_id AND ddr.status = 'submitted'
                ORDER BY dt.name
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['driver_id' => $driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDriverDocuments: " . $e->getMessage());
            return [];
        }
    }

    public function getAllTypes()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_types ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllTypes: " . $e->getMessage());
            return [];
        }
    }

    public function getDocumentsReport($filters)
    {
        $sql = "SELECT 
                    d.name as driver_name,
                    dt.name as document_type,
                    ddr.* 
                FROM driver_documents_required ddr
                JOIN drivers d ON ddr.driver_id = d.id
                JOIN document_types dt ON ddr.document_type_id = dt.id";
        // Apply filters
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 