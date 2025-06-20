<?php

namespace App\Controllers\Calls;

use App\Core\Controller;
use App\Core\Database;

class BaseCallController extends Controller
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            // For API requests (like form submissions), send a JSON error
            // instead of redirecting.
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-Type: application/json');
                http_response_code(401); // Unauthorized
                echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.', 'redirect' => BASE_PATH . '/auth/login']);
                exit();
            } else {
                // For regular page loads, redirect to the login page.
                $_SESSION['error'] = 'يجب تسجيل الدخول أولاً';
                header('Location: ' . BASE_PATH . '/auth/login');
                exit();
            }
        }
    }

    protected function checkRateLimit()
    {
        if (!isset($_SESSION['call_timestamps'])) {
            $_SESSION['call_timestamps'] = [];
        }

        $limit = 50; // 10 مكالمات
        $window = 60; // خلال 60 ثانية
        $currentTime = time();

        // تصفية الطوابع الزمنية التي تجاوزت المدة المحددة
        $_SESSION['call_timestamps'] = array_filter(
            $_SESSION['call_timestamps'],
            fn($ts) => ($currentTime - $ts) < $window
        );

        // التحقق من تجاوز الحد
        if (count($_SESSION['call_timestamps']) >= $limit) {
            $oldestTimestamp = min($_SESSION['call_timestamps']);
            return ($oldestTimestamp + $window) - $currentTime;
        }

        // تسجيل طابع زمني جديد
        $_SESSION['call_timestamps'][] = $currentTime;
        return 0;
    }

    protected function getNewDriverStatus($callStatus)
    {
        $statusMap = [
            'no_answer' => 'pending',
            'busy' => 'pending',
            'not_available' => 'pending',
            'wrong_number' => 'blocked',
            'rescheduled' => 'rescheduled',
            'answered' => 'waiting_chat'
        ];

        return isset($statusMap[$callStatus]) ? $statusMap[$callStatus] : null;
    }

    protected function sendJsonResponse($data, $statusCode = 200)
    {
        // Clean any previously buffered output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set headers
        header_remove('Set-Cookie');
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        // Output the JSON
        echo json_encode($data);
        
        // Terminate script execution
        exit();
    }
} 