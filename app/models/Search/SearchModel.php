<?php

namespace App\Models\Search;

use App\Core\Database;
use PDO;

class SearchModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Search for tickets and phone numbers
     * @param string $query Search query
     * @param int $limit Maximum results to return
     * @return array Search results
     */
    public function searchTicketsAndPhones($query, $limit = 10)
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        $sql = "
            SELECT
                'ticket' as type,
                t.id as ticket_id,
                t.ticket_number,
                t.phone,
                td.edited_by as user_id,
                u.username,
                u.name,
                t.created_at,
                t.is_vip,
                CONCAT('Ticket #', t.ticket_number) as display_text,
                CONCAT('/tickets/view/', t.id) as url
            FROM tickets t
            LEFT JOIN ticket_details td ON t.id = td.ticket_id
            LEFT JOIN users u ON td.edited_by = u.id
            WHERE t.ticket_number LIKE :query_ticket
            GROUP BY t.id

            UNION ALL

            SELECT
                'phone' as type,
                t.id as ticket_id,
                t.ticket_number,
                t.phone,
                td.edited_by as user_id,
                u.username,
                u.name,
                t.created_at,
                t.is_vip,
                CONCAT('Phone: ', t.phone, ' (Ticket #', t.ticket_number, ')') as display_text,
                CONCAT('/tickets/view/', t.id) as url
            FROM tickets t
            LEFT JOIN ticket_details td ON t.id = td.ticket_id
            LEFT JOIN users u ON td.edited_by = u.id
            WHERE t.phone LIKE :query_phone
            GROUP BY t.id

            ORDER BY
                CASE
                    WHEN ticket_number = :exact_ticket THEN 1
                    WHEN phone = :exact_phone THEN 2
                    ELSE 3
                END,
                created_at DESC
            LIMIT :limit
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $searchQuery = '%' . $query . '%';
            $exactQuery = $query;

            $stmt->bindParam(':query_ticket', $searchQuery, PDO::PARAM_STR);
            $stmt->bindParam(':query_phone', $searchQuery, PDO::PARAM_STR);
            $stmt->bindParam(':exact_ticket', $exactQuery, PDO::PARAM_STR);
            $stmt->bindParam(':exact_phone', $exactQuery, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug logging
            error_log('SearchModel: Query executed for "' . $query . '", Results: ' . count($results));

            // Remove duplicates based on ticket_id
            $seen = [];
            $uniqueResults = [];

            foreach ($results as $result) {
                if (!in_array($result['ticket_id'], $seen)) {
                    $seen[] = $result['ticket_id'];
                    $uniqueResults[] = $result;
                }
            }

            return $uniqueResults;

        } catch (\Exception $e) {
            error_log('SearchModel Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get search suggestions for autocomplete
     * @param string $query Search query
     * @param int $limit Maximum suggestions
     * @return array Suggestions array
     */
    public function getSearchSuggestions($query, $limit = 5)
    {
        $results = $this->searchTicketsAndPhones($query, $limit);

        $suggestions = [];

        foreach ($results as $result) {
            $suggestions[] = [
                'value' => $result['type'] === 'ticket' ? $result['ticket_number'] : $result['phone'],
                'label' => $result['display_text'],
                'url' => $result['url'],
                'type' => $result['type']
            ];
        }

        // Debug logging
        error_log("Search query: {$query}, Results: " . count($results) . ", Suggestions: " . count($suggestions));

        return $suggestions;
    }
}
