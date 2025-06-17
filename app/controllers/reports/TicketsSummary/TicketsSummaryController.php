<?php

namespace App\Controllers\Reports\TicketsSummary;

use App\Core\Controller;

class TicketsSummaryController extends Controller
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
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->summaryModel = $this->model('reports/TicketsSummary/TicketsSummaryReport');
    }

    public function index()
    {
        $summary = $this->summaryModel->getSummary();

        $data = [
            'title' => 'ملخص تقرير التذاكر',
            'summary' => $summary
        ];

        $this->view('reports/TicketsSummary/index', $data);
    }
} 