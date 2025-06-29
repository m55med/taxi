<?php

namespace App\Models\Driver;

use App\Core\Database;
use PDO;
use PDOException;
use Exception;

class Driver
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log("Error in Driver model constructor: " . $e->getMessage());
            throw $e;
        }
    }

    public function isPhoneExists($phone)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM drivers WHERE phone = :phone");
        $stmt->execute([':phone' => $phone]);
        return $stmt->fetchColumn() > 0;
    }

    public function update($data)
    {
        if (!isset($data['id']) || empty($data['id'])) {
            return false;
        }

        $driverId = $data['id'];
        unset($data['id']);

        // Whitelist fields that can be updated from this form.
        $allowedFields = [
            'name', 'email', 'gender', 'country_id', 
            'app_status', 'car_type_id', 'notes'
        ];
        
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            // Nothing to update
            return true; 
        }

        $setParts = [];
        $params = [':id' => $driverId];
        foreach ($updateData as $key => $value) {
            $setParts[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        $setClause = implode(', ', $setParts);

        try {
            $sql = "UPDATE drivers SET $setClause, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error in Driver->update: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($driverId, $status)
    {
        try {
            error_log("\n=== Driver Status Update Started ===");
            error_log("Updating status for driver ID: {$driverId} to {$status}");

            // التحقق من وجود السائق
            $checkStmt = $this->db->prepare("SELECT id FROM drivers WHERE id = :id");
            $checkStmt->execute([':id' => $driverId]);
            if (!$checkStmt->fetch()) {
                error_log("Error: Driver with ID {$driverId} not found");
                return false;
            }

            $sql = "
                UPDATE drivers 
                SET app_status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ";
            error_log("Prepared SQL: " . $sql);

            $stmt = $this->db->prepare($sql);
            error_log("Statement prepared successfully");

            $params = [
                ':id' => $driverId,
                ':status' => $status
            ];
            error_log("Parameters: " . json_encode($params));

            try {
                $this->db->beginTransaction();
                error_log("Transaction started");

                $result = $stmt->execute($params);
                error_log("Execute result: " . ($result ? "true" : "false"));

                if ($result) {
                    $rowCount = $stmt->rowCount();
                    error_log("Rows affected: " . $rowCount);

                    if ($rowCount > 0) {
                        $this->db->commit();
                        error_log("Transaction committed");
                        error_log("=== Driver Status Update Completed Successfully ===\n");
                        return true;
                    } else {
                        $this->db->rollBack();
                        error_log("No rows were updated - rolling back");
                        error_log("=== Driver Status Update Failed ===\n");
                        return false;
                    }
                } else {
                    $this->db->rollBack();
                    $error = $stmt->errorInfo();
                    error_log("Database error: " . json_encode($error));
                    error_log("=== Driver Status Update Failed ===\n");
                    return false;
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log("PDO Exception in execute:");
                error_log("Message: " . $e->getMessage());
                error_log("Code: " . $e->getCode());
                error_log("=== Driver Status Update Failed ===\n");
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception in prepare:");
            error_log("Message: " . $e->getMessage());
            error_log("Code: " . $e->getCode());
            error_log("=== Driver Status Update Failed ===\n");
            return false;
        }
    }

    public function updateDocuments($driverId, $submittedDocIds, $notes)
    {
        try {
            $this->db->beginTransaction();

            // 1. Clear all existing document entries for this driver.
            // This is the simplest way to handle additions, updates, and removals.
            $deleteStmt = $this->db->prepare("DELETE FROM driver_documents_required WHERE driver_id = :driver_id");
            $deleteStmt->execute([':driver_id' => $driverId]);

            // 2. Prepare the INSERT statement for the submitted documents.
            $insertStmt = $this->db->prepare("
                INSERT INTO driver_documents_required (driver_id, document_type_id, status, note, updated_by, updated_at)
                VALUES (:driver_id, :doc_id, 'submitted', :note, :user_id, NOW())
            ");

            // 3. Loop through ONLY the submitted document IDs and insert them.
            if (!empty($submittedDocIds)) {
                foreach ($submittedDocIds as $docId) {
                    $note = isset($notes[$docId]) ? trim($notes[$docId]) : null;
                    $insertStmt->execute([
                        ':driver_id' => $driverId,
                        ':doc_id'    => $docId,
                        ':note'      => $note,
                        ':user_id'   => $_SESSION['user_id']
                    ]);
                }
            }
            
            // 4. Get all required document types to check for missing ones.
            $requiredTypesStmt = $this->db->prepare("SELECT id FROM document_types WHERE is_required = 1");
            $requiredTypesStmt->execute();
            $requiredDocTypes = $requiredTypesStmt->fetchAll(PDO::FETCH_COLUMN);

            // 5. Determine if the driver has any missing *required* documents.
            $hasMissing = false;
            if (!empty($requiredDocTypes)) {
                // Find which required docs are missing from the submitted list.
                $missingDocs = array_diff($requiredDocTypes, $submittedDocIds);
                if (!empty($missingDocs)) {
                    $hasMissing = true;
                }
            }

            // 6. Update the has_missing_documents flag in the main drivers table.
            $updateFlagStmt = $this->db->prepare("
                UPDATE drivers 
                SET has_missing_documents = :has_missing,
                    updated_at = NOW()
                WHERE id = :driver_id
            ");
            $updateFlagStmt->execute([
                ':driver_id' => $driverId,
                ':has_missing' => (int)$hasMissing
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in updateDocuments: " . $e->getMessage());
            return false;
        }
    }

    public function bulkInsert($drivers)
    {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO drivers (
                    name, phone, email, rating, car_type_id, 
                    app_status, data_source, added_by
                ) VALUES (
                    :name, :phone, :email, :rating, :car_type_id,
                    :app_status, :data_source, :added_by
                )
            ");

            $stats = [
                'total' => count($drivers),
                'added' => 0,
                'skipped' => 0,
                'skipped_phones' => []
            ];

            foreach ($drivers as $driver) {
                // التحقق من وجود رقم الهاتف
                if ($this->isPhoneExists($driver['phone'])) {
                    $stats['skipped']++;
                    $stats['skipped_phones'][] = $driver['phone'];
                    continue;
                }

                $result = $stmt->execute([
                    ':name' => $driver['name'],
                    ':phone' => $driver['phone'],
                    ':email' => $driver['email'],
                    ':rating' => $driver['rating'],
                    ':car_type_id' => $driver['car_type_id'],
                    ':app_status' => $driver['app_status'],
                    ':data_source' => $driver['data_source'],
                    ':added_by' => $driver['added_by']
                ]);

                if ($result) {
                    $stats['added']++;
                }
            }

            $this->db->commit();

            // تحضير رسالة النجاح
            $message = sprintf(
                'تم إضافة %d من %d سائق. تم تخطي %d سائق لوجود أرقام هواتفهم مسبقاً.',
                $stats['added'],
                $stats['total'],
                $stats['skipped']
            );

            return [
                'status' => true,
                'stats' => $stats,
                'message' => $message
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Bulk insert error: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء إضافة البيانات: ' . $e->getMessage()
            ];
        }
    }

    public function getAllDrivers()
    {
        try {
            $stmt = $this->db->query("
                SELECT d.*, ct.name as car_type_name, u.username as added_by_name
                FROM drivers d
                LEFT JOIN car_types ct ON d.car_type_id = ct.id
                LEFT JOIN users u ON d.added_by = u.id
                ORDER BY d.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all drivers error: " . $e->getMessage());
            return [];
        }
    }

    public function getDriversReport($filters = [])
    {
        try {
            $sql = "SELECT 
                        d.*,
                        u.username as added_by_name
                    FROM drivers d
                    LEFT JOIN users u ON d.added_by = u.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['main_system_status'])) {
                $sql .= " AND d.main_system_status = ?";
                $params[] = $filters['main_system_status'];
            }

            if (!empty($filters['data_source'])) {
                $sql .= " AND d.data_source = ?";
                $params[] = $filters['data_source'];
            }

            if (!empty($filters['added_by'])) {
                $sql .= " AND d.added_by = ?";
                $params[] = $filters['added_by'];
            }

            if (isset($filters['has_missing_documents'])) {
                $sql .= " AND d.has_missing_documents = ?";
                $params[] = $filters['has_missing_documents'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(d.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(d.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY d.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDriversReport: " . $e->getMessage());
            return [];
        }
    }

    public function getConversionRates($filters = [])
    {
        try {
            $sql = "SELECT 
                        data_source,
                        COUNT(*) as total_drivers,
                        COUNT(CASE WHEN main_system_status = 'completed' THEN 1 END) as completed_drivers,
                        ROUND((COUNT(CASE WHEN main_system_status = 'completed' THEN 1 END) / COUNT(*)) * 100, 2) as conversion_rate
                    FROM drivers
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " GROUP BY data_source";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getConversionRates: " . $e->getMessage());
            return [];
        }
    }

    public function getById($driverId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, c.name as country_name, ct.name as car_type_name, u.username as added_by_username
                FROM drivers d
                LEFT JOIN countries c ON d.country_id = c.id
                LEFT JOIN car_types ct ON d.car_type_id = ct.id
                LEFT JOIN users u ON d.added_by = u.id
                WHERE d.id = :driver_id
            ");
            $stmt->execute([':driver_id' => $driverId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return null;
        }
    }

    public function getCallHistory($driverId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT dc.*, u.username as staff_name 
                FROM driver_calls dc 
                LEFT JOIN users u ON dc.call_by = u.id 
                WHERE dc.driver_id = :driver_id 
                ORDER BY dc.created_at DESC
            ");
            $stmt->execute([':driver_id' => $driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCallHistory: " . $e->getMessage());
            return [];
        }
    }

    public function getAssignmentHistory($driverId)
    {
        $sql = "SELECT da.created_at, da.note, u_from.username as from_username, u_to.username as to_username
                FROM driver_assignments da
                JOIN users u_from ON da.from_user_id = u_from.id
                JOIN users u_to ON da.to_user_id = u_to.id
                WHERE da.driver_id = :driver_id
                ORDER BY da.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':driver_id' => $driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignableUsers()
    {
        // Fetches users who can be assigned a driver (e.g., agents, team leaders)
        $sql = "SELECT id, username, is_online, role_id FROM users WHERE status = 'active' AND role_id IN (3, 4, 5)"; // Assuming roles 3,4,5 are agent, leader, quality
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignDriver($driverId, $fromUserId, $toUserId, $note)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO driver_assignments (driver_id, from_user_id, to_user_id, note)
                VALUES (:driver_id, :from_user_id, :to_user_id, :note)
            ");
            $stmt->execute([
                ':driver_id' => $driverId,
                ':from_user_id' => $fromUserId,
                ':to_user_id' => $toUserId,
                ':note' => $note,
            ]);

            // $updateStmt = $this->db->prepare("UPDATE drivers SET assigned_to = :to_user_id WHERE id = :driver_id");
            // $updateStmt->execute([':to_user_id' => $toUserId, ':driver_id' => $driverId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in assignDriver: " . $e->getMessage());
            return false;
        }
    }

    // الموديل المسؤل عن تحرير الهولد في النظام بعد 5 دقائق من اخر تحديث علي الصف في الصفوف المعلقة
    public function releaseHeldDrivers()
    {
        try {
            $sql = "
                UPDATE drivers
                SET hold = 0
                WHERE hold = 1
                  AND updated_at <= NOW() - INTERVAL 5 MINUTE
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return ['status' => true, 'affected_rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            error_log("Error in releaseHeldDrivers: " . $e->getMessage());
            return ['status' => false, 'message' => 'Database error.'];
        }
    }

    public function searchByPhone($phoneQuery)
    {
        try {
            $sql = "SELECT id, name, phone FROM drivers WHERE phone LIKE CONCAT(:query, '%') LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':query' => $phoneQuery]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in searchByPhone: " . $e->getMessage());
            return [];
        }
    }
} 