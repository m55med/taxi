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
    $excludedPrefixes = ['/login', '/register', '/auth/login', '/auth/register', '/tickets/edit-logs/'];
// التحقق هل الرابط الحالي يبدأ بأي من الروابط المستثناة

$isExcluded = false;
foreach ($excludedPrefixes as $excluded) {
    if (strpos($path, $excluded) === 0) {
        $isExcluded = true;
        break;
    }
}

if (

    isset($_SESSION['user']) &&

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

    if (isset($_SESSION['user'])) {

        $_SESSION['last_activity'] = time();

    }



    // تحديث الصلاحيات وتسجيل الحالة

    if (isset($_SESSION['user'])) {

        $userId = $_SESSION['user']['id'] ?? 0;
        $this->checkAndHandleForceLogout($userId);
        $this->checkAndRefreshPermissions($userId);

        $userModel = new User();
        $userModel->updateOnlineStatus($userId, 1); // Set as online

    }



    // تشغيل الراوتر

    $url = self::parseUrl();

    $uri = empty($url) ? '' : implode('/', $url);



    try {

        (new Router())->loadRoutes(APPROOT . '/routes/web.php')->dispatch($uri, $_SERVER['REQUEST_METHOD']);

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

            header('Location: ' . BASE_URL . '/login');

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
        // First, check if .htaccess rewrite passed the URL via $_GET['url']
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $url = filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL);
            if (!empty($url)) {
                return explode('/', $url);
            }
        }

        // Handle environments without .htaccess / rewrite rules
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Get the path from REQUEST_URI
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null) {
            $path = '';
        }
        
        // Remove query string if present
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // If document root is the main directory (not public/), we need to handle it differently
        // Check if script is in /public/ directory
        $isInPublic = strpos($scriptName, '/public/') !== false || strpos($scriptName, '/public') === 0;
        
        if ($isInPublic) {
            // Script is in /public/, so remove /public/ from path
            if (strpos($path, '/public/') === 0) {
                $path = substr($path, 7); // Remove '/public/'
            } elseif (strpos($path, '/public') === 0) {
                $path = substr($path, 7); // Remove '/public'
            }
        }
        
        // Remove the script name from the URI
        if ($path && $scriptName) {
            // Remove /public/index.php or /index.php
            if (strpos($path, $scriptName) === 0) {
                $path = substr($path, strlen($scriptName));
            } elseif (strpos($path, '/index.php') !== false) {
                $path = substr($path, strpos($path, '/index.php') + 10);
            } elseif (strpos($path, 'index.php') === 0) {
                $path = substr($path, 10);
            }
        }
        
        // Remove leading slash and clean up
        $path = trim($path ?? '', '/');
        
        // If path is empty or just 'index.php', return empty array
        if (empty($path) || $path === 'index.php') {
            return [];
        }
        
        // Split into array
        $parts = explode('/', $path);
        
        // Filter out empty parts
        $parts = array_filter($parts, function($part) {
            return !empty($part) && $part !== 'index.php';
        });
        
        return array_values($parts);
    }

}
