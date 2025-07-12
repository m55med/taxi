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
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM ticket_details td
                    JOIN tickets t ON td.ticket_id = t.id
                    JOIN users u ON t.created_by = u.id
                    LEFT JOIN platforms p ON td.platform_id = p.id
                    LEFT JOIN ticket_categories cat ON td.category_id = cat.id
                    LEFT JOIN ticket_subcategories subcat ON td.subcategory_id = subcat.id
                    LEFT JOIN ticket_codes code ON td.code_id = code.id
                    LEFT JOIN team_members tm ON u.id = tm.user_id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['user_id'])) $conditions[] = "t.created_by = :user_id";
        if (!empty($filters['team_id'])) $conditions[] = "tm.team_id = :team_id";
        if (!empty($filters['platform_id'])) $conditions[] = "td.platform_id = :platform_id";
        if (!empty($filters['category_id'])) $conditions[] = "td.category_id = :category_id";
        if (isset($filters['is_vip']) && $filters['is_vip'] !== '') $conditions[] = "td.is_vip = :is_vip";
        if (!empty($filters['search'])) $conditions[] = "(td.notes LIKE :search OR td.phone LIKE :search)";
        if (!empty($filters['date_from'])) $conditions[] = "DATE(td.created_at) >= :date_from";
        if (!empty($filters['date_to'])) $conditions[] = "DATE(td.created_at) <= :date_to";

        // Assign params after building conditions
        foreach ($filters as $key => $value) {
            if (!empty($value) || (isset($filters[$key]) && $filters[$key] !== '')) {
                if ($key === 'search') $params[":$key"] = '%' . $value . '%';
                else $params[":$key"] = $value;
            }
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getTickets($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT t.id, t.ticket_number, td.is_vip, p.name as platform_name, td.phone,
                       cat.name as category_name, subcat.name as subcategory_name, code.name as code_name,
                       u.username as created_by_user, td.created_at
                " . $queryParts['base'] . $queryParts['where']
                . " ORDER BY td.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTicketsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(td.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        return [
            'users' => $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC),
            'teams' => $this->db->query("SELECT id, name FROM teams ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'platforms' => $this->db->query("SELECT id, name FROM platforms ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
            'categories' => $this->db->query("SELECT id, name FROM ticket_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
} 