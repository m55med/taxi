<?php

namespace App\Core;

use App\Models\User\User;
use App\Core\Router;

class App
{
    private const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public function __construct()
    {
        // Session must be started to check for user_id
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle session timeout for inactivity
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['error'] = 'تم تسجيل خروجك تلقائيًا بسبب عدم النشاط.';
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        
        // Update last activity time on each request
        if(isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();
        }

        // Force logout check, permission refresh, and online status update
        if (isset($_SESSION['user_id'])) {
            $this->checkAndHandleForceLogout($_SESSION['user_id']);
            $this->checkAndRefreshPermissions($_SESSION['user_id']);

            $userModel = new User();
            $userModel->updateOnlineStatus($_SESSION['user_id'], 1); // Set as online
        }

        $url = self::parseUrl();

        $uri = empty($url) ? '' : implode('/', $url);
        
        // Load routes and dispatch the request.
        // The router will handle 404s, controller/method calling, and permissions.
        $router = Router::load('../app/routes/web.php');
        $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
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
            $userModel->logout($userId);
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['error'] = 'تم تسجيل خروجك بواسطة مسؤول' . (!empty($logoutMessage) && $logoutMessage !== '1' ? ': ' . htmlspecialchars($logoutMessage) : '.');
            header('Location: ' . BASE_PATH . '/auth/login');
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
            $_SESSION['permissions'] = $userModel->getUserPermissions($userId);
            
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
