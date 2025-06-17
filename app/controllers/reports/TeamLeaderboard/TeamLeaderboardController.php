<?php

namespace App\Controllers\Reports\TeamLeaderboard;

use App\Core\Controller;

class TeamLeaderboardController extends Controller
{
    private $leaderboardModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'team_leader'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->leaderboardModel = $this->model('reports/TeamLeaderboard/TeamLeaderboardReport');
    }

    public function index()
    {
        $leaderboard = $this->leaderboardModel->getLeaderboard();
        
        $data = [
            'title' => 'تقرير لوحة صدارة الفرق',
            'leaderboard' => $leaderboard,
        ];

        $this->view('reports/TeamLeaderboard/index', $data);
    }
} 