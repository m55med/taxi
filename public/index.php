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

// Define Base Path for URLs
define('BASE_PATH', $_ENV['APP_URL'] ?? 'http://localhost');

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize the application
$app = new \App\Core\App();

