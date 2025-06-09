<?php

namespace App\Models\Reports;

use App\Core\Database;
use PDO;

class CallsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCallsReport($filters = [])
    {
        $baseSql = "FROM driver_calls dc
                    LEFT JOIN users u ON dc.call_by = u.id
                    LEFT JOIN drivers d ON dc.driver_id = d.id";
        
        $whereConditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $status_mapping = [
                'completed' => 'answered', // Mapping view value to db value
                'no_answer' => 'no_answer',
                'busy' => 'busy'
            ];
            if(array_key_exists($filters['status'], $status_mapping)){
                $whereConditions[] = "dc.call_status = ?";
                $params[] = $status_mapping[$filters['status']];
            }
        }
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(dc.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(dc.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

        // Stats query
        $statsSql = "SELECT
                        COUNT(dc.id) as total_calls,
                        SUM(CASE WHEN dc.call_status = 'answered' THEN 1 ELSE 0 END) as successful_calls
                     $baseSql $whereClause";
        
        $stmtStats = $this->db->prepare($statsSql);
        $stmtStats->execute($params);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

        $stats['failed_calls'] = ($stats['total_calls'] ?? 0) - ($stats['successful_calls'] ?? 0);
        $stats['avg_duration'] = 'N/A'; // Duration column is missing from schema

        // List query
        $listSql = "SELECT
                        dc.id as call_id,
                        d.phone as phone_number,
                        dc.call_status as status,
                        u.username as staff_name,
                        dc.created_at
                    $baseSql $whereClause
                    ORDER BY dc.created_at DESC";
        
        $stmtList = $this->db->prepare($listSql);
        $stmtList->execute($params);
        $callsList = $stmtList->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($callsList as &$call) {
            $call['duration'] = 'N/A';
        }

        return array_merge($stats, ['calls' => $callsList]);
    }
} 