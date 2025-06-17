<?php

namespace App\Controllers\Reports\Referrals;

use App\Core\Controller;
use App\Core\Auth;

class ReferralsController extends Controller
{
    private $referralModel;

    public function __construct()
    {
        parent::__construct();
        Auth::check();
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
        $this->referralModel = $this->model('reports/Referrals/ReferralsReport');
    }

    public function index()
    {
        $referrals = $this->referralModel->getReferrals();

        $data = [
            'title' => 'تقرير الإحالات',
            'referrals' => $referrals
        ];

        $this->view('reports/Referrals/index', $data);
    }
} 