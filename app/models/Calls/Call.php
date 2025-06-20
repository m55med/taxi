<?php

namespace App\Models\Calls;

use App\Core\Model;
use PDO;
use PDOException;

class Call extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function getTodayCallsCount() {
        try {
            $userId = $_SESSION['user_id'];
            $sql = "SELECT COUNT(*) as count FROM driver_calls WHERE call_by = ? AND DATE(created_at) = CURDATE()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error in getTodayCallsCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalPendingCalls() {
        try {
            $sql = "SELECT COUNT(*) as count FROM drivers WHERE main_system_status IN ('pending', 'no_answer', 'rescheduled', 'reconsider')";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error in getTotalPendingCalls: " . $e->getMessage());
            return 0;
        }
    }

    public function findAndLockNextDriver($skippedDriverId = null) {
        try {
            $this->db->beginTransaction();

            // --- START DIAGNOSTIC ---
            // نستخدم استعلامًا بسيطًا جدًا لجلب أي سائق غير محجوز حاليًا.
            // إذا فشل هذا ، فهذا يعني أن جميع السائقين في حالة الحجز.
            $sql = "SELECT * FROM drivers WHERE hold = 0 AND main_system_status NOT IN ('completed', 'blocked') ORDER BY id ASC LIMIT 1";
            if ($skippedDriverId) {
                $sql = "SELECT * FROM drivers WHERE hold = 0 AND id != ? AND main_system_status NOT IN ('completed', 'blocked') ORDER BY id ASC LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$skippedDriverId]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);
            // --- END DIAGNOSTIC ---

            if ($driver) {
                $this->setDriverHold($driver['id'], true);
            }
            $this->db->commit();
            return $driver;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in findAndLockNextDriver: " . $e->getMessage());
            return null;
        }
    }

    public function getNextDriver($lock = false, $skippedDriverId = null)
    {
        try {
            $userId = $_SESSION['user_id'];
            
            // This optimized query joins driver_calls once to get the latest call details
            // which is more efficient than using subqueries in the WHERE clause.
            $sql = "
                SELECT 
                    d.id, d.name, d.phone, d.email, d.gender, d.nationality, 
                    d.app_status, d.main_system_status, d.data_source, d.created_at,
                    a.id as assignment_id,
                    dc_latest.created_at as last_call_date
                FROM drivers d
                LEFT JOIN (
                    SELECT driver_id, MAX(created_at) AS max_created_at
                    FROM driver_calls
                    GROUP BY driver_id
                ) dc_max ON d.id = dc_max.driver_id
                LEFT JOIN driver_calls dc_latest ON d.id = dc_latest.driver_id AND dc_latest.created_at = dc_max.max_created_at
                LEFT JOIN driver_assignments a ON d.id = a.driver_id AND a.to_user_id = :userId AND a.is_seen = 0
                WHERE d.hold = 0
                  AND (:skippedDriverId IS NULL OR d.id != :skippedDriverId)
                  AND (dc_latest.next_call_at IS NULL OR dc_latest.next_call_at <= NOW())
                  AND d.main_system_status NOT IN ('completed', 'blocked')
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':skippedDriverId' => $skippedDriverId
            ]);

            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($drivers)) {
                return null;
            }

            $prioritizedDriver = $this->prioritizeDrivers($drivers);

            if ($lock && $prioritizedDriver) {
                 $lockStmt = $this->db->prepare("SELECT * FROM drivers WHERE id = :id FOR UPDATE");
                 $lockStmt->execute([':id' => $prioritizedDriver['id']]);
                 return $prioritizedDriver;
            }
            
