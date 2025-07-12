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
    
    private function getAggregatedTeamData($filters) {
        // This is a simplified cache key. In a real app, make it more specific.
        $cacheKey = 'team_leaderboard_data_' . md5(json_encode($filters));
        
        // This is a pseudo-caching mechanism for a single request lifecycle.
        // In a real application, you'd use a proper caching system like Redis or Memcached.
        static $cachedData = [];
        if (isset($cachedData[$cacheKey])) {
            return $cachedData[$cacheKey];
        }

        $stmt = $this->db->prepare("SELECT t.id, t.name, COUNT(tm.user_id) as member_count 
                                    FROM teams t
                                    LEFT JOIN team_members tm ON t.id = tm.team_id
                                    GROUP BY t.id, t.name");
        $stmt->execute();
        $allTeams = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $teamStats = [];
        foreach ($allTeams as $team) {
            $teamStats[$team['id']] = [
                'team_id' => $team['id'],
                'team_name' => $team['name'],
                'member_count' => (int)$team['member_count'],
                'total_points' => 0, 'total_tickets' => 0, 'total_calls' => 0,
                'sum_of_ratings' => 0, 'total_reviews' => 0,
            ];
        }

        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);
        $allUsersWithStats = $usersData['users'];

        foreach ($allUsersWithStats as $user) {
            if (!empty($user['team_id']) && isset($teamStats[$user['team_id']])) {
                $teamId = $user['team_id'];
                $teamStats[$teamId]['total_points'] += $user['total_points'] ?? 0;
                $teamStats[$teamId]['total_tickets'] += ($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0);
                $teamStats[$teamId]['total_calls'] += ($user['incoming_calls'] ?? 0) + ($user['outgoing_calls'] ?? 0);
                $teamStats[$teamId]['sum_of_ratings'] += ($user['quality_score'] ?? 0) * ($user['total_reviews'] ?? 0);
                $teamStats[$teamId]['total_reviews'] += $user['total_reviews'] ?? 0;
            }
        }

        foreach ($teamStats as &$team) {
            $team['avg_quality_score'] = ($team['total_reviews'] > 0) ? ($team['sum_of_ratings'] / $team['total_reviews']) : 0;
        }
        unset($team);

        usort($teamStats, fn($a, $b) => $b['total_points'] <=> $a['total_points']);
        
        $cachedData[$cacheKey] = $teamStats;
        return $teamStats;
    }

    public function getTeamLeaderboard($filters, $limit = 25, $offset = 0) {
        $fullData = $this->getAggregatedTeamData($filters);
        return array_slice($fullData, $offset, $limit);
    }

    public function getTeamsCount($filters) {
        $fullData = $this->getAggregatedTeamData($filters);
        return count($fullData);
    }
} 