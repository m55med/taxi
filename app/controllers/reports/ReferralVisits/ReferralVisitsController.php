<?php

namespace App\Controllers\Reports\ReferralVisits;

use App\Core\Controller;

class ReferralVisitsController extends Controller
{
    private $visitModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], ['admin', 'developer', 'marketing'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->visitModel = $this->model('reports/ReferralVisits/ReferralVisitsReport');
    }

    public function index()
    {
        $filters = [
            'affiliate_name' => $_GET['affiliate_name'] ?? '',
            'registration_status' => $_GET['registration_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $visits = $this->visitModel->getVisits($filters);

        $data = [
            'title' => 'تقرير زيارات الإحالة',
            'visits' => $visits,
            'filters' => $filters
        ];

        $this->view('reports/ReferralVisits/index', $data);
    }
} 