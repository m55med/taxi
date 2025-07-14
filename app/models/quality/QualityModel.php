<?php

namespace App\Models\Quality;

use App\Core\Model;
use PDO;

class QualityModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get filtered reviews with all necessary details for the quality page.
     */
    public function getFilteredReviews($filters = [])
    {
        $sql = "
            SELECT 
                r.id as review_id,
                r.rating,
                r.review_notes,
                r.reviewed_at,
                r.reviewable_type,
                r.reviewable_id,
                reviewer.username as reviewer_name,
                
                -- Agent being reviewed
                COALESCE(agent_ticket.username, agent_call.username) as agent_name,
                COALESCE(td.edited_by, dc.call_by) as agent_id,

                -- Classification Info
                cat.name as category_name,
                sub.name as subcategory_name,
                code.name as code_name,

                -- Context-specific info
                CASE
                    WHEN r.reviewable_type LIKE '%TicketDetail' THEN 'Ticket'
                    WHEN r.reviewable_type LIKE '%DriverCall' THEN 'Call'
                    ELSE 'Other'
                END as context_type,
                
                td.ticket_id,
                t.ticket_number,
                dc.driver_id,
                dr.name as driver_name,
                
                -- Discussion Info
                (SELECT COUNT(*) FROM discussions d WHERE d.discussable_type LIKE '%Review' AND d.discussable_id = r.id AND d.status = 'open') as open_discussion_count,
                (SELECT d.id FROM discussions d WHERE d.discussable_type LIKE '%Review' AND d.discussable_id = r.id ORDER BY d.created_at DESC LIMIT 1) as discussion_id

            FROM reviews r
            JOIN users reviewer ON r.reviewed_by = reviewer.id

            -- Joins for classification
            LEFT JOIN ticket_categories cat ON r.ticket_category_id = cat.id
            LEFT JOIN ticket_subcategories sub ON r.ticket_subcategory_id = sub.id
            LEFT JOIN ticket_codes code ON r.ticket_code_id = code.id
            
            -- Joins for context (Ticket or Call)
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN tickets t ON td.ticket_id = t.id
            
            LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
            LEFT JOIN drivers dr ON dc.driver_id = dr.id
            
            -- Joins to find the agent being reviewed
            LEFT JOIN users agent_ticket ON td.edited_by = agent_ticket.id
            LEFT JOIN users agent_call ON dc.call_by = agent_call.id
        ";

        $whereClauses = [];
        $params = [];

        // --- AUTHORIZATION LOGIC ---
        $role = $_SESSION['role_name'] ?? 'guest';
        $userId = $_SESSION['user_id'] ?? 0;

        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];

        if ($role === 'agent') {
            // Agent can only see reviews of their own work
            $whereClauses[] = "(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)";
            $params[':agent_review_user_id_ticket'] = $userId;
            $params[':agent_review_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles)) {
            // If user is not an agent and not a high-access role, return nothing.
            return [];
        }

        // --- FILTERING LOGIC ---

        // Date range filter
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(r.reviewed_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(r.reviewed_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        // Context type filter
        if (!empty($filters['context_type'])) {
            if ($filters['context_type'] === 'Ticket') {
                $whereClauses[] = "r.reviewable_type LIKE '%TicketDetail'";
            } elseif ($filters['context_type'] === 'Call') {
                $whereClauses[] = "r.reviewable_type LIKE '%DriverCall'";
            }
        }

        // Classification filters
        if (!empty($filters['category_id'])) {
            $whereClauses[] = "r.ticket_category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['subcategory_id'])) {
            $whereClauses[] = "r.ticket_subcategory_id = :subcategory_id";
            $params[':subcategory_id'] = $filters['subcategory_id'];
        }
        if (!empty($filters['code_id'])) {
            $whereClauses[] = "r.ticket_code_id = :code_id";
            $params[':code_id'] = $filters['code_id'];
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY r.reviewed_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // It's good practice to log the error
            error_log('QualityModel Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }

    /**
     * Get filtered discussions for the quality page.
     */
    public function getFilteredDiscussions($filters = [])
    {
        $sql = "
            SELECT
                d.id as discussion_id,
                d.reason,
                d.status,
                d.created_at,
                opener.username as opener_name,
                (SELECT COUNT(*) FROM discussion_replies dr WHERE dr.discussion_id = d.id) as replies_count,
                
                -- Agent being discussed
                COALESCE(agent_ticket.username, agent_call.username) as agent_name,
                COALESCE(td.edited_by, dc.call_by) as agent_id,

                -- Context Info
                r.reviewable_type,
                td.ticket_id,
                t.ticket_number,
                dc.driver_id,
                dr.name as driver_name
                
            FROM discussions d
            JOIN users opener ON d.opened_by = opener.id

            -- Joins to get to the context (Ticket/Call)
            LEFT JOIN reviews r ON d.discussable_id = r.id AND d.discussable_type LIKE '%Review'
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN tickets t ON td.ticket_id = t.id
            LEFT JOIN driver_calls dc on r.reviewable_id = dc.id and r.reviewable_type LIKE '%DriverCall'
            LEFT JOIN drivers dr ON dc.driver_id = dr.id
            
            -- Joins to find the agent being discussed
            LEFT JOIN users agent_ticket ON td.edited_by = agent_ticket.id
            LEFT JOIN users agent_call ON dc.call_by = agent_call.id
        ";

        $whereClauses = [];
        $params = [];

        // --- AUTHORIZATION LOGIC ---
        $role = $_SESSION['role_name'] ?? 'guest';
        $userId = $_SESSION['user_id'] ?? 0;

        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];

        if ($role === 'agent') {
            // Agent can only see discussions about their work
            $whereClauses[] = "(td.edited_by = :agent_discussion_user_id_ticket OR dc.call_by = :agent_discussion_user_id_call)";
            $params[':agent_discussion_user_id_ticket'] = $userId;
            $params[':agent_discussion_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles)) {
            // If user is not an agent and not a high-access role, return nothing.
            return [];
        }

        // --- FILTERING LOGIC ---

        // Date range filter
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "DATE(d.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "DATE(d.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        // Context type filter
        if (!empty($filters['context_type'])) {
            if ($filters['context_type'] === 'Ticket') {
                $whereClauses[] = "r.reviewable_type LIKE '%TicketDetail'";
            } elseif ($filters['context_type'] === 'Call') {
                $whereClauses[] = "r.reviewable_type LIKE '%DriverCall'";
            }
        }

        // Status filter
        if (!empty($filters['status']) && in_array($filters['status'], ['open', 'closed'])) {
            $whereClauses[] = "d.status = :status";
            $params[':status'] = $filters['status'];
        }

        // Classification filters (based on the review's classification)
        if (!empty($filters['category_id'])) {
            $whereClauses[] = "r.ticket_category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['subcategory_id'])) {
            $whereClauses[] = "r.ticket_subcategory_id = :subcategory_id";
            $params[':subcategory_id'] = $filters['subcategory_id'];
        }
        if (!empty($filters['code_id'])) {
            $whereClauses[] = "r.ticket_code_id = :code_id";
            $params[':code_id'] = $filters['code_id'];
        }


        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY d.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('QualityModel Discussions Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }
}