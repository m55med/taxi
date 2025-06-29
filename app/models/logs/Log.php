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
        
        $ticketsQuery = "
            SELECT 
                'Ticket' as activity_type, 
                td.id as activity_id, 
                t.ticket_number as details_primary,
                CONCAT('Platform: ', p.name, ' | Country: ', c.name) as details_secondary,
                td.created_at as activity_date, 
                td.edited_by as user_id, 
                u.username,
                tm.team_id, 
                teams.name as team_name, 
                t.id as link_id, 
                'tickets/view' as link_prefix,
                td.is_vip
            FROM ticket_details td
            JOIN tickets t ON td.ticket_id = t.id
            JOIN users u ON td.edited_by = u.id
            JOIN platforms p ON td.platform_id = p.id
            JOIN countries c ON td.country_id = c.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";
        
        $outgoingCallsQuery = "
            SELECT
                'Outgoing Call' as activity_type, 
                dc.id as activity_id, 
                CONCAT('Call to driver: ', d.name) as details_primary,
                CONCAT('Status: ', dc.call_status, '. Notes: ', dc.notes) as details_secondary,
                dc.created_at as activity_date, 
                dc.call_by as user_id, 
                u.username,
                tm.team_id, 
                teams.name as team_name, 
                dc.driver_id as link_id, 
                'drivers/details' as link_prefix,
                NULL as is_vip
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";

        $incomingCallsQuery = "
            SELECT
                'Incoming Call' as activity_type,
                ic.id as activity_id,
                CONCAT('Call from: ', ic.caller_phone_number) as details_primary,
                CASE 
                    WHEN td.ticket_id IS NOT NULL THEN CONCAT('Status: ', ic.status, ' | Linked to Ticket: #', t.ticket_number)
                    ELSE CONCAT('Status: ', ic.status)
                END as details_secondary,
                ic.call_started_at as activity_date,
                ic.call_received_by as user_id,
                u.username,
                tm.team_id,
                teams.name as team_name,
                t.id as link_id,
                'tickets/view' as link_prefix,
                NULL as is_vip
            FROM incoming_calls ic
            JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
        ";

        $assignmentsQuery = "
            SELECT
                'Assignment' as activity_type, 
                da.id as activity_id, 
                CONCAT('Assigning driver: ', d.name) as details_primary,
                CONCAT('From: ', u_from.username, ' To: ', u_to.username) as details_secondary,
                da.created_at as activity_date, 
                da.from_user_id as user_id, 
                u_from.username,
                tm.team_id, 
                teams.name as team_name, 
                da.driver_id as link_id, 
                'drivers/details' as link_prefix,
                NULL as is_vip
            FROM driver_assignments da
            JOIN users u_from ON da.from_user_id = u_from.id
            JOIN users u_to ON da.to_user_id = u_to.id
            JOIN drivers d ON da.driver_id = d.id
            LEFT JOIN team_members tm ON u_from.id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";

        $queries = [];
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'ticket') $queries[] = $ticketsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'outgoing_call') $queries[] = $outgoingCallsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'incoming_call') $queries[] = $incomingCallsQuery;
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
            $search_term = '%' . $filters['search'] . '%';
            $whereClauses .= " AND (details_primary LIKE :search OR details_secondary LIKE :search OR username LIKE :search)";
            $params[':search'] = $search_term;
        }

        // Query for counting total records
        $countQuery = "SELECT COUNT(*) FROM ({$baseQuery}) AS activities" . $whereClauses;
        
        // Query for fetching paginated records
        $finalQuery = "SELECT * FROM ({$baseQuery}) AS activities" . $whereClauses . " ORDER BY activity_date DESC";
        if ($limit) {
            $finalQuery .= " LIMIT :limit OFFSET :offset";
        }
        
        try {
            // Get total count
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetchColumn();

            // Get paginated results
            if($limit){
                $params[':limit'] = (int) $limit;
                $params[':offset'] = (int) $offset;
            }

            $stmt = $this->db->prepare($finalQuery);
            // Bind params explicitly for LIMIT and OFFSET
            foreach ($params as $key => $val) {
                if (($key === ':limit' || $key === ':offset') && $limit) {
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

    public function getActivitiesSummary($filters)
    {
        $params = [];

        // Simplified queries for summary, only selecting fields needed for filtering and grouping
        $ticketsQuery = "
            SELECT 
                CASE WHEN td.is_vip = 1 THEN 'VIP Ticket' ELSE 'Normal Ticket' END as activity_type, 
                t.ticket_number as details_primary,
                CONCAT('Platform: ', p.name, ' | Country: ', c.name) as details_secondary,
                td.created_at as activity_date, 
                td.edited_by as user_id, 
                u.username,
                tm.team_id
            FROM ticket_details td
            JOIN tickets t ON td.ticket_id = t.id
            JOIN users u ON td.edited_by = u.id
            JOIN platforms p ON td.platform_id = p.id
            JOIN countries c ON td.country_id = c.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
        ";
        
        $outgoingCallsQuery = "
            SELECT
                'Outgoing Call' as activity_type, 
                CONCAT('Call to driver: ', d.name) as details_primary,
                CONCAT('Status: ', dc.call_status, '. Notes: ', dc.notes) as details_secondary,
                dc.created_at as activity_date, 
                dc.call_by as user_id, 
                u.username,
                tm.team_id
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
        ";

        $incomingCallsQuery = "
            SELECT
                'Incoming Call' as activity_type,
                CONCAT('Call from: ', ic.caller_phone_number) as details_primary,
                CONCAT('Status: ', ic.status) as details_secondary,
                ic.call_started_at as activity_date,
                ic.call_received_by as user_id,
                u.username,
                tm.team_id
            FROM incoming_calls ic
            JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
        ";

        $assignmentsQuery = "
            SELECT
                'Assignment' as activity_type, 
                CONCAT('Assigning driver: ', d.name) as details_primary,
                CONCAT('From: ', u_from.username, ' To: ', u_to.username) as details_secondary,
                da.created_at as activity_date, 
                da.from_user_id as user_id, 
                u_from.username,
                tm.team_id
            FROM driver_assignments da
            JOIN users u_from ON da.from_user_id = u_from.id
            JOIN users u_to ON da.to_user_id = u_to.id
            JOIN drivers d ON da.driver_id = d.id
            LEFT JOIN team_members tm ON u_from.id = tm.user_id
        ";

        $queries = [];
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'ticket') $queries[] = $ticketsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'outgoing_call') $queries[] = $outgoingCallsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'incoming_call') $queries[] = $incomingCallsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'assignment') $queries[] = $assignmentsQuery;
        
        if (empty($queries)) return [];

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
            $search_term = '%' . $filters['search'] . '%';
            $whereClauses .= " AND (details_primary LIKE :search OR details_secondary LIKE :search OR username LIKE :search)";
            $params[':search'] = $search_term;
        }

        $summaryQuery = "SELECT activity_type, COUNT(*) as count FROM ({$baseQuery}) AS activities" . $whereClauses . " GROUP BY activity_type";

        try {
            $stmt = $this->db->prepare($summaryQuery);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Ensure all possible activity types are present in the final array, even if count is 0
            $all_activity_types = ['Normal Ticket', 'VIP Ticket', 'Outgoing Call', 'Incoming Call', 'Assignment'];
            $summary = [];
            foreach ($all_activity_types as $type) {
                $summary[$type] = $results[$type] ?? 0;
            }

            // If a specific activity type is filtered, only return that type's summary
            if (isset($filters['activity_type']) && $filters['activity_type'] !== 'all') {
                $filtered_type_key_map = [
                    'ticket' => ['Normal Ticket', 'VIP Ticket'], 
                    'outgoing_call' => ['Outgoing Call'], 
                    'incoming_call' => ['Incoming Call'], 
                    'assignment' => ['Assignment']
                ];
                $filtered_summary = [];
                $keys_to_filter = $filtered_type_key_map[$filters['activity_type']] ?? [];
                foreach($keys_to_filter as $key){
                    if(isset($summary[$key])){
                        $filtered_summary[$key] = $summary[$key];
                    }
                }
                 return $filtered_summary;
            }


            return $summary;
        } catch (PDOException $e) {
            error_log("Error in getActivitiesSummary: " . $e->getMessage());
            error_log("Query: " . $summaryQuery);
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }

    public function getActivitiesByIds($ids) {
        if (empty($ids)) {
            return [];
        }

        $grouped_ids = [];
        foreach ($ids as $id_str) {
            $parts = explode('-', $id_str, 2);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                $grouped_ids[$type][] = $id;
            }
        }

        $queries = [];
        $params = [];

        if (!empty($grouped_ids['Ticket'])) {
            $placeholders = rtrim(str_repeat('?,', count($grouped_ids['Ticket'])), ',');
            $queries[] = "
                SELECT 'Ticket' as activity_type, td.id as activity_id, t.ticket_number as details_primary,
                       CONCAT('Platform: ', p.name, ' | Country: ', c.name) as details_secondary, td.created_at as activity_date,
                       td.edited_by as user_id, u.username, tm.team_id, teams.name as team_name,
                       t.id as link_id, 'tickets/view' as link_prefix, td.is_vip
                FROM ticket_details td
                JOIN tickets t ON td.ticket_id = t.id JOIN users u ON td.edited_by = u.id JOIN platforms p ON td.platform_id = p.id
                JOIN countries c ON td.country_id = c.id LEFT JOIN team_members tm ON u.id = tm.user_id LEFT JOIN teams ON tm.team_id = teams.id
                WHERE td.id IN ($placeholders)
            ";
            $params = array_merge($params, $grouped_ids['Ticket']);
        }
        if (!empty($grouped_ids['Outgoing Call'])) {
             $placeholders = rtrim(str_repeat('?,', count($grouped_ids['Outgoing Call'])), ',');
             $queries[] = "
                SELECT 'Outgoing Call' as activity_type, dc.id as activity_id, CONCAT('Call to driver: ', d.name) as details_primary,
                       CONCAT('Status: ', dc.call_status, '. Notes: ', dc.notes) as details_secondary, dc.created_at as activity_date,
                       dc.call_by as user_id, u.username, tm.team_id, teams.name as team_name, dc.driver_id as link_id,
                       'drivers/details' as link_prefix, NULL as is_vip
                FROM driver_calls dc
                JOIN users u ON dc.call_by = u.id JOIN drivers d ON dc.driver_id = d.id
                LEFT JOIN team_members tm ON u.id = tm.user_id LEFT JOIN teams ON tm.team_id = teams.id
                WHERE dc.id IN ($placeholders)
             ";
             $params = array_merge($params, $grouped_ids['Outgoing Call']);
        }
        if (!empty($grouped_ids['Incoming Call'])) {
             $placeholders = rtrim(str_repeat('?,', count($grouped_ids['Incoming Call'])), ',');
             $queries[] = "
                SELECT 'Incoming Call' as activity_type, ic.id as activity_id, CONCAT('Call from: ', ic.caller_phone_number) as details_primary,
                       CONCAT('Status: ', ic.status) as details_secondary, ic.call_started_at as activity_date,
                       ic.call_received_by as user_id, u.username, tm.team_id, teams.name as team_name,
                       ic.id as link_id, 'logs' as link_prefix, NULL as is_vip
                FROM incoming_calls ic
                JOIN users u ON ic.call_received_by = u.id LEFT JOIN team_members tm ON u.id = tm.user_id LEFT JOIN teams ON tm.team_id = teams.id
                WHERE ic.id IN ($placeholders)
             ";
             $params = array_merge($params, $grouped_ids['Incoming Call']);
        }
        if (!empty($grouped_ids['Assignment'])) {
             $placeholders = rtrim(str_repeat('?,', count($grouped_ids['Assignment'])), ',');
             $queries[] = "
                SELECT 'Assignment' as activity_type, da.id as activity_id, CONCAT('Assigning driver: ', d.name) as details_primary,
                       CONCAT('From: ', u_from.username, ' To: ', u_to.username) as details_secondary, da.created_at as activity_date,
                       da.from_user_id as user_id, u_from.username, tm.team_id, teams.name as team_name,
                       da.driver_id as link_id, 'drivers/details' as link_prefix, NULL as is_vip
                FROM driver_assignments da
                JOIN users u_from ON da.from_user_id = u_from.id JOIN users u_to ON da.to_user_id = u_to.id
                JOIN drivers d ON da.driver_id = d.id LEFT JOIN team_members tm ON u_from.id = tm.user_id LEFT JOIN teams ON tm.team_id = teams.id
                WHERE da.id IN ($placeholders)
             ";
             $params = array_merge($params, $grouped_ids['Assignment']);
        }

        if (empty($queries)) return [];
        
        $finalQuery = implode(" UNION ALL ", $queries) . " ORDER BY activity_date DESC";
        
        try {
            $stmt = $this->db->prepare($finalQuery);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getActivitiesByIds: " . $e->getMessage());
            return [];
        }
    }
} 