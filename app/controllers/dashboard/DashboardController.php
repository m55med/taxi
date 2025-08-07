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

        // Get date range from GET parameters, with defaults
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        $userDataForModel = [
            'id' => $user->id,
            'role_name' => $user->role_name
        ];
        
        // Fetch all dashboard data using the centralized model method
        $dashboardData = $this->dashboardModel->getDashboardData($userDataForModel, $dateFrom, $dateTo);

        // This is a master data array passed to the view
        $data = [
            'title' => 'Dashboard',
            'dashboardData' => $dashboardData,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $this->view('dashboard/index', $data);
    }
}
