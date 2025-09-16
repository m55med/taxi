<?php

namespace App\Models\Reports\DriverDocumentsCompliance;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class DriverDocumentsComplianceReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM driver_documents_required ddr
                    JOIN drivers d ON ddr.driver_id = d.id
                    JOIN document_types dt ON ddr.document_type_id = dt.id
                    LEFT JOIN users u ON ddr.updated_by = u.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['driver_id'])) {
            $conditions[] = "ddr.driver_id = :driver_id";
            $params[':driver_id'] = $filters['driver_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "ddr.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['document_type_id'])) {
            $conditions[] = "ddr.document_type_id = :document_type_id";
            $params[':document_type_id'] = $filters['document_type_id'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "ddr.note LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(ddr.updated_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(ddr.updated_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getDocuments($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        
        $sql = "SELECT ddr.id, ddr.status, ddr.note, ddr.updated_at,
                       d.id as driver_id, d.name as driver_name,
                       dt.name as document_type_name,
                       u.username as updated_by_user
                " . $queryParts['base'] 
                . $queryParts['where']
                . " ORDER BY ddr.updated_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    public function getDocumentsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(ddr.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }

    public function getStats()
    {
        $sql = "SELECT status, COUNT(id) as count FROM driver_documents_required GROUP BY status";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    public function getFilterOptions()
    {
        return [
            'drivers' => $this->db->query("SELECT id, name FROM drivers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'document_types' => $this->db->query("SELECT id, name FROM document_types ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'statuses' => $this->db->query("SELECT DISTINCT status FROM driver_documents_required ORDER BY status ASC")->fetchAll(PDO::FETCH_COLUMN)
        ];
    }
} 