<?php



namespace App\Models\Calls;



use App\Core\Model;

use App\Models\Admin\TeamMember;

use PDO;

use PDOException;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Call extends Model

{

    public function __construct()

    {

        parent::__construct();

    }



    public function getDb()

    {

        return $this->db;

    }



    // =================================================================

    // PRIMARY QUEUE LOGIC

    // =================================================================



    public function findAndLockNextDriver($userId, $skippedDriverIds = [])

    {

        $debug = [

            'query' => '',

            'params' => [],

            'error' => '',

            'count' => 0,

            'driver_id' => null

        ];



        $isTransactionActive = $this->db->inTransaction();



        try {

            if (!$isTransactionActive) {

                $this->db->beginTransaction();

            }



            $queryParams = [':userId' => $userId];



            $sql = "

            SELECT d.id FROM drivers d

            LEFT JOIN (

                SELECT t1.driver_id, t1.created_at, t1.next_call_at 

                FROM driver_calls t1

                INNER JOIN (

                    SELECT driver_id, MAX(id) as max_id 

                    FROM driver_calls 

                    GROUP BY driver_id

                ) t2 ON t1.id = t2.max_id

            ) AS lc ON d.id = lc.driver_id



            LEFT JOIN driver_assignments a 

                ON d.id = a.driver_id 

                AND a.to_user_id = :userId 

                AND a.is_seen = 0



            WHERE

                d.hold = 0

                AND (

                    d.main_system_status IS NULL OR

                    d.main_system_status = '' OR

                    d.main_system_status IN ('pending', 'reconsider') OR

                    (

                        d.main_system_status IN ('no_answer', 'rescheduled') AND 

                        (lc.next_call_at IS NULL OR lc.next_call_at <= UTC_TIMESTAMP())

                    )

                )";



            // Exclude skipped drivers if any

            if (!empty($skippedDriverIds)) {

                $in_keys = [];

                foreach ($skippedDriverIds as $key => $id) {

                    $paramName = ":skipped_id_$key";

                    $in_keys[] = $paramName;

                    $queryParams[$paramName] = $id;

                }

                $sql .= " AND d.id NOT IN (" . implode(',', $in_keys) . ")";

            }



            // Sorting logic (assignments, status priority, then call/create date)

            $sql .= "

            ORDER BY

                CASE WHEN a.id IS NOT NULL THEN 0 ELSE 1 END ASC,

                CASE d.main_system_status

                    WHEN 'reconsider'  THEN 1

                    WHEN 'rescheduled' THEN 2

                    WHEN 'no_answer'   THEN 3

                    WHEN 'pending'     THEN 4

                    ELSE 99

                END ASC,

                CASE

                    WHEN d.main_system_status IN ('rescheduled', 'no_answer') THEN lc.created_at

                    ELSE d.created_at

                END ASC

            LIMIT 1

        ";



            $debug['query'] = $sql;

            $debug['params'] = $queryParams;



            // Execute

            $stmt = $this->db->prepare($sql);

            $stmt->execute($queryParams);

            $driverId = $stmt->fetchColumn();

            $debug['driver_id'] = $driverId;



            if ($driverId) {

                $driverStmt = $this->db->prepare("SELECT * FROM drivers WHERE id = :id FOR UPDATE");

                $driverStmt->execute([':id' => $driverId]);

                $driver = $driverStmt->fetch(PDO::FETCH_ASSOC);



                if ($driver) {

                    $this->setDriverHold($driver['id'], true);

                    $debug['count'] = 1;

                }



                if (!$isTransactionActive) {

                    $this->db->commit();

                }



                return ['driver' => $driver, 'debug_info' => $debug];

            }



            $debug['count'] = 0;



            if (!$isTransactionActive) {

                $this->db->commit();

            }



            return ['driver' => null, 'debug_info' => $debug];



        } catch (PDOException $e) {

            if (!$isTransactionActive && $this->db->inTransaction()) {

                $this->db->rollBack();

            }



            $debug['error'] = $e->getMessage();

            error_log("CRITICAL Error in findAndLockNextDriver: " . $e->getMessage());



            return ['driver' => null, 'debug_info' => $debug];

        }

    }





    public function findAndLockDriverByPhone($phone, $currentUserId)

    {

        // First, find the driver and who is holding them, if anyone.

        $sql = "SELECT d.*, u.username as hold_by_username

                FROM drivers d

                LEFT JOIN users u ON d.hold_by = u.id

                WHERE d.phone = :phone

                LIMIT 1";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([':phone' => $phone]);

        $driver = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($driver) {

            // If the driver is on hold by someone else, return the driver data with the holder's name.

            if ($driver['hold'] && $driver['hold_by'] != $currentUserId) {

                return $driver; // Return immediately, do not lock.

            }



            // Otherwise, lock the driver for the current user.

            $this->setDriverHold($driver['id'], true, $currentUserId);

            // Re-fetch to get the full, most recent data after locking.

            return $this->getDriverById($driver['id']);

        }



        return null;

    }



    // =================================================================

    // CALL & DRIVER STATUS MANAGEMENT

    // =================================================================



    /**

     * Records a call in the database.

     *

     * @param array $data The call data.

     * @return bool True on success, false on failure.

     */

    public function recordCall($data)

    {

        $isTransactionActive = $this->db->inTransaction();

        if (!$isTransactionActive) {

            $this->db->beginTransaction();

        }



        try {

            // Team ID is now passed directly from the controller

            // $teamIdAtAction = TeamMember::getCurrentTeamIdForUser($data['call_by']);



            // 1. Insert the call record

            $sql = 'INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at, ticket_category_id, ticket_subcategory_id, ticket_code_id, team_id_at_action) 

                    VALUES (:driver_id, :call_by, :call_status, :notes, :next_call_at, :ticket_category_id, :ticket_subcategory_id, :ticket_code_id, :team_id_at_action)';



            $stmt = $this->db->prepare($sql);



            $stmt->bindValue(':driver_id', $data['driver_id']);

            $stmt->bindValue(':call_by', $data['call_by']);

            $stmt->bindValue(':call_status', $data['call_status']);

            $stmt->bindValue(':notes', $data['notes']);

            $stmt->bindValue(':next_call_at', $data['next_call_at']);

            $stmt->bindValue(':ticket_category_id', $data['ticket_category_id']);

            $stmt->bindValue(':ticket_subcategory_id', $data['ticket_subcategory_id']);

            $stmt->bindValue(':ticket_code_id', $data['ticket_code_id']);

            $stmt->bindValue(':team_id_at_action', $data['team_id_at_action'] ?? null);



            if (!$stmt->execute()) {

                throw new PDOException("Failed to insert call record.");

            }



            // 2. Update driver's main_system_status based on call status

            $this->updateDriverStatusBasedOnCall($data['driver_id'], $data['call_status']);



            if (!$isTransactionActive) {

                $this->db->commit();

            }

            return true;



        } catch (PDOException $e) {

            if (!$isTransactionActive && $this->db->inTransaction()) {

                $this->db->rollBack();

            }

            error_log("Error in recordCall transaction: " . $e->getMessage());

            return false;

        }

    }



    public function updateDriverStatusBasedOnCall($driver_id, $call_status)

    {

        $new_status = null;

        switch ($call_status) {

            case 'answered':

                $new_status = 'completed'; // Driver is now considered processed.

                break;

            case 'no_answer':

            case 'busy':

            case 'not_available':

                $new_status = 'no_answer';

                break;

            case 'rescheduled':

                $new_status = 'rescheduled';

                break;

            case 'wrong_number':

                $new_status = 'blocked';

                break;

            default:

                // For any other status, do not change the driver's main_system_status

                return true;

        }



        $sql = "UPDATE drivers SET main_system_status = :status WHERE id = :driver_id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':status', $new_status);

        $stmt->bindValue(':driver_id', $driver_id);

        return $stmt->execute();

    }



    public function releaseDriverHold($driverId)

    {

        return $this->setDriverHold($driverId, false, null);

    }



    public function setDriverHold($driverId, $isHeld, $userId = null)

    {

        $sql = "UPDATE drivers SET hold = :is_held, hold_by = :user_id WHERE id = :driver_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([

            ':is_held' => (int) $isHeld,

            ':user_id' => $isHeld ? $userId : null,

            ':driver_id' => $driverId

        ]);

    }



    // =================================================================

    // DATA FETCHING & HELPERS

    // =================================================================



    public function getUnseenAssignment($userId)

    {

        $sql = "SELECT * FROM driver_assignments WHERE to_user_id = :user_id AND is_seen = 0 ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([':user_id' => $userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بتنسيق 12 ساعة


        if ($result) {


            return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);


        }



        return $result;

    }



    public function markAssignmentAsSeen($assignmentId)

    {

        $sql = "UPDATE driver_assignments SET is_seen = 1 WHERE id = :assignment_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':assignment_id' => $assignmentId]);

    }



    public function getDriverById($driverId)

    {

        try {

            $sql = "SELECT d.*, da.has_many_trips, u.username as hold_by_username

                    FROM drivers d 

                    LEFT JOIN driver_attributes da ON d.id = da.driver_id

                    LEFT JOIN users u ON d.hold_by = u.id

                    WHERE d.id = :driver_id";



            $stmt = $this->db->prepare($sql);

            $stmt->execute([':driver_id' => $driverId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);



            // تحويل التواريخ للعرض بتنسيق 12 ساعة


            if ($result) {


                return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);


            }



            return $result;

        } catch (PDOException $e) {

            error_log("Error in Call::getDriverById for ID {$driverId}: " . $e->getMessage());

            return false;

        }

    }



    public function getCallHistory($driver_id)

    {

        $sql = "

            (SELECT 

                'call' as event_type, 

                dc.id as event_id, 

                dc.created_at as event_date, 

                u.username as created_by,

                JSON_OBJECT(

                    'status', dc.call_status,

                    'notes', dc.notes,

                    'next_call_at', dc.next_call_at,

                    'category', cat.name,

                    'subcategory', subcat.name,

                    'code', code.name

                ) as details

            FROM driver_calls dc

            JOIN users u ON dc.call_by = u.id

            LEFT JOIN ticket_categories cat ON dc.ticket_category_id = cat.id

            LEFT JOIN ticket_subcategories subcat ON dc.ticket_subcategory_id = subcat.id

            LEFT JOIN ticket_codes code ON dc.ticket_code_id = code.id

            WHERE dc.driver_id = :driver_id1)

            UNION ALL

            (SELECT 

                'assignment' as event_type, 

                da.id as event_id, 

                da.created_at as event_date, 

                u_from.username as created_by,

                JSON_OBJECT(

                    'recipient_name', u_to.username,

                    'notes', da.note

                ) as details

            FROM driver_assignments da

            JOIN users u_from ON da.from_user_id = u_from.id

            JOIN users u_to ON da.to_user_id = u_to.id

            WHERE da.driver_id = :driver_id2)

            

            ORDER BY event_date DESC

        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([':driver_id1' => $driver_id, ':driver_id2' => $driver_id]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return \convert_dates_for_display_12h($results, ['event_date']);

    }



    public function getTodayCallsCount()

    {

        $sql = "SELECT COUNT(*) FROM driver_calls WHERE call_by = :user_id AND DATE(created_at) = CURDATE()";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([':user_id' => $_SESSION['user_id']]);

        return $stmt->fetchColumn();

    }



    public function getTotalPendingCalls()

    {

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM drivers WHERE main_system_status IN ('pending', 'reconsider', 'no_answer', 'rescheduled') AND hold = 0");

        $stmt->execute();

        return $stmt->fetchColumn();

    }



    public function getUsers()

    {

        // Get users who have the permission to make calls.

        // This is a simplified stand-in. A proper implementation would join with a permissions table.

        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE status = 'active' ORDER BY username ASC");

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return \convert_dates_for_display_12h($results, ['event_date']);

    }



    /**

     * Releases all drivers held by a specific user.

     * Useful for logout or session expiration.

     *

     * @param int $userId The ID of the user.

     * @return bool True on success, false on failure.

     */

    public function releaseAllHeldDrivers(int $userId): bool

    {

        // For now, the 'hold' is not user-specific. We will release the single

        // driver held in the session if it matches. A more robust implementation

        // would require a 'held_by_user_id' column in the drivers table.

        if (isset($_SESSION['locked_driver_id'])) {

            return $this->releaseDriverHold($_SESSION['locked_driver_id']);

        }

        return true; // No driver was held, so the operation is successful.

    }



    public function updateDriverAttribute($driverId, $hasManyTrips)

    {

        // This will insert a new record or update an existing one

        $sql = "INSERT INTO driver_attributes (driver_id, has_many_trips) 

                VALUES (:driver_id, :has_many_trips)

                ON DUPLICATE KEY UPDATE has_many_trips = :has_many_trips";



        $this->query($sql);

        $this->bind(':driver_id', $driverId);

        $this->bind(':has_many_trips', $hasManyTrips, \PDO::PARAM_BOOL);



        return $this->execute();

    }

    /**
     * Get call statistics for a date range
     */
    public function getCallStats($filters = [])
    {
        try {
            $startDate = $filters['start_date'] ?? date('Y-m-d');
            $endDate = $filters['end_date'] ?? date('Y-m-d');

            $sql = "SELECT
                        'incoming' as type,
                        COUNT(*) as count,
                        AVG(TIMESTAMPDIFF(SECOND, call_started_at, call_ended_at)) as avg_duration_seconds
                    FROM incoming_calls
                    WHERE DATE(call_started_at) BETWEEN ? AND ?

                    UNION ALL

                    SELECT
                        'outgoing' as type,
                        COUNT(*) as count,
                        NULL as avg_duration_seconds
                    FROM driver_calls
                    WHERE DATE(created_at) BETWEEN ? AND ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stats = [
                'incoming' => 0,
                'outgoing' => 0,
                'total' => 0,
                'avg_incoming_duration' => 0,
                'avg_outgoing_duration' => 0
            ];

            foreach ($results as $row) {
                $count = (int)$row['count'];
                $stats[$row['type']] = $count;
                $stats['total'] += $count;

                if ($row['type'] === 'incoming' && $row['avg_duration_seconds']) {
                    $stats['avg_incoming_duration'] = round((float)$row['avg_duration_seconds'], 1);
                }
            }

            return $stats;

        } catch (\Exception $e) {
            return [
                'incoming' => 0,
                'outgoing' => 0,
                'total' => 0,
                'avg_incoming_duration' => 0,
                'avg_outgoing_duration' => 0
            ];
        }
    }

}

