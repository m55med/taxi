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

    public function getFilteredOutgoingCalls($filters = [])
    {
        $sql = "
            SELECT 
                dc.id as call_id,
                dc.call_status,
                dc.notes,
                dc.next_call_at,
                dc.created_at,
                dr.id as driver_id,
                dr.name as driver_name,
                dr.phone as driver_phone,
                caller.username as caller_name,
                cat.name as category_name,
                sub.name as subcategory_name,
                code.name as code_name
            FROM driver_calls dc
            JOIN users caller ON dc.call_by = caller.id
            JOIN drivers dr ON dc.driver_id = dr.id
            LEFT JOIN ticket_categories cat ON dc.ticket_category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON dc.ticket_subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON dc.ticket_code_id = code.id
        ";
        
        $params = [];
        $whereClauses = [];

        // --- FILTERING LOGIC ---
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(dc.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(dc.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['call_by'])) {
            $whereClauses[] = "dc.call_by = :call_by";
            $params[':call_by'] = $filters['call_by'];
        }
        if (!empty($filters['call_status'])) {
            $whereClauses[] = "dc.call_status = :call_status";
            $params[':call_status'] = $filters['call_status'];
        }
        if (!empty($filters['category_id'])) {
            $whereClauses[] = "dc.ticket_category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['subcategory_id'])) {
            $whereClauses[] = "dc.ticket_subcategory_id = :subcategory_id";
            $params[':subcategory_id'] = $filters['subcategory_id'];
        }
        if (!empty($filters['code_id'])) {
            $whereClauses[] = "dc.ticket_code_id = :code_id";
            $params[':code_id'] = $filters['code_id'];
        }
        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(dr.name LIKE :search_term OR dr.phone LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY dc.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredOutgoingCalls Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }

    public function getFilteredIncomingCalls($filters = [])
    {
        $sql = "
            SELECT 
                ic.id as call_id,
                ic.caller_phone_number,
                ic.call_started_at,
                ic.call_ended_at,
                ic.status,
                ic.linked_ticket_detail_id,
                receiver.username as receiver_name,
                t.ticket_number
            FROM incoming_calls ic
            JOIN users receiver ON ic.call_received_by = receiver.id
            LEFT JOIN ticket_details td ON ic.linked_ticket_detail_id = td.id
            LEFT JOIN tickets t ON td.ticket_id = t.id
        ";

        $params = [];
        $whereClauses = [];

        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(ic.call_started_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(ic.call_started_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['call_received_by'])) {
            $whereClauses[] = "ic.call_received_by = :call_received_by";
            $params[':call_received_by'] = $filters['call_received_by'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['answered', 'missed'])) {
            $whereClauses[] = "ic.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . $filters['search_term'] . '%';
            $whereClauses[] = "(ic.caller_phone_number LIKE :search_term)";
            $params[':search_term'] = $searchTerm;
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY ic.call_started_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('ListingModel::getFilteredIncomingCalls Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }
} 