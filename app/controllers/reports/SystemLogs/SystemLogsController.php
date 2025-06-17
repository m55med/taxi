<?php

namespace App\Controllers\Reports\SystemLogs;

use App\Core\Controller;

class SystemLogsController extends Controller
{
    private $logModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        if ($_SESSION['role'] !== 'developer') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->logModel = $this->model('reports/SystemLogs/SystemLogsReport');
    }

    public function index()
    {
        $filters = [
            'level' => $_GET['level'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
        ];

        $reportData = $this->logModel->getLogs($filters);

        $data = [
            'title' => 'تقرير سجلات النظام',
            'logs' => $reportData['logs'],
            'users' => $reportData['users'],
            'levels' => $reportData['levels'],
            'filters' => $filters
        ];

        $this->view('reports/SystemLogs/index', $data);
    }
} 