<?php

namespace App\Controllers\Reports\Tickets;

use App\Core\Controller;

class TicketsController extends Controller
{
    private $ticketModel;

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
        $this->ticketModel = $this->model('reports/Tickets/TicketsReport');
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
        ];

        $reportData = $this->ticketModel->getTickets($filters);

        $data = [
            'title' => 'تقرير التذاكر المفصل',
            'tickets' => $reportData['tickets'],
            'users' => $reportData['users'],
            'statuses' => ['new', 'in_progress', 'on_hold', 'resolved', 'closed'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
            'filters' => $filters
        ];

        $this->view('reports/Tickets/index', $data);
    }
} 