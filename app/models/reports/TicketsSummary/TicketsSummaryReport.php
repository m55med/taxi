<?php

namespace App\Models\Reports\TicketsSummary;

use App\Core\Database;
use PDO;

class TicketsSummaryReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getSummary()
    {
        $summary = [];

        // Summary by Status - DISABLED as 'status' column does not exist in 'tickets' table
        // $sqlStatus = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
        // $stmtStatus = $this->db->query($sqlStatus);
        // $summary['by_status'] = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);
        $summary['by_status'] = [];

        // Summary by Priority - DISABLED as 'priority' column does not exist in 'tickets' table
        // $sqlPriority = "SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority";
        // $stmtPriority = $this->db->query($sqlPriority);
        // $summary['by_priority'] = $stmtPriority->fetchAll(PDO::FETCH_ASSOC);
        $summary['by_priority'] = [];
        
        // Summary by Category
        $sqlCategory = "SELECT tc.name, COUNT(t.id) as count 
                        FROM tickets t 
                        JOIN ticket_categories tc ON t.category_id = tc.id 
                        GROUP BY tc.name";
        $stmtCategory = $this->db->query($sqlCategory);
        $summary['by_category'] = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }
} 