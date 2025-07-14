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
        $this->userModel = $this->model('user/User');
        $this->dashboardModel = $this->model('dashboard/Dashboard');
        parent::__construct();
    }

    public function index()
    {
        // Fetch the full user object, which includes the role name
        $user = $this->userModel->getUserById($_SESSION['user_id']);

        if (!$user) {
            // This case should ideally not happen if Auth::requireLogin() works
            redirect('logout');
            return;
        }

        // The model expects an array with user details
        $userDataForModel = [
            'id' => $user['id'],
            'role_name' => $user['role_name']
        ];


        // Fetch all dashboard data using the centralized model method
        $dashboardData = $this->dashboardModel->getDashboardData($userDataForModel);

        // This is a master data array passed to the view
        $data = [
            'title' => 'Dashboard',
            'dashboardData' => $dashboardData,
        ];

        $this->view('dashboard/index', $data);
    }
}
