<?php
namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;

class Coupon {
    private $pdo;
    const HOLD_DURATION_MINUTES = 5; // Hold coupons for 5 minutes

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Get a paginated and filtered list of coupons.
     */
    public function getCoupons($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, 
                       cnt.name as country_name, 
                       u_used.username as used_by_username,
                       u_held.username as held_by_username,
                       t.ticket_number
                FROM coupons c
                LEFT JOIN countries cnt ON c.country_id = cnt.id
                LEFT JOIN users u_used ON c.used_by = u_used.id
                LEFT JOIN users u_held ON c.held_by = u_held.id
                LEFT JOIN tickets t ON c.used_in_ticket = t.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND c.code LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $sql .= " AND c.is_used = :is_used";
            $params[':is_used'] = $filters['is_used'];
        }
        if (!empty($filters['country_id'])) {
            $sql .= " AND c.country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt_paginated = $this->pdo->prepare($sql);
        $stmt_paginated->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt_paginated->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => &$val) {
            $stmt_paginated->bindParam($key, $val);
        }
        $stmt_paginated->execute();
        $results = $stmt_paginated->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $count_sql = "SELECT COUNT(id) FROM coupons WHERE 1=1";
         if (!empty($filters['search'])) {
            $count_sql .= " AND code LIKE :search";
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $count_sql .= " AND is_used = :is_used";
        }
        if (!empty($filters['country_id'])) {
            $count_sql .= " AND country_id = :country_id";
        }

        $stmt_count = $this->pdo->prepare($count_sql);
        if(!empty($params)){
             foreach ($params as $key => &$val) {
                $stmt_count->bindParam($key, $val);
            }
        }
       
        $stmt_count->execute();
        $total_records = (int) $stmt_count->fetchColumn();

