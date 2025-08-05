<?php

namespace App\Controllers\Reports\Coupons;

use App\Core\Controller;
use App\Core\Auth;

class CouponsController extends Controller
{
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        if (!in_array($_SESSION['role_name'], ['admin', 'developer', 'quality_manager'])) {
            $_SESSION['error'] = 'You are not authorized to access this page.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->couponModel = $this->model('Reports/Coupons/CouponsReport');
    }

    public function index()
    {
        // Pagination settings
        $records_per_page = 25;
        $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $records_per_page;

        // Sanitize and prepare filters
        $filters = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING),
            'code' => filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING)
        ];

        // Fetch data from the model
        $stats = $this->couponModel->getCouponStats();
        $total_coupons = $this->couponModel->getTotalCouponsCount($filters);
        $coupons = $this->couponModel->getCouponsDetails($filters, $records_per_page, $offset);

        $total_pages = ceil($total_coupons / $records_per_page);

        $data = [
            'title' => 'Coupons Report',
            'stats' => $stats,
            'coupons' => $coupons,
            'filters' => $filters,
            'current_page' => $current_page,
            'total_pages' => $total_pages
        ];

        $this->view('reports/Coupons/index', $data);
    }
}