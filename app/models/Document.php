<?php

class Document
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDocumentsReport($filters = [])
    {
        try {
            $sql = "SELECT 
                        dr.*,
                        d.name as driver_name,
                        d.phone as driver_phone,
                        dt.name as document_type,
                        u.username as updated_by_name
                    FROM driver_documents_required dr
                    LEFT JOIN drivers d ON dr.driver_id = d.id
                    LEFT JOIN document_types dt ON dr.document_type_id = dt.id
                    LEFT JOIN users u ON dr.updated_by = u.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['document_type_id'])) {
                $sql .= " AND dr.document_type_id = ?";
                $params[] = $filters['document_type_id'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND dr.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['updated_by'])) {
                $sql .= " AND dr.updated_by = ?";
                $params[] = $filters['updated_by'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(dr.updated_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(dr.updated_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY dr.updated_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDocumentsReport: " . $e->getMessage());
            return [];
        }
    }

    public function getDocumentTypes()
    {
        try {
            $sql = "SELECT * FROM document_types ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDocumentTypes: " . $e->getMessage());
            return [];
        }
    }
} 