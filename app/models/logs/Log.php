<?php

namespace App\Models\Logs;

use App\Core\Database;
use PDO;
use PDOException;

class Log
{
    private $db;
    private $pointsConfig;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pointsConfig = null; // Lazy load points config
    }

    private function getPointsConfig()
    {
        if ($this->pointsConfig === null) {
            $this->pointsConfig = [
                'incoming_call_platform_id' => $this->fetchIncomingCallPlatformId(),
                'outgoing_call_points' => $this->fetchCallPoints(),
            ];
        }
        return $this->pointsConfig;
    }

    private function fetchIncomingCallPlatformId() {
        $stmt = $this->db->prepare("SELECT id FROM platforms WHERE name = 'Incoming Call'");
        $stmt->execute();
        return $stmt->fetchColumn() ?: -1;
    }

    private function fetchCallPoints() {
        // Fetches the currently valid points for an outgoing call.
        $stmt = $this->db->prepare("SELECT points FROM call_points WHERE call_type = 'outgoing' AND UTC_TIMESTAMP() BETWEEN valid_from AND COALESCE(valid_to, UTC_TIMESTAMP()) ORDER BY valid_from DESC LIMIT 1");
        $stmt->execute();
        $points = $stmt->fetchColumn();
        // DIAGNOSTIC: Return 5.0 as a fallback if no points are defined in the database.
        return ($points === false) ? 5.0 : (float)$points;
    }
    
    private function getTicketPoints($ticketDetailId, $platformId, $codeId, $isVip, $activityDate)
    {
        if ($platformId == $this->getPointsConfig()['incoming_call_platform_id']) {
            return 0;
        }

        if (empty($codeId)) {
            return 0;
        }

        $sql = "SELECT points FROM ticket_code_points 
                WHERE code_id = :code_id 
                AND is_vip = :is_vip
                AND :activity_date >= valid_from 
                AND (:activity_date <= valid_to OR valid_to IS NULL)
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':code_id' => $codeId,
            ':is_vip' => $isVip,
            ':activity_date' => $activityDate
        ]);

        $points = $stmt->fetchColumn();
        // DIAGNOSTIC: Return 10.0 as a fallback if no points are defined for this specific code.
        return ($points === false) ? 10.0 : (float)$points;
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
                td.team_id_at_action as team_id,
                teams.name as team_name, 
                t.id as link_id, 
                'tickets/view' as link_prefix,
                td.is_vip,
                td.platform_id,
                td.code_id
            FROM ticket_details td
            JOIN tickets t ON td.ticket_id = t.id
            JOIN users u ON td.edited_by = u.id
            JOIN platforms p ON td.platform_id = p.id
            JOIN countries c ON td.country_id = c.id
            LEFT JOIN teams ON td.team_id_at_action = teams.id
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
                dc.team_id_at_action as team_id,
                teams.name as team_name, 
                dc.driver_id as link_id, 
                'drivers/details' as link_prefix,
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN teams ON dc.team_id_at_action = teams.id
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
                ic.team_id_at_action as team_id,
                teams.name as team_name,
                t.id as link_id,
                'tickets/view' as link_prefix,
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
            FROM incoming_calls ic
            JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN teams ON ic.team_id_at_action = teams.id
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
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
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
        
        if (empty($queries)) {
            return ['activities' => [], 'total' => 0];
        }

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
            
            // Calculate points after fetching
            foreach ($activities as $activity) {
                $activity->points = 0; // Default points

                // FINAL DIAGNOSTIC TEST: Assign hardcoded points based on type to isolate the issue.
                if ($activity->activity_type === 'Ticket') {
                    // Check if it's an incoming call ticket, which should be 0.
                    // We need to query the platform name for the given platform_id.
                    $platform_stmt = $this->db->prepare("SELECT name FROM platforms WHERE id = ?");
                    $platform_stmt->execute([$activity->platform_id]);
                    $platform_name = $platform_stmt->fetchColumn();

                    if ($platform_name === 'Incoming Call') {
                        $activity->points = 0.0;
                    } else {
                        $activity->points = 11.11; // Hardcoded diagnostic value for other tickets.
                    }

                } elseif ($activity->activity_type === 'Outgoing Call') {
                    if (str_contains($activity->details_secondary, 'Status: answered')) {
                         $activity->points = 22.22; // Hardcoded diagnostic value for answered calls.
                    } else {
                        $activity->points = 0.0;
                    }
                } elseif ($activity->activity_type === 'Incoming Call') {
                    $activity->points = 33.33; // Hardcoded diagnostic value.
                }
            }

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
                CASE 
                    WHEN td.ticket_id IS NOT NULL THEN CONCAT('Status: ', ic.status, ' | Linked to Ticket: #', t.ticket_number)
                    ELSE CONCAT('Status: ', ic.status)
                END as details_secondary,
                ic.call_started_at as activity_date,
                ic.call_received_by as user_id,
                u.username,
                tm.team_id
            FROM incoming_calls ic
            JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
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
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'ticket') $queries[] = $ticketsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'outgoing_call') $queries[] = $outgoingCallsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'incoming_call') $queries[] = $incomingCallsQuery;
        if ($filters['activity_type'] === 'all' || $filters['activity_type'] === 'assignment') $queries[] = $assignmentsQuery;
        
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

        $finalQuery = "SELECT activity_type, COUNT(*) as count FROM ({$baseQuery}) AS activities" . $whereClauses . " GROUP BY activity_type";

        try {
            $stmt = $this->db->prepare($finalQuery);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getActivitiesSummary: " . $e->getMessage());
            return [];
        }
    }

    public function getActivitiesByIds($ids) {
        if (empty($ids)) {
            return [];
        }

        // We can't easily reuse the main getActivities query here because the IDs
        // are a mix of types (ticket, call, etc). We have to query each type.
        // This is not ideal but necessary for this feature.
        // For simplicity, this example will just fetch basic info.
        // A full implementation would need to join with users, teams, etc.

        // Sanitize IDs
        $sanitizedIds = [];

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        foreach ($ids as $id_str) {
            list($type, $id) = explode('-', $id_str);
            $sanitizedIds[$type][] = (int)$id;
        }

        $all_activities = [];

        // Fetch Tickets
        if (!empty($sanitizedIds['Ticket'])) {
            $ticket_ids = $sanitizedIds['Ticket'];
            $sql = "SELECT 'Ticket' as activity_type, td.id as activity_id, t.ticket_number as details_primary, 
                           CONCAT('Platform: ', p.name, ' | Country: ', c.name) as details_secondary, 
                           td.created_at as activity_date, u.username, teams.name as team_name, td.is_vip
                    FROM ticket_details td
                    JOIN tickets t ON td.ticket_id = t.id
                    JOIN users u ON td.edited_by = u.id
                    JOIN platforms p ON td.platform_id = p.id
                    JOIN countries c ON td.country_id = c.id
                    LEFT JOIN team_members tm ON u.id = tm.user_id
                    LEFT JOIN teams ON tm.team_id = teams.id
                    WHERE td.id IN (" . implode(',', array_fill(0, count($ticket_ids), '?')) . ")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ticket_ids);
            $all_activities = array_merge($all_activities, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        // NOTE: Add similar queries for Outgoing Call, Incoming Call, Assignment if needed.
        // This is just a sample implementation for bulk export.

        return $all_activities;
    }
} 
