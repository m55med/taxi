<?php

namespace App\Models\Reports;

use App\Core\Model;
use PDO;

class TripsReportModel extends Model
{
    private const CACHE_LIFETIME = 600; // Cache lifetime in seconds (10 minutes)

    public function getDashboardData(array $filters = []): array
    {
        // Force UTF-8 connection to solve garbled text issues
        $this->db->exec("SET NAMES 'utf8mb4'");

        // Use a simplified cache key for the dashboard view
        $cacheKey = 'trips_dashboard_' . md5(json_encode($filters));
        $cacheDir = APPROOT . '/app/cache/reports';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < self::CACHE_LIFETIME)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        list($whereClause, $params) = $this->buildWhereClause($filters);

        $result = [
            'general_stats' => $this->getGeneralStats($whereClause, $params),
            'driver_kpis' => $this->getSuspiciousDrivers($whereClause, $params),
            'passenger_kpis' => $this->getSuspiciousPassengers($whereClause, $params),
            'cost_kpis' => $this->getCostKpis($whereClause, $params),
            'time_kpis' => $this->getTimeKpis($whereClause, $params),
        ];

        file_put_contents($cacheFile, json_encode($result));
        return $result;
    }

    public function getTripsList(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        list($whereClause, $params) = $this->buildWhereClause($filters);
        $sql = "SELECT * FROM trips WHERE 1=1 " . $whereClause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTripsCount(array $filters = []): int
    {
        list($whereClause, $params) = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) FROM trips WHERE 1=1 " . $whereClause;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function buildWhereClause(array $filters): array
    {
        $sql = "";
        $params = [];
        if (!empty($filters['start_date'])) { $sql .= " AND DATE(created_at) >= ?"; $params[] = $filters['start_date']; }
        if (!empty($filters['end_date'])) { $sql .= " AND DATE(created_at) <= ?"; $params[] = $filters['end_date']; }
        if (!empty($filters['order_status'])) { $sql .= " AND order_status = ?"; $params[] = $filters['order_status']; }
        if (!empty($filters['payment_method'])) { $sql .= " AND payment_method = ?"; $params[] = $filters['payment_method']; }
        if (!empty($filters['requested_vehicle_type'])) { $sql .= " AND requested_vehicle_type LIKE ?"; $params[] = '%' . $filters['requested_vehicle_type'] . '%'; }
        if (!empty($filters['driver_query'])) { $sql .= " AND (driver_name LIKE ? OR driver_id = ?)"; $params[] = '%' . $filters['driver_query'] . '%'; $params[] = $filters['driver_query']; }
        if (!empty($filters['passenger_query'])) { $sql .= " AND (passenger_name LIKE ? OR passenger_phone LIKE ? OR passenger_id = ?)"; $params[] = '%' . $filters['passenger_query'] . '%'; $params[] = '%' . $filters['passenger_query'] . '%'; $params[] = $filters['passenger_query']; }
        return [$sql, $params];
    }
    
    private function getGeneralStats($whereClause, $params): array
    {
        $sql = "SELECT
                    COALESCE(COUNT(*), 0) as total_trips,
                    COALESCE(SUM(CASE WHEN order_status = 'FINISHED_PAID' THEN 1 ELSE 0 END), 0) as completed_trips,
                    COALESCE(SUM(CASE WHEN order_status LIKE 'CANCELLED%' THEN 1 ELSE 0 END), 0) as cancelled_trips,
                    AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at)) as avg_duration_seconds,
                    AVG(CAST(REPLACE(trip_distance_km, ' كم', '') AS DECIMAL(10,2))) as avg_distance_km
                FROM trips WHERE 1=1 " . $whereClause;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getSuspiciousDrivers($whereClause, $params): array
    {
        $sql = "SELECT 
                    driver_id,
                    driver_name,
                    COALESCE(COUNT(*), 0) AS total_trips,
                    COALESCE(SUM(CASE WHEN order_status LIKE 'CANCELLED_BY_DRIVER%' OR cancellation_reason = 'REJECT_SERVICE' THEN 1 ELSE 0 END), 0) AS cancelled_by_driver,
                    COALESCE(ROUND(SUM(CASE WHEN order_status LIKE 'CANCELLED_BY_DRIVER%' OR cancellation_reason = 'REJECT_SERVICE' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2), 0) AS cancellation_rate,
                    COALESCE(SUM(final_cost_omr), 0) as total_revenue,
                    COALESCE(AVG(rating_by_passenger), 0) as avg_rating
                FROM trips
                WHERE driver_id IS NOT NULL " . $whereClause . "
                GROUP BY driver_id, driver_name
                HAVING cancellation_rate > 50 OR cancelled_by_driver > 5
                ORDER BY cancellation_rate DESC
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSuspiciousPassengers($whereClause, $params): array
    {
        $sql = "SELECT 
                    passenger_id,
                    passenger_name,
                    COALESCE(COUNT(*), 0) AS total_requests,
                    COALESCE(SUM(CASE WHEN order_status = 'CANCELLED_BY_PASSENGER' THEN 1 ELSE 0 END), 0) AS cancelled_by_passenger,
                    COALESCE(ROUND(SUM(CASE WHEN order_status = 'CANCELLED_BY_PASSENGER' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2), 0) AS cancellation_rate,
                    COALESCE(SUM(final_cost_omr), 0) AS total_paid
                FROM trips
                WHERE passenger_id IS NOT NULL " . $whereClause . "
                GROUP BY passenger_id, passenger_name
                HAVING cancellation_rate > 40 OR cancelled_by_passenger > 5
                ORDER BY cancellation_rate DESC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCostKpis($whereClause, $params): array
    {
        $sql = "SELECT
                    COALESCE(AVG(final_cost_omr), 0) as avg_cost,
                    COALESCE(SUM(final_cost_omr), 0) as total_revenue,
                    COALESCE(SUM(coupon_discount_omr), 0) as total_discounts,
                    COALESCE(SUM(bonus_amount_omr), 0) as total_bonuses,
                    COALESCE(SUM(tax_omr), 0) as total_tax,
                    COALESCE(SUM(CASE WHEN payment_method = 'CASH' THEN 1 ELSE 0 END), 0) as cash_trips,
                    COALESCE(SUM(CASE WHEN payment_method LIKE '%CARD%' THEN 1 ELSE 0 END), 0) as card_trips
                FROM trips WHERE 1=1 " . $whereClause;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getTimeKpis($whereClause, $params): array
    {
        $sql = "SELECT
                    AVG(TIMESTAMPDIFF(SECOND, created_at, arrived_at)) as avg_arrival_seconds,
                    AVG(TIMESTAMPDIFF(SECOND, arrived_at, loaded_at)) as avg_loading_seconds
                FROM trips WHERE 1=1 AND arrived_at IS NOT NULL AND loaded_at IS NOT NULL " . $whereClause;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFilterOptions(): array
    {
        $statuses = $this->db->query("SELECT DISTINCT order_status FROM trips WHERE order_status IS NOT NULL ORDER BY order_status")->fetchAll(PDO::FETCH_COLUMN);
        $paymentMethods = $this->db->query("SELECT DISTINCT payment_method FROM trips WHERE payment_method IS NOT NULL ORDER BY payment_method")->fetchAll(PDO::FETCH_COLUMN);
        return [
            'statuses' => $statuses,
            'payment_methods' => $paymentMethods
        ];
    }
} 