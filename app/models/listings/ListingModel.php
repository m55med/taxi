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

    public function getFilteredTickets($filters = [], $paginate = true)
    {
        $limit = isset($filters['limit']) && $paginate ? (int)$filters['limit'] : 25;
        $page = isset($filters['page']) && $paginate ? (int)$filters['page'] : 1;
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT 
                t.id as ticket_id,
                t.ticket_number,
                t.created_by,
                td.phone,
                td.is_vip,
                td.notes,
                td.created_at,
                td.edited_by,
                creator.username as created_by_username,
                editor.username as edited_by_username,
                p.name as platform_name,
                c.name as country_name,
                cat.name as category_name,
                sub.name as subcategory_name,
                code.name as code_name,
                -- Reviews data
                AVG(r.rating) as avg_review_rating,
                COUNT(r.id) as review_count,
                GROUP_CONCAT(DISTINCT CONCAT(reviewer.username, ':', r.rating) SEPARATOR '|') as reviews_details,
                -- Team data
                tm.name as team_name,
                -- VIP assignment
                vip_marketer.name as vip_marketer_name
            FROM tickets t
            JOIN ticket_details td ON t.id = td.ticket_id
            LEFT JOIN users creator ON t.created_by = creator.id
            LEFT JOIN users editor ON td.edited_by = editor.id
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN countries c ON td.country_id = c.id
            LEFT JOIN ticket_categories cat ON td.category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON td.code_id = code.id
            -- Reviews join
            LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN users reviewer ON r.reviewed_by = reviewer.id
            -- Team join
            LEFT JOIN team_members team_mem ON td.edited_by = team_mem.user_id
            LEFT JOIN teams tm ON team_mem.team_id = tm.id
            -- VIP marketer join
            LEFT JOIN ticket_vip_assignments vip_assign ON td.id = vip_assign.ticket_detail_id
            LEFT JOIN users vip_marketer ON vip_assign.marketer_id = vip_marketer.id
        ";

        // To make sure we only get the latest detail for each ticket
        $sql .= " WHERE td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)";
        
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
        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(t.ticket_number LIKE :search_term OR td.phone LIKE :search_term OR COALESCE(creator.username, '') LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
            error_log('ListingModel::getFilteredTickets - Search term: ' . $filters['search_term'] . ' (as: ' . $searchTerm . ')');
        }
        if (!empty($filters['has_reviews'])) {
            if ($filters['has_reviews'] === '1') {
                $whereClauses[] = "r.id IS NOT NULL";
            } else {
                $whereClauses[] = "r.id IS NULL";
            }
        }

        // Debug logging for WHERE clauses
        error_log('ListingModel::getFilteredTickets - WHERE clauses: ' . json_encode($whereClauses));
        error_log('ListingModel::getFilteredTickets - WHERE count: ' . count($whereClauses));

        if (count($whereClauses) > 0) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }

        $sql .= " GROUP BY t.id, td.id";

        // Get total count for pagination
        if ($paginate) {
            $countSql = "SELECT COUNT(DISTINCT t.id) as total FROM tickets t 
                        JOIN ticket_details td ON t.id = td.ticket_id
                        LEFT JOIN users creator ON t.created_by = creator.id
                        LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
                        WHERE td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)";
            
            if (count($whereClauses) > 0) {
                $countSql .= " AND " . implode(' AND ', $whereClauses);
            }

            try {
                $countStmt = $this->db->prepare($countSql);
                $countStmt->execute($params);
                $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            } catch (\PDOException $e) {
                error_log('ListingModel::getFilteredTickets Count Error: ' . $e->getMessage());
                return ['error' => 'Database count query failed', 'data' => []];
            }
        }

        $sql .= " ORDER BY td.created_at DESC";

        if ($paginate) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        try {
            error_log('ListingModel::getFilteredTickets - Final SQL: ' . $sql);
            error_log('ListingModel::getFilteredTickets - Parameters: ' . json_encode($params));
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log('ListingModel::getFilteredTickets - Result count: ' . count($results));
            if (!empty($results)) {
                error_log('ListingModel::getFilteredTickets - First result ticket_number: ' . ($results[0]['ticket_number'] ?? 'N/A'));
                error_log('ListingModel::getFilteredTickets - First result phone: ' . ($results[0]['phone'] ?? 'N/A'));
                error_log('ListingModel::getFilteredTickets - First result creator: ' . ($results[0]['created_by_username'] ?? 'N/A'));
            }

            if ($paginate) {
                return [
                    'data' => $results,
                    'total' => $totalRecords,
                    'limit' => $limit,
                    'current_page' => $page,
                    'total_pages' => ceil($totalRecords / $limit)
                ];
            } else {
                return ['data' => $results];
            }
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredTickets Error: ' . $e->getMessage());
            error_log('ListingModel::getFilteredTickets SQL was: ' . $sql);
            error_log('ListingModel::getFilteredTickets Params were: ' . json_encode($params));
            return ['error' => 'Database query failed'];
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

    /**
     * Get ticket statistics based on current filters.
     */
    public function getTicketStats($filters = [])
    {
        $params = [];
        $whereClauses = [];

        // Apply same filters as main query
        $whereBase = "td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)";

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
        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(t.ticket_number LIKE :search_term OR td.phone LIKE :search_term OR COALESCE(creator.username, '') LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
            error_log('ListingModel::getTicketStats - Search term: ' . $filters['search_term'] . ' (as: ' . $searchTerm . ')');
        }
        if (!empty($filters['has_reviews'])) {
            if ($filters['has_reviews'] === '1') {
                $whereClauses[] = "r.id IS NOT NULL";
            } else {
                $whereClauses[] = "r.id IS NULL";
            }
        }

        $whereSql = $whereBase;
        if (count($whereClauses) > 0) {
            $whereSql .= " AND " . implode(' AND ', $whereClauses);
        }

        $sql = "
            SELECT 
                COUNT(DISTINCT t.id) as total,
                SUM(CASE WHEN td.is_vip = 1 THEN 1 ELSE 0 END) as vip_count,
                SUM(CASE WHEN td.is_vip = 0 THEN 1 ELSE 0 END) as normal_count,
                COUNT(DISTINCT t.created_by) as unique_creators,
                COUNT(DISTINCT td.platform_id) as platforms_used,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN t.id END) as reviewed_tickets,
                AVG(r.rating) as avg_rating
            FROM tickets t
            JOIN ticket_details td ON t.id = td.ticket_id
            LEFT JOIN users creator ON t.created_by = creator.id
            LEFT JOIN reviews r ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            WHERE {$whereSql}
        ";

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
                'reviewed_tickets' => (int)($result['reviewed_tickets'] ?? 0),
                'avg_rating' => round((float)($result['avg_rating'] ?? 0), 2),
                'review_coverage' => $result['total'] > 0 ? round(($result['reviewed_tickets'] / $result['total']) * 100, 1) : 0
            ];
        } catch (\PDOException $e) {
            error_log('ListingModel::getTicketStats Error: ' . $e->getMessage());
            return [
                'total' => 0, 'vip_count' => 0, 'normal_count' => 0,
                'unique_creators' => 0, 'platforms_used' => 0, 'reviewed_tickets' => 0,
                'avg_rating' => 0, 'review_coverage' => 0
            ];
        }
    }

    /**
     * Get search suggestions.
     */
    public function getSearchSuggestions($query, $type = 'ticket')
    {
        $suggestions = [];
        $query = '%' . $query . '%';

        try {
            switch ($type) {
                case 'ticket':
                    $sql = "SELECT DISTINCT t.ticket_number as value, 
                                   CONCAT(t.ticket_number, ' - ', td.phone) as label
                            FROM tickets t 
                            JOIN ticket_details td ON t.id = td.ticket_id
                            WHERE t.ticket_number LIKE :query 
                            AND td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)
                            ORDER BY t.ticket_number DESC LIMIT 10";
                    break;
                case 'phone':
                    $sql = "SELECT DISTINCT td.phone as value, 
                                   CONCAT(td.phone, ' - ', t.ticket_number) as label
                            FROM ticket_details td 
                            JOIN tickets t ON td.ticket_id = t.id
                            WHERE td.phone LIKE :query 
                            AND td.id = (SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id)
                            ORDER BY td.created_at DESC LIMIT 10";
                    break;
                case 'user':
                    $sql = "SELECT DISTINCT u.username as value, 
                                   CONCAT(u.username, ' (', u.name, ')') as label
                            FROM users u 
                            WHERE (u.username LIKE :query OR u.name LIKE :query)
                            ORDER BY u.username LIMIT 10";
                    break;
                default:
                    return [];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':query' => $query]);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('ListingModel::getSearchSuggestions Error: ' . $e->getMessage());
        }

        return $suggestions;
    }
}