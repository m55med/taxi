<?php

class DriversReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDriversReport($filters = [])
    {
        $baseQuery = "FROM drivers d LEFT JOIN users u ON d.added_by = u.id";
        
        $whereConditions = [];
        $params = [];

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

        // Fetch drivers list
        $listSql = "SELECT 
                        d.id, d.name, d.phone, d.main_system_status, d.data_source, d.created_at,
                        u.username AS added_by_name
                    {$baseQuery} {$whereClause} ORDER BY d.created_at DESC";
        
        $stmtList = $this->db->prepare($listSql);
        $stmtList->execute($params);
        $driversList = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        // Fetch stats
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
                    {$baseQuery} {$whereClause}";

        $stmtStats = $this->db->prepare($statsSql);
        $stmtStats->execute($params);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

        $stats['docs_completion_rate'] = ($stats['total_drivers'] > 0) ? round(($stats['complete_docs'] / $stats['total_drivers']) * 100, 1) : 0;
        
        return array_merge($stats, ['drivers' => $driversList]);
    }
} 