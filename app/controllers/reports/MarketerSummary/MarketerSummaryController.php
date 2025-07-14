<?php

namespace App\Controllers\Reports\MarketerSummary;

use App\Core\Controller;

class MarketerSummaryController extends Controller
{
    private $summaryModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role_name'], ['admin', 'developer', 'marketing'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->summaryModel = $this->model('reports/MarketerSummary/MarketerSummaryReport');
    }

    public function index()
    {
        $filters = [
            'marketer_id' => $_GET['marketer_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $summary = $this->summaryModel->getSummary($filters);

        $data = [
            'title' => 'ملخص تقرير المسوقين',
            'summary' => $summary,
            'marketers' => $this->summaryModel->getMarketers(),
            'filters' => $filters
        ];

        $this->view('reports/MarketerSummary/index', $data);
    }
}