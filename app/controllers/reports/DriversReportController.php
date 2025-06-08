<?php

class DriversReportController extends Controller
{
    private $driversReportModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->driversReportModel = $this->model('reports/DriversReport');
    }

    public function index()
    {
        $filters = [
            'main_system_status' => $_GET['main_system_status'] ?? '',
            'data_source' => $_GET['data_source'] ?? '',
            'has_missing_documents' => $_GET['has_missing_documents'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = $this->driversReportModel->getDriversReport($filters);
        
        $this->view('reports/drivers', $data);
    }
} 