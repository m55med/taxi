<?php

namespace App\Controllers\TokenManagement;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\TokenManagement\TokenManagement;

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class TokenManagementController extends Controller
{
    private $tokenManagementModel;

    /**
     * Convert UTC datetime to Cairo timezone (12-hour format)
     */
    private function convertToCairoTime($utcDateTime)
    {
        if (empty($utcDateTime)) {
            return $utcDateTime;
        }

        try {
            $utc = new \DateTime($utcDateTime, new \DateTimeZone('UTC'));
            $cairo = new \DateTimeZone('Africa/Cairo');
            $utc->setTimezone($cairo);
            return $utc->format('Y-m-d h:i:s A');
        } catch (\Exception $e) {
            error_log('Error converting datetime to Cairo timezone: ' . $e->getMessage());
            return $utcDateTime;
        }
    }

    /**
     * Convert datetime fields in array or object from UTC to Cairo timezone
     */
    private function convertArrayTimesToCairo(&$array, $fields = ['created_at', 'last_activity'])
    {
        if (!is_array($array) && !is_object($array)) {
            return;
        }

        foreach ($array as $key => &$item) {
            if (is_array($item) || is_object($item)) {
                // Recursive for nested arrays or objects
                $this->convertArrayTimesToCairo($item, $fields);
            } elseif (in_array($key, $fields) && !empty($item)) {
                $item = $this->convertToCairoTime($item);
            }
        }
    }

    public function __construct()
    {
        parent::__construct();

        // التحقق من تسجيل الدخول
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }

        // التحقق من صلاحية الأدمن فقط
        if (!Auth::hasRole('admin') && !Auth::hasRole('developer')) {
            $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
            redirect('dashboard');
        }

        $this->tokenManagementModel = new TokenManagement();
    }

    /**
     * عرض صفحة إدارة التوكنات
     */
    public function index()
    {
        // Update token activity on every page access
        \App\Core\Auth::updateCurrentUserTokenActivity();

        $filters = [];

        // معالجة الفلاتر من GET parameters
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int)$_GET['user_id'];
        }

        if (!empty($_GET['team_id'])) {
            $filters['team_id'] = (int)$_GET['team_id'];
        }

        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }

        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        // جلب البيانات
        $tokens = $this->tokenManagementModel->getAllTokens($filters);
        $usersList = $this->tokenManagementModel->getUsersList();
        $teamsList = $this->tokenManagementModel->getTeamsList();
        $stats = $this->tokenManagementModel->getTokenStats($filters);

        // تحويل التوقيت لتوقيت القاهرة
        $this->convertArrayTimesToCairo($tokens, ['created_at', 'last_activity']);

        $data = [
            'page_main_title' => 'Token Management',
            'tokens' => $tokens,
            'users_list' => $usersList,
            'teams_list' => $teamsList,
            'stats' => $stats,
            'filters' => [
                'user_id' => $filters['user_id'] ?? '',
                'team_id' => $filters['team_id'] ?? '',
                'status' => $filters['status'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? ''
            ]
        ];

        $this->view('token-management/index', $data);
    }

    /**
     * إلغاء توكن (AJAX)
     */
    public function revoke($tokenId)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $result = $this->tokenManagementModel->revokeToken($tokenId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Token revoked successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to revoke token']);
        }
    }

    /**
     * حذف توكن نهائياً (AJAX)
     */
    public function delete($tokenId)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $result = $this->tokenManagementModel->deleteToken($tokenId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Token deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete token']);
        }
    }

    /**
     * تصدير التوكنات إلى CSV
     */
    public function export()
    {
        $filters = [];

        // معالجة الفلاتر من GET parameters
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int)$_GET['user_id'];
        }

        if (!empty($_GET['team_id'])) {
            $filters['team_id'] = (int)$_GET['team_id'];
        }

        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }

        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $tokens = $this->tokenManagementModel->getAllTokens($filters);

        // تحويل التوقيت لتوقيت القاهرة
        $this->convertArrayTimesToCairo($tokens, ['created_at', 'last_activity']);

        // إعداد headers للـ CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tokens_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');

        // كتابة BOM للدعم العربي
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // كتابة headers
        fputcsv($output, [
            'ID',
            'Token',
            'User ID',
            'User Name',
            'Username',
            'Team',
            'Created At (Cairo)',
            'Last Activity (Cairo)',
            'Expires After (Minutes)',
            'Status'
        ]);

        // كتابة البيانات
        foreach ($tokens as $token) {
            fputcsv($output, [
                $token['id'],
                $token['token'],
                $token['user_id'],
                $token['user_name'],
                $token['user_username'],
                $token['team_name'] ?? 'No Team',
                $token['created_at'],
                $token['last_activity'],
                $token['expires_after_minutes'],
                $token['status']
            ]);
        }

        fclose($output);
        exit;
    }
}
