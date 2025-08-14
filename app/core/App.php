<?php

namespace App\Core;

use App\Models\User\User;
use App\Core\Router;

class App
{
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public function __construct()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // نتحقق من المسار الحالي
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($requestUri, PHP_URL_PATH);

    $excludedPrefixes = ['/login', '/register', '/auth/login', '/auth/register'];

// التحقق هل الرابط الحالي يبدأ بأي من الروابط المستثناة
$isExcluded = false;
foreach ($excludedPrefixes as $excluded) {
    if (strpos($path, $excluded) === 0) {
        $isExcluded = true;
        break;
    }
}

if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) &&
    !$isExcluded
) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login?reason=timeout');
    exit;
}



    // تحديث وقت النشاط الحالي
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }

    // تحديث الصلاحيات وتسجيل الحالة
    if (isset($_SESSION['user_id'])) {
        $this->checkAndHandleForceLogout($_SESSION['user_id']);
        $this->checkAndRefreshPermissions($_SESSION['user_id']);

        $userModel = new User();
        $userModel->updateOnlineStatus($_SESSION['user_id'], 1); // Set as online
    }

    // تشغيل الراوتر
    $url = self::parseUrl();
    $uri = empty($url) ? '' : implode('/', $url);

    try {
        $router = Router::load('../app/routes/web.php');
        $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<pre style='color: red; background: #f9f9f9; padding: 10px;'>";
        echo $e->getMessage() . "\n\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
        echo "</pre>";
        exit;
    }
}


    /**
     * Checks for a force-logout signal and handles it.
     * @param int $userId
     */
    private function checkAndHandleForceLogout(int $userId)
    {
        $forceLogoutFile = APPROOT . '/database/force_logout/' . $userId;
        if (file_exists($forceLogoutFile)) {
            $userModel = new User();
            $logoutMessage = trim(file_get_contents($forceLogoutFile));
            unlink($forceLogoutFile);
            $userModel->logout($userId); // This line is important, it should not be removed.

            // Clean up session and redirect.
            session_unset();
            session_destroy();
            
            // A new session must be started to store the flash message for the next request.
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['error'] = 'You have been logged out by an administrator' . (!empty($logoutMessage) && $logoutMessage !== '1' ? ': ' . htmlspecialchars($logoutMessage) : '.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Checks for a permission-refresh signal and updates the session.
     * @param int $userId The ID of the user to notify.
     */
    private function checkAndRefreshPermissions(int $userId)
    {
        $refreshDir = APPROOT . '/cache/refresh_permissions/';
        // Ensure the directory exists before checking for the file
        if (!is_dir($refreshDir)) {
            // Attempt to create it if it doesn't exist
            if (!mkdir($refreshDir, 0777, true) && !is_dir($refreshDir)) {
                // If creation fails, log the error and skip. This prevents a fatal error.
                error_log("Failed to create refresh_permissions directory: " . $refreshDir);
                return;
            }
        }

        $refreshFile = $refreshDir . $userId;
        if (file_exists($refreshFile)) {
            $userModel = new \App\Models\User\User();
            $_SESSION['user']['permissions'] = $userModel->getUserPermissions($userId);

            unlink($refreshFile);
        }
    }

    public static function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
