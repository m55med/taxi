<?php

namespace App\Models\Review;

use App\Core\Database;
use PDO;

class Review {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getWaitingDrivers($filters = []) {
        try {
            $conditions = ["1=1"];
            $params = [];

            // إضافة فلتر الحالة
            if (!empty($filters['status'])) {
                $conditions[] = "d.main_system_status = ?";
                $params[] = $filters['status'];
            } else {
                $conditions[] = "d.main_system_status IN ('waiting_chat', 'completed', 'reconsider')";
            }

            // إضافة فلتر البحث
            if (!empty($filters['search'])) {
                $conditions[] = "(d.name LIKE ? OR d.phone LIKE ?)";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
            }

            $sql = "SELECT 
                        d.*,
                        COALESCE(
                            (SELECT notes 
                             FROM driver_calls 
                             WHERE driver_id = d.id 
                             ORDER BY created_at DESC 
                             LIMIT 1
                            ), ''
                        ) as last_call_notes
                    FROM drivers d
                    WHERE " . implode(" AND ", $conditions) . "
                    ORDER BY 
                        CASE 
                            WHEN d.main_system_status = 'waiting_chat' THEN 1
                            WHEN d.main_system_status = 'reconsider' AND d.hold = 1 THEN 2
                            WHEN d.main_system_status = 'reconsider' AND d.hold = 0 THEN 3
                            ELSE 4
                        END,
                        d.updated_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getWaitingDrivers: " . $e->getMessage());
            return [];
        }
    }

    public function getDriverDetails($driverId) {
        try {
            // جلب بيانات السائق
            $stmt = $this->db->prepare("SELECT * FROM drivers WHERE id = ?");
            $stmt->execute([$driverId]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$driver) {
                return null;
            }

            // جلب المستندات المطلوبة
            $documentsQuery = "
                SELECT 
                    dt.id,
                    dt.name,
                    COALESCE(ddr.status, 'missing') as status,
                    ddr.note
                FROM document_types dt
                LEFT JOIN driver_documents_required ddr 
                    ON dt.id = ddr.document_type_id 
                    AND ddr.driver_id = ?
                ORDER BY dt.name ASC";
            
            $stmt = $this->db->prepare($documentsQuery);
            $stmt->execute([$driverId]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'driver' => $driver,
                'documents' => $documents
            ];
        } catch (PDOException $e) {
            error_log("Error in getDriverDetails: " . $e->getMessage());
            return null;
        }
    }

    public function updateDriver($data) {
        try {
            $this->db->beginTransaction();

            $driverId = $data['driver_id'];
            $newStatus = $data['status'];
            $notes = $data['notes'];
            $userId = $_SESSION['user_id'];
            $documents = $data['documents'] ?? [];

            // 1. تحديث حالة السائق في جدول drivers
            $stmt = $this->db->prepare("
                UPDATE drivers 
                SET main_system_status = ?,
                    notes = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newStatus, $notes, $driverId]);

            // 2. تسجيل المكالمة في جدول driver_calls بناءً على الحالة
            $callStatus = '';
            if ($newStatus === 'completed') {
                $callStatus = 'answered';
            } elseif ($newStatus === 'reconsider') {
                $callStatus = 'rescheduled';
            }

            if (!empty($callStatus)) {
                $stmt = $this->db->prepare("
                    INSERT INTO driver_calls 
                        (driver_id, call_by, call_status, notes, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$driverId, $userId, $callStatus, $notes]);
            }

            // 3. تحديث المستندات (قبول/رفض)
            if (!empty($documents)) {
                $stmt = $this->db->prepare("
                    INSERT INTO driver_documents_required 
                        (driver_id, document_type_id, status, note, updated_by, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        status = VALUES(status),
                        note = VALUES(note),
                        updated_by = VALUES(updated_by),
                        updated_at = NOW()
                ");

                foreach ($documents as $docId => $docData) {
                    $stmt->execute([
                        $driverId,
                        $docId,
                        $docData['status'],
                        $docData['note'] ?? '',
                        $userId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in updateDriver: " . $e->getMessage());
            return false;
        }
    }

    public function transferDriver($data) {
        try {
            $this->db->beginTransaction();

            // إنشاء تحويل جديد
            $stmt = $this->db->prepare("
                INSERT INTO driver_assignments 
                    (driver_id, from_user_id, to_user_id, note, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $data['driver_id'],
                $_SESSION['user_id'],
                $data['to_user_id'],
                $data['note']
            ]);

            if (!$result) {
                throw new PDOException("Failed to create assignment");
            }

            // تسجيل المكالمة
            $stmt = $this->db->prepare("
                INSERT INTO driver_calls 
                    (driver_id, call_by, call_status, notes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['driver_id'],
                $_SESSION['user_id'],
                'transferred',
                "تم التحويل إلى موظف آخر. " . $data['note']
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in transferDriver: " . $e->getMessage());
            return false;
        }
    }
} 