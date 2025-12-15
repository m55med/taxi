<?php

// Set default timezone
date_default_timezone_set('UTC');

// Autoload vendor libraries
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    $dotenv->required(['TRENGO_API_TOKEN']);
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // .env file is not mandatory.
}

// Configure error reporting based on environment
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// تسجيل وعرض الأخطاء بشكل مناسب
set_exception_handler(function ($exception) {
    error_log($exception->getMessage());
    http_response_code(500);

    $debug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';

    $data = [
        'showDetails' => $debug,
        'exception' => $exception
    ];

    $errorViewPath = dirname(__DIR__) . '/app/views/errors/500.php';
    if (file_exists($errorViewPath)) {
        require $errorViewPath;
    } else {
        echo "<h1>⚠️ حصل خطأ غير متوقع</h1><p>نحن نعمل على إصلاح المشكلة، شكرًا لصبرك.</p>";
    }
});

// Define application root directory
define('APPROOT', dirname(__DIR__) . '/app');

// 🔧 URLROOT and BASE_URL definitions (Corrected)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
// More reliable way to get the base path, trims /public from the end if it exists
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = preg_replace('/\/public$/', '', $base_url);


define('URLROOT', $protocol . $host . $base_url);
define('BASE_URL', URLROOT);

// Debug output to browser console (optional)
if (
    !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') &&
    stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') === false
) {
    // echo "<script>console.log('🔗 URLROOT = " . URLROOT . "');</script>";
}

// Configure session cookie parameters for security
// This must be called before session_start()
session_set_cookie_params([
    'lifetime' => 0, // Until browser closes
    'path' => '/',
    'domain' => '', // Use default domain
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // Enable on HTTPS
    'httponly' => true, // Prevent JavaScript access (XSS protection)
    'samesite' => 'Lax' // CSRF protection
]);

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define application environment
define('ENVIRONMENT', 'development');

// Load Helpers
require_once APPROOT . '/helpers/url_helper.php';
require_once APPROOT . '/helpers/session_helper.php';
require_once APPROOT . '/helpers/view_helper.php';

// Load the main App class definition
require_once APPROOT . '/Core/App.php';

// Load API routes
require_once APPROOT . '/routes/api.php';

// Parse the URL statically
$url = App\Core\App::parseUrl();

// Handle health check endpoint
if (isset($url[0]) && $url[0] === 'health' && empty($url[1])) {
    require_once APPROOT . '/Controllers/Api/ApiController.php';
    $healthController = new App\Controllers\Api\ApiController();
    $healthController->health();
    exit;
}

// Handle API routes first
if (isset($url[0]) && $url[0] === 'api') {
    if (handle_api_routes($url)) {
        exit; // API route was handled
    }
}

// Handle normal web request
$app = new App\Core\App();
