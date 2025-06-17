<?php

namespace App\Controllers\Reports\DriverCalls;

use App\Core\Controller;

class DriverCallsController extends Controller
{
    private $callsReportModel;

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
        $this->callsReportModel = $this->model('reports/DriverCalls/DriverCallsReport');
    }

    public function index()
    {
        $filters = [
            'country_id' => $_GET['country_id'] ?? '',
            'car_type_id' => $_GET['car_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        $reportData = $this->callsReportModel->getCallsReport($filters);

        $data = [
            'title' => 'تقرير مكالمات السائقين',
            'calls' => $reportData['calls'],
            'countries' => $reportData['countries'],
            'car_types' => $reportData['car_types'],
            'filters' => $filters
        ];

        $this->view('reports/DriverCalls/index', $data);
    }
} 