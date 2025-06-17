<?php

namespace App\Controllers\Reports\MyActivity;

use App\Core\Controller;

class MyActivityController extends Controller
{
    private $myActivityModel;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        $this->myActivityModel = $this->model('reports/MyActivity/MyActivityReport');
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $reportData = $this->myActivityModel->getReportData($userId, $filters);

        $data = [
            'title' => 'تقرير نشاطي',
            'summary' => $reportData['summary'],
            'calls' => $reportData['calls'],
            'tickets' => $reportData['tickets'],
            'discussions' => $reportData['discussions'],
            'coupons' => $reportData['coupons'],
            'referral_visits' => $reportData['referral_visits'],
            'filters' => $filters,
            'is_marketer' => $_SESSION['role'] === 'marketer'
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportToExcel($reportData);
            exit;
        }

        $this->view('reports/MyActivity/index', $data);
    }
    
    private function exportToExcel($data)
    {
        // This will be implemented later.
        echo "Exporting data...";
    }
} 