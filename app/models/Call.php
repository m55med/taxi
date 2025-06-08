<?php

class Call {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDriversWithStatus() {
        try {
            $userId = $_SESSION['user_id'];
            
            $sql = "SELECT 
                d.*,
                COALESCE(a.id, 0) as assignment_id,
                COALESCE(
                    (SELECT notes 
                     FROM driver_calls 
                     WHERE driver_id = d.id 
                     ORDER BY created_at DESC 
                     LIMIT 1
                    ), ''
                ) as last_call_notes,
                d.has_missing_documents
            FROM drivers d
            LEFT JOIN driver_assignments a ON d.id = a.driver_id 
                AND a.to_user_id = ? 
                AND a.is_seen = 0
            WHERE d.main_system_status IN ('pending', 'waiting_chat', 'needs_documents', 'no_answer', 'rescheduled')
            ORDER BY 
                CASE WHEN a.id IS NOT NULL THEN 0 ELSE 1 END,
                CASE 
                    WHEN d.has_missing_documents = 1 THEN 0 
                    WHEN d.main_system_status = 'needs_documents' THEN 1
                    WHEN d.main_system_status = 'no_answer' THEN 2
                    WHEN d.main_system_status = 'rescheduled' THEN 3
                    ELSE 4 
                END,
                d.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log the number of results for debugging
            error_log("Retrieved " . count($results) . " drivers from database");
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getDriversWithStatus: " . $e->getMessage());
            error_log("SQL: " . $sql);
            return [];
        }
    }

