<?php
// Set error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define application root directory
define('APPROOT', dirname(__DIR__));

// Autoload vendor libraries
require_once APPROOT . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(APPROOT);
$dotenv->load();

// Define Base Path for URLs dynamically to support proxies like ngrok
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_dir = rtrim(str_replace('/public', '', $script_path), '/');
define('BASE_PATH', $protocol . $host . $base_dir);

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Helpers
require_once '../app/helpers/url_helper.php';

// Initialize and run the application
$app = new \App\Core\App();