        return [
            'data' => $results,
            'total' => $total_records,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total_records / $limit)
        ];
    }

    /**
     * Find a coupon by its ID.
     */
    public function findCouponById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        if ($result) {

            return convert_dates_for_display($result, ['created_at', 'updated_at']);

        }


        return $result;
    }
    
    /**
     * Check if a coupon code already exists.
     */
    public function isCodeExists($code) {
        $stmt = $this->pdo->prepare("SELECT id FROM coupons WHERE code = :code");
        $stmt->execute([':code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Add multiple coupons in a batch.
     * Returns stats about the operation.
     */
    public function addBulkCoupons($codes, $value, $country_id) {
        $stats = ['added' => 0, 'skipped' => 0, 'skipped_codes' => []];
        
        $sql = "INSERT INTO coupons (code, value, country_id) VALUES (:code, :value, :country_id)";
        $stmt = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();
        try {
            foreach ($codes as $code) {
                if (empty($code)) continue;

                if ($this->isCodeExists($code)) {
                    $stats['skipped']++;
                    $stats['skipped_codes'][] = $code;
                } else {
                    $stmt->execute([
                        ':code' => $code,
                        ':value' => $value,
                        ':country_id' => $country_id ? $country_id : null
                    ]);
                    $stats['added']++;
                }
            }
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // In a real app, you'd log this error
            return false;
        }

        return $stats;
    }

    /**
     * Update a coupon's data.
     */
    public function updateCoupon($id, $data) {
        // A coupon can't be updated if it is used.
        $coupon = $this->findCouponById($id);
        if ($coupon && $coupon['is_used']) {
            return false;
        }

        $sql = "UPDATE coupons SET code = :code, value = :value, country_id = :country_id WHERE id = :id AND is_used = 0";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':code' => $data['code'],
            ':value' => $data['value'],
            ':country_id' => $data['country_id'] ? $data['country_id'] : null
        ]);
    }

    /**
     * Delete a coupon.
     */
    public function deleteCoupon($id) {
        // A coupon can't be deleted if it is used.
        $coupon = $this->findCouponById($id);
        if ($coupon && $coupon['is_used']) {
            return false;
        }

        $sql = "DELETE FROM coupons WHERE id = :id AND is_used = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Delete multiple unused coupons by their IDs.
     */
    public function deleteBulkCoupons($ids) {
        if (empty($ids)) {
            return 0;
        }
        // Ensure all IDs are integers for security
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // We only delete coupons that are NOT used
        $sql = "DELETE FROM coupons WHERE id IN ($placeholders) AND is_used = 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get all coupons based on filters, without pagination.
     */
    public function getCouponsWithoutPagination($filters = []) {
        $sql = "SELECT c.*, 
                       cnt.name as country_name, 
                       u_used.username as used_by_username,
                       t.ticket_number
                FROM coupons c
                LEFT JOIN countries cnt ON c.country_id = cnt.id
                LEFT JOIN users u_used ON c.used_by = u_used.id
                LEFT JOIN tickets t ON c.used_in_ticket = t.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND c.code LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $sql .= " AND c.is_used = :is_used";
            $params[':is_used'] = $filters['is_used'];
        }
        if (!empty($filters['country_id'])) {
            $sql .= " AND c.country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }

        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    /**
     * Get a list of all countries for dropdowns.
     */
    public function getCountries() {
        $stmt = $this->pdo->prepare("SELECT id, name FROM countries ORDER BY name ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    /**
     * Get statistics about coupons based on filters.
     */
    public function getCouponStats($filters = []) {
        $base_sql = "SELECT 
                        COUNT(id) as total,
                        SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used,
                        SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as unused
                     FROM coupons WHERE 1=1";
        
        $params = [];
        if (!empty($filters['search'])) {
            $base_sql .= " AND code LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_used']) && $filters['is_used'] !== '') {
            $base_sql .= " AND is_used = :is_used";
            $params[':is_used'] = $filters['is_used'];
        }
        if (!empty($filters['country_id'])) {
            $base_sql .= " AND country_id = :country_id";
            $params[':country_id'] = $filters['country_id'];
        }

        $stmt = $this->pdo->prepare($base_sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure we always return numbers
        return [
            'total' => (int)($stats['total'] ?? 0),
            'used' => (int)($stats['used'] ?? 0),
            'unused' => (int)($stats['unused'] ?? 0),
        ];
    }

    /**
     * Get available coupons for a specific country, excluding those held by other users
     * or those in the exclude list. A coupon is available if it's not used and either
     * not held or held by the current user.
     *
     * @param int $countryId The ID of the country to fetch coupons for.
     * @param int $currentUserId The ID of the current user.
     * @param array $excludeIds A list of coupon IDs to exclude from the result.
     * @return array An array containing the list of available coupons and the debug info.
     */
    public function getAvailableByCountry(int $countryId, int $currentUserId, array $excludeIds = [])
    {
        // Base query for available coupons
        $sql = "SELECT id, code, `value`
                FROM coupons
                WHERE country_id = ? 
                  AND is_used = 0
                  AND (
                      held_by IS NULL OR 
                      held_by = ? OR 
                      held_at < UTC_TIMESTAMP() - INTERVAL " . self::HOLD_DURATION_MINUTES . " MINUTE
                  )";
    
        $params = [$countryId, $currentUserId];
    
        // Exclude coupons that are already selected in the form
        if (!empty($excludeIds)) {
            // Generates ?,?,? for the IN clause
            $excludePlaceholders = implode(',', array_fill(0, count($excludeIds), '?'));

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

            $sql .= " AND id NOT IN ($excludePlaceholders)";
            // Add the IDs to the parameter array
            $params = array_merge($params, $excludeIds);
        }
    
        // Limit the number of results to prevent overwhelming the user
        $sql .= " ORDER BY created_at DESC LIMIT 20";
    
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Prepare debug info
            $debug_sql = $this->interpolateQuery($sql, $params);
            return [
                'coupons' => $coupons,
                'debug' => [
                    'sql' => $debug_sql,
                    'params' => $params,
                    'count' => count($coupons)
                ]
            ];
        } catch (\Exception $e) {
            // Return error information for debugging
            return [
                'coupons' => [],
                'debug' => [
                    'sql' => $this->interpolateQuery($sql, $params),
                    'params' => $params,
                    'count' => 0,
                    'EXCEPTION_MESSAGE' => $e->getMessage()
                ]
            ];
        }
    }
    

    private function interpolateQuery($query, $params) {
        $keys = array();
        $values = $params;
    
        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }
    
            if (is_string($value))
                $values[$key] = "'" . $value . "'";
    
            if (is_array($value))
                $values[$key] = implode(',', $value);
    
            if (is_null($value))
                $values[$key] = 'NULL';
        }
    
        // Walk the array to see if we can replace parameters
        $query = preg_replace($keys, $values, $query, 1, $count);
    
        return $query;
    }

    /**
     * Places a temporary hold on a coupon for a specific user.
     * A hold prevents other users from seeing or using this coupon for a short period.
     */
    public function hold($couponId, $userId)
    {
        // Atomically check if the coupon is available and hold it
        $sql = "UPDATE coupons
                SET held_by = :user_id, held_at = UTC_TIMESTAMP()
                WHERE id = :coupon_id
                  AND is_used = 0
                  AND (
                      held_by IS NULL OR
                      held_by = :user_id OR
                      held_at < UTC_TIMESTAMP() - INTERVAL " . self::HOLD_DURATION_MINUTES . " MINUTE
                  )";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':coupon_id' => $couponId
        ]);
    
        // The operation is successful if one row was affected
        return $stmt->rowCount() > 0;
    }
    

    /**
     * Releases a specific coupon that was held by the user.
     */
    public function release($couponId, $userId)
    {
        // Only the user who holds the coupon can release it
        $sql = "UPDATE coupons 
                SET held_by = NULL, held_at = NULL 
                WHERE id = :coupon_id AND held_by = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':coupon_id' => $couponId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Releases all coupons held by a specific user.
     * Typically used when the user navigates away from the page.
     */
    public function releaseAllForUser($userId)
    {
        $sql = "UPDATE coupons
                SET held_by = NULL, held_at = NULL
                WHERE held_by = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }
} 