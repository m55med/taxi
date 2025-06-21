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
            // Step 1: Fetch all document types and create a name lookup map for efficiency.
            $allTypesStmt = $this->db->prepare("SELECT id, name FROM document_types");
            $allTypesStmt->execute();
            $docTypeMap = $allTypesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Step 2: Fetch all users and create a name lookup map.
            $allUsersStmt = $this->db->prepare("SELECT id, name FROM users");
            $allUsersStmt->execute();
            $userMap = $allUsersStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Step 3: Fetch the raw required document data for the specified driver.
            $sql = "
                SELECT 
                    id,
                    driver_id,
                    document_type_id,
                    status,
                    note,
                    updated_at,
                    updated_by
                FROM driver_documents_required
                WHERE driver_id = :driver_id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['driver_id' => $driverId]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Step 4: Manually enrich the documents with names from the maps.
            $enrichedDocuments = [];
            foreach ($documents as $doc) {
                // Add the document type name.
                $doc['name'] = $docTypeMap[$doc['document_type_id']] ?? 'مستند غير معروف';
                // Add the updater's name.
                $doc['updated_by_name'] = $userMap[$doc['updated_by']] ?? 'غير معروف';
                $enrichedDocuments[] = $doc;
            }

            // Sort by the newly added name field.
            usort($enrichedDocuments, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            if ($keyedById) {
                $keyedDocuments = [];
                foreach ($enrichedDocuments as $doc) {
                    $keyedDocuments[$doc['document_type_id']] = $doc;
                }
                return $keyedDocuments;
            }

            return $enrichedDocuments;

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