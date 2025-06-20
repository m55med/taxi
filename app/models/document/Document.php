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

    public function updateDriverDocuments($driverId, $submittedDocIds, $notes)
    {
        try {
            $this->db->beginTransaction();

            // 1. Get current documents for the driver
            $stmt = $this->db->prepare("SELECT document_type_id FROM driver_documents_required WHERE driver_id = :driver_id AND status = 'submitted'");
            $stmt->execute([':driver_id' => $driverId]);
            $currentDocIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. Determine which documents to remove
            $docsToRemove = array_diff($currentDocIds, $submittedDocIds);
            if (!empty($docsToRemove)) {
                $inClause = implode(',', array_fill(0, count($docsToRemove), '?'));
                $removeStmt = $this->db->prepare("DELETE FROM driver_documents_required WHERE driver_id = ? AND document_type_id IN ($inClause)");
                $removeStmt->execute(array_merge([$driverId], $docsToRemove));
            }

            // 3. Determine which documents to add and which to update
            $docsToAdd = array_diff($submittedDocIds, $currentDocIds);
            $docsToUpdate = array_intersect($currentDocIds, $submittedDocIds);

            // Add new documents
            if (!empty($docsToAdd)) {
                $addStmt = $this->db->prepare("INSERT INTO driver_documents_required (driver_id, document_type_id, status, note, updated_by) VALUES (?, ?, 'submitted', ?, ?)");
                foreach ($docsToAdd as $docId) {
                    $note = $notes[$docId] ?? '';
                    $addStmt->execute([$driverId, $docId, $note, $_SESSION['user_id']]);
                }
            }
            
            // Update existing documents (specifically their notes)
            if (!empty($docsToUpdate)) {
                $updateStmt = $this->db->prepare("UPDATE driver_documents_required SET note = ?, updated_by = ?, updated_at = NOW() WHERE driver_id = ? AND document_type_id = ?");
                foreach ($docsToUpdate as $docId) {
                     if (isset($notes[$docId])) {
                        $updateStmt->execute([$notes[$docId], $_SESSION['user_id'], $driverId, $docId]);
                    }
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
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