<?php

namespace App\Models\Reports\TicketDiscussions;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class TicketDiscussionsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    private function buildQueryParts($filters) {
        $baseSql = "FROM discussions d
                    JOIN tickets t ON d.discussable_id = t.id AND d.discussable_type = 'App\\\\Models\\\\Tickets\\\\Ticket'
                    JOIN users u ON d.opened_by = u.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['ticket_id'])) $conditions[] = "d.discussable_id = :ticket_id";
        if (!empty($filters['opened_by'])) $conditions[] = "d.opened_by = :opened_by";
        if (!empty($filters['status'])) $conditions[] = "d.status = :status";
        if (!empty($filters['search'])) $conditions[] = "(d.reason LIKE :search OR d.notes LIKE :search)";
        if (!empty($filters['date_from'])) $conditions[] = "DATE(d.created_at) >= :date_from";
        if (!empty($filters['date_to'])) $conditions[] = "DATE(d.created_at) <= :date_to";

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key === 'search') $params[":$key"] = '%' . $value . '%';
                else $params[":$key"] = $value;
            }
        }
        
        $whereSql = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";
        return ['base' => $baseSql, 'where' => $whereSql, 'params' => $params];
    }

    public function getDiscussions($filters, $limit, $offset)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT d.id, t.ticket_number, d.reason, d.notes, d.status, d.created_at,
                       u.id as user_id, u.username as opened_by_user,
                       t.id as ticket_id_val
                " . $queryParts['base'] . $queryParts['where']
                . " ORDER BY d.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($queryParts['params'] as $key => &$val) $stmt->bindParam($key, $val);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
    
    public function getDiscussionsCount($filters)
    {
        $queryParts = $this->buildQueryParts($filters);
        $sql = "SELECT COUNT(d.id) " . $queryParts['base'] . $queryParts['where'];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParts['params']);
        return (int)$stmt->fetchColumn();
    }
    
    public function getFilterOptions()
    {
        $usersSql = "SELECT DISTINCT u.id, u.username 
                     FROM users u 
                     JOIN discussions d ON u.id = d.opened_by 
                     WHERE d.discussable_type = 'App\\\\Models\\\\Tickets\\\\Ticket' ORDER BY u.username ASC";
        return ['users' => $this->db->query($usersSql)->fetchAll(PDO::FETCH_ASSOC)];
    }
} 