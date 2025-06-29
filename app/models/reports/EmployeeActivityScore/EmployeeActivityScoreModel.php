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

    public function getEmployeeScores($filters) {
        // 1. Get the comprehensive user report data.
        // The UsersReport model already does all the heavy lifting of calculating points.
        $usersData = $this->usersReportModel->getUsersReportWithPoints($filters);
        $allUsersWithStats = $usersData['users'];

        // 2. Sort the users by their final total points in descending order.
        usort($allUsersWithStats, function($a, $b) {
            $pointsA = $a['points_details']['final_total_points'] ?? 0;
            $pointsB = $b['points_details']['final_total_points'] ?? 0;
            return $pointsB <=> $pointsA;
        });

        // 3. Return the sorted list of users.
        return $allUsersWithStats;
    }
} 