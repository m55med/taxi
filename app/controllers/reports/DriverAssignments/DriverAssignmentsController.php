<?php

namespace App\Controllers\Reports\DriverAssignments;

use App\Core\Controller;

class DriverAssignmentsController extends Controller
{
    private $assignmentModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->assignmentModel = $this->model('reports/DriverAssignments/DriverAssignmentsReport');
    }

    public function index()
    {
        $filters = [
            'driver_id' => $_GET['driver_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $reportData = $this->assignmentModel->getAssignments($filters);

        $data = [
            'title' => 'تقرير مهام السائقين',
            'assignments' => $reportData['assignments'],
            'drivers' => $reportData['drivers'],
            'filters' => $filters
        ];

        $this->view('reports/DriverAssignments/index', $data);
    }
} 