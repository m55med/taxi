<?php

namespace App\Models\Reports;

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
        $whereConditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $whereConditions[] = "(d.name LIKE ? OR d.phone LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if (!empty($filters['main_system_status'])) {
            $whereConditions[] = "d.main_system_status = ?";
            $params[] = $filters['main_system_status'];
        }
        if (!empty($filters['data_source'])) {
            $whereConditions[] = "d.data_source = ?";
            $params[] = $filters['data_source'];
        }
        if (isset($filters['has_missing_documents']) && $filters['has_missing_documents'] !== '') {
            $whereConditions[] = "d.has_missing_documents = ?";
            $params[] = $filters['has_missing_documents'];
        }
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(d.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(d.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        return ['clause' => $whereClause, 'params' => $params];
    }

    public function getDriversStats($filters = [])
    {
        $baseQuery = "FROM drivers d LEFT JOIN users u ON d.added_by = u.id";
        $filterData = $this->getWhereClause($filters);
        
        $statsSql = "SELECT
                        COUNT(*) as total_drivers,
                        SUM(CASE WHEN d.app_status = 'active' THEN 1 ELSE 0 END) as active_drivers,
                        SUM(CASE WHEN d.main_system_status = 'pending' THEN 1 ELSE 0 END) as pending_drivers,
                        SUM(CASE WHEN d.app_status = 'banned' THEN 1 ELSE 0 END) as banned_drivers,
                        SUM(CASE WHEN d.hold = 1 THEN 1 ELSE 0 END) as on_hold_drivers,
                        SUM(CASE WHEN d.has_missing_documents = 0 THEN 1 ELSE 0 END) as complete_docs,
                        SUM(CASE WHEN d.has_missing_documents = 1 THEN 1 ELSE 0 END) as missing_docs,
                        SUM(CASE WHEN d.data_source = 'form' THEN 1 ELSE 0 END) as source_form,
                        SUM(CASE WHEN d.data_source = 'referral' THEN 1 ELSE 0 END) as source_referral,
                        SUM(CASE WHEN d.data_source = 'telegram' THEN 1 ELSE 0 END) as source_telegram,
                        SUM(CASE WHEN d.data_source = 'staff' THEN 1 ELSE 0 END) as source_staff,
                        SUM(CASE WHEN d.main_system_status = 'waiting_chat' THEN 1 ELSE 0 END) as waiting_chat,
                        SUM(CASE WHEN d.main_system_status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                        SUM(CASE WHEN d.main_system_status = 'rescheduled' THEN 1 ELSE 0 END) as rescheduled,
                        SUM(CASE WHEN d.main_system_status = 'reconsider' THEN 1 ELSE 0 END) as reconsider
                    {$baseQuery} {$filterData['clause']}";

        $stmtStats = $this->db->prepare($statsSql);
        $stmtStats->execute($filterData['params']);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

        // Manually build distribution arrays
        $stats['source_distribution'] = [
            'form' => $stats['source_form'],
            'referral' => $stats['source_referral'],
            'telegram' => $stats['source_telegram'],
            'staff' => $stats['source_staff'],
        ];

        $stats['status_distribution'] = [
            'pending' => $stats['pending_drivers'],
            'waiting_chat' => $stats['waiting_chat'],
            'no_answer' => $stats['no_answer'],
            'rescheduled' => $stats['rescheduled'],
            'reconsider' => $stats['reconsider'],
        ];

        $stats['docs_completion_rate'] = ($stats['total_drivers'] > 0) ? round(($stats['complete_docs'] / $stats['total_drivers']) * 100, 1) : 0;
        
        return $stats;
    }

    public function countDrivers($filters = [])
    {
        $baseQuery = "FROM drivers d LEFT JOIN users u ON d.added_by = u.id LEFT JOIN countries c ON d.country_id = c.id";
        $filterData = $this->getWhereClause($filters);
        $countSql = "SELECT COUNT(d.id) {$baseQuery} {$filterData['clause']}";
        
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($filterData['params']);
        return $stmt->fetchColumn();
    }

    public function getPaginatedDrivers($limit, $offset, $filters = [])
    {
        $baseQuery = "FROM drivers d LEFT JOIN users u ON d.added_by = u.id LEFT JOIN countries c ON d.country_id = c.id";
        $filterData = $this->getWhereClause($filters);

        $listSql = "SELECT 
                        d.id, d.name, d.phone, d.main_system_status, d.app_status, d.data_source, d.created_at,
                        u.username AS added_by_name,
                        c.name as country_name
                    {$baseQuery} {$filterData['clause']} 
                    ORDER BY d.created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        
        $stmtList = $this->db->prepare($listSql);
        $stmtList->execute($filterData['params']);
        return $stmtList->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilterOptions()
    {
        $statuses = $this->db->query("SELECT DISTINCT main_system_status FROM drivers ORDER BY main_system_status")->fetchAll(PDO::FETCH_COLUMN);
        $sources = $this->db->query("SELECT DISTINCT data_source FROM drivers ORDER BY data_source")->fetchAll(PDO::FETCH_COLUMN);
        
        return [
            'statuses' => $statuses,
            'sources' => $sources,
        ];
    }
} 