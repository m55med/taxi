<?php

namespace App\Controllers\Reports\ReferralRegistrations;

use App\Core\Controller;
use App\Core\Auth;

class ReferralRegistrationsController extends Controller
{
    private $registrationModel;

    public function __construct()
    {
        parent::__construct();
        Auth::check();
        if (!in_array($_SESSION['role_name'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->registrationModel = $this->model('reports/ReferralRegistrations/ReferralRegistrationsReport');
    }

    public function index()
    {
        $filters = [
            'referrer_id' => $_GET['referrer_id'] ?? null
        ];

        $reportData = $this->registrationModel->getRegistrations($filters);

        $data = [
            'title' => 'تقرير تسجيلات الإحالة',
            'registrations' => $reportData['registrations'],
            'referrers' => $reportData['referrers'],
            'filters' => $filters
        ];

        $this->view('reports/ReferralRegistrations/index', $data);
    }
}