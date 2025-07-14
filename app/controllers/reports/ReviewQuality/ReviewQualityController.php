<?php

namespace App\Controllers\Reports\ReviewQuality;

use App\Core\Controller;

class ReviewQualityController extends Controller
{
    private $qualityModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role_name'], ['admin', 'developer', 'quality_control'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->qualityModel = $this->model('reports/ReviewQuality/ReviewQualityReport');
    }

    public function index()
    {
        $filters = [
            'agent_id' => $_GET['agent_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $summary = $this->qualityModel->getQualitySummary($filters);

        $data = [
            'title' => 'تقرير جودة المراجعات',
            'summary' => $summary,
            'agents' => $this->qualityModel->getAgents(),
            'filters' => $filters
        ];

        $this->view('reports/ReviewQuality/index', $data);
    }
}