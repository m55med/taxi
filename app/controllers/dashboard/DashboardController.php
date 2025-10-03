<?php

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User\User;
use App\Models\Dashboard\Dashboard;

class DashboardController extends Controller
{
    private $userModel;
    private $dashboardModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->userModel = $this->model('User/User');
        $this->dashboardModel = $this->model('Dashboard/Dashboard');
        parent::__construct();
    }

    public function index()
    {
        $userId = Auth::getUserId();
        if (!$userId) {
            redirect('auth/logout');
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            redirect('auth/logout');
            return;
        }

        // Get date range from GET parameters, with defaults (1st day -> last day of current month)
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // أول يوم في الشهر
        $dateTo   = $_GET['date_to']   ?? date('Y-m-t');  // آخر يوم في الشهر

        // ======= تحويل التواريخ من Cairo إلى UTC قبل الاستعلام =======
        // الحل الأمثل: احفظ التاريخ الأصلي للمقارنة في قاعدة البيانات
        if (!empty($dateFrom)) {
            // احفظ التاريخ الأصلي الذي أرسله المستخدم
            $originalDateFrom = $dateFrom;

            // للتوافق مع باقي النظام، احتفظ بالتحويل إلى UTC
            $dateFromCairo = new \DateTimeImmutable($dateFrom . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
            $dateFrom = $dateFromCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }

        if (!empty($dateTo)) {
            $originalDateTo = $dateTo;
            $dateToCairo = new \DateTimeImmutable($dateTo . ' 23:59:59', new \DateTimeZone('Africa/Cairo'));
            $dateTo = $dateToCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }

        $userDataForModel = [
            'id' => $user->id,
            'role_name' => $user->role_name
        ];

        // Fetch all dashboard data using the centralized model method
        $dashboardData = $this->dashboardModel->getDashboardData($userDataForModel, $dateFrom, $dateTo, $originalDateFrom ?? null, $originalDateTo ?? null);

        // أعد التواريخ الأصلية للـ view (للروابط)
        $originalDateFrom = $originalDateFrom ?? $dateFrom;
        $originalDateTo = $originalDateTo ?? $dateTo;

        // Pass everything to the view
        $data = [
            'title' => 'Dashboard',
            'dashboardData' => $dashboardData,
            'date_from' => $originalDateFrom,
            'date_to' => $originalDateTo,
        ];

        $this->view('dashboard/index', $data);
    }

}
