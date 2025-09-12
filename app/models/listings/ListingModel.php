<?php

namespace App\Models\Listings;

use App\Core\Model;
use PDO;

class ListingModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getFilteredTickets($filters = [], $withPagination = false)
    {
        $params = [];
        $whereClauses = [];

        // --- FILTERING LOGIC ---
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(td.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(td.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['created_by'])) {
            $whereClauses[] = "t.created_by = :created_by";
            $params[':created_by'] = $filters['created_by'];
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

        // Handle classification_filter (format: "Category > Subcategory > Code")
        if (!empty($filters['classification_filter'])) {
            $classificationParts = array_map('trim', explode('>', $filters['classification_filter']));
            if (count($classificationParts) === 3) {
                $categoryName = $classificationParts[0];
                $subcategoryName = $classificationParts[1];
                $codeName = $classificationParts[2];

                // Get IDs from names
                $whereClauses[] = "cat.name = :category_name AND sub.name = :subcategory_name AND code.name = :code_name";
                $params[':category_name'] = $categoryName;
                $params[':subcategory_name'] = $subcategoryName;
                $params[':code_name'] = $codeName;
            }
        }

        // Handle has_reviews filter
        if (isset($filters['has_reviews']) && $filters['has_reviews'] !== '') {
            if ($filters['has_reviews'] == '1') {
                // Tickets with reviews
                $whereClauses[] = "EXISTS (
                    SELECT 1 FROM reviews r
                    WHERE r.reviewable_id = td.id
                    AND r.reviewable_type LIKE '%TicketDetail'
                )";
            } elseif ($filters['has_reviews'] == '0') {
                // Tickets without reviews
                $whereClauses[] = "NOT EXISTS (
                    SELECT 1 FROM reviews r
                    WHERE r.reviewable_id = td.id
                    AND r.reviewable_type LIKE '%TicketDetail'
                )";
            }
        }

        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(t.ticket_number LIKE :search_term OR td.phone LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
        }

        $whereSql = count($whereClauses) > 0 ? " AND " . implode(' AND ', $whereClauses) : "";

        // Get total count first
        $countSql = "
            SELECT COUNT(*) as total
            FROM tickets t
            INNER JOIN (
                SELECT ticket_id, MAX(id) as max_id
                FROM ticket_details
                GROUP BY ticket_id
            ) latest ON t.id = latest.ticket_id
            JOIN ticket_details td ON td.id = latest.max_id
            WHERE 1=1 {$whereSql}
        ";

        try {
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $countResult['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredTickets Count Error: ' . $e->getMessage());
            $total = 0;
        }

        if ($withPagination) {
            // Handle pagination
            $page = isset($filters['page']) ? max(1, intval($filters['page'])) : 1;
            $limit = isset($filters['limit']) ? max(1, intval($filters['limit'])) : 25;
            $offset = ($page - 1) * $limit;

            $totalPages = ceil($total / $limit);

            // Get paginated data
            $dataSql = "
                SELECT
                    t.id as ticket_id,
                    t.ticket_number,
                    td.phone,
                    td.is_vip,
                    td.notes,
                    td.created_at,
                    creator.username as created_by_username,
                    editor.username as edited_by_username,
                    tm.name as team_name,
                    vm.username as vip_marketer_name,
                    p.name as platform_name,
                    c.name as country_name,
                    cat.name as category_name,
                    sub.name as subcategory_name,
                    code.name as code_name,
                    COUNT(DISTINCT r.id) as review_count,
                    ROUND(AVG(r.rating), 1) as avg_review_rating,
                    GROUP_CONCAT(DISTINCT CONCAT(r.reviewed_by, ':', r.rating) SEPARATOR '|') as reviews_details
                FROM tickets t
                INNER JOIN (
                    SELECT ticket_id, MAX(id) as max_id
                    FROM ticket_details
                    GROUP BY ticket_id
                ) latest ON t.id = latest.ticket_id
                JOIN ticket_details td ON td.id = latest.max_id
                JOIN users creator ON t.created_by = creator.id
                LEFT JOIN users editor ON td.edited_by = editor.id
                LEFT JOIN teams tm ON td.team_id_at_action = tm.id
                LEFT JOIN users vm ON td.assigned_team_leader_id = vm.id AND td.is_vip = 1
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN countries c ON td.country_id = c.id
                LEFT JOIN ticket_categories cat ON td.category_id = cat.id
                LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
                LEFT JOIN ticket_codes code ON td.code_id = code.id
                LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                WHERE 1=1 {$whereSql}
                GROUP BY t.id, t.ticket_number, td.phone, td.is_vip, td.notes, td.created_at,
                         creator.username, editor.username, tm.name, vm.username,
                         p.name, c.name, cat.name, sub.name, code.name
                ORDER BY td.created_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            try {
                $stmt = $this->db->prepare($dataSql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('ListingModel::getFilteredTickets Data Error: ' . $e->getMessage());
                $data = [];
            }

            return [
                'data' => $data,
                'total' => $total,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit
            ];
        } else {
            // Get all data without pagination (for export)
            $dataSql = "
                SELECT
                    t.id as ticket_id,
                    t.ticket_number,
                    td.phone,
                    td.is_vip,
                    td.notes,
                    td.created_at,
                    creator.username as created_by_username,
                    editor.username as edited_by_username,
                    tm.name as team_name,
                    vm.username as vip_marketer_name,
                    p.name as platform_name,
                    c.name as country_name,
                    cat.name as category_name,
                    sub.name as subcategory_name,
                    code.name as code_name,
                    COUNT(DISTINCT r.id) as review_count,
                    ROUND(AVG(r.rating), 1) as avg_review_rating,
                    GROUP_CONCAT(DISTINCT CONCAT(r.reviewed_by, ':', r.rating) SEPARATOR '|') as reviews_details
                FROM tickets t
                INNER JOIN (
                    SELECT ticket_id, MAX(id) as max_id
                    FROM ticket_details
                    GROUP BY ticket_id
                ) latest ON t.id = latest.ticket_id
                JOIN ticket_details td ON td.id = latest.max_id
                JOIN users creator ON t.created_by = creator.id
                LEFT JOIN users editor ON td.edited_by = editor.id
                LEFT JOIN teams tm ON td.team_id_at_action = tm.id
                LEFT JOIN users vm ON td.assigned_team_leader_id = vm.id AND td.is_vip = 1
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN countries c ON td.country_id = c.id
                LEFT JOIN ticket_categories cat ON td.category_id = cat.id
                LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
                LEFT JOIN ticket_codes code ON td.code_id = code.id
                LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                WHERE 1=1 {$whereSql}
                GROUP BY t.id, t.ticket_number, td.phone, td.is_vip, td.notes, td.created_at,
                         creator.username, editor.username, tm.name, vm.username,
                         p.name, c.name, cat.name, sub.name, code.name
                ORDER BY td.created_at DESC
            ";

            try {
                $stmt = $this->db->prepare($dataSql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('ListingModel::getFilteredTickets Error: ' . $e->getMessage());
                return [];
            }
        }
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
    public function getTicketStats($filters = [])
    {
        $params = [];
        $whereClauses = [];

        // Build WHERE clauses for filters
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(td.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(td.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['created_by'])) {
            $whereClauses[] = "t.created_by = :created_by";
            $params[':created_by'] = $filters['created_by'];
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

        // Handle classification_filter (format: "Category > Subcategory > Code")
        if (!empty($filters['classification_filter'])) {
            $classificationParts = array_map('trim', explode('>', $filters['classification_filter']));
            if (count($classificationParts) === 3) {
                $categoryName = $classificationParts[0];
                $subcategoryName = $classificationParts[1];
                $codeName = $classificationParts[2];

                // Get IDs from names
                $whereClauses[] = "cat.name = :category_name AND sub.name = :subcategory_name AND code.name = :code_name";
                $params[':category_name'] = $categoryName;
                $params[':subcategory_name'] = $subcategoryName;
                $params[':code_name'] = $codeName;
            }
        }

        // Handle has_reviews filter
        if (isset($filters['has_reviews']) && $filters['has_reviews'] !== '') {
            if ($filters['has_reviews'] == '1') {
                // Tickets with reviews
                $whereClauses[] = "EXISTS (
                    SELECT 1 FROM reviews r2
                    WHERE r2.reviewable_id = td.id
                    AND r2.reviewable_type LIKE '%TicketDetail'
                )";
            } elseif ($filters['has_reviews'] == '0') {
                // Tickets without reviews
                $whereClauses[] = "NOT EXISTS (
                    SELECT 1 FROM reviews r2
                    WHERE r2.reviewable_id = td.id
                    AND r2.reviewable_type LIKE '%TicketDetail'
                )";
            }
        }

        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(t.ticket_number LIKE :search_term OR td.phone LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
        }

        $whereSql = count($whereClauses) > 0 ? " AND " . implode(' AND ', $whereClauses) : "";

        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN td.is_vip = 1 THEN 1 ELSE 0 END) as vip_count,
                SUM(CASE WHEN td.is_vip = 0 THEN 1 ELSE 0 END) as normal_count,
                COUNT(DISTINCT t.created_by) as unique_creators,
                COUNT(DISTINCT td.platform_id) as platforms_used,
                COUNT(DISTINCT td.category_id) as categories_used,
                ROUND(AVG(r.rating), 1) as avg_rating,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN t.id END) as reviewed_tickets,
                ROUND(COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN t.id END) / COUNT(*) * 100, 1) as review_coverage
            FROM tickets t
            INNER JOIN (
                SELECT ticket_id, MAX(id) as max_id
                FROM ticket_details
                GROUP BY ticket_id
            ) latest ON t.id = latest.ticket_id
            JOIN ticket_details td ON td.id = latest.max_id
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
                'total' => $result['total'] ?? 0,
                'vip_count' => $result['vip_count'] ?? 0,
                'normal_count' => $result['normal_count'] ?? 0,
                'unique_creators' => $result['unique_creators'] ?? 0,
                'platforms_used' => $result['platforms_used'] ?? 0,
                'categories_used' => $result['categories_used'] ?? 0,
                'reviewed_tickets' => $result['reviewed_tickets'] ?? 0,
                'avg_rating' => $result['avg_rating'] ?? 0,
                'review_coverage' => $result['review_coverage'] ?? 0
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