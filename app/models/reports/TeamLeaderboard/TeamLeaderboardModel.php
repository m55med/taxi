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
        // 1. Get all teams and initialize stats, including member count
        $stmt = $this->db->prepare("SELECT t.id, t.name, COUNT(tm.user_id) as member_count 
                                    FROM teams t
                                    LEFT JOIN team_members tm ON t.id = tm.team_id
                                    GROUP BY t.id, t.name
                                    ORDER BY t.name ASC");
        $stmt->execute();
        $allTeams = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $teamStats = [];
        foreach ($allTeams as $team) {
            $teamStats[$team['id']] = [
                'team_id' => $team['id'],
                'team_name' => $team['name'],
                'member_count' => (int)$team['member_count'],
                'total_points' => 0,
                'total_tickets' => 0,
                'total_calls' => 0,
                'sum_of_ratings' => 0,
                'total_reviews' => 0,
            ];
        }

        // 2. Get detailed stats for all users for the given date range
        $userReportFilters = [
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
        ];
        $usersData = $this->usersReportModel->getUsersReportWithPoints($userReportFilters);
        $allUsersWithStats = $usersData['users'];

        // 3. Aggregate the user stats into their respective teams
        foreach ($allUsersWithStats as $user) {
            if (!empty($user['team_id']) && isset($teamStats[$user['team_id']])) {
                $teamId = $user['team_id'];
                
                $teamStats[$teamId]['total_points'] += $user['total_points'] ?? 0;
                $teamStats[$teamId]['total_tickets'] += ($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0);
                $teamStats[$teamId]['total_calls'] += ($user['incoming_calls'] ?? 0) + ($user['outgoing_calls'] ?? 0);
                
                // Correctly calculate sum of ratings for an accurate team average
                $teamStats[$teamId]['sum_of_ratings'] += ($user['quality_score'] ?? 0) * ($user['total_reviews'] ?? 0);
                $teamStats[$teamId]['total_reviews'] += $user['total_reviews'] ?? 0;
            }
        }

        // 4. Calculate final averages for each team
        foreach ($teamStats as &$team) {
            $team['avg_quality_score'] = ($team['total_reviews'] > 0)
                ? ($team['sum_of_ratings'] / $team['total_reviews'])
                : 0;
        }
        unset($team);

        // 5. Sort teams by total points descending
        usort($teamStats, function($a, $b) {
            return $b['total_points'] <=> $a['total_points'];
        });

        return $teamStats;
    }
} 