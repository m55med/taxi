<?php

namespace App\Models\Calls;

use App\Core\Model;
use PDO;
use PDOException;

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
                    SELECT t1.driver_id, t1.created_at, t1.next_call_at FROM driver_calls t1
                    INNER JOIN (
                        SELECT driver_id, MAX(id) as max_id FROM driver_calls GROUP BY driver_id
                    ) t2 ON t1.id = t2.max_id
                ) AS lc ON d.id = lc.driver_id
                LEFT JOIN driver_assignments a ON d.id = a.driver_id AND a.to_user_id = :userId AND a.is_seen = 0
                WHERE
                    d.hold = 0
                    AND (
                        d.main_system_status IN ('pending', 'reconsider') OR
                        (d.main_system_status IN ('no_answer', 'rescheduled') AND (lc.next_call_at IS NULL OR lc.next_call_at <= NOW()))
                    )
            ";

            if (!empty($skippedDriverIds)) {
                $in_keys = [];
                foreach ($skippedDriverIds as $key => $id) {
                    $in_keys[] = ":skipped_id_$key";
                    $queryParams[":skipped_id_$key"] = $id;
                }
                $sql .= " AND d.id NOT IN (" . implode(',', $in_keys) . ")";
            }

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
                }
                if (!$isTransactionActive) {
                    $this->db->commit();
                }
                return ['driver' => $driver, 'debug_info' => $debug];
            }

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
            $this->beginTransaction();
        }

        try {
            // 1. Insert the call record
            $this->query('INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at, ticket_category_id, ticket_subcategory_id, ticket_code_id) VALUES (:driver_id, :call_by, :call_status, :notes, :next_call_at, :ticket_category_id, :ticket_subcategory_id, :ticket_code_id)');
            $this->bind(':driver_id', $data['driver_id']);
            $this->bind(':call_by', $data['call_by']);
            $this->bind(':call_status', $data['call_status']);
            $this->bind(':notes', $data['notes']);
            $this->bind(':next_call_at', $data['next_call_at']);
            $this->bind(':ticket_category_id', $data['ticket_category_id']);
            $this->bind(':ticket_subcategory_id', $data['ticket_subcategory_id']);
            $this->bind(':ticket_code_id', $data['ticket_code_id']);
            
            if (!$this->execute()) {
                throw new PDOException("Failed to insert call record.");
            }

            // 2. Update driver's main_system_status based on call status
            $this->updateDriverStatusBasedOnCall($data['driver_id'], $data['call_status']);

            if (!$isTransactionActive) {
                $this->commit();
            }
            return true;

        } catch (PDOException $e) {
            if (!$isTransactionActive) {
                $this->rollBack();
            }
            error_log("Error in recordCall transaction: " . $e->getMessage());
            return false;
        }
    }

    public function updateDriverStatusBasedOnCall($driver_id, $call_status)
    {
        // No update needed if call was answered, status is handled by other processes
        if ($call_status === 'answered') {
            return true; 
        }

        $this->query("UPDATE drivers SET main_system_status = :status WHERE id = :driver_id");
        $this->bind(':status', $call_status); // Directly use call_status for simplicity
        $this->bind(':driver_id', $driver_id);
        return $this->execute();
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = "SELECT COUNT(*) FROM drivers WHERE hold = 0 AND main_system_status IN ('pending', 'reconsider', 'no_answer', 'rescheduled')";
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }

    public function getUsers()
    {
        $sql = "SELECT id, username, is_online FROM users WHERE status = 'active' ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function updateDriverAttribute($driverId, $hasManyTrips) {
        // This will insert a new record or update an existing one
        $sql = "INSERT INTO driver_attributes (driver_id, has_many_trips) 
                VALUES (:driver_id, :has_many_trips)
                ON DUPLICATE KEY UPDATE has_many_trips = :has_many_trips";
        
        $this->query($sql);
        $this->bind(':driver_id', $driverId);
        $this->bind(':has_many_trips', $hasManyTrips, \PDO::PARAM_BOOL);
        
        return $this->execute();
    }
}
