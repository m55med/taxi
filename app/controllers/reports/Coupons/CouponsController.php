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
        Auth::check();
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->couponModel = $this->model('reports/Coupons/CouponsReport');
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'is_active' => $_GET['is_active'] ?? '',
        ];

        $coupons = $this->couponModel->getCoupons($filters);

        $data = [
            'title' => 'تقرير الكوبونات',
            'coupons' => $coupons,
            'filters' => $filters
        ];

        $this->view('reports/Coupons/index', $data);
    }
} 