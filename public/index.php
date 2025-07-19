<?php

// Set default timezone
date_default_timezone_set('UTC');

// Autoload vendor libraries
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // .env file is not mandatory.
}

// Configure error reporting based on environment
if ($_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// إعدادات عرض الأخطاء حسب APP_DEBUG
if ($_ENV['APP_DEBUG'] === 'true') {
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

define('BASE_PATH', '/');


// Define application root directory
define('APPROOT', dirname(__DIR__) . '/app');

// Define Base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// This gets /taxi/public → we want just /taxi
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$script_dir = rtrim(dirname($script_name), '/');
$base_url = str_replace('/public', '', $script_dir);

// Final BASE_URL: http://localhost/taxi (not http://localhost)
define('BASE_URL', $protocol . $host . $base_url);
define('URLROOT', BASE_URL);


// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define application environment ('development' or 'production')
define('ENVIRONMENT', 'development');

// Load Helpers
require_once APPROOT . '/helpers/url_helper.php';
require_once APPROOT . '/helpers/session_helper.php';
require_once '../app/helpers/view_helper.php';

// Load the main App class definition but don't instantiate it yet
require_once APPROOT . '/core/App.php';

// Load API routes
require_once '../app/routes/api.php';

// Parse the URL statically
$url = App\Core\App::parseUrl();


// Handle API routes first
if (isset($url[0]) && $url[0] === 'api') {
    if (handle_api_routes($url)) {
        exit; // API route was handled
    }
}

// If it's not a handled API route, proceed with the normal web flow
$app = new App\Core\App();

// The App's constructor already handled the routing, so we don't need to call anything else.
// If the API route was not hit, the regular web page route should have been processed by the App's constructor.

