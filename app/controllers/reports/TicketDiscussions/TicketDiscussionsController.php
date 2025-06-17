<?php

namespace App\Controllers\Reports\TicketDiscussions;

use App\Core\Controller;

class TicketDiscussionsController extends Controller
{
    private $discussionModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'quality_control'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->discussionModel = $this->model('reports/TicketDiscussions/TicketDiscussionsReport');
    }

    public function index()
    {
        $filters = [
            'ticket_id' => $_GET['ticket_id'] ?? '',
            'opened_by' => $_GET['opened_by'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        $reportData = $this->discussionModel->getDiscussions($filters);

        $data = [
            'title' => 'تقرير مناقشات التذاكر',
            'discussions' => $reportData['discussions'],
            'users' => $reportData['users'],
            'filters' => $filters
        ];

        $this->view('reports/TicketDiscussions/index', $data);
    }
} 