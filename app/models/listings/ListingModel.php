<?php

namespace App\Models\Listings;

use App\Core\Model;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class ListingModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getFilteredTickets($filters = [], $withPagination = false)
    {
        $params = [];
        $whereClauses = [];
    
        // ---------------------------
        // Filters
        // ---------------------------
        if (!empty($filters['start_date'])) {
            // الحل الأمثل: قارن التواريخ باليوم في توقيت القاهرة
            // خذ التاريخ الأصلي الذي أرسله المستخدم (قبل التحويل)
            $cairoDate = $filters['original_start_date'] ?? date('Y-m-d', strtotime($filters['start_date']));
            $params[':cairo_date'] = $cairoDate;
            $whereClauses[] = "DATE(CONVERT_TZ(td.created_at, '+00:00', '+02:00')) = :cairo_date";
        }
        if (!empty($filters['created_by'])) {
            $whereClauses[] = "td.created_by = :created_by";
            $params[':created_by'] = $filters['created_by'];
        }
        if (!empty($filters['team_id'])) {
            $whereClauses[] = "td.team_id_at_action = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
        if (!empty($filters['platform_id'])) {
            $whereClauses[] = "td.platform_id = :platform_id";
            $params[':platform_id'] = $filters['platform_id'];
        }
        if (isset($filters['is_vip']) && in_array($filters['is_vip'], ['1','0'])) {
            $whereClauses[] = "td.is_vip = :is_vip";
            $params[':is_vip'] = $filters['is_vip'];
        }
        if (!empty($filters['category_id'])) {
            $whereClauses[] = "td.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['subcategory_id'])) {
            $whereClauses[] = "td.subcategory_id = :subcategory_id";
            $params[':subcategory_id'] = $filters['subcategory_id'];
        }
        if (!empty($filters['code_id'])) {
            $whereClauses[] = "td.code_id = :code_id";
            $params[':code_id'] = $filters['code_id'];
        }
    
        // classification_filter
        if (!empty($filters['classification_filter'])) {
            $parts = array_map('trim', explode('>', $filters['classification_filter']));
            if (count($parts) === 3) {
                $whereClauses[] = "td.category_id = (SELECT id FROM ticket_categories WHERE name = :category_name LIMIT 1)
                                    AND td.subcategory_id = (SELECT id FROM ticket_subcategories WHERE name = :subcategory_name LIMIT 1)
                                    AND td.code_id = (SELECT id FROM ticket_codes WHERE name = :code_name LIMIT 1)";
                $params[':category_name'] = $parts[0];
                $params[':subcategory_name'] = $parts[1];
                $params[':code_name'] = $parts[2];
            }
        }
    
        // has_reviews
        if (isset($filters['has_reviews']) && $filters['has_reviews'] !== '') {
            if ($filters['has_reviews'] == '1') {
                $whereClauses[] = "EXISTS (
                    SELECT 1 FROM reviews r
                    WHERE r.reviewable_id = td.id
                    AND r.reviewable_type LIKE '%TicketDetail'
                )";
            } elseif ($filters['has_reviews'] == '0') {
                $whereClauses[] = "NOT EXISTS (
                    SELECT 1 FROM reviews r
                    WHERE r.reviewable_id = td.id
                    AND r.reviewable_type LIKE '%TicketDetail'
                )";
            }
        }
    
        // search
        if (!empty($filters['search_term'])) {
            $term = '%' . trim($filters['search_term']) . '%';
            $whereClauses[] = "(t.ticket_number LIKE :search_ticket_number
                                OR td.phone LIKE :search_phone
                                OR EXISTS (SELECT 1 FROM users u WHERE u.id = t.created_by AND u.username LIKE :search_ticket_creator)
                                OR EXISTS (SELECT 1 FROM users u2 WHERE u2.id = td.created_by AND u2.username LIKE :search_detail_creator))";
    
            $params[':search_ticket_number'] = $term;
            $params[':search_phone'] = $term;
            $params[':search_ticket_creator'] = $term;
            $params[':search_detail_creator'] = $term;
        }
    
        $whereSql = count($whereClauses) ? " AND " . implode(' AND ', $whereClauses) : "";
    
        // ---------------------------
        // COUNT
        // ---------------------------
        $countSql = "
            SELECT COUNT(td.id) AS total
            FROM ticket_details td
            JOIN tickets t ON t.id = td.ticket_id
            WHERE 1=1 {$whereSql}
        ";
        try {
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn() ?: 0;
        } catch (\PDOException $e) {
            error_log('getFilteredTickets Count Error: ' . $e->getMessage());
            $total = 0;
        }
    
        // ---------------------------
        // DATA
        // ---------------------------
        if ($withPagination) {
            $page = max(1, intval($filters['page'] ?? 1));
            $limit = max(1, intval($filters['limit'] ?? 25));
            $offset = ($page - 1) * $limit;
            $totalPages = ceil($total / $limit);
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
    
        $dataSql = "
            SELECT
                t.id AS ticket_id,
                t.ticket_number,
                t.created_by AS ticket_created_by,
                t.created_at AS ticket_created_at,
                t.updated_at AS ticket_updated_at,
                td.id AS ticket_detail_id,
                td.created_by AS detail_created_by,
                td.edited_by,
                td.team_id_at_action,
                td.phone,
                td.is_vip,
                td.notes,
                td.created_at AS detail_created_at,
                td.updated_at AS detail_updated_at,
                creator.username AS ticket_created_by_username,
                detail_creator.username AS detail_created_by_username,
                editor.username AS editor_username,
                tm.name AS team_name,
                vm.username AS vip_marketer_name,
                p.name AS platform_name,
                c.name AS country_name,
                cat.name AS category_name,
                sub.name AS subcategory_name,
                code.name AS code_name,
                r.review_count,
                r.avg_review_rating,
                r.reviews_details
            FROM ticket_details td
            JOIN tickets t ON t.id = td.ticket_id
            JOIN users creator ON t.created_by = creator.id
            LEFT JOIN users detail_creator ON td.created_by = detail_creator.id
            LEFT JOIN users editor ON td.edited_by = editor.id
            LEFT JOIN teams tm ON td.team_id_at_action = tm.id
            LEFT JOIN users vm ON td.assigned_team_leader_id = vm.id AND td.is_vip = 1
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN countries c ON td.country_id = c.id
            LEFT JOIN ticket_categories cat ON td.category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON td.code_id = code.id
            LEFT JOIN (
                SELECT reviewable_id,
                       COUNT(*) AS review_count,
                       ROUND(AVG(rating),1) AS avg_review_rating,
                       GROUP_CONCAT(CONCAT(reviewed_by, ':', rating) SEPARATOR '|') AS reviews_details
                FROM reviews
                WHERE reviewable_type LIKE '%TicketDetail'
                GROUP BY reviewable_id
            ) r ON r.reviewable_id = td.id
            WHERE 1=1 {$whereSql}
            ORDER BY td.created_at DESC
            " . ($withPagination ? "LIMIT :limit OFFSET :offset" : "");
    
        try {
            $stmt = $this->db->prepare($dataSql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('getFilteredTickets Data Error: ' . $e->getMessage());
            $results = [];
        }
    
        if ($withPagination) {
            return [
                'data' => $results,
                'total' => $total,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit
            ];
        }
    
        return $results;
    }
    
            
        


public function getSearchSuggestions($q = '', $type = 'ticket')
{
    $q = trim($q);
    if (!$q) return [];

    if ($type === 'ticket') {
        $stmt = $this->db->prepare("
            SELECT ticket_number 
            FROM tickets 
            WHERE ticket_number LIKE :search
            ORDER BY ticket_number ASC
            LIMIT 10
        ");
        $stmt->execute([':search' => "%$q%"]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // لو هتدعم أنواع تانية في المستقبل
    return [];
}



    private function _buildCallQueryParts($filters = [])
    {
        $params = [];
        $whereClauses = [];

        // Common filters
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "call_time >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "call_time <= :end_date_with_time";
            $params[':end_date_with_time'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['user_id'])) {
            $whereClauses[] = "user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search_term'])) {
            $whereClauses[] = "(contact_name LIKE :search_term OR contact_phone LIKE :search_term)";
            $params[':search_term'] = '%' . $filters['search_term'] . '%';
        }

        // Type-specific filters
        $callType = $filters['call_type'] ?? 'all';
        $typeWhere = '';
        if ($callType === 'incoming') {
            $typeWhere = "WHERE call_type = 'Incoming'";
        } elseif ($callType === 'outgoing') {
            $typeWhere = "WHERE call_type = 'Outgoing'";
        }
        
        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = ($typeWhere ? " AND " : " WHERE ") . implode(' AND ', $whereClauses);
        }
        
        return ['typeWhere' => $typeWhere, 'whereSql' => $whereSql, 'params' => $params];
    }

    public function getFilteredCalls($filters = [], $paginate = true)
    {
        $limit = isset($filters['limit']) && $paginate ? (int)$filters['limit'] : 25;
        $page = isset($filters['page']) && $paginate ? (int)$filters['page'] : 1;
        $offset = ($page - 1) * $limit;

        $queryParts = $this->_buildCallQueryParts($filters);
        $params = $queryParts['params'];
        $typeWhere = $queryParts['typeWhere'];
        $whereSql = $queryParts['whereSql'];

        $baseQuery = "
            (SELECT
                'Outgoing' as call_type, dc.id, dc.created_at as call_time, dc.call_status as status,
                dr.id as contact_id, dr.name as contact_name, dr.phone as contact_phone,
                dc.call_by as user_id, u.username as user_name,
                dc.ticket_category_id as category_id, dc.ticket_subcategory_id as subcategory_id, dc.ticket_code_id as code_id,
                cat.name as category_name, sub.name as subcategory_name, code.name as code_name,
                dc.next_call_at, NULL as duration_seconds, NULL as ticket_number, NULL as ticket_id
            FROM driver_calls dc
            JOIN users u ON dc.call_by = u.id
            JOIN drivers dr ON dc.driver_id = dr.id
            LEFT JOIN ticket_categories cat ON dc.ticket_category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON dc.ticket_subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON dc.ticket_code_id = code.id)
            UNION ALL
            (SELECT
                'Incoming' as call_type, ic.id, ic.call_started_at as call_time, ic.status,
                NULL as contact_id, ic.caller_phone_number as contact_name, ic.caller_phone_number as contact_phone,
                ic.call_received_by as user_id, u.username as user_name,
                td.category_id, td.subcategory_id, td.code_id,
                cat.name as category_name, sub.name as subcategory_name, code.name as code_name,
                NULL as next_call_at, 
                TIMESTAMPDIFF(SECOND, ic.call_started_at, ic.call_ended_at) as duration_seconds, 
                t.ticket_number,
                t.id as ticket_id
            FROM incoming_calls ic
            JOIN users u ON ic.call_received_by = u.id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
            LEFT JOIN ticket_categories cat ON td.category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON td.code_id = code.id)
        ";

        $fullQuery = "SELECT * FROM ({$baseQuery}) as all_calls " . $typeWhere;
        
        // Get Total
        $totalSql = "SELECT COUNT(*) as total FROM ({$baseQuery}) as all_calls " . $typeWhere . $whereSql;
        try {
            $totalStmt = $this->db->prepare($totalSql);
            $totalStmt->execute($params);
            $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredCalls Count Error: ' . $e->getMessage());
            return ['error' => 'Database count query failed: ' . $e->getMessage(), 'data' => []];
        }

        // Get Data
        $dataSql = $fullQuery . $whereSql . " ORDER BY call_time DESC";
        if ($paginate) {
            $dataSql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        try {
            $dataStmt = $this->db->prepare($dataSql);
            $dataStmt->execute($params);
            $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'data' => $results, 'total' => $totalRecords, 'limit' => $limit, 'page' => $page,
                'total_pages' => $paginate ? ceil($totalRecords / $limit) : 1
            ];
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredCalls Data Error: ' . $e->getMessage());
            return ['error' => 'Database data query failed: ' . $e->getMessage(), 'data' => []];
        }
    }

    public function getCallStats($filters = [])
    {
        $queryParts = $this->_buildCallQueryParts($filters);
        $params = $queryParts['params'];
        $typeWhere = $queryParts['typeWhere'];
        $whereSql = $queryParts['whereSql'];

        $baseQuery = "
            (SELECT 'Outgoing' as call_type, created_at as call_time, call_by as user_id FROM driver_calls)
            UNION ALL
            (SELECT 'Incoming' as call_type, call_started_at as call_time, call_received_by as user_id FROM incoming_calls)
        ";
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN call_type = 'Incoming' THEN 1 ELSE 0 END) as incoming,
                    SUM(CASE WHEN call_type = 'Outgoing' THEN 1 ELSE 0 END) as outgoing
                FROM ({$baseQuery}) as all_calls
                " . $typeWhere . $whereSql;
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total' => (int)($result['total'] ?? 0),
                'incoming' => (int)($result['incoming'] ?? 0),
                'outgoing' => (int)($result['outgoing'] ?? 0),
            ];
        } catch (\PDOException $e) {
            error_log('ListingModel::getCallStats Error: ' . $e->getMessage());
            return ['total' => 0, 'incoming' => 0, 'outgoing' => 0];
        }
    }
    /**
     * حذف تفصيلة تذكرة مع الصلاحيات والتسجيل
     *
     * @param int $ticketDetailId معرف تفصيلة التذكرة
     * @param int $userId معرف المستخدم الحالي
     * @return array نتيجة العملية
     */
    public function deleteTicketDetail($ticketDetailId, $userId)
    {
        $this->db->beginTransaction();

        try {
            // Step 1: التحقق من وجود تفصيلة التذكرة
            $detailSql = "SELECT td.*, t.created_by as ticket_creator_id, t.ticket_number,
                                 tl.team_leader_id
                          FROM ticket_details td
                          JOIN tickets t ON td.ticket_id = t.id
                          LEFT JOIN teams tl ON tl.team_leader_id IS NOT NULL
                          LEFT JOIN team_members tm ON tm.team_id = tl.id AND tm.user_id = t.created_by
                          WHERE td.id = :detail_id";

            $stmt = $this->db->prepare($detailSql);
            $stmt->execute([':detail_id' => $ticketDetailId]);
            $detail = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$detail) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'تفصيلة التذكرة غير موجودة'];
            }

            // Step 2: التحقق من الصلاحيات
            $canDelete = $this->canUserDeleteTicketDetail($userId, $detail);

            if (!$canDelete) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'ليس لديك صلاحية حذف هذه التفصيلة'];
            }

            // Step 3: تسجيل عملية الحذف في ticket_edit_logs
            error_log("LOGGING DELETE: Detail ID: $ticketDetailId, User: $userId");

            $logSql = "INSERT INTO ticket_edit_logs (ticket_detail_id, edited_by, field_name, old_value, new_value, created_at)
                      VALUES (:ticket_detail_id, :edited_by, 'DELETED', :old_value, 'RECORD DELETED', UTC_TIMESTAMP())";

            $oldValue = json_encode([
                'ticket_number' => $detail['ticket_number'],
                'phone' => $detail['phone'],
                'category_id' => $detail['category_id'],
                'subcategory_id' => $detail['subcategory_id'],
                'code_id' => $detail['code_id'],
                'notes' => $detail['notes'],
                'deleted_at' => \DateTimeHelper::getCurrentUTC()
            ]);

            $logStmt = $this->db->prepare($logSql);
            $logStmt->execute([
                ':ticket_detail_id' => $ticketDetailId,
                ':edited_by' => $userId,
                ':old_value' => $oldValue
            ]);

            // Step 4: حذف التفصيلة
            $deleteSql = "DELETE FROM ticket_details WHERE id = :detail_id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([':detail_id' => $ticketDetailId]);

            // Step 5: التحقق من وجود تفاصيل أخرى للتذكرة
            $checkSql = "SELECT COUNT(*) as remaining FROM ticket_details WHERE ticket_id = :ticket_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':ticket_id' => $detail['ticket_id']]);
            $remaining = $checkStmt->fetch(PDO::FETCH_ASSOC)['remaining'];

            // إذا لم يتبق تفاصيل، يمكن حذف التذكرة الأساسية أيضاً
            if ($remaining == 0) {
                $deleteTicketSql = "DELETE FROM tickets WHERE id = :ticket_id";
                $deleteTicketStmt = $this->db->prepare($deleteTicketSql);
                $deleteTicketStmt->execute([':ticket_id' => $detail['ticket_id']]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'تم حذف تفصيلة التذكرة بنجاح',
                'ticket_deleted' => ($remaining == 0),
                'log_id' => $this->db->lastInsertId()
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('ListingModel::deleteTicketDetail Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'حدث خطأ أثناء حذف التفصيلة'];
        }
    }

    /**
     * التحقق من صلاحية المستخدم لحذف تفصيلة التذكرة
     *
     * @param int $userId معرف المستخدم
     * @param array $detail بيانات تفصيلة التذكرة
     * @return bool هل يمكن للمستخدم الحذف
     */
    private function canUserDeleteTicketDetail($userId, $detail)
    {
        // التحقق من دور المستخدم
        $userSql = "SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :user_id";
        $userStmt = $this->db->prepare($userSql);
        $userStmt->execute([':user_id' => $userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $userRole = strtolower($user['role_name']);

        // 1. الإدمن يمكنه حذف أي تفصيلة
        if ($userRole === 'admin') {
            return true;
        }

        // 2. الكوالتي يمكنه حذف أي تفصيلة
        if ($userRole === 'quality') {
            return true;
        }

        // 3. الشخص الذي أنشأ التذكرة يمكنه حذف تفاصيله
        if ($detail['ticket_creator_id'] == $userId) {
            return true;
        }

        // 4. التم ليدر الذي يتبع له الشخص الذي أنشأ التذكرة
        $teamLeaderSql = "SELECT tl.team_leader_id
                         FROM teams tl
                         JOIN team_members tm ON tm.team_id = tl.id
                         WHERE tm.user_id = :creator_id AND tl.team_leader_id = :user_id";

        $teamLeaderStmt = $this->db->prepare($teamLeaderSql);
        $teamLeaderStmt->execute([
            ':creator_id' => $detail['ticket_creator_id'],
            ':user_id' => $userId
        ]);

        $isTeamLeader = $teamLeaderStmt->fetch(PDO::FETCH_ASSOC);

        return $isTeamLeader !== false;
    }

    /**
     * الحصول على سجل حذف تفصيلة التذكرة (للعرض)
     *
     * @param int $ticketDetailId معرف تفصيلة التذكرة المحذوفة
     * @return array|null بيانات سجل الحذف
     */
    public function getDeleteLog($ticketDetailId)
    {
        $sql = "SELECT tel.*, u.name as editor_name, u.username as editor_username
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                WHERE tel.ticket_detail_id = :ticket_detail_id
                AND tel.field_name = 'DELETED'
                ORDER BY tel.created_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_detail_id' => $ticketDetailId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة
            return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);
        }

        return null;
    }

    /**
     * الحصول على سجلات التعديلات والحذوفات لتفاصيل التذكرة
     *
     * @param int $ticketDetailId معرف تفصيلة التذكرة
     * @return array سجلات التعديلات والحذوفات
     */
    public function getTicketDetailLogs($ticketDetailId)
    {
        $sql = "SELECT tel.*,
                       u.name as editor_name,
                       u.username as editor_username
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                WHERE tel.ticket_detail_id = :ticket_detail_id
                ORDER BY tel.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_detail_id' => $ticketDetailId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة
        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);
    }

    /**
     * الحصول على جميع سجلات التعديلات والحذوفات لتذكرة معينة
     *
     * @param int|null $ticketId معرف التذكرة (null للحصول على جميع السجلات)
     * @param int $limit عدد النتائج المطلوبة
     * @return array سجلات التعديلات والحذوفات
     */
    public function getTicketLogs($ticketId = null, $limit = null)
    {
        error_log("GETTING TICKET LOGS: Ticket ID: " . ($ticketId ?? 'NULL') . ", Limit: " . ($limit ?? 'NULL'));

        $whereClause = "";
        $params = [];

        if ($ticketId !== null) {
            $whereClause = "WHERE t.id = :ticket_id OR tel.ticket_detail_id IN (
                SELECT id FROM ticket_details WHERE ticket_id = :ticket_id2
            )";
            $params[':ticket_id'] = $ticketId;
            $params[':ticket_id2'] = $ticketId;
        }

        $limitClause = $limit ? "LIMIT " . (int)$limit : "";

        $sql = "SELECT tel.*,
                       u.name as editor_name,
                       u.username as editor_username,
                       t.ticket_number
                FROM ticket_edit_logs tel
                LEFT JOIN users u ON tel.edited_by = u.id
                LEFT JOIN ticket_details td ON tel.ticket_detail_id = td.id
                LEFT JOIN tickets t ON td.ticket_id = t.id
                $whereClause
                ORDER BY tel.created_at DESC
                $limitClause";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("TICKET LOGS RESULTS: Found " . count($results) . " records");

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة
        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);
    }

    public function getTicketStats($filters = [])
    {
        $params = [];
        $whereClauses = [];

        // Debug logging for search issues
        if (!empty($filters['search_term'])) {
            error_log('ListingModel::getTicketStats - Search term: ' . $filters['search_term']);
        }

        // Build WHERE clauses for filters
    if (!empty($filters['start_date'])) {
        // استخدم نفس منطق الفلترة بالتوقيت القاهرة
        $cairoDate = $filters['original_start_date'] ?? date('Y-m-d', strtotime($filters['start_date']));
        $whereClauses[] = "DATE(CONVERT_TZ(td.created_at, '+00:00', '+02:00')) = :cairo_date";
        $params[':cairo_date'] = $cairoDate;
    }
        if (!empty($filters['created_by'])) {
            $whereClauses[] = "td.created_by = :created_by";
            $params[':created_by'] = $filters['created_by'];
        }
        if (!empty($filters['team_id'])) {
            $whereClauses[] = "td.team_id_at_action = :team_id";
            $params[':team_id'] = $filters['team_id'];
        }
    if (!empty($filters['platform_id'])) {
        $whereClauses[] = "td.platform_id = :platform_id";
        $params[':platform_id'] = $filters['platform_id'];
    }
    if (!empty($filters['is_vip']) && in_array($filters['is_vip'], ['1', '0'])) {
        $whereClauses[] = "td.is_vip = :is_vip";
        $params[':is_vip'] = $filters['is_vip'];
    }
    if (!empty($filters['category_id'])) {
        $whereClauses[] = "td.category_id = :category_id";
        $params[':category_id'] = $filters['category_id'];
    }
    if (!empty($filters['subcategory_id'])) {
        $whereClauses[] = "td.subcategory_id = :subcategory_id";
        $params[':subcategory_id'] = $filters['subcategory_id'];
    }
    if (!empty($filters['code_id'])) {
        $whereClauses[] = "td.code_id = :code_id";
        $params[':code_id'] = $filters['code_id'];
    }

    // Handle classification_filter
    if (!empty($filters['classification_filter'])) {
        $classificationParts = array_map('trim', explode('>', $filters['classification_filter']));
        if (count($classificationParts) === 3) {
            $whereClauses[] = "cat.name = :category_name AND sub.name = :subcategory_name AND code.name = :code_name";
            $params[':category_name'] = $classificationParts[0];
            $params[':subcategory_name'] = $classificationParts[1];
            $params[':code_name'] = $classificationParts[2];
        }
    }

    // Handle has_reviews filter
    if (isset($filters['has_reviews']) && $filters['has_reviews'] !== '') {
        if ($filters['has_reviews'] == '1') {
            $whereClauses[] = "EXISTS (
                SELECT 1 FROM reviews r2
                WHERE r2.reviewable_id = td.id
                AND r2.reviewable_type LIKE '%TicketDetail'
            )";
        } elseif ($filters['has_reviews'] == '0') {
            $whereClauses[] = "NOT EXISTS (
                SELECT 1 FROM reviews r2
                WHERE r2.reviewable_id = td.id
                AND r2.reviewable_type LIKE '%TicketDetail'
            )";
        }
    }

    if (!empty($filters['search_term'])) {
        $searchTerm = trim($filters['search_term']);
        // Partial match for ticket number (preserves leading zeros), partial for phone/username
        $whereClauses[] = "(t.ticket_number LIKE :search_ticket_number
        OR td.phone LIKE :search_phone
        OR EXISTS (SELECT 1 FROM users u WHERE u.id = t.created_by AND u.username LIKE :search_ticket_creator)
        OR EXISTS (SELECT 1 FROM users u2 WHERE u2.id = td.created_by AND u2.username LIKE :search_detail_creator))";

        $params[':search_ticket_number']   = '%' . $searchTerm . '%';
        $params[':search_phone']           = '%' . $searchTerm . '%';
        $params[':search_ticket_creator']  = '%' . $searchTerm . '%';
        $params[':search_detail_creator']  = '%' . $searchTerm . '%';

    }

    $whereSql = count($whereClauses) > 0 ? " AND " . implode(' AND ', $whereClauses) : "";

    $sql = "SELECT
                COUNT(td.id) as total,
                SUM(CASE WHEN td.is_vip = 1 THEN 1 ELSE 0 END) as vip_count,
                SUM(CASE WHEN td.is_vip = 0 THEN 1 ELSE 0 END) as normal_count,
                COUNT(DISTINCT t.created_by) as unique_creators,
                COUNT(DISTINCT td.platform_id) as platforms_used,
                COUNT(DISTINCT td.category_id) as categories_used,
                ROUND(AVG(r.rating), 1) as avg_rating,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN td.id END) as reviewed_tickets,
                ROUND(
                    (COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN td.id END) / 
                     NULLIF(COUNT(DISTINCT td.id),0)) * 100, 1
                ) as review_coverage
            FROM ticket_details td
            JOIN tickets t ON t.id = td.ticket_id
            LEFT JOIN ticket_categories cat ON td.category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON td.code_id = code.id
            LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            WHERE 1=1 {$whereSql}";

    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int)($result['total'] ?? 0),
            'vip_count' => (int)($result['vip_count'] ?? 0),
            'normal_count' => (int)($result['normal_count'] ?? 0),
            'unique_creators' => (int)($result['unique_creators'] ?? 0),
            'platforms_used' => (int)($result['platforms_used'] ?? 0),
            'categories_used' => (int)($result['categories_used'] ?? 0),
            'reviewed_tickets' => (int)($result['reviewed_tickets'] ?? 0),
            'avg_rating' => (float)($result['avg_rating'] ?? 0),
            'review_coverage' => (float)($result['review_coverage'] ?? 0)
        ];
    } catch (\PDOException $e) {
        error_log('ListingModel::getTicketStats Error: ' . $e->getMessage());
        return [
            'total' => 0,
            'vip_count' => 0,
            'normal_count' => 0,
            'unique_creators' => 0,
            'platforms_used' => 0,
            'categories_used' => 0,
            'reviewed_tickets' => 0,
            'avg_rating' => 0,
            'review_coverage' => 0
        ];
    }
}


}