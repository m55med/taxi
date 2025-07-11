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
if (getenv('APP_DEBUG') === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Set up global exception handler
set_exception_handler(function($exception) {
    // Log the exception
    error_log($exception);

    // Show detailed errors in development, generic in production
    if (getenv('APP_DEBUG') === 'true') {
        // You can create a more detailed error view if you want
        http_response_code(500);
        echo "<h1>Fatal Error</h1>";
        echo "<pre>";
        echo "<strong>Message:</strong> " . $exception->getMessage() . "\n\n";
        echo "<strong>Stack Trace:</strong>\n" . $exception->getTraceAsString();
        echo "</pre>";
    } else {
        // On production, show a friendly error page.
        // We'll create this view file next.
        http_response_code(500);
        if (file_exists(dirname(__DIR__) . '/app/views/errors/500.php')) {
            require dirname(__DIR__) . '/app/views/errors/500.php';
        } else {
            echo "<h1>An unexpected error occurred.</h1><p>We are working to fix the problem. Please try again later.</p>";
        }
    }
});

// Define application root directory
define('APPROOT', dirname(__DIR__) . '/app');


// Define Base Path for URLs dynamically to support proxies like ngrok
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_dir = rtrim(str_replace('/public', '', $script_path), '/');
define('BASE_PATH', $protocol . $host . $base_dir);
define('URLROOT', BASE_PATH);

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

