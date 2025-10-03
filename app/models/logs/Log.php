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
        $this->pointsConfig = null;
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
        $stmt = $this->db->prepare("
            SELECT points FROM call_points 
            WHERE call_type = 'outgoing' 
              AND UTC_TIMESTAMP() BETWEEN valid_from AND COALESCE(valid_to, UTC_TIMESTAMP()) 
            ORDER BY valid_from DESC LIMIT 1
        ");
        $stmt->execute();
        $points = $stmt->fetchColumn();
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
                                        LEFT JOIN users u ON t.team_leader_id = u.id 
                                        ORDER BY t.name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getTeams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * getActivities: builds UNION of activity types, now using LEFT JOINs where appropriate.
     * If $filters['debug'] is true -> will print diagnostics + final query + params + rows and die().
     */
    public function getActivities($filters, $limit = 50, $offset = 0)
    {
        $params = [];

        // TICKETS: use ticket_details as source, LEFT JOIN optional lookups so we don't drop rows
        $ticketsQuery = "
            SELECT
                'Ticket' as activity_type,
                td.id as activity_id,
                t.ticket_number as details_primary,
                CONCAT('Platform: ', COALESCE(p.name,''), ' | Country: ', COALESCE(c.name,'')) as details_secondary,
                td.created_at as activity_date,
                td.created_by as user_id,
                COALESCE(u.username, '') as username,
                td.team_id_at_action as team_id,
                teams.name as team_name,
                t.id as link_id,
                'tickets/view' as link_prefix,
                td.is_vip,
                td.platform_id,
                td.code_id
            FROM ticket_details td
            LEFT JOIN tickets t ON td.ticket_id = t.id
            LEFT JOIN users u ON td.created_by = u.id
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN countries c ON td.country_id = c.id
            LEFT JOIN teams ON td.team_id_at_action = teams.id
        ";

        // OUTGOING CALLS
        $outgoingCallsQuery = "
            SELECT
                'Outgoing Call' as activity_type,
                dc.id as activity_id,
                CONCAT('Call to driver: ', COALESCE(d.name,'')) as details_primary,
                CONCAT('Status: ', COALESCE(dc.call_status,''), '. Notes: ', COALESCE(dc.notes,'')) as details_secondary,
                dc.created_at as activity_date,
                dc.call_by as user_id,
                COALESCE(u.username, '') as username,
                dc.team_id_at_action as team_id,
                teams.name as team_name,
                dc.driver_id as link_id,
                'drivers/details' as link_prefix,
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
            FROM driver_calls dc
            LEFT JOIN users u ON dc.call_by = u.id
            LEFT JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN teams ON dc.team_id_at_action = teams.id
        ";

        // INCOMING CALLS
        $incomingCallsQuery = "
            SELECT
                'Incoming Call' as activity_type,
                ic.id as activity_id,
                CONCAT('Call from: ', COALESCE(ic.caller_phone_number,'')) as details_primary,
                CASE
                    WHEN td.ticket_id IS NOT NULL THEN CONCAT('Status: ', COALESCE(ic.status,''), ' | Linked to Ticket: #', COALESCE(t.ticket_number,''))
                    ELSE CONCAT('Status: ', COALESCE(ic.status,''))
                END as details_secondary,
                ic.call_started_at as activity_date,
                ic.call_received_by as user_id,
                COALESCE(u.username, '') as username,
                ic.team_id_at_action as team_id,
                teams.name as team_name,
                t.id as link_id,
                'tickets/view' as link_prefix,
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
            FROM incoming_calls ic
            LEFT JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN teams ON ic.team_id_at_action = teams.id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
        ";

        // ASSIGNMENTS
        $assignmentsQuery = "
            SELECT
                'Assignment' as activity_type,
                da.id as activity_id,
                CONCAT('Assigning driver: ', COALESCE(d.name,'')) as details_primary,
                CONCAT('From: ', COALESCE(u_from.username,''), ' To: ', COALESCE(u_to.username,'')) as details_secondary,
                da.created_at as activity_date,
                da.from_user_id as user_id,
                COALESCE(u_from.username, '') as username,
                tm.team_id,
                teams.name as team_name,
                da.driver_id as link_id,
                'drivers/details' as link_prefix,
                NULL as is_vip,
                NULL as platform_id,
                NULL as code_id
            FROM driver_assignments da
            LEFT JOIN users u_from ON da.from_user_id = u_from.id
            LEFT JOIN users u_to ON da.to_user_id = u_to.id
            LEFT JOIN drivers d ON da.driver_id = d.id
            LEFT JOIN team_members tm ON da.from_user_id = tm.user_id
            LEFT JOIN teams ON tm.team_id = teams.id
        ";

        $queries = [];
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'ticket') $queries[] = $ticketsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'outgoing_call') $queries[] = $outgoingCallsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'incoming_call') $queries[] = $incomingCallsQuery;
        if (!isset($filters['activity_type']) || $filters['activity_type'] === 'all' || $filters['activity_type'] === 'assignment') $queries[] = $assignmentsQuery;

        if (empty($queries)) {
            return ['activities' => [], 'total' => 0];
        }

        $baseQuery = implode(" UNION ALL ", $queries);

        // Build where
        $whereClauses = " WHERE 1=1 ";
        if (!empty($filters['user_id']) && $filters['user_id'] !== 'all') {
            $whereClauses .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['team_id']) && $filters['team_id'] !== 'all') {
            $whereClauses .= " AND team_id = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['original_date_from'])) {
            $whereClauses .= " AND DATE(CONVERT_TZ(activity_date, '+00:00', '+02:00')) >= :date_from";
            $params[':date_from'] = $filters['original_date_from'];
        }
        if (!empty($filters['original_date_to'])) {
            $whereClauses .= " AND DATE(CONVERT_TZ(activity_date, '+00:00', '+02:00')) <= :date_to";
            $params[':date_to'] = $filters['original_date_to'];
        }
        if (!empty($filters['search'])) {
            $whereClauses .= " AND (details_primary LIKE :search OR details_secondary LIKE :search OR username LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $finalQuery = "SELECT * FROM ({$baseQuery}) AS activities" . $whereClauses . " ORDER BY activity_date DESC";
        if ($limit) {
            $finalQuery .= " LIMIT :limit OFFSET :offset";
        }

        // If debug requested — run diagnostics + show info + die
        if (!empty($filters['debug'])) {
            // Diagnostic checks
            $diagnostics = [
                'total_details' => "SELECT COUNT(*) FROM ticket_details",
                'null_created_by' => "SELECT COUNT(*) FROM ticket_details WHERE created_by IS NULL",
                'missing_user_fk' => "SELECT COUNT(*) FROM ticket_details td LEFT JOIN users u ON td.created_by = u.id WHERE td.created_by IS NOT NULL AND u.id IS NULL",
                'missing_platform_fk' => "SELECT COUNT(*) FROM ticket_details td LEFT JOIN platforms p ON td.platform_id = p.id WHERE p.id IS NULL",
                'missing_country_fk' => "SELECT COUNT(*) FROM ticket_details td LEFT JOIN countries c ON td.country_id = c.id WHERE td.country_id IS NOT NULL AND c.id IS NULL",
            ];
            echo "<h2>Debug Diagnostics</h2><pre>";
            foreach ($diagnostics as $k => $sql) {
                try {
                    $res = $this->db->query($sql)->fetchColumn();
                } catch (\Exception $e) {
                    $res = "ERROR: " . $e->getMessage();
                }
                echo "{$k}: {$res}\nSQL: {$sql}\n\n";
            }
            echo "---- Final union query below ----\n";
            echo $finalQuery . "\n\n";
            echo "Params: " . print_r($params, true) . "\n\n";

            // Try executing finalQuery to show the rows (LIMIT small to avoid heavy load)
            try {
                $previewSql = $finalQuery;
                // override to small preview
                if (strpos(strtolower($previewSql), 'limit') === false) {
                    $previewSql .= " LIMIT 20";
                }
                $stmt = $this->db->prepare($previewSql);
                // bind params same way as normal execution
                if ($limit) {
                    $params[':limit'] = (int) $limit;
                    $params[':offset'] = (int) $offset;
                }
                foreach ($params as $key => $val) {
                    if (($key === ':limit' || $key === ':offset') && $limit) {
                        $stmt->bindValue($key, $val, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($key, $val);
                    }
                }
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "Preview rows: \n" . print_r($rows, true);
            } catch (\Exception $e) {
                echo "Error executing finalQuery preview: " . $e->getMessage() . "\n";
            }
            echo "</pre>";
            die("DEBUG STOPPED");
        }

        // Normal execution
        try {
            $countQuery = "SELECT COUNT(*) FROM ({$baseQuery}) AS activities" . $whereClauses;
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = (int) $countStmt->fetchColumn();

            if ($limit) {
                $params[':limit'] = (int) $limit;
                $params[':offset'] = (int) $offset;
            }

            $stmt = $this->db->prepare($finalQuery);
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

        // Use LEFT JOINs here as well to avoid dropping ticket_details
        $ticketsQuery = "
            SELECT 
                CASE WHEN td.is_vip = 1 THEN 'VIP Ticket' ELSE 'Normal Ticket' END as activity_type, 
                t.ticket_number as details_primary,
                CONCAT('Platform: ', COALESCE(p.name,''), ' | Country: ', COALESCE(c.name,'')) as details_secondary,
                td.created_at as activity_date, 
                td.created_by as user_id, 
                COALESCE(u.username, '') as username,
                COALESCE(tm.team_id, NULL) as team_id
            FROM ticket_details td
            LEFT JOIN tickets t ON td.ticket_id = t.id
            LEFT JOIN users u ON td.created_by = u.id
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN countries c ON td.country_id = c.id
            LEFT JOIN team_members tm ON td.created_by = tm.user_id
        ";

        $outgoingCallsQuery = "
            SELECT
                'Outgoing Call' as activity_type, 
                CONCAT('Call to driver: ', COALESCE(d.name,'')) as details_primary,
                CONCAT('Status: ', COALESCE(dc.call_status,''), '. Notes: ', COALESCE(dc.notes,'')) as details_secondary,
                dc.created_at as activity_date, 
                dc.call_by as user_id, 
                COALESCE(u.username,'') as username,
                tm.team_id
            FROM driver_calls dc
            LEFT JOIN users u ON dc.call_by = u.id
            LEFT JOIN drivers d ON dc.driver_id = d.id
            LEFT JOIN team_members tm ON dc.call_by = tm.user_id
        ";

        $incomingCallsQuery = "
            SELECT
                'Incoming Call' as activity_type,
                CONCAT('Call from: ', COALESCE(ic.caller_phone_number,'')) as details_primary,
                CASE 
                    WHEN td.ticket_id IS NOT NULL THEN CONCAT('Status: ', COALESCE(ic.status,''), ' | Linked to Ticket: #', COALESCE(t.ticket_number,''))
                    ELSE CONCAT('Status: ', COALESCE(ic.status,''))
                END as details_secondary,
                ic.call_started_at as activity_date,
                ic.call_received_by as user_id,
                COALESCE(u.username,'') as username,
                tm.team_id
            FROM incoming_calls ic
            LEFT JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN team_members tm ON ic.call_received_by = tm.user_id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
        ";

        $assignmentsQuery = "
            SELECT
                'Assignment' as activity_type, 
                CONCAT('Assigning driver: ', COALESCE(d.name,'')) as details_primary,
                CONCAT('From: ', COALESCE(u_from.username,''), ' To: ', COALESCE(u_to.username,'')) as details_secondary,
                da.created_at as activity_date, 
                da.from_user_id as user_id, 
                COALESCE(u_from.username,'') as username,
                tm.team_id
            FROM driver_assignments da
            LEFT JOIN users u_from ON da.from_user_id = u_from.id
            LEFT JOIN users u_to ON da.to_user_id = u_to.id
            LEFT JOIN drivers d ON da.driver_id = d.id
            LEFT JOIN team_members tm ON da.from_user_id = tm.user_id
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
        if (!empty($filters['original_date_from'])) {
            $whereClauses .= " AND DATE(CONVERT_TZ(activity_date, '+00:00', '+02:00')) >= :date_from";
            $params[':date_from'] = $filters['original_date_from'];
        }
        if (!empty($filters['original_date_to'])) {
            $whereClauses .= " AND DATE(CONVERT_TZ(activity_date, '+00:00', '+02:00')) <= :date_to";
            $params[':date_to'] = $filters['original_date_to'];
        }
        if (!empty($filters['search'])) {
            $whereClauses .= " AND (details_primary LIKE :search OR details_secondary LIKE :search OR username LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $finalQuery = "SELECT activity_type, COUNT(*) as count FROM ({$baseQuery}) AS activities" . $whereClauses . " GROUP BY activity_type";

        try {
            $stmt = $this->db->prepare($finalQuery);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getActivitiesSummary: " . $e->getMessage());
            error_log("Query: " . $finalQuery);
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }

    public function getActivitiesByIds($ids) {
        if (empty($ids)) {
            return [];
        }

        $sanitizedIds = [];
        foreach ($ids as $id_str) {
            list($type, $id) = explode('-', $id_str);
            $sanitizedIds[$type][] = (int)$id;
        }

        $all_activities = [];

        if (!empty($sanitizedIds['Ticket'])) {
            $ticket_ids = $sanitizedIds['Ticket'];
            $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
            $sql = "SELECT 'Ticket' as activity_type, td.id as activity_id, t.ticket_number as details_primary, 
                           CONCAT('Platform: ', COALESCE(p.name,''), ' | Country: ', COALESCE(c.name,'')) as details_secondary, 
                           td.created_at as activity_date, COALESCE(u.username,'') as username, teams.name as team_name, td.is_vip
                    FROM ticket_details td
                    LEFT JOIN tickets t ON td.ticket_id = t.id
                    LEFT JOIN users u ON td.created_by = u.id
                    LEFT JOIN platforms p ON td.platform_id = p.id
                    LEFT JOIN countries c ON td.country_id = c.id
                    LEFT JOIN team_members tm ON td.created_by = tm.user_id
                    LEFT JOIN teams ON tm.team_id = teams.id
                    WHERE td.id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ticket_ids);
            $all_activities = array_merge($all_activities, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        // Add similar fetching for other types if needed...

        return $all_activities;
    }
}
