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
        $sql = "SELECT
            r.id as review_id,
            r.rating,
            r.review_notes,
            r.reviewed_at,
            r.reviewable_type,
            r.reviewable_id,
            reviewer.name as reviewer_name,

            -- Agent being reviewed
            COALESCE(agent_ticket.name, agent_call.name) as agent_name,
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
        LEFT JOIN users agent_call ON dc.call_by = agent_call.id";

        $whereClauses = [];
        $params = [];

        // --- AUTHORIZATION LOGIC ---
        // Use proper Auth class methods for consistency, with fallback to session
        $role = \App\Core\Auth::getUserRole();
        $userId = \App\Core\Auth::getUserId();
        
        // Fallback: If Auth class returns null, try reading from session directly
        if (!$role && isset($_SESSION['user']['role_name'])) {
            $role = $_SESSION['user']['role_name'];
        }
        if (!$userId && isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        }
        
        // Final fallback values
        $role = $role ?? 'guest';
        $userId = $userId ?? 0;

        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer', 'Quality'];

        if ($role === 'agent' && empty($filters['agent_id'])) {
            // Only apply agent restriction if no specific agent_id is being filtered
            $whereClauses[] = "(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)";
            $params[':agent_review_user_id_ticket'] = $userId;
            $params[':agent_review_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles)) {
            return ['error' => 'Access denied for role: ' . $role . '. Allowed roles: ' . implode(', ', $highAccessRoles) . ', agent'];
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

        // Agent (reviewed person) filter
        if (!empty($filters['agent_id'])) {
            $whereClauses[] = "(td.edited_by = :agent_id_ticket OR dc.call_by = :agent_id_call)";
            $params[':agent_id_ticket'] = $filters['agent_id'];
            $params[':agent_id_call'] = $filters['agent_id'];
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY r.reviewed_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add debug information if result is empty
            if (empty($result)) {
                error_log('QualityModel: No reviews found. Role: ' . $role . ', User ID: ' . $userId . ', Query: ' . $sql);
            }
            
            return $result;
        } catch (\PDOException $e) {
            // It's good practice to log the error
            error_log('QualityModel Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            return ['error' => 'Database query failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get filtered discussions for the quality page.
     */
    public function getFilteredDiscussions($filters = [])
    {
        $sql = "SELECT
            d.id as discussion_id,
            d.reason,
            d.status,
            d.created_at,
            opener.name as opener_name,
            (SELECT COUNT(*) FROM discussion_replies dr WHERE dr.discussion_id = d.id) as replies_count,

            -- Agent being discussed
            COALESCE(agent_ticket.name, agent_call.name) as agent_name,
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
        LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
        LEFT JOIN drivers dr ON dc.driver_id = dr.id

        -- Joins to find the agent being discussed
        LEFT JOIN users agent_ticket ON td.edited_by = agent_ticket.id
        LEFT JOIN users agent_call ON dc.call_by = agent_call.id";

        $whereClauses = [];
        $params = [];

        // --- AUTHORIZATION LOGIC ---
        // Use proper Auth class methods for consistency, with fallback to session
        $role = \App\Core\Auth::getUserRole();
        $userId = \App\Core\Auth::getUserId();
        
        // Fallback: If Auth class returns null, try reading from session directly
        if (!$role && isset($_SESSION['user']['role_name'])) {
            $role = $_SESSION['user']['role_name'];
        }
        if (!$userId && isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        }
        
        // Final fallback values
        $role = $role ?? 'guest';
        $userId = $userId ?? 0;

        $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];

        if ($role === 'agent') {
            $whereClauses[] = "(td.edited_by = :agent_discussion_user_id_ticket OR dc.call_by = :agent_discussion_user_id_call)";
            $params[':agent_discussion_user_id_ticket'] = $userId;
            $params[':agent_discussion_user_id_call'] = $userId;
        } elseif (!in_array($role, $highAccessRoles)) {
            return ['error' => 'Access denied for role: ' . $role . '. Allowed roles: ' . implode(', ', $highAccessRoles) . ', agent'];
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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (\PDOException $e) {
            error_log('QualityModel Discussions Error: ' . $e->getMessage());
            return ['error' => 'Database query failed'];
        }
    }

    /**
     * Update a review
     */
    public function updateReview($reviewId, $rating, $reviewNotes)
    {
        try {
            // Check if review exists
            $checkSql = "SELECT id FROM reviews WHERE id = :review_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Review not found'];
            }

            // Update the review
            $sql = "UPDATE reviews SET 
                        rating = :rating, 
                        review_notes = :review_notes
                    WHERE id = :review_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':review_notes', $reviewNotes, PDO::PARAM_STR);
            $stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Review updated successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to update review'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a review
     */
    public function deleteReview($reviewId)
    {
        try {
            // Check if review exists
            $checkSql = "SELECT id FROM reviews WHERE id = :review_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Review not found'];
            }

            // Delete the review
            $sql = "DELETE FROM reviews WHERE id = :review_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Review deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to delete review'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Search reviews with pagination and filtering
     */


    public function searchReviews($filters = [], $searchQuery = '', $page = 1, $perPage = 25)
    {
        // Validate inputs
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage)); // Limit perPage to reasonable range

        $baseSql = "SELECT
            r.id as review_id,
            r.rating,
            r.review_notes,
            r.reviewed_at,
            r.reviewable_type,
            r.reviewable_id,
            reviewer.name as reviewer_name,
            COALESCE(agent_ticket.name, agent_call.name) as agent_name,
            COALESCE(td.edited_by, dc.call_by) as agent_id,
            cat.name as category_name,
            sub.name as subcategory_name,
            code.name as code_name,
            CASE
                WHEN r.reviewable_type LIKE '%TicketDetail' THEN 'Ticket'
                WHEN r.reviewable_type LIKE '%DriverCall' THEN 'Call'
                ELSE 'Other'
            END as context_type,
            td.ticket_id,
            t.ticket_number,
            dc.driver_id,
            dr.name as driver_name,
            (SELECT COUNT(*) FROM discussions d WHERE d.discussable_type LIKE '%Review' AND d.discussable_id = r.id AND d.status = 'open') as open_discussion_count,
            (SELECT d.id FROM discussions d WHERE d.discussable_type LIKE '%Review' AND d.discussable_id = r.id ORDER BY d.created_at DESC LIMIT 1) as discussion_id
        FROM reviews r
        JOIN users reviewer ON r.reviewed_by = reviewer.id
        LEFT JOIN ticket_categories cat ON r.ticket_category_id = cat.id
        LEFT JOIN ticket_subcategories sub ON r.ticket_subcategory_id = sub.id
        LEFT JOIN ticket_codes code ON r.ticket_code_id = code.id
        LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
        LEFT JOIN tickets t ON td.ticket_id = t.id
        LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'
        LEFT JOIN drivers dr ON dc.driver_id = dr.id
        LEFT JOIN users agent_ticket ON td.edited_by = agent_ticket.id
        LEFT JOIN users agent_call ON dc.call_by = agent_call.id";

        $whereClauses = [];
        $params = [];
     
         // --- AUTHORIZATION ---
         $role = \App\Core\Auth::getUserRole() ?? ($_SESSION['user']['role_name'] ?? 'guest');
         $userId = \App\Core\Auth::getUserId() ?? ($_SESSION['user']['id'] ?? 0);
     
         $highAccessRoles = ['admin', 'quality_manager', 'Team_leader', 'developer', 'Quality'];
     
         if ($role === 'agent' && empty($filters['agent_id'])) {
             $whereClauses[] = "(td.edited_by = :agent_review_user_id_ticket OR dc.call_by = :agent_review_user_id_call)";
             $params[':agent_review_user_id_ticket'] = $userId;
             $params[':agent_review_user_id_call'] = $userId;
         } elseif (!in_array($role, $highAccessRoles)) {
             return ['error' => 'Access denied for role: ' . $role];
         }
     
        // --- SEARCH LOGIC ---
        if (!empty($searchQuery)) {
            $searchTerms = explode(' ', trim($searchQuery));
            $searchConditions = [];

            foreach ($searchTerms as $index => $term) {
                if (empty($term)) continue;
                $paramName = ":search_term_$index";
                $params[$paramName] = "%$term%";
                $searchConditions[] = "
                    reviewer.name LIKE $paramName
                    OR COALESCE(agent_ticket.name, agent_call.name) LIKE $paramName
                    OR t.ticket_number LIKE $paramName
                    OR cat.name LIKE $paramName
                    OR sub.name LIKE $paramName
                    OR code.name LIKE $paramName
                    OR r.review_notes LIKE $paramName
                ";
            }

            if (!empty($searchConditions)) {
                $whereClauses[] = "(" . implode(" OR ", $searchConditions) . ")";
            }
        }
     
         // --- FILTERING LOGIC ---
         if (!empty($filters['start_date'])) {
             $whereClauses[] = "DATE(r.reviewed_at) >= :start_date";
             $params[':start_date'] = $filters['start_date'];
         }
         if (!empty($filters['end_date'])) {
             $whereClauses[] = "DATE(r.reviewed_at) <= :end_date";
             $params[':end_date'] = $filters['end_date'];
         }
         if (!empty($filters['context_type'])) {
             if ($filters['context_type'] === 'Ticket') {
                 $whereClauses[] = "r.reviewable_type LIKE '%TicketDetail'";
             } elseif ($filters['context_type'] === 'Call') {
                 $whereClauses[] = "r.reviewable_type LIKE '%DriverCall'";
             }
         }
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
         if (!empty($filters['agent_id'])) {
             $whereClauses[] = "(td.edited_by = :agent_id_ticket OR dc.call_by = :agent_id_call)";
             $params[':agent_id_ticket'] = $filters['agent_id'];
             $params[':agent_id_call'] = $filters['agent_id'];
         }
     
         // --- Apply WHERE ---
         if ($whereClauses) {
             $baseSql .= " WHERE " . implode(" AND ", $whereClauses);
         }
     
        // --- Count query ---
        $countSql = "SELECT COUNT(*) as total FROM ($baseSql) as count_query";

        // Debug logging
        error_log("Count SQL: " . $countSql);
        error_log("Params: " . json_encode($params));

        try {
            $countStmt = $this->db->prepare($countSql);

            // Bind parameters safely - only bind what's actually needed
            if (!empty($params)) {
                foreach ($params as $key => $val) {
                    // Check if the parameter is actually used in the count query
                    if (strpos($countSql, $key) !== false) {
                        $countStmt->bindValue($key, $val);
                        error_log("Binding parameter: $key = $val");
                    } else {
                        error_log("Skipping parameter: $key (not found in query)");
                    }
                }
            }

            $countStmt->execute();
            $totalCount = $countStmt->fetchColumn();
            error_log("Total count: " . $totalCount);
        } catch (\PDOException $e) {
            error_log('Count query error: ' . $e->getMessage() . ' SQL: ' . $countSql . ' Params: ' . json_encode($params));
            // Fallback: try executing without any parameters if binding fails
            try {
                $countStmt = $this->db->query($countSql);
                $totalCount = $countStmt->fetchColumn();
                error_log("Fallback count successful: " . $totalCount);
            } catch (\PDOException $fallbackError) {
                error_log('Fallback count query failed: ' . $fallbackError->getMessage());
                return ['error' => 'Database error in count query: ' . $e->getMessage()];
            }
        }
     
         // --- Pagination query ---
         $offset = ($page - 1) * $perPage;
         $mainSql = $baseSql . " ORDER BY r.reviewed_at DESC LIMIT :limit OFFSET :offset";
     
        try {
            $stmt = $this->db->prepare($mainSql);

            // Bind WHERE clause parameters safely
            if (!empty($params)) {
                foreach ($params as $key => $val) {
                    if (strpos($mainSql, $key) !== false) {
                        $stmt->bindValue($key, $val);

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

                    }
                }
            }

            // Bind pagination parameters
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
           error_log(
               'Main query error: ' . $e->getMessage()
               . ' SQL: ' . $mainSql
               . ' Params: ' . json_encode(array_merge($params, [':limit' => $perPage, ':offset' => $offset]))
           );
           return ['error' => 'Database error in main query: ' . $e->getMessage()];
        }
     
         return [
             'reviews' => $result,
             'total' => $totalCount,
             'page' => $page,
             'per_page' => $perPage,
             'total_pages' => ceil($totalCount / $perPage)
         ];
     }
     
    

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions($searchQuery = '', $limit = 5)
    {
        if (empty($searchQuery)) {
            return [];
        }

        $sql = "SELECT DISTINCT
            COALESCE(agent_ticket.name, agent_call.name) as agent_name,
            t.ticket_number,
            cat.name as category_name
        FROM reviews r
        JOIN users reviewer ON r.reviewed_by = reviewer.id

        -- Joins for classification
        LEFT JOIN ticket_categories cat ON r.ticket_category_id = cat.id

        -- Joins for context
        LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
        LEFT JOIN tickets t ON td.ticket_id = t.id
        LEFT JOIN driver_calls dc ON r.reviewable_id = dc.id AND r.reviewable_type LIKE '%DriverCall'

        -- Joins to find the agent being reviewed
        LEFT JOIN users agent_ticket ON td.edited_by = agent_ticket.id
        LEFT JOIN users agent_call ON dc.call_by = agent_call.id

        WHERE (
            reviewer.name LIKE :query
            OR COALESCE(agent_ticket.name, agent_call.name) LIKE :query
            OR t.ticket_number LIKE :query
            OR cat.name LIKE :query
        )
        ORDER BY
            CASE
                WHEN reviewer.name LIKE :query THEN 1
                WHEN COALESCE(agent_ticket.name, agent_call.name) LIKE :query THEN 2
                WHEN t.ticket_number LIKE :query THEN 3
                ELSE 4
            END,
            reviewer.name
        LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $suggestions = [];

            foreach ($results as $row) {
                if (!empty($row['agent_name'])) {
                    $suggestions[] = $row['agent_name'];
                }
                if (!empty($row['ticket_number'])) {
                    $suggestions[] = 'Ticket #' . $row['ticket_number'];
                }
                if (!empty($row['category_name'])) {
                    $suggestions[] = $row['category_name'];
                }
            }

            return array_unique($suggestions);
        } catch (\PDOException $e) {
            error_log('QualityModel Search Suggestions Error: ' . $e->getMessage());
            return [];
        }
    }
}