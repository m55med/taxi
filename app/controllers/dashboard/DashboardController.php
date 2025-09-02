<?php

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User\User;
use App\Models\Dashboard\Dashboard;

class DashboardController extends Controller
{
    private $userModel;
    private $dashboardModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->userModel = $this->model('User/User');
        $this->dashboardModel = $this->model('Dashboard/Dashboard');
        parent::__construct();
    }

    public function index()
    {
        $userId = Auth::getUserId();
        if (!$userId) {
            redirect('auth/logout');
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            redirect('auth/logout');
            return;
        }

        // Get date range from GET parameters, with defaults (1st day -> last day of current month)
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // أول يوم في الشهر
        $dateTo   = $_GET['date_to']   ?? date('Y-m-t');  // آخر يوم في الشهر

        $userDataForModel = [
            'id' => $user->id,
            'role_name' => $user->role_name
        ];
        
        // Fetch all dashboard data using the centralized model method
        $dashboardData = $this->dashboardModel->getDashboardData($userDataForModel, $dateFrom, $dateTo);

        // Pass everything to the view
        $data = [
            'title' => 'Dashboard',
            'dashboardData' => $dashboardData,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->view('dashboard/index', $data);
    }

}
