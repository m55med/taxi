<?php

namespace App\Models\Reports\Tickets;

use App\Core\Database;
use PDO;

class TicketsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTickets($filters)
    {
        $sql = "SELECT
                    t.id,
                    t.ticket_number,
                    t.is_vip,
                    p.name as platform_name,
                    t.phone,
                    cat.name as category_name,
                    subcat.name as subcategory_name,
                    code.name as code_name,
                    t.notes,
                    c.name as country_name,
                    u.username as created_by_user,
                    t.created_at
                FROM
                    tickets t
                JOIN platforms p ON t.platform_id = p.id
                JOIN ticket_categories cat ON t.category_id = cat.id
                JOIN ticket_subcategories subcat ON t.subcategory_id = subcat.id
                JOIN ticket_codes code ON t.code_id = code.id
                JOIN users u ON t.created_by = u.id
                LEFT JOIN countries c ON t.country_id = c.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $conditions[] = "t.created_by = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        // Note: Filters for 'status' and 'priority' are ignored as these columns do not exist in the 'tickets' table.

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For filters dropdown
        $users = $this->db->query("SELECT id, username FROM users WHERE role_id IN (SELECT id FROM roles WHERE name IN ('agent', 'employee')) ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'tickets' => $tickets,
            'users' => $users,
        ];
    }
} 