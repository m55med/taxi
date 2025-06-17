<?php

namespace App\Controllers\Reports\TicketReviews;

use App\Core\Controller;

class TicketReviewsController extends Controller
{
    private $reviewModel;

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
        $this->reviewModel = $this->model('reports/TicketReviews/TicketReviewsReport');
    }

    public function index()
    {
        $filters = [
            'reviewer_id' => $_GET['reviewer_id'] ?? '',
            'agent_id' => $_GET['agent_id'] ?? '',
            'rating_from' => $_GET['rating_from'] ?? '',
            'rating_to' => $_GET['rating_to'] ?? '',
        ];

        $reportData = $this->reviewModel->getReviews($filters);

        $data = [
            'title' => 'تقرير مراجعات التذاكر',
            'reviews' => $reportData['reviews'],
            'reviewers' => $reportData['reviewers'],
            'agents' => $reportData['agents'],
            'filters' => $filters
        ];

        $this->view('reports/TicketReviews/index', $data);
    }
} 