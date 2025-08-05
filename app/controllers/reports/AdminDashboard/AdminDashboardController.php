<?php

namespace App\Controllers\Reports\AdminDashboard;

use App\Core\Controller;
use App\Core\Auth;

class AdminDashboardController extends Controller
{
    private $dashboardModel;

    public function __construct()
    {
        parent::__construct();
        Auth::check();

        // Authorization: Only Admins/Developers can access
        if (!in_array($_SESSION['role_name'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->dashboardModel = $this->model('Reports/AdminDashboard/AdminDashboardReport');
    }

    public function index()
    {
        $overviewData = $this->dashboardModel->getSystemOverview();

        $data = [
            'title' => 'لوحة تحكم التقارير للمدير',
            'overview' => $overviewData,
        ];

        $this->view('reports/AdminDashboard/index', $data);
    }
}