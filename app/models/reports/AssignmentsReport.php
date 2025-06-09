<?php
namespace App\Models\Reports;

use App\Core\Database;
use PDO;
class AssignmentsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getFilteredQueryParts($filters = [])
    {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['from_staff_id'])) {
            $whereConditions[] = "da.from_user_id = ?";
            $params[] = $filters['from_staff_id'];
        }
        if (!empty($filters['to_staff_id'])) {
            $whereConditions[] = "da.to_user_id = ?";
            $params[] = $filters['to_staff_id'];
        }
        if (!empty($filters['reason'])) {
            $whereConditions[] = "da.note = ?"; // Assuming reason is stored in 'note'
            $params[] = $filters['reason'];
        }
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(da.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(da.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

        return ['where' => $whereClause, 'params' => $params];
    }

    public function getAssignments($filters = [])
    {
        $queryParts = $this->getFilteredQueryParts($filters);
        $sql = "SELECT
                    da.id,
                    d.name AS driver_name,
                    from_user.username AS from_staff_name,
                    to_user.username AS to_staff_name,
                    da.created_at AS assigned_at,
                    da.note AS reason,
                    da.note AS notes
                FROM driver_assignments da
                JOIN drivers d ON da.driver_id = d.id
                JOIN users from_user ON da.from_user_id = from_user.id
                JOIN users to_user ON da.to_user_id = to_user.id
                {$queryParts['where']}
                ORDER BY da.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary($filters = [])
    {
        $queryParts = $this->getFilteredQueryParts($filters);
        $sql = "SELECT COUNT(*) as total_assignments FROM driver_assignments da {$queryParts['where']}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // More complex stats are placeholders for now
        $summary['most_active_staff'] = 'N/A';
        $summary['most_active_count'] = 0;
        $summary['most_common_reason'] = 'N/A';

        return $summary;
    }

    public function getStaffMembers()
    {
        $sql = "SELECT id, username FROM users WHERE role_id IN (SELECT id FROM roles WHERE name IN ('admin', 'employee', 'agent', 'quality_manager', 'developer'))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 