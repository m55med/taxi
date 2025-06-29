<?php
namespace App\Models\Reports\TeamLeaderboard;
use App\Core\Database;
use App\Models\Reports\Users\UsersReport;

class TeamLeaderboardModel {
    private $db;
    private $usersReportModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->usersReportModel = new UsersReport();
    }

    public function getTeamLeaderboard($filters) {
        // 1. Get detailed stats for all users using the existing, powerful model
        // We pass a modified filter set that ignores specific users/teams to get a full dataset
        $userReportFilters = [
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
        ];
        $usersData = $this->usersReportModel->getUsersReportWithPoints($userReportFilters);
        $allUsersWithStats = $usersData['users'];

        // 2. Aggregate the results by team
        $teamStats = [];
        foreach ($allUsersWithStats as $user) {
            $teamId = $user['team_id'] ?? 'unassigned';
            $teamName = $user['team_name'] ?? 'Unassigned';

            // Initialize team if not exists
            if (!isset($teamStats[$teamId])) {
                $teamStats[$teamId] = [
                    'team_id' => $teamId,
                    'team_name' => $teamName,
                    'total_points' => 0,
                    'total_tickets' => 0,
                    'total_calls' => 0,
                ];
            }
            
            // 3. Aggregate stats for the team
            $teamStats[$teamId]['total_points'] += $user['points_details']['final_total_points'] ?? 0;
            $teamStats[$teamId]['total_tickets'] += ($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0);
            $teamStats[$teamId]['total_calls'] += ($user['call_stats']['total_incoming_calls'] ?? 0) + ($user['call_stats']['total_outgoing_calls'] ?? 0);
        }

        // 4. Remove the 'Unassigned' group before sorting
        if (isset($teamStats['unassigned'])) {
            unset($teamStats['unassigned']);
        }

        // 5. Sort teams by total points descending
        usort($teamStats, function($a, $b) {
            return $b['total_points'] <=> $a['total_points'];
        });

        return $teamStats;
    }
} 