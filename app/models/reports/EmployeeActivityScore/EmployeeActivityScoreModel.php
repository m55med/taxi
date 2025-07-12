<?php
namespace App\Models\Reports\EmployeeActivityScore;
use App\Core\Database;
use App\Models\Reports\Users\UsersReport;

class EmployeeActivityScoreModel {
    private $db;
    private $usersReportModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->usersReportModel = new UsersReport();
    }

    private function getFilteredAndSortedUsers($filters) {
        static $cachedData = [];
        $cacheKey = md5(json_encode($filters));

        if (isset($cachedData[$cacheKey])) {
            return $cachedData[$cacheKey];
        }
        
        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);
        $allUsersWithStats = $usersData['users'];

        // Post-fetch filtering for username since it's not in the base query context
        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $allUsersWithStats = array_filter($allUsersWithStats, function($user) use ($searchTerm) {
                return strpos(strtolower($user['username']), $searchTerm) !== false;
            });
        }
        
        usort($allUsersWithStats, function($a, $b) {
            $pointsA = $a['points_details']['final_total_points'] ?? 0;
            $pointsB = $b['points_details']['final_total_points'] ?? 0;
            return $pointsB <=> $pointsA;
        });
        
        $cachedData[$cacheKey] = $allUsersWithStats;
        return $allUsersWithStats;
    }

    public function getEmployeeScores($filters, $limit = 25, $offset = 0) {
        $allUsers = $this->getFilteredAndSortedUsers($filters);
        return array_slice($allUsers, $offset, $limit);
    }

    public function getScoresCount($filters) {
        return count($this->getFilteredAndSortedUsers($filters));
    }
} 