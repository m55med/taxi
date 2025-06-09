<?php

namespace App\Controllers\Reports;

use App\Core\Controller;

class CallsReportController extends Controller
{
    private $callsReportModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->callsReportModel = $this->model('reports/CallsReport');
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = $this->callsReportModel->getCallsReport($filters);
        
        $this->view('reports/calls', $data);
    }
} 