    public function getUsers() {
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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Retrieved " . count($results) . " users from database");
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getUsers: " . $e->getMessage());
            return [];
        }
    }

    public function recordCall($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO driver_calls (driver_id, call_by, call_status, notes, next_call_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['driver_id'],
                $data['user_id'],
                $data['status'],
                $data['notes'],
                $data['next_call_at']
            ]);

            if (!$result) {
                $this->db->rollBack();
                error_log("Failed to insert call record");
                return false;
            }

            // تحرير حالة الـ hold بعد تسجيل المكالمة
            $this->setDriverHold($data['driver_id'], false);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in recordCall: " . $e->getMessage());
            return false;
        }
    }

    public function updateDriverStatus($driverId, $status) {
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

    public function assignDriver($data) {
        try {
            $this->db->beginTransaction();

            // إلغاء أي تحويلات سابقة غير مشاهدة لنفس السائق
            $stmt = $this->db->prepare("UPDATE driver_assignments 
                                      SET is_seen = 1 
                                      WHERE driver_id = ? 
                                      AND is_seen = 0");
            $stmt->execute([$data['driver_id']]);

            // إنشاء تحويل جديد
            $stmt = $this->db->prepare("INSERT INTO driver_assignments 
                                      (driver_id, from_user_id, to_user_id, note, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
            
            $result = $stmt->execute([
                $data['driver_id'],
                $data['from_user_id'],
                $data['to_user_id'],
                $data['note']
            ]);

            if (!$result) {
                $this->db->rollBack();
                error_log("Failed to create new assignment");
                return false;
            }

            // تحرير حالة الـ hold عند التحويل
            $this->setDriverHold($data['driver_id'], false);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in assignDriver: " . $e->getMessage());
            return false;
        }
    }

    public function markAssignmentAsSeen($assignmentId) {
        try {
            $stmt = $this->db->prepare("UPDATE driver_assignments SET is_seen = 1 WHERE id = ?");
            return $stmt->execute([$assignmentId]);
        } catch (PDOException $e) {
            error_log("Error in markAssignmentAsSeen: " . $e->getMessage());
            return false;
        }
    }

    public function getCallHistory($driverId) {
        try {
            $stmt = $this->db->prepare("SELECT 
                dc.*, 
                u.username as staff_name 
            FROM driver_calls dc 
            LEFT JOIN users u ON dc.call_by = u.id 
            WHERE dc.driver_id = ? 
            ORDER BY dc.created_at DESC");
            
            $stmt->execute([$driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCallHistory: " . $e->getMessage());
            return [];
        }
    }

    public function getRequiredDocuments($driverId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM driver_documents_required WHERE driver_id = ?");
            $stmt->execute([$driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRequiredDocuments: " . $e->getMessage());
            return [];
        }
    }

    public function getNextDriver($lock = false) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Base query with priority logic
            $sql = "SELECT d.id, d.name, d.phone, d.email, d.gender, d.nationality, d.app_status, d.data_source, a.id as assignment_id
                    FROM drivers d
                    LEFT JOIN driver_assignments a ON d.id = a.driver_id AND a.to_user_id = :userId AND a.is_seen = 0
                    WHERE d.hold = 0
                      AND (
                          (SELECT next_call_at FROM driver_calls WHERE driver_id = d.id ORDER BY created_at DESC LIMIT 1) IS NULL OR
                          (SELECT next_call_at FROM driver_calls WHERE driver_id = d.id ORDER BY created_at DESC LIMIT 1) <= NOW()
                      )
                      AND d.main_system_status NOT IN ('completed', 'blocked')
                    ORDER BY 
                        a.id IS NOT NULL DESC, -- Assigned first
                        d.main_system_status = 'reconsider' DESC,
                        (SELECT created_at FROM driver_calls WHERE driver_id = d.id ORDER BY created_at DESC LIMIT 1) ASC, -- Oldest call first
                        d.created_at ASC -- Oldest driver first
                    LIMIT 1";
            
            if ($lock) {
                $sql .= " FOR UPDATE";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':userId' => $userId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error in getNextDriver: " . $e->getMessage());
            return null;
        }
    }

    public function findAndLockNextDriver() {
        try {
            $this->db->beginTransaction();

            $driver = $this->getNextDriver(true); // true to lock inside transaction

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

    public function releaseDriverHold($driverId) {
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

    public function releaseAllHeldDrivers($userId) {
        // This function might be useful for a logout process
        // For now, it's not used but good to have
        // It needs a way to associate a held driver with a user, e.g., a `held_by_user_id` column
        return true; 
    }

    public function getTodayCallsCount() {
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

    public function getTotalPendingCalls() {
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

    public function getDriverByPhone($phone) {
        try {
            $sql = "SELECT 
                d.*,
                COALESCE(
                    (SELECT notes 
                     FROM driver_calls 
                     WHERE driver_id = d.id 
                     ORDER BY created_at DESC 
                     LIMIT 1
                    ), ''
                ) as last_call_notes,
                COALESCE(
                    (SELECT next_call_at 
                     FROM driver_calls 
                     WHERE driver_id = d.id 
                     ORDER BY created_at DESC 
                     LIMIT 1
                    ), NULL
                ) as next_scheduled_call
            FROM drivers d
            WHERE d.phone = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$phone]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error in getDriverByPhone: " . $e->getMessage());
            return null;
        }
    }

    public function getCallsReport($filters = [])
    {
        try {
            $sql = "SELECT 
                        c.*,
                        d.name as driver_name,
                        d.phone as driver_phone,
                        u.username as staff_name
                    FROM driver_calls c
                    LEFT JOIN drivers d ON c.driver_id = d.id
                    LEFT JOIN users u ON c.call_by = u.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['call_status'])) {
                $sql .= " AND c.call_status = ?";
                $params[] = $filters['call_status'];
            }

            if (!empty($filters['call_by'])) {
                $sql .= " AND c.call_by = ?";
                $params[] = $filters['call_by'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(c.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(c.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCallsReport: " . $e->getMessage());
            return [];
        }
    }

    public function getCallAnalysis($filters = [])
    {
        try {
            $sql = "SELECT 
                        u.username as staff_name,
                        c.call_status,
                        COUNT(*) as total_calls,
                        COUNT(CASE WHEN c.call_status = 'answered' THEN 1 END) as answered_calls,
                        COUNT(CASE WHEN c.call_status = 'no_answer' THEN 1 END) as no_answer_calls,
                        COUNT(CASE WHEN c.call_status = 'busy' THEN 1 END) as busy_calls,
                        COUNT(CASE WHEN c.call_status = 'rescheduled' THEN 1 END) as rescheduled_calls
                    FROM driver_calls c
                    LEFT JOIN users u ON c.call_by = u.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(c.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(c.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " GROUP BY u.username, c.call_status";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCallAnalysis: " . $e->getMessage());
            return [];
        }
    }

    public function findAndLockDriverByPhone($phone) {
        try {
            $this->db->beginTransaction();

            $userId = $_SESSION['user_id'];
            $sql = "SELECT d.*, a.id as assignment_id
                    FROM drivers d
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
} 