            return $prioritizedDriver;

        } catch (PDOException $e) {
            error_log("Error in getNextDriver: " . $e->getMessage());
            return null;
        }
    }

    private function prioritizeDrivers($drivers) {
        usort($drivers, function($a, $b) {
            // Define a function to get the priority level (lower is better)
            $get_priority = function($driver) {
                // Priority 1: Assigned to the current user
                if (!is_null($driver['assignment_id'])) {
                    return 1;
                }
                // Priority 2: Status-based priority as requested
                switch ($driver['main_system_status']) {
                    case 'rescheduled': return 2;
                    case 'no_answer':   return 3;
                    case 'reconsider':  return 4; // Keeping this important status
                    case 'pending':     return 5;
                }
                // Default to a low priority if status is not in the list
                return 99;
            };
    
            $a_priority = $get_priority($a);
            $b_priority = get_priority($b);
    
            // If priorities are different, sort by priority
            if ($a_priority !== $b_priority) {
                return $a_priority <=> $b_priority;
            }
    
            // Tie-breaking: If priorities are the same, older is better.
            // For 'pending' drivers (never called), use their creation date.
            // For others, use their last call date.
            $a_date = is_null($a['last_call_date']) ? $a['created_at'] : $a['last_call_date'];
            $b_date = is_null($b['last_call_date']) ? $b['created_at'] : $b['last_call_date'];
            
            // Sort ascending by date (oldest first)
            return strtotime($a_date) <=> strtotime($b_date);
        });
    
        return $drivers[0] ?? null;
    }

    public function findAndLockDriverByPhone($phone = null) {
        if (!$phone) return null;
        try {
            $this->db->beginTransaction();

            // First, find the driver by phone, regardless of their hold status.
            $findStmt = $this->db->prepare("SELECT * FROM drivers WHERE phone = :phone LIMIT 1");
            $findStmt->execute([':phone' => $phone]);
            $driver = $findStmt->fetch(PDO::FETCH_ASSOC);

            if ($driver) {
                // If found, explicitly release any previous hold and then apply a new one for the current user.
                // This ensures the user can access the driver even if it was stuck on hold.
                $this->setDriverHold($driver['id'], false); // Release old hold
                $this->setDriverHold($driver['id'], true);  // Apply new hold
            }

            $this->db->commit();
            return $driver;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in findAndLockDriverByPhone: " . $e->getMessage());
            return null;
        }
    }

    public function getUsers() {
        try {
            $stmt = $this->db->prepare("SELECT u.id, u.username, u.is_online, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.status = 'active' AND r.name IN ('employee', 'agent', 'quality_manager') ORDER BY u.is_online DESC, u.username ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUsers: " . $e->getMessage());
            return [];
        }
    }

    public function releaseDriverHold($driverId = null) {
        if (!$driverId) return false;
        return $this->setDriverHold($driverId, false);
    }

    private function setDriverHold($driverId, $isHeld) {
        try {
            $stmt = $this->db->prepare("UPDATE drivers SET hold = ? WHERE id = ?");
            return $stmt->execute([$isHeld ? 1 : 0, $driverId]);
        } catch (PDOException $e) {
            error_log("Error in setDriverHold: " . $e->getMessage());
            return false;
        }
    }

    public function getCallHistory($driverId = null) {
        if (!$driverId) return [];
        try {
            // Fetch calls
            $stmt_calls = $this->db->prepare("
                SELECT 
                    'call' as event_type, 
                    dc.id, 
                    dc.created_at as event_date, 
                    u.username as actor_name, 
                    dc.call_status as details, 
                    dc.notes, 
                    dc.next_call_at,
                    NULL as recipient_name
                FROM driver_calls dc 
                LEFT JOIN users u ON dc.call_by = u.id 
                WHERE dc.driver_id = ?
            ");
            $stmt_calls->execute([$driverId]);
            $calls = $stmt_calls->fetchAll(PDO::FETCH_ASSOC);

            // Fetch assignments
            $stmt_assignments = $this->db->prepare("
                SELECT 
                    'assignment' as event_type, 
                    da.id, 
                    da.created_at as event_date, 
                    u_from.username as actor_name, 
                    'assignment' as details, 
                    da.note as notes, 
                    NULL as next_call_at,
                    u_to.username as recipient_name
                FROM driver_assignments da
                LEFT JOIN users u_from ON da.from_user_id = u_from.id
                LEFT JOIN users u_to ON da.to_user_id = u_to.id
                WHERE da.driver_id = ?
            ");
            $stmt_assignments->execute([$driverId]);
            $assignments = $stmt_assignments->fetchAll(PDO::FETCH_ASSOC);

            // Merge and sort
            $history = array_merge($calls, $assignments);
            usort($history, function($a, $b) {
                return strtotime($b['event_date']) - strtotime($a['event_date']); // Sort descending
            });

            return $history;

        } catch (PDOException $e) {
            error_log("Error in getCallHistory: " . $e->getMessage());
            return [];
        }
    }

    public function recordCall($data = []) {
        if (empty($data['driver_id'])) return false;
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$data['driver_id'], $_SESSION['user_id'], $data['call_status'], $data['notes'], $data['next_call_at']]);
            if (!$result) {
                $this->db->rollBack();
                return false;
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in recordCall: " . $e->getMessage());
            return false;
        }
    }

    public function getDriver($agentId, $country_id = null, $car_type_id = null)
    {
        // Base query conditions for all drivers
        $baseConditions = "d.hold = 0 AND d.app_status != 'banned'";
        $params = [':agentId' => $agentId];

        if ($country_id) {
            $baseConditions .= " AND d.country_id = :country_id";
            $params[':country_id'] = $country_id;
        }
        if ($car_type_id) {
            $baseConditions .= " AND d.car_type_id = :car_type_id";
            $params[':car_type_id'] = $car_type_id;
        }

        $sql = "
            SELECT d.*, 
                   da.id as assignment_id,
                   CASE
                       WHEN da.to_user_id = :agentId AND da.is_seen = 0 THEN 1
                       WHEN dc.call_status = 'rescheduled' AND dc.next_call_at <= NOW() AND dc.call_by = :agentId THEN 2
                       WHEN d.main_system_status = 'reconsider' THEN 3
                       WHEN d.main_system_status = 'waiting_chat' THEN 4
                       WHEN d.main_system_status = 'no_answer' AND dc.created_at < (NOW() - INTERVAL 4 HOUR) THEN 5
                       WHEN d.main_system_status = 'pending' THEN 6
                       WHEN d.main_system_status = 'needs_documents' THEN 7
                       ELSE 8
                   END as priority
            FROM drivers d
            LEFT JOIN (
                SELECT * FROM driver_assignments da WHERE da.is_seen = 0
            ) da ON d.id = da.driver_id AND da.to_user_id = :agentId
            LEFT JOIN (
                SELECT * FROM driver_calls WHERE id IN (SELECT MAX(id) FROM driver_calls GROUP BY driver_id)
            ) dc ON d.id = dc.driver_id
            WHERE ($baseConditions)
              AND d.id NOT IN (SELECT driver_id FROM driver_calls WHERE call_by = :agentId AND created_at > (NOW() - INTERVAL 12 HOUR))
            HAVING priority <= 7
            ORDER BY priority ASC, d.created_at ASC
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $driver = $stmt->fetch(PDO::FETCH_OBJ);

            if ($driver && isset($driver->assignment_id)) {
                $assignStmt = $this->db->prepare("UPDATE driver_assignments SET is_seen = 1 WHERE id = :assignment_id");
                $assignStmt->execute([':assignment_id' => $driver->assignment_id]);
            }
            
            return $driver;

        } catch (PDOException $e) {
            error_log("Error in getDriver: " . $e->getMessage());
            return null;
        }
    }
} 