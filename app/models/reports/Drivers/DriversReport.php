<?php

namespace App\Models\Reports\Drivers;

use App\Core\Database;
use PDO;

class DriversReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getWhereClause($filters)
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(d.name LIKE :search OR d.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['main_system_status'])) {
            $conditions[] = "d.main_system_status = :status";
            $params[':status'] = $filters['main_system_status'];
        }
        if (!empty($filters['data_source'])) {
            $conditions[] = "d.data_source = :source";
            $params[':source'] = $filters['data_source'];
        }
        if (isset($filters['has_missing_documents']) && $filters['has_missing_documents'] !== '') {
            $conditions[] = "d.has_missing_documents = :missing_docs";
            $params[':missing_docs'] = $filters['has_missing_documents'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(d.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(d.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereClause = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['clause' => $whereClause, 'params' => $params];
    }

    public function getDriversStats($filters = [])
    {
        $filterData = $this->getWhereClause($filters);
        
        $statsSql = "SELECT
                        COUNT(d.id) as total_drivers,
                        SUM(CASE WHEN d.app_status = 'active' THEN 1 ELSE 0 END) as active_drivers,
                        SUM(CASE WHEN d.main_system_status = 'completed' THEN 1 ELSE 0 END) as completed_drivers,
                        SUM(CASE WHEN d.app_status = 'banned' THEN 1 ELSE 0 END) as banned_drivers,
                        (SELECT COUNT(DISTINCT data_source) FROM drivers) as total_sources
                    FROM drivers d
                    {$filterData['clause']}";

        $stmtStats = $this->db->prepare($statsSql);
        $stmtStats->execute($filterData['params']);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
        
        // Fetch source distribution for charts
        $sourceSql = "SELECT data_source, COUNT(id) as count FROM drivers d {$filterData['clause']} GROUP BY data_source";
        $stmtSource = $this->db->prepare($sourceSql);
        $stmtSource->execute($filterData['params']);
        $stats['source_distribution'] = $stmtSource->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Fetch status distribution for charts
        $statusSql = "SELECT main_system_status, COUNT(id) as count FROM drivers d {$filterData['clause']} GROUP BY main_system_status";
        $stmtStatus = $this->db->prepare($statusSql);
        $stmtStatus->execute($filterData['params']);
        $stats['status_distribution'] = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }

    public function countDrivers($filters = [])
    {
        $filterData = $this->getWhereClause($filters);
        $countSql = "SELECT COUNT(d.id) FROM drivers d {$filterData['clause']}";
        
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($filterData['params']);
        return (int)$stmt->fetchColumn();
    }

    public function getPaginatedDrivers($limit, $offset, $filters = [])
    {
        $filterData = $this->getWhereClause($filters);

        $listSql = "SELECT 
                        d.id, d.name, d.phone, d.main_system_status, d.data_source, d.created_at,
                        d.app_status, d.has_missing_documents,
                        u.username AS added_by_name,
                        c.name as country_name
                    FROM drivers d 
                    LEFT JOIN users u ON d.added_by = u.id
                    LEFT JOIN countries c ON d.country_id = c.id
                    {$filterData['clause']} 
                    ORDER BY d.created_at DESC
                    LIMIT :limit OFFSET :offset";
        
        $stmtList = $this->db->prepare($listSql);
        $stmtList->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmtList->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($filterData['params'] as $key => &$val) {
            $stmtList->bindParam($key, $val);
        }
        
        $stmtList->execute();
        return $stmtList->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFilterOptions()
    {
        $status_options = $this->db->query("SELECT DISTINCT main_system_status FROM drivers ORDER BY main_system_status ASC")->fetchAll(PDO::FETCH_COLUMN);
        $source_options = $this->db->query("SELECT DISTINCT data_source FROM drivers ORDER BY data_source ASC")->fetchAll(PDO::FETCH_COLUMN);
        return [
            'statuses' => $status_options,
            'sources' => $source_options,
        ];
    }
} 