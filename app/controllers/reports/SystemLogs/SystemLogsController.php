<?php

namespace App\Controllers\Reports\SystemLogs;

use App\Core\Controller;

class SystemLogsController extends Controller
{
    private $logModel;

    public function __construct()
    {
        parent::__construct();
        // Use the standard authorization method to check for multiple roles
        $this->authorize(['developer', 'admin']);
        
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