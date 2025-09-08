<?php

namespace App\Controllers\Reports\TicketRework;

use App\Core\Controller;

class TicketReworkController extends Controller
{
    private $reworkModel;

    public function __construct()
    {
        parent::__construct();
        if (!in_array($_SESSION['role_name'], ['admin', 'developer', 'quality_control'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->reworkModel = $this->model('Reports/TicketRework/TicketReworkReport');
    }

    public function index()
    {
        $filters = [
            'agent_id' => $_GET['agent_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $reworks = $this->reworkModel->getReworks($filters);

        $data = [
            'title' => 'تقرير إعادة العمل على التذاكر',
            'reworks' => $reworks,
            'agents' => $this->reworkModel->getAgents(),
            'filters' => $filters
        ];

        $this->view('reports/TicketRework/index', $data);
    }
}