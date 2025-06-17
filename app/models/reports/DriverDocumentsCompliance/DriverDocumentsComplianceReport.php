<?php

namespace App\Models\Reports\DriverDocumentsCompliance;

use App\Core\Database;
use PDO;

class DriverDocumentsComplianceReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDocumentsCompliance($filters)
    {
        $sql = "SELECT
                    ddr.id,
                    d.name as driver_name,
                    dt.name as document_type_name,
                    ddr.status,
                    ddr.note,
                    u.username as updated_by_user,
                    ddr.updated_at
                FROM
                    driver_documents_required ddr
                JOIN drivers d ON ddr.driver_id = d.id
                JOIN document_types dt ON ddr.document_type_id = dt.id
                LEFT JOIN users u ON ddr.updated_by = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "ddr.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['document_type_id'])) {
            $conditions[] = "ddr.document_type_id = :document_type_id";
            $params[':document_type_id'] = $filters['document_type_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY ddr.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For filters dropdown
        $document_types = $this->db->query("SELECT id, name FROM document_types ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'documents' => $documents,
            'document_types' => $document_types,
        ];
    }
} 