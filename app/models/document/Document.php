<?php

namespace App\Models\Document;

use App\Core\Database;
use PDO;
use Exception;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Document
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all document requirements for a specific driver, including their status.
     * Joins with document_types to get the name of each document.
     */
    public function getDriverDocuments($driverId)
    {
        $sql = "
            SELECT
                ddr.id,
                ddr.document_type_id,
                dt.name,
                ddr.status,
                ddr.note,
                ddr.updated_at,
                u.username AS updated_by
            FROM driver_documents_required ddr
            JOIN document_types dt ON ddr.document_type_id = dt.id
            LEFT JOIN users u ON ddr.updated_by = u.id
            WHERE ddr.driver_id = :driver_id
            ORDER BY dt.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':driver_id' => $driverId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    /**
     * Gets all available document types from the database.
     */
    public function getAllDocumentTypes()
    {
        $sql = "SELECT id, name, is_required FROM document_types ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    /**
     * Get the documents that are not yet assigned to a driver.
     */
    public function getUnassignedDocumentTypes($driverId)
    {
        $sql = "
            SELECT dt.id, dt.name
            FROM document_types dt
            WHERE NOT EXISTS (
                SELECT 1
                FROM driver_documents_required ddr
                WHERE ddr.document_type_id = dt.id AND ddr.driver_id = :driver_id
            )
            ORDER BY dt.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':driver_id' => $driverId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }


    /**
     * Adds or updates a document requirement for a driver.
     */
    public function upsertDriverDocument($driverId, $docTypeId, $status, $note)
    {
        // First, check if the record exists
        $checkSql = "SELECT id FROM driver_documents_required WHERE driver_id = :driver_id AND document_type_id = :doc_type_id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':driver_id' => $driverId, ':doc_type_id' => $docTypeId]);

        $params = [
            ':driver_id' => $driverId,
            ':doc_type_id' => $docTypeId,
            ':status' => $status,
            ':note' => $note,
            ':user_id' => $_SESSION['user_id'] ?? null
        ];

        if ($checkStmt->fetch()) {
            // Update existing record
            $sql = "
                UPDATE driver_documents_required
                SET status = :status, note = :note, updated_by = :user_id, updated_at = UTC_TIMESTAMP()
                WHERE driver_id = :driver_id AND document_type_id = :doc_type_id
            ";
        } else {
            // Insert new record
            $sql = "
                INSERT INTO driver_documents_required (driver_id, document_type_id, status, note, updated_by, updated_at)
                VALUES (:driver_id, :doc_type_id, :status, :note, :user_id, UTC_TIMESTAMP())
            ";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function addDriverDocument($driverId, $docTypeId)
    {
        $sql = "INSERT INTO driver_documents_required (driver_id, document_type_id, status, updated_by) VALUES (:driver_id, :doc_type_id, 'missing', :user_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId,
            ':doc_type_id' => $docTypeId,
            ':user_id' => $_SESSION['user_id'] ?? null
        ]);
        $id = $this->db->lastInsertId();
        return $this->getDriverDocumentById($id);
    }

    public function updateDriverDocument($driverDocId, $status, $note)
    {
        $sql = "UPDATE driver_documents_required SET status = :status, note = :note, updated_by = :user_id, updated_at = UTC_TIMESTAMP() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':note' => $note,
            ':user_id' => $_SESSION['user_id'] ?? null,
            ':id' => $driverDocId
        ]);
        return $this->getDriverDocumentById($driverDocId);
    }

    public function removeDriverDocumentById($driverDocId)
    {
        $sql = "DELETE FROM driver_documents_required WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $driverDocId]);
    }

    public function getDriverDocumentById($id)
    {
        $sql = "
            SELECT
                ddr.id,
                ddr.document_type_id,
                dt.name,
                ddr.status,
                ddr.note,
                ddr.updated_at,
                u.username AS updated_by
            FROM driver_documents_required ddr
            JOIN document_types dt ON ddr.document_type_id = dt.id
            LEFT JOIN users u ON ddr.updated_by = u.id
            WHERE ddr.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        if ($result) {

            return convert_dates_for_display($result, ['created_at', 'updated_at']);

        }


        return $result;
    }

    public function getDriverIdByDriverDocumentId($driverDocId)
    {
        $sql = "SELECT driver_id FROM driver_documents_required WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $driverDocId]);
        return $stmt->fetchColumn();
    }

    /**
     * Recalculates and updates the `has_missing_documents` flag for the driver.
     */
    public function updateDriverMissingDocsFlag($driverId)
    {
        // Logic to determine if any *required* documents are still missing.
        $sql = "
            SELECT COUNT(*)
            FROM document_types dt
            WHERE dt.is_required = 1
            AND NOT EXISTS (
                SELECT 1 FROM driver_documents_required ddr
                WHERE ddr.driver_id = :driver_id
                AND ddr.document_type_id = dt.id
                AND ddr.status = 'submitted' -- Or whatever status counts as 'not missing'
            )
        ";
        
        // A document is considered "missing" if its status is not 'submitted'
        // or if there is no entry for it in driver_documents_required.
        $checkMissingSql = "
            SELECT EXISTS (
                SELECT 1
                FROM document_types dt
                WHERE dt.is_required = 1
                AND (
                    -- The document is explicitly marked as missing or rejected
                    EXISTS (
                        SELECT 1 FROM driver_documents_required ddr
                        WHERE ddr.driver_id = :driver_id_1
                        AND ddr.document_type_id = dt.id
                        AND ddr.status IN ('missing', 'rejected')
                    )
                    OR
                    -- There is no entry for this required document at all
                    NOT EXISTS (
                        SELECT 1 FROM driver_documents_required ddr
                        WHERE ddr.driver_id = :driver_id_2
                        AND ddr.document_type_id = dt.id
                    )
                )
            )
        ";
        $stmt = $this->db->prepare($checkMissingSql);
        $stmt->execute([
            ':driver_id_1' => $driverId,
            ':driver_id_2' => $driverId
        ]);
        $hasMissing = (bool) $stmt->fetchColumn();

        // Update the flag in the drivers table
        $updateSql = "UPDATE drivers SET has_missing_documents = :has_missing WHERE id = :driver_id";
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->execute([
            ':has_missing' => (int)$hasMissing,
            ':driver_id' => $driverId
        ]);
    }
}