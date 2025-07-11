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

    public function getFilteredTickets($filters = [])
    {
        $sql = "
            SELECT 
                t.id as ticket_id,
                t.ticket_number,
                td.phone,
                td.is_vip,
                td.notes,
                td.created_at,
                creator.username as created_by_username,
                p.name as platform_name,
                c.name as country_name,
                cat.name as category_name,
                sub.name as subcategory_name,
                code.name as code_name
            FROM tickets t
            JOIN ticket_details td ON t.id = td.ticket_id
            JOIN users creator ON t.created_by = creator.id
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN countries c ON td.country_id = c.id
            LEFT JOIN ticket_categories cat ON td.category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON td.subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON td.code_id = code.id
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
            $whereClauses[] = "(t.ticket_number LIKE :search_term OR td.phone LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
        }

        if (count($whereClauses) > 0) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY td.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredTickets Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }

    public function getFilteredCalls($filters = [])
    {
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 25;
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $offset = ($page - 1) * $limit;

        $params = [':limit' => $limit, ':offset' => $offset];
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
            if (!empty($filters['category_id'])) {
                $whereClauses[] = "category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            if (!empty($filters['subcategory_id'])) {
                $whereClauses[] = "subcategory_id = :subcategory_id";
                $params[':subcategory_id'] = $filters['subcategory_id'];
            }
            if (!empty($filters['code_id'])) {
                $whereClauses[] = "code_id = :code_id";
                $params[':code_id'] = $filters['code_id'];
            }
        }

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
        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = ($typeWhere ? " AND " : " WHERE ") . implode(' AND ', $whereClauses);
        }

        // Get Total
        $totalSql = "SELECT COUNT(*) as total FROM ({$baseQuery}) as all_calls " . $typeWhere . $whereSql;
        try {
            $totalStmt = $this->db->prepare($totalSql);
            // We need to remove limit and offset from params for the count query
            $countParams = $params;
            unset($countParams[':limit'], $countParams[':offset']);
            $totalStmt->execute($countParams);
            $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredCalls Count Error: ' . $e->getMessage());
            return ['error' => 'Database count query failed: ' . $e->getMessage(), 'data' => []];
        }

        // Get Data
        $dataSql = $fullQuery . $whereSql . " ORDER BY call_time DESC LIMIT :limit OFFSET :offset";
        try {
            $dataStmt = $this->db->prepare($dataSql);
            $dataStmt->execute($params);
            $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'data' => $results, 'total' => $totalRecords, 'limit' => $limit, 'page' => $page,
                'total_pages' => ceil($totalRecords / $limit)
            ];
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredCalls Data Error: ' . $e->getMessage());
            return ['error' => 'Database data query failed: ' . $e->getMessage(), 'data' => []];
        }
    }
}