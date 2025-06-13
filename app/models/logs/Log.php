<?php

namespace App\Models\Logs;

use App\Core\Database;
use PDO;
use PDOException;

class Log
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTeamIdForLeader($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM teams WHERE team_leader_id = :leader_id");
            $stmt->execute([':leader_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result ? $result->id : null;
        } catch (PDOException $e) {
            error_log("Error in getTeamIdForLeader: " . $e->getMessage());
            return null;
        }
    }

    public function getUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, username FROM users WHERE status = 'active' ORDER BY username ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getUsers: " . $e->getMessage());
            return [];
        }
    }

    public function getTeams()
    {
        try {
            $stmt = $this->db->prepare("SELECT t.id, t.name, u.username as leader_name 
                                        FROM teams t 
                                        JOIN users u ON t.team_leader_id = u.id 
                                        ORDER BY t.name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getTeams: " . $e->getMessage());
            return [];
        }
    }

    public function getActivities($filters, $limit = 50, $offset = 0)
    {
        $params = [];
        $countParams = [];
        
        $ticketsQuery = "
            SELECT 
                'ticket' as activity_type, t.id as activity_id, t.ticket_number as details_primary,
                CONCAT('تصنيف: ', tc.name, ' / ', ts.name, ' / ', tco.name) as details_secondary,
                t.created_at as activity_date, t.created_by as user_id, u.username,
                tm.team_id, teams.name as team_name, t.id as link_id, 'tickets/details' as link_prefix
            FROM tickets t
            JOIN users u ON t.created_by = u.id
            JOIN ticket_categories tc ON t.category_id = tc.id
            JOIN ticket_subcategories ts ON t.subcategory_id = ts.id
            JOIN ticket_codes tco ON t.code_id = tco.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";
        $callsQuery = "
            SELECT
                'call' as activity_type, dc.id as activity_id, CONCAT('مكالمة للسائق: ', d.name) as details_primary,
                CONCAT('الحالة: ', dc.call_status, '. ملاحظات: ', dc.notes) as details_secondary,
                dc.created_at as activity_date, dc.call_by as user_id, u.username,
                tm.team_id, teams.name as team_name, dc.driver_id as link_id, 'drivers/details' as link_prefix
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";
        $assignmentsQuery = "
            SELECT
                'assignment' as activity_type, da.id as activity_id, CONCAT('تحويل السائق: ', d.name) as details_primary,
                CONCAT('من: ', u_from.username, ' إلى: ', u_to.username) as details_secondary,
                da.created_at as activity_date, da.from_user_id as user_id, u_from.username,
                tm.team_id, teams.name as team_name, da.driver_id as link_id, 'drivers/details' as link_prefix
            FROM driver_assignments da
            JOIN users u_from ON da.from_user_id = u_from.id
            JOIN users u_to ON da.to_user_id = u_to.id
            JOIN drivers d ON da.driver_id = d.id
            LEFT JOIN team_members tm ON u_from.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";

        $queries = [];
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'ticket') $queries[] = $ticketsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'call') $queries[] = $callsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'assignment') $queries[] = $assignmentsQuery;
        if (empty($queries)) return ['activities' => [], 'total' => 0];

        $baseQuery = implode(" UNION ALL ", $queries);

        $whereClauses = " WHERE 1=1 ";
        if (!empty($filters['user_id']) && $filters['user_id'] !== 'all') {
            $whereClauses .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['team_id']) && $filters['team_id'] !== 'all') {
            $whereClauses .= " AND team_id = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['date_from'])) {
            $whereClauses .= " AND DATE(activity_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClauses .= " AND DATE(activity_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $whereClauses .= " AND (details_primary LIKE :search OR details_secondary LIKE :search OR username LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Query for counting total records
        $countQuery = "SELECT COUNT(*) FROM ({$baseQuery}) AS activities" . $whereClauses;
        
        // Query for fetching paginated records
        $finalQuery = "SELECT * FROM ({$baseQuery}) AS activities" . $whereClauses . " ORDER BY activity_date DESC LIMIT :limit OFFSET :offset";
        
        try {
            // Get total count
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetchColumn();

            // Get paginated results
            $params[':limit'] = (int) $limit;
            $params[':offset'] = (int) $offset;

            $stmt = $this->db->prepare($finalQuery);
            // Bind params explicitly for LIMIT and OFFSET
            foreach ($params as $key => $val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val);
                }
            }
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_OBJ);

            return ['activities' => $activities, 'total' => $totalRecords];

        } catch (PDOException $e) {
            error_log("Error in getActivities: " . $e->getMessage());
            error_log("Query: " . $finalQuery);
            error_log("Params: " . print_r($params, true));
            return ['activities' => [], 'total' => 0];
        }
    }
} 