<?php

namespace App\Models\Reports\Calls;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class CallsReport
{
    private $db;
    private $baseQuery = "FROM driver_calls dc
                        LEFT JOIN users u ON dc.call_by = u.id
                        LEFT JOIN drivers d ON dc.driver_id = d.id";

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getWhereClause($filters)
    {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $status_mapping = [
                'completed' => 'answered',
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
        
        return [
            'where' => !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "",
            'params' => $params
        ];
    }

    public function getCallsStats($filters = [])
    {
        $queryParts = $this->getWhereClause($filters);
        $statsSql = "SELECT
                        COUNT(dc.id) as total_calls,
                        SUM(CASE WHEN dc.call_status = 'answered' THEN 1 ELSE 0 END) as successful_calls
                     {$this->baseQuery} {$queryParts['where']}";
        
        $stmtStats = $this->db->prepare($statsSql);
        $stmtStats->execute($queryParts['params']);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

        $stats['failed_calls'] = ($stats['total_calls'] ?? 0) - ($stats['successful_calls'] ?? 0);
        $stats['avg_duration'] = 'N/A';

        return $stats;
    }

    public function countCalls($filters = [])
    {
        $queryParts = $this->getWhereClause($filters);
        $sql = "SELECT COUNT(dc.id) {$this->baseQuery} {$queryParts['where']}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return $stmt->fetchColumn();
    }
    
    public function getPaginatedCalls($limit, $offset, $filters = [])
    {
        $queryParts = $this->getWhereClause($filters);
        $listSql = "SELECT
                        dc.id as call_id,
                        d.phone as phone_number,
                        dc.call_status as status,
                        u.username as staff_name,
                        dc.created_at
                    {$this->baseQuery} {$queryParts['where']}
                    ORDER BY dc.created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        
        $stmtList = $this->db->prepare($listSql);
        $stmtList->execute($queryParts['params']);
        $callsList = $stmtList->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($callsList as &$call) {
            $call['duration'] = 'N/A';
        }

        return $callsList;
    }
} 