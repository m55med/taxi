<?php

namespace App\Models\Review;

use App\Core\Database;
use PDO;
use PDOException;

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

    public function getReviews($type, $reviewable_id)
    {
        // Map simple type to fully qualified class name
        $typeMap = [
            'ticket_detail' => 'App\\Models\\Tickets\\TicketDetail',
            'driver_call' => 'App\\Models\\Call\\DriverCall'
            // Add other reviewable types here
        ];

        if (!array_key_exists($type, $typeMap)) {
            error_log("Invalid reviewable type provided: " . $type);
            return [];
        }
        $reviewable_type_fqcn = $typeMap[$type];

        $sql = "SELECT r.*, u.username as reviewer_name 
                FROM reviews r
                JOIN users u ON r.reviewed_by = u.id
                WHERE (r.reviewable_type = :reviewable_type_fqcn OR r.reviewable_type = :reviewable_type_simple)
                AND r.reviewable_id = :reviewable_id
                ORDER BY r.reviewed_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':reviewable_type_fqcn' => $reviewable_type_fqcn,
                ':reviewable_type_simple' => $type,
                ':reviewable_id' => $reviewable_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getReviews: " . $e->getMessage());
            return [];
        }
    }

    public function addReview($type, $reviewable_id, $userId, $data)
    {
        // Map simple type to fully qualified class name
        $typeMap = [
            'ticket_detail' => 'App\\Models\\Tickets\\TicketDetail',
            'driver_call' => 'App\\Models\\Call\\DriverCall'
            // Add other reviewable types here
        ];

        if (!array_key_exists($type, $typeMap)) {
            error_log("Invalid reviewable type for addReview: " . $type);
            return false;
        }
        $reviewable_type = $typeMap[$type];

        $sql = "INSERT INTO reviews (reviewable_type, reviewable_id, reviewed_by, review_result, review_notes) 
                VALUES (:reviewable_type, :reviewable_id, :reviewed_by, :review_result, :review_notes)";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':reviewable_type' => $reviewable_type,
                ':reviewable_id' => $reviewable_id,
                ':reviewed_by' => $userId,
                ':review_result' => $data['review_result'],
                ':review_notes' => $data['review_notes']
            ]);
        } catch (PDOException $e) {
            error_log("Error in addReview: " . $e->getMessage());
            return false;
        }
    }

    public function getEntityIdForRedirect($reviewable_type, $reviewable_id)
    {
        if ($reviewable_type === 'ticket_detail') {
            $sql = "SELECT ticket_id FROM ticket_details WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $reviewable_id]);
            $ticket_id = $stmt->fetchColumn();
            return ['type' => 'ticket', 'id' => $ticket_id];
        }
        
        if ($reviewable_type === 'driver_call') {
            $sql = "SELECT driver_id FROM driver_calls WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $reviewable_id]);
            $driver_id = $stmt->fetchColumn();
            return ['type' => 'driver', 'id' => $driver_id];
        }

        if ($reviewable_type === 'ticket') {
            return ['type' => 'ticket', 'id' => $reviewable_id];
        }

        return null;
    }

    public function getReviewsForHistory(array $historyIds)
    {
        if (empty($historyIds)) {
            return [];
        }

        // We need to check for both the old simple type and the new FQCN
        $reviewable_type_simple = 'ticket_detail';
        $reviewable_type_fqcn = 'App\\Models\\Tickets\\TicketDetail';

        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($historyIds), '?'));

        $sql = "SELECT r.*, u.username as reviewer_name 
                FROM reviews r
                JOIN users u ON r.reviewed_by = u.id
                WHERE (r.reviewable_type = ? OR r.reviewable_type = ?)
                AND r.reviewable_id IN ($placeholders)
                ORDER BY r.reviewed_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind the parameters
            $params = array_merge([$reviewable_type_fqcn, $reviewable_type_simple], $historyIds);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getReviewsForHistory: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Efficiently fetches all reviews for a list of items of a specific type.
     * e.g., get all reviews for a list of driver_call IDs.
     *
     * @param string $type The simple type of the items (e.g., 'driver_call').
     * @param array $itemIds An array of item IDs.
     * @return array A list of reviews.
     */
    public function getReviewsForMultipleItems(string $type, array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        // Map simple type to FQCN for robust checking
        $typeMap = [
            'ticket_detail' => 'App\\Models\\Tickets\\TicketDetail',
            'driver_call'   => 'App\\Models\\Call\\DriverCall'
        ];
        $reviewable_type_fqcn = $typeMap[$type] ?? '';

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $sql = "SELECT r.*, u.username as reviewer_name 
                FROM reviews r
                JOIN users u ON r.reviewed_by = u.id
                WHERE (r.reviewable_type = ? OR r.reviewable_type = ?)
                AND r.reviewable_id IN ($placeholders)
                ORDER BY r.reviewed_at DESC";

        // Prepare parameters for execute()
        $params = [$type, $reviewable_type_fqcn, ...$itemIds];
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getReviewsForMultipleItems: " . $e->getMessage());
            return [];
        }
    }
} 