<?php

namespace App\Models\Call;

use App\Core\Model;
use PDO;
use PDOException;

class Documents extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollBack()
    {
        return $this->db->rollBack();
    }

    public function getAllTypes()
    {
        try {
            $stmt = $this->db->query("SELECT id, name, is_required FROM document_types ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllTypes: " . $e->getMessage());
            return [];
        }
    }

    public function getDriverDocuments($driverId)
    {
        try {
            $sql = "
                SELECT 
                    dt.id,
                    dt.name,
                    dt.is_required,
                    COALESCE(ddr.status, 'submitted') as status,
                    COALESCE(ddr.note, '') as note,
                    ddr.updated_at,
                    u.username as updated_by_name
                FROM document_types dt
                LEFT JOIN driver_documents_required ddr ON dt.id = ddr.document_type_id AND ddr.driver_id = :driver_id
                LEFT JOIN users u ON ddr.updated_by = u.id
                ORDER BY dt.name ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':driver_id' => $driverId]);
            
            $documents = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['is_required'] = (bool)$row['is_required'];
                $documents[$row['id']] = $row;
            }
            
            return $documents;
        } catch (PDOException $e) {
            error_log("Error in getDriverDocuments: " . $e->getMessage());
            return [];
        }
    }

    public function updateDocument($driverId, $documentId, $status, $note)
    {
        try {
            if ($status === 'submitted' && empty($note)) {
                $sql = "
                    DELETE FROM driver_documents_required 
                    WHERE driver_id = :driver_id 
                    AND document_type_id = :doc_id
                ";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    ':driver_id' => $driverId,
                    ':doc_id' => $documentId
                ]);
            } else {
                $sql = "
                    INSERT INTO driver_documents_required 
                        (driver_id, document_type_id, status, note, updated_by) 
                    VALUES 
                        (:driver_id, :doc_id, :status, :note, :user_id)
                    ON DUPLICATE KEY UPDATE 
                        status = VALUES(status),
                        note = VALUES(note),
                        updated_by = VALUES(updated_by)
                ";

                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    ':driver_id' => $driverId,
                    ':doc_id' => $documentId,
                    ':status' => $status,
                    ':note' => $note,
                    ':user_id' => $_SESSION['user_id']
                ]);
            }

            if ($success) {
                $this->updateDriverDocumentStatus($driverId);
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error in updateDocument: " . $e->getMessage());
            return false;
        }
    }

    private function updateDriverDocumentStatus($driverId)
    {
        try {
            $sql = "
                UPDATE drivers d
                SET has_missing_documents = EXISTS (
                    SELECT 1 
                    FROM document_types dt
                    LEFT JOIN driver_documents_required ddr 
                        ON dt.id = ddr.document_type_id 
                        AND ddr.driver_id = :driver_id
                    WHERE dt.is_required = 1 
                    AND (ddr.status IS NULL OR ddr.status = 'missing')
                )
                WHERE d.id = :driver_id
            ";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':driver_id' => $driverId]);
        } catch (PDOException $e) {
            error_log("Error in updateDriverDocumentStatus: " . $e->getMessage());
            return false;
        }
    }
} 