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
            // This should not happen if requireLogin() is effective, but as a safeguard:
            redirect('auth/logout');
            return;
        }

        // Fetch the full user object, which includes the role name
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            // This case might happen if user was deleted but session persists
            redirect('auth/logout');
            return;
        }

        // The model expects an array with user details
        $userDataForModel = [
            'id' => $user->id,
            'role_name' => $user->role_name
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
