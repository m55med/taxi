<?php

namespace App\Controllers\Reports\TicketCoupons;

use App\Core\Controller;

class TicketCouponsController extends Controller
{
    private $couponModel;

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
        $this->couponModel = $this->model('reports/TicketCoupons/TicketCouponsReport');
    }

    public function index()
    {
        $filters = [
            'coupon_id' => $_GET['coupon_id'] ?? '',
            'ticket_id' => $_GET['ticket_id'] ?? '',
        ];

        $reportData = $this->couponModel->getTicketCoupons($filters);

        $data = [
            'title' => 'تقرير كوبونات التذاكر',
            'ticket_coupons' => $reportData['ticket_coupons'],
            'coupons' => $reportData['coupons'],
            'tickets' => $reportData['tickets'],
            'filters' => $filters
        ];

        $this->view('reports/TicketCoupons/index', $data);
    }
} 