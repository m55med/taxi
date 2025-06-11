<?php

namespace App\Models\Call;

use App\Core\Model;
use PDO;
use PDOException;

class Calls extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function recordCall($data)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['driver_id'],
                $data['call_by'],
                $data['call_status'],
                $data['notes'],
                $data['next_call_at']
            ]);

            if (!$result) {
                $this->db->rollBack();
                error_log("Failed to insert call record for driver_id: " . $data['driver_id']);
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

    public function getCallHistory($driverId)
    {
        try {
            $sql = "
                (SELECT 
                    'call' as event_type,
                    dc.id as event_id,
                    dc.created_at as event_date,
                    dc.notes as notes,
                    dc.call_status as details,
                    u.username as actor_name,
                    NULL as recipient_name,
                    NULL as next_call_at
                FROM driver_calls dc
                LEFT JOIN users u ON dc.call_by = u.id
                WHERE dc.driver_id = :driverId1)
                
                UNION ALL
                
                (SELECT
                    'assignment' as event_type,
                    da.id as event_id,
                    da.created_at as event_date,
                    da.note as notes,
                    'تم التحويل' as details,
                    uf.username as actor_name,
                    ut.username as recipient_name,
                    NULL as next_call_at
                FROM driver_assignments da
                LEFT JOIN users uf ON da.from_user_id = uf.id
                LEFT JOIN users ut ON da.to_user_id = ut.id
                WHERE da.driver_id = :driverId2)
                
                ORDER BY event_date DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':driverId1' => $driverId, ':driverId2' => $driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error in getCallHistory: " . $e->getMessage());
            return [];
        }
    }

    public function updateDriverStatus($driverId, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE drivers SET main_system_status = ? WHERE id = ?");
            $result = $stmt->execute([$status, $driverId]);
            
            if ($result) {
                error_log("Successfully updated driver status: ID=$driverId, Status=$status");
            } else {
                error_log("Failed to update driver status: ID=$driverId, Status=$status");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in updateDriverStatus: " . $e->getMessage());
            return false;
        }
    }

    public function releaseDriverHold($driverId)
    {
        return $this->setDriverHold($driverId, false);
    }

    private function setDriverHold($driverId, $isHeld)
    {
        try {
            $stmt = $this->db->prepare("UPDATE drivers SET hold = ? WHERE id = ?");
            return $stmt->execute([$isHeld ? 1 : 0, $driverId]);
        } catch (PDOException $e) {
            error_log("Error in setDriverHold: " . $e->getMessage());
            return false;
        }
    }

    public function getTodayCallsCount()
    {
        try {
            $userId = $_SESSION['user_id'];
            $sql = "SELECT COUNT(*) as count 
                   FROM driver_calls 
                   WHERE call_by = ? 
                   AND DATE(created_at) = CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
            
        } catch (PDOException $e) {
            error_log("Error in getTodayCallsCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalPendingCalls()
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                   FROM drivers d
                   WHERE d.main_system_status IN ('pending', 'no_answer', 'rescheduled', 'reconsider')
                   AND (
                       -- Include drivers with no calls
                       NOT EXISTS (
                           SELECT 1 
                           FROM driver_calls dc 
                           WHERE dc.driver_id = d.id
                       )
                       OR
                       -- Include drivers with past due next_call_at
                       EXISTS (
                           SELECT 1 
                           FROM driver_calls dc 
                           WHERE dc.driver_id = d.id 
                           AND dc.next_call_at <= NOW()
                           ORDER BY dc.created_at DESC 
                           LIMIT 1
                       )
                   )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
            
        } catch (PDOException $e) {
            error_log("Error in getTotalPendingCalls: " . $e->getMessage());
            return 0;
        }
    }

    public function findAndLockNextDriver($excludeDriverId = null)
    {
        try {
            $this->db->beginTransaction();

            $driver = $this->getNextDriver(true, $excludeDriverId); // true to lock inside transaction

            if ($driver) {
                $this->setDriverHold($driver['id'], true);
                $this->db->commit();
                return $driver;
            } else {
                $this->db->rollBack();
                return null; // No available driver
            }

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in findAndLockNextDriver: " . $e->getMessage());
            return null;
        }
    }

    public function getNextDriver($lock = false, $excludeDriverId = null)
    {
        try {
            $userId = $_SESSION['user_id'];
            $params = [':userId' => $userId];
            
            // Base query with priority logic using LEFT JOIN for performance
            $sql = "SELECT 
                        d.id, d.name, d.phone, d.email, d.gender, c.name as nationality, ct.name as car_type, d.app_status, d.data_source, 
                        d.country_id, d.car_type_id, d.notes,
                        a.id as assignment_id
                    FROM drivers d
                    LEFT JOIN countries c ON d.country_id = c.id
                    LEFT JOIN car_types ct ON d.car_type_id = ct.id
                    LEFT JOIN driver_assignments a ON d.id = a.driver_id AND a.to_user_id = :userId AND a.is_seen = 0
                    LEFT JOIN (
                        SELECT driver_id, MAX(created_at) as last_call_time
                        FROM driver_calls
                        GROUP BY driver_id
                    ) latest_call ON d.id = latest_call.driver_id
                    LEFT JOIN driver_calls dc ON d.id = dc.driver_id AND dc.created_at = latest_call.last_call_time
                    WHERE d.hold = 0
                      AND (dc.id IS NULL OR dc.next_call_at IS NULL OR dc.next_call_at <= NOW())
                      AND d.main_system_status NOT IN ('completed', 'blocked')";
            
            if ($excludeDriverId) {
                $sql .= " AND d.id != :excludeDriverId";
                $params[':excludeDriverId'] = $excludeDriverId;
            }

            $sql .= " ORDER BY 
                        a.id IS NOT NULL DESC, -- Assigned first
                        d.main_system_status = 'reconsider' DESC,
                        dc.created_at ASC, -- Oldest call first
                        d.created_at ASC -- Oldest driver first
                    LIMIT 1";
            
            if ($lock) {
                $sql .= " FOR UPDATE";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error in getNextDriver: " . $e->getMessage());
            return null;
        }
    }

    public function findAndLockDriverByPhone($phone)
    {
        try {
            $this->db->beginTransaction();

            $userId = $_SESSION['user_id'];
            $sql = "SELECT 
                        d.id, d.name, d.phone, d.email, d.gender, c.name as nationality, ct.name as car_type, d.app_status, d.data_source,
                        d.country_id, d.car_type_id, d.notes,
                        d.main_system_status, d.registered_at, d.added_by, d.hold, d.has_missing_documents,
                        d.created_at, d.updated_at, a.id as assignment_id
                    FROM drivers d
                    LEFT JOIN countries c ON d.country_id = c.id
                    LEFT JOIN car_types ct ON d.car_type_id = ct.id
                    LEFT JOIN driver_assignments a ON d.id = a.driver_id AND a.to_user_id = :userId AND a.is_seen = 0
                    WHERE d.phone = :phone AND d.hold = 0 FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':phone' => $phone, ':userId' => $userId]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($driver) {
                $this->setDriverHold($driver['id'], true);
                $this->db->commit();
                return $driver;
            } else {
                $this->db->rollBack();
                return null; // Driver not found or already held
            }

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in findAndLockDriverByPhone: " . $e->getMessage());
            return null;
        }
    }

    public function getUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT 
                u.id, 
                u.username, 
                u.is_online,
                r.name as role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.status = 'active' 
            AND r.name IN ('employee', 'agent', 'quality_manager')
            ORDER BY u.is_online DESC, u.username ASC");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUsers: " . $e->getMessage());
            return [];
        }
    }

    public function getCountries()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM countries ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCountries: " . $e->getMessage());
            return [];
        }
    }

    public function getCarTypes()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM car_types ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCarTypes: " . $e->getMessage());
            return [];
        }
    }

    public function updateDriverInfo($data)
    {
        try {
            $sql = "UPDATE drivers SET 
                        name = :name, 
                        email = :email, 
                        gender = :gender, 
                        country_id = :country_id, 
                        car_type_id = :car_type_id,
                        app_status = :app_status,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :driver_id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':name' => $data['name'] ?? null,
                ':email' => $data['email'] ?? null,
                ':gender' => $data['gender'] ?? null,
                ':country_id' => !empty($data['country_id']) ? $data['country_id'] : null,
                ':car_type_id' => !empty($data['car_type_id']) ? $data['car_type_id'] : null,
                ':app_status' => $data['app_status'] ?? 'inactive',
                ':notes' => $data['notes'] ?? null,
                ':driver_id' => $data['driver_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateDriverInfo: " . $e->getMessage());
            return false;
        }
    }
    
    public function releaseAllHeldDrivers($userId)
    {
        // This function is intended to be implemented to release drivers held by a specific user.
        // For now, it's a placeholder.
        return true;
    }
}