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

    public function getDriverDocuments($driverId, $keyedById = false)
    {
        try {
            $sql = "
                SELECT 
                    ddr.id,
                    ddr.driver_id,
                    ddr.document_type_id,
                    ddr.status,
                    ddr.note,
                    ddr.updated_at,
                    ddr.updated_by,
                    dt.name AS name, -- Alias document type name as 'name' for consistency
                    u.username AS updated_by_name
                FROM 
                    driver_documents_required ddr
                JOIN 
                    document_types dt ON ddr.document_type_id = dt.id
                LEFT JOIN 
                    users u ON ddr.updated_by = u.id
                WHERE 
                    ddr.driver_id = :driver_id
                ORDER BY 
                    dt.name ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['driver_id' => $driverId]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($keyedById) {
                $keyedDocuments = [];
                foreach ($documents as $doc) {
                    $keyedDocuments[$doc['document_type_id']] = $doc;
                }
                return $keyedDocuments;
            }

            return $documents;

        } catch (PDOException $e) {
            error_log("Error in getDriverDocuments: " . $e->getMessage());
            return [];
        }
    }

    public function updateDriverDocuments($driverId, $documentsData)
    {
        try {
            $this->db->beginTransaction();

            // 1. Get all current document IDs for the driver from the database.
            $stmt = $this->db->prepare("SELECT document_type_id FROM driver_documents_required WHERE driver_id = :driver_id");
            $stmt->execute([':driver_id' => $driverId]);
            $currentDocIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch as a flat array of IDs.

            // 2. Get all document IDs submitted from the frontend.
            $submittedDocIds = array_map(function($doc) { return $doc['id']; }, $documentsData);

            // 3. Determine which documents to DELETE.
            // These are documents that exist in the DB but not in the submission.
            $docsToRemove = array_diff($currentDocIds, $submittedDocIds);
            if (!empty($docsToRemove)) {
                $inClause = implode(',', array_fill(0, count($docsToRemove), '?'));
                $removeStmt = $this->db->prepare("DELETE FROM driver_documents_required WHERE driver_id = ? AND document_type_id IN ($inClause)");
                $removeStmt->execute(array_merge([$driverId], $docsToRemove));
            }

            // 4. Prepare statements for INSERT and UPDATE.
            $upsertStmt = $this->db->prepare("
                INSERT INTO driver_documents_required (driver_id, document_type_id, status, note, updated_by, updated_at)
                VALUES (:driver_id, :doc_id, :status, :note, :user_id, NOW())
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    note = VALUES(note),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()
            ");

            // 5. Loop through submitted data and perform UPSERT (INSERT or UPDATE).
            foreach ($documentsData as $doc) {
                $upsertStmt->execute([
                    ':driver_id' => $driverId,
                    ':doc_id'    => $doc['id'],
                    ':status'    => $doc['status'],
                    ':note'      => $doc['note'] ?? '',
                    ':user_id'   => $_SESSION['user_id']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error updating driver documents: " . $e->getMessage());
            throw $e; // Re-throw to be caught by controller
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