<?php

class AnalyticsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getConversionRates($filters = [])
    {
        $sql = "SELECT 
                    data_source, 
                    COUNT(id) as total_drivers, 
                    SUM(CASE WHEN main_system_status = 'completed' THEN 1 ELSE 0 END) as completed_drivers
                FROM drivers";

        $whereConditions = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= " GROUP BY data_source";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($rates as &$rate) {
            $rate['conversion_rate'] = ($rate['total_drivers'] > 0) ? 
                round(($rate['completed_drivers'] / $rate['total_drivers']) * 100, 1) : 0;
        }
        
        return $rates;
    }

    public function getCallAnalysis($filters = [])
    {
        // Placeholder data due to missing schema fields (call outcomes like 'interested', call duration)
        return [
            'outcomes' => ['interested' => 0, 'not_interested' => 0, 'callback' => 0, 'no_answer' => 0],
            'duration' => ['average' => 0, 'max' => 0, 'min' => 0],
            'staff_performance' => []
        ];
    }
} 