<?php

namespace App\Controllers\Reports\EmployeeActivityScore;

use App\Core\Controller;

class EmployeeActivityScoreController extends Controller
{
    private $scoreModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'team_leader'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->scoreModel = $this->model('reports/EmployeeActivityScore/EmployeeActivityScoreReport');
    }

    public function index()
    {
        $filters = [
            'team_id' => $_GET['team_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $scores = $this->scoreModel->getActivityScores($filters);
        
        $data = [
            'title' => 'تقرير نقاط نشاط الموظفين',
            'scores' => $scores,
            'teams' => $this->scoreModel->getTeams(),
            'filters' => $filters
        ];

        $this->view('reports/EmployeeActivityScore/index', $data);
    }
} 