<?php

namespace App\Models\Reports\TeamPerformance;

use App\Core\Database;
use PDO;

class TeamPerformanceReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getReportData($teamId, $filters)
    {
        // Get team members
        $stmt = $this->db->prepare("SELECT u.id, u.username FROM users u JOIN team_members tm ON u.id = tm.user_id WHERE tm.team_id = :team_id");
        $stmt->execute([':team_id' => $teamId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $memberIds = array_column($members, 'id');

        if (empty($memberIds)) {
            return ['members_performance' => [], 'team_summary' => []];
        }
        
        // Filter by specific member if requested
        if (!empty($filters['member_id']) && in_array($filters['member_id'], $memberIds)) {
            $memberIds = [(int)$filters['member_id']];
        }

        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));

        $sql = "SELECT 
                    u.id as user_id,
                    u.username,
                    COUNT(DISTINCT dc.id) as total_calls,
                    COUNT(DISTINCT t.id) as total_tickets
                FROM users u
                LEFT JOIN driver_calls dc ON u.id = dc.call_by AND dc.call_by IN ($placeholders)
                LEFT JOIN tickets t ON u.id = t.created_by AND t.created_by IN ($placeholders)
                WHERE u.id IN ($placeholders)";

        $params = array_merge($memberIds, $memberIds, $memberIds);

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(dc.created_at) >= ? AND DATE(t.created_at) >= ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(dc.created_at) <= ? AND DATE(t.created_at) <= ?";
            $params[] = $filters['date_to'];
            $params[] = $filters['date_to'];
        }
        
        $sql .= " GROUP BY u.id, u.username ORDER BY u.username";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $membersPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate team summary
        $teamSummary = [
            'total_calls' => array_sum(array_column($membersPerformance, 'total_calls')),
            'total_tickets' => array_sum(array_column($membersPerformance, 'total_tickets')),
            'member_count' => count($members)
        ];

        return [
            'members_performance' => $membersPerformance,
            'team_summary' => $teamSummary,
        ];
    }
} 