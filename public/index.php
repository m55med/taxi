<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define application root directory
define('APPROOT', dirname(__DIR__) . '/app');

// Autoload vendor libraries
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

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

