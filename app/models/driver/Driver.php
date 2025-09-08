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
            // التحقق من وجود السائق
            $checkStmt = $this->db->prepare("SELECT id FROM drivers WHERE id = :id");
            $checkStmt->execute([':id' => $driverId]);
            if (!$checkStmt->fetch()) {
                return false;
            }

            $sql = "
                UPDATE drivers 
                SET app_status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $params = [
                ':id' => $driverId,
                ':status' => $status
            ];

            try {
                $this->db->beginTransaction();
                $result = $stmt->execute($params);
                if ($result) {
                    $rowCount = $stmt->rowCount();
                    if ($rowCount > 0) {
                        $this->db->commit();
                        return true;
                    } else {
                        $this->db->rollBack();
                        return false;
                    }
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log("PDO Exception in execute:" . $e->getMessage());
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception in prepare:" . $e->getMessage());
            return false;
        }
    }

    public function updateDocuments($driverId, $submittedDocIds, $notes)
    {
        try {
            $this->db->beginTransaction();

            // 1. Clear all existing document entries for this driver.
            $deleteStmt = $this->db->prepare("DELETE FROM driver_documents_required WHERE driver_id = :driver_id");
            $deleteStmt->execute([':driver_id' => $driverId]);

            // 2. Prepare the INSERT statement for the submitted documents.
            $insertStmt = $this->db->prepare("
                INSERT INTO driver_documents_required (driver_id, document_type_id, status, note, updated_by, updated_at)
                VALUES (:driver_id, :doc_id, 'submitted', :note, :user_id, NOW())
            ");

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
            
            $requiredTypesStmt = $this->db->prepare("SELECT id FROM document_types WHERE is_required = 1");
            $requiredTypesStmt->execute();
            $requiredDocTypes = $requiredTypesStmt->fetchAll(PDO::FETCH_COLUMN);

            $hasMissing = false;
            if (!empty($requiredDocTypes)) {
                $missingDocs = array_diff($requiredDocTypes, $submittedDocIds);
                if (!empty($missingDocs)) {
                    $hasMissing = true;
                }
            }

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

    public function bulkInsert($drivers, $commonData)
    {
        $stats = ['total' => count($drivers), 'added' => 0, 'skipped' => 0, 'errors' => 0, 'skipped_phones' => []];

        $this->db->beginTransaction();
        try {
            $sql_driver = "
                INSERT INTO drivers (name, phone, email, country_id, main_system_status, data_source, notes, added_by, car_type_id) 
                VALUES (:name, :phone, :email, :country_id, :main_system_status, :data_source, :notes, :added_by, :car_type_id)
            ";
            $stmt_driver = $this->db->prepare($sql_driver);

            $sql_attrib = "
                INSERT INTO driver_attributes (driver_id, has_many_trips) VALUES (:driver_id, 0)
            ";
            $stmt_attrib = $this->db->prepare($sql_attrib);
            
            $sql_docs = "
                INSERT INTO driver_documents_required (driver_id, document_type_id, status, updated_by)
                VALUES (:driver_id, :doc_id, 'missing', :user_id)
            ";
            $stmt_docs = $this->db->prepare($sql_docs);

            foreach ($drivers as $driver) {
                if (empty($driver['phone']) || $this->isPhoneExists($driver['phone'])) {
                    $stats['skipped']++;
                    $stats['skipped_phones'][] = $driver['phone'] ?? 'N/A';
                    continue;
                }

                $params_driver = [
                    ':name' => $driver['name'],
                    ':phone' => $driver['phone'],
                    ':email' => $driver['email'],
                    ':country_id' => $commonData['country_id'],
                    ':main_system_status' => $commonData['main_system_status'],
                    ':data_source' => $commonData['data_source'],
                    ':notes' => $commonData['notes'],
                    ':added_by' => $commonData['added_by'],
                    // Directly use the POST value to ensure integrity
                    ':car_type_id' => (int)($_POST['car_type_id'] ?? 1) 
                ];

                if ($stmt_driver->execute($params_driver)) {
                    $driverId = $this->db->lastInsertId();
                    
                    // Set trip attribute
                    $stmt_attrib->execute([':driver_id' => $driverId]);

                    // Set required documents
                    if (!empty($commonData['required_doc_ids'])) {
                        foreach ($commonData['required_doc_ids'] as $docId) {
                            $stmt_docs->execute([
                                ':driver_id' => $driverId,
                                ':doc_id'    => $docId,
                                ':user_id'   => $commonData['added_by']
                            ]);
                        }
                    }
                    $stats['added']++;
                } else {
                    $stats['errors']++;
                }
            }

            $this->db->commit();

            return ['status' => true, 'stats' => $stats];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in bulkInsert: " . $e->getMessage());
            $stats['errors'] = $stats['total'] - $stats['added'] - $stats['skipped'];
            return ['status' => false, 'message' => 'A database error occurred during the bulk insert.', 'stats' => $stats];
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
            $sql = "SELECT d.*, 
                           c.name as country_name,
                           ct.name as car_type_name,
                           u.username as added_by_username,
                           da.has_many_trips
                    FROM drivers d
                    LEFT JOIN countries c ON d.country_id = c.id
                    LEFT JOIN car_types ct ON d.car_type_id = ct.id
                    LEFT JOIN users u ON d.added_by = u.id
                    LEFT JOIN driver_attributes da ON d.id = da.driver_id
                    WHERE d.id = :driverId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':driverId' => $driverId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Driver->getById: " . $e->getMessage());
            return null;
        }
    }

    public function getCallHistory($driverId)
    {
        try {
            $sql = "SELECT 
                        dc.*, 
                        u.username as staff_name,
                        tc.name as category_name,
                        tsc.name as subcategory_name,
                        tco.name as code_name
                    FROM driver_calls dc
                    JOIN users u ON dc.call_by = u.id
                    LEFT JOIN ticket_categories tc ON dc.ticket_category_id = tc.id
                    LEFT JOIN ticket_subcategories tsc ON dc.ticket_subcategory_id = tsc.id
                    LEFT JOIN ticket_codes tco ON dc.ticket_code_id = tco.id
                    WHERE dc.driver_id = :driverId
                    ORDER BY dc.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':driverId' => $driverId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching call history: " . $e->getMessage());
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

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in assignDriver: " . $e->getMessage());
            return false;
        }
    }

    public function releaseHeldDrivers()
    {
        // Logic to release drivers held by the current user.
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return;

        $sql = "UPDATE drivers SET hold_by = NULL, held_at = NULL WHERE hold_by = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
    }
    
    public function searchByNameOrPhone($query)
{
    $sql = "SELECT 
                d.id, d.name, d.phone, d.email, 
                d.app_status, d.registered_at,
                c.name as country_name
            FROM drivers d
            LEFT JOIN countries c ON d.country_id = c.id
            WHERE d.name LIKE :name 
               OR d.phone LIKE :phone 
               OR d.email LIKE :email 
               OR d.id = :id
            ORDER BY d.name ASC
            LIMIT 25";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':name'  => '%' . $query . '%',
        ':phone' => '%' . $query . '%',
        ':email' => '%' . $query . '%',
        ':id'    => $query
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    

    public function searchByPhone($phoneQuery)
    {
        try {
            // Search for drivers where the phone number contains the query string.
            // Also join with users to see if the driver is on hold and by whom.
            $sql = "SELECT d.id, d.name, d.phone, d.hold, u.username as hold_by_username
                    FROM drivers d
                    LEFT JOIN users u ON d.hold_by = u.id
                    WHERE d.phone LIKE :query
                    LIMIT 10"; // Limit results to avoid overwhelming the UI
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':query' => '%' . $phoneQuery . '%']);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error in searchByPhone: " . $e->getMessage());
            // Return an empty array on error so the frontend doesn't break.
            return [];
        }
    }

    public function getDrivers($filters)
    {
        // ... implementation ...
    }

    public function updateCoreInfo($driverId, $data) {
        $sql = 'UPDATE drivers SET name = :name, email = :email, gender = :gender, country_id = :country_id, app_status = :app_status, car_type_id = :car_type_id, notes = :notes, updated_at = NOW() WHERE id = :id';
        
        $country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        $car_type_id = !empty($data['car_type_id']) ? $data['car_type_id'] : null;

        $params = [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':gender' => $data['gender'],
            ':country_id' => $country_id,
            ':app_status' => $data['app_status'],
            ':car_type_id' => $car_type_id,
            ':notes' => $data['notes'],
            ':id' => $driverId
        ];

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("ERROR in DriverModel::updateCoreInfo for ID {$driverId}: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateTripAttribute($driverId, $hasManyTrips) {
        $sql = 'INSERT INTO driver_attributes (driver_id, has_many_trips) VALUES (:driver_id, :has_many_trips) ON DUPLICATE KEY UPDATE has_many_trips = VALUES(has_many_trips)';
        $params = [
            ':driver_id' => $driverId,
            ':has_many_trips' => (int)(bool)($hasManyTrips ?? 0)
        ];

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("ERROR in DriverModel::updateTripAttribute for ID {$driverId}: " . $e->getMessage());
            return false;
        }
    }

    public function getFilteredDrivers($filters = [])
    {
        $select = "
            SELECT 
                d.id, d.name, d.phone, d.email, d.main_system_status, d.app_status,
                ct.name as car_type_name,
                da.has_many_trips,
                COUNT(DISTINCT dc.id) as call_count,
                COUNT(DISTINCT CASE WHEN ddr.status = 'missing' THEN ddr.document_type_id END) as missing_documents_count
        ";

        $from = "
            FROM drivers d
            LEFT JOIN car_types ct ON d.car_type_id = ct.id
            LEFT JOIN driver_attributes da ON d.id = da.driver_id
            LEFT JOIN driver_calls dc ON d.id = dc.driver_id
            LEFT JOIN driver_documents_required ddr ON d.id = ddr.driver_id
        ";

        $groupBy = " GROUP BY d.id, ct.name, da.has_many_trips";
        
        $params = [];
        $whereClauses = [];
        $havingClauses = [];

        if (!empty($filters['search_term'])) {
            $searchTerm = $filters['search_term'];
            $whereClauses[] = "(d.name LIKE :search_name OR d.phone LIKE :search_phone OR d.email LIKE :search_email OR d.id = :search_id)";
            $params[':search_name'] = '%' . $searchTerm . '%';
            $params[':search_phone'] = '%' . $searchTerm . '%';
            $params[':search_email'] = '%' . $searchTerm . '%';
            $params[':search_id'] = $searchTerm; // Exact match for ID
        }
        if (!empty($filters['main_system_status'])) {
            $whereClauses[] = "d.main_system_status = :main_status";
            $params[':main_status'] = $filters['main_system_status'];
        }
        if (!empty($filters['app_status'])) {
            $whereClauses[] = "d.app_status = :app_status";
            $params[':app_status'] = $filters['app_status'];
        }
        if (!empty($filters['car_type_id'])) {
            $whereClauses[] = "d.car_type_id = :car_type_id";
            $params[':car_type_id'] = $filters['car_type_id'];
        }
        if (!empty($filters['country_id'])) {
            $whereClauses[] = "d.country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }
        if (isset($filters['has_many_trips']) && $filters['has_many_trips'] !== '') {
            $whereClauses[] = "da.has_many_trips = :has_many_trips";
            $params[':has_many_trips'] = $filters['has_many_trips'];
        }
        if (isset($filters['has_missing_documents']) && $filters['has_missing_documents'] !== '') {
            $havingClauses[] = ($filters['has_missing_documents'] == '1') ? "missing_documents_count > 0" : "missing_documents_count = 0";
        }

        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $havingSql = "";
        if (!empty($havingClauses)) {
            $havingSql = " HAVING " . implode(' AND ', $havingClauses);
        }

        $totalSql = "SELECT COUNT(*) as total FROM (SELECT d.id " . $from . $whereSql . $groupBy . $havingSql . ") as sub";
        try {
            $totalStmt = $this->db->prepare($totalSql);
            $totalStmt->execute($params);
            $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('DriverModel::getFilteredDrivers Count Error: ' . $e->getMessage());
            return ['error' => 'Database count query failed: ' . $e->getMessage()];
        }

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 25;
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $offset = ($page - 1) * $limit;

        $dataSql = $select . $from . $whereSql . $groupBy . $havingSql . " ORDER BY d.created_at DESC LIMIT :limit OFFSET :offset";
        
        try {
            $dataStmt = $this->db->prepare($dataSql);
            $dataStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $dataStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => &$val) {
                $dataStmt->bindParam($key, $val);
            }
            $dataStmt->execute();
            $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $results, 'total' => $totalRecords, 'limit' => $limit, 'page' => $page,
                'total_pages' => ceil($totalRecords / $limit)
            ];
        } catch (PDOException $e) {
            error_log('DriverModel::getFilteredDrivers Data Error: ' . $e->getMessage());
            return ['error' => 'Database data query failed: ' . $e->getMessage()];
        }
    }

    public function getDriverStats($filters = [])
    {
        $stats = [
            'total' => 0, 'pending' => 0, 'waiting_chat' => 0, 'no_answer' => 0, 
            'rescheduled' => 0, 'completed' => 0, 'blocked' => 0, 
            'reconsider' => 0, 'needs_documents' => 0
        ];
        
        $params = [];
        $whereClauses = [];

        if (!empty($filters['date_from'])) {
            $whereClauses[] = "DATE(created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "DATE(created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereSql = "";
        if (!empty($whereClauses)) {
            $whereSql = " WHERE " . implode(' AND ', $whereClauses);
        }

        try {
            $sql = "SELECT main_system_status, COUNT(id) as count FROM drivers" . $whereSql . " GROUP BY main_system_status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($statusCounts as $status => $count) {
                if (array_key_exists($status, $stats)) {
                    $stats[$status] = (int)$count;
                }
            }

            $totalSql = "SELECT COUNT(id) FROM drivers" . $whereSql;
            $totalStmt = $this->db->prepare($totalSql);
            $totalStmt->execute($params);
            $stats['total'] = (int)$totalStmt->fetchColumn();

            return $stats;
        } catch (PDOException $e) {
            error_log('DriverModel::getDriverStats Error: ' . $e->getMessage());
            return $stats;
        }
    }

    public function bulkUpdate($driverIds, $field, $value)
    {
        if (empty($driverIds) || !is_array($driverIds) || empty($field)) {
            return false;
        }

        $allowedFields = ['main_system_status', 'app_status', 'car_type_id'];
        if (!in_array($field, $allowedFields)) {
            return false; // Invalid field for bulk update
        }
        
        $placeholders = implode(',', array_fill(0, count($driverIds), '?'));
        $sql = "UPDATE drivers SET `{$field}` = ?, updated_at = NOW() WHERE id IN ({$placeholders})";
        
        $params = array_merge([$value], $driverIds);

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("DriverModel::bulkUpdate Error: " . $e->getMessage());
            return false;
        }
    }
}