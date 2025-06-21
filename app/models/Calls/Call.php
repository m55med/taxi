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

    public function findAndLockDriverByPhone($phone)
    {
        try {
            $this->db->beginTransaction();

            // First, try for an exact match
            $sql = "SELECT * FROM drivers WHERE phone = :phone AND hold = 0 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':phone' => $phone]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            // If no exact match, try a broader search (e.g., for partial numbers)
            if (!$driver) {
                $sql = "SELECT * FROM drivers WHERE phone LIKE :phone AND hold = 0 ORDER BY created_at DESC LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':phone' => '%' . $phone . '%']);
                $driver = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($driver) {
                // Lock the driver to prevent others from accessing it
                $this->setDriverHold($driver['id'], true);
                $this->db->commit();
                return $driver;
            }

            $this->db->commit();
            return null;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in findAndLockDriverByPhone: " . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // CALL & DRIVER STATUS MANAGEMENT
    // =================================================================

    public function recordCall($data)
    {
        $sql = "INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at) VALUES (:driver_id, :call_by, :call_status, :notes, :next_call_at)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateDriverStatusBasedOnCall($driverId, $callStatus)
    {
        $newStatus = '';
        switch ($callStatus) {
            case 'answered':
                $newStatus = 'waiting_chat';
                break;
            case 'no_answer':
                $newStatus = 'no_answer';
                break;
            case 'rescheduled':
            case 'busy':
            case 'not_available':
                $newStatus = 'rescheduled';
                break;
            case 'wrong_number':
                $newStatus = 'blocked';
                break;
        }

        if (!empty($newStatus)) {
            $sql = "UPDATE drivers SET main_system_status = :status WHERE id = :driver_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':status' => $newStatus, ':driver_id' => $driverId]);
        }
        return true;
    }

    public function releaseDriverHold($driverId)
    {
        return $this->setDriverHold($driverId, false);
    }

    public function setDriverHold($driverId, $isHeld)
    {
        $sql = "UPDATE drivers SET hold = :is_held WHERE id = :driver_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':is_held' => (int) $isHeld, ':driver_id' => $driverId]);
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
        $stmt = $this->db->prepare("SELECT * FROM drivers WHERE id = :id");
        $stmt->execute([':id' => $driverId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCallHistory($driverId)
    {
        $sql = "
            (SELECT 
                'call' as event_type, 
                dc.id as event_id, 
                dc.created_at as event_date, 
                dc.call_by as actor_id, 
                u.username as actor_name, 
                dc.call_status as details, 
                dc.notes, 
                dc.next_call_at,
                NULL as recipient_id,
                NULL as recipient_name
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            WHERE dc.driver_id = :driver_id1)
            
            UNION
            
            (SELECT 
                'assignment' as event_type, 
                da.id as event_id, 
                da.created_at as event_date, 
                da.from_user_id as actor_id, 
                u_from.username as actor_name,
                NULL as details, 
                da.note as notes, 
                NULL as next_call_at,
                da.to_user_id as recipient_id,
                u_to.username as recipient_name
            FROM driver_assignments da
            JOIN users u_from ON da.from_user_id = u_from.id
            JOIN users u_to ON da.to_user_id = u_to.id
            WHERE da.driver_id = :driver_id2)
            
            ORDER BY event_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':driver_id1' => $driverId, ':driver_id2' => $driverId]);
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
}
