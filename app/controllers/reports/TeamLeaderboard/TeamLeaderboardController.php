<?php

namespace App\Controllers\Reports\TeamLeaderboard;

use App\Core\Controller;

class TeamLeaderboardController extends Controller
{
    private $leaderboardModel;

    public function __construct()
    {
        parent::__construct();
        // Simple auth check
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $this->leaderboardModel = $this->model('reports/TeamLeaderboard/TeamLeaderboardModel');
    }

    public function index()
    {
        // 1. Set up filters
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-t'),
        ];

        // 2. Get data from the model
        $leaderboardData = $this->leaderboardModel->getTeamLeaderboard($filters);

        // 3. Prepare data for the view
        $data = [
            'page_main_title' => 'Team Leaderboard',
            'leaderboard' => $leaderboardData,
            'filters' => $filters,
        ];

        // 4. Load the view
        $this->view('reports/team-leaderboard/index', $data);
    }
} 