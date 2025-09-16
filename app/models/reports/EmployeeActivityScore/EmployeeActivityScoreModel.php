<?php

namespace App\Models\Reports\EmployeeActivityScore;

use App\Core\Database;
use App\Models\Reports\Users\UsersReport;

class EmployeeActivityScoreModel
{
    private $db;
    private $usersReportModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->usersReportModel = new UsersReport();
    }

    /**
     * Apply filters, sorting and caching
     */
    private function getFilteredAndSortedUsers($filters)
    {
        static $cachedData = [];
        $cacheKey = md5(json_encode($filters));

        if (isset($cachedData[$cacheKey])) {
            return $cachedData[$cacheKey];
        }

        // تأكد إن users دايمًا array حتى لو null
        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);
        $allUsersWithStats = $usersData['users'] ?? [];

        // Post-fetch filtering for username
        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $allUsersWithStats = array_filter($allUsersWithStats, function ($user) use ($searchTerm) {
                return isset($user['username']) && strpos(strtolower($user['username']), $searchTerm) !== false;
            });
        }

        // Safeguards: ضمن وجود المفاتيح الأساسية
        foreach ($allUsersWithStats as &$user) {
            if (!isset($user['username'])) {
                $user['username'] = '';
            }

            if (!isset($user['team_name'])) {
                $user['team_name'] = 'N/A';
            }

            if (!isset($user['points_details']) || !is_array($user['points_details'])) {
                $user['points_details'] = ['final_total_points' => 0];
            } elseif (!isset($user['points_details']['final_total_points'])) {
                $user['points_details']['final_total_points'] = 0;
            }

            // ضمان وجود counters أساسية لو الview بيستخدمها
            if (!isset($user['normal_tickets'])) {
                $user['normal_tickets'] = 0;
            }
            if (!isset($user['vip_tickets'])) {
                $user['vip_tickets'] = 0;
            }
            if (!isset($user['call_stats']) || !is_array($user['call_stats'])) {
                $user['call_stats'] = [
                    'total_incoming_calls' => 0,
                    'total_outgoing_calls' => 0
                ];
            }
        }
        unset($user); // break reference

        // Sort by total points desc
        usort($allUsersWithStats, function ($a, $b) {
            $pointsA = $a['points_details']['final_total_points'] ?? 0;
            $pointsB = $b['points_details']['final_total_points'] ?? 0;
            return $pointsB <=> $pointsA;
        });

        $cachedData[$cacheKey] = $allUsersWithStats;
        return $allUsersWithStats;
    }

    /**
     * Paginated employees with points
     */
    public function getEmployeeScores($filters, $limit = 25, $offset = 0)
    {
        $allUsers = $this->getFilteredAndSortedUsers($filters);
        return array_slice($allUsers, $offset, $limit);
    }

    /**
     * Count of employees with filters
     */
    public function getScoresCount($filters)
    {
        return count($this->getFilteredAndSortedUsers($filters));
    }
}
