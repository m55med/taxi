<?php
// public/telegram/index.php
// This file is the entry point for the Telegram Webhook.

// Set a default timezone
date_default_timezone_set('UTC');

// Load Composer's autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
} catch (\Throwable $th) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    error_log("FATAL: Error loading .env file: " . $th->getMessage());
    echo "❌ Server configuration error.";
    exit;
}

// Handle browser access (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/plain; charset=utf-8');
    $token_status = (isset($_ENV['TELEGRAM_BOT_TOKEN']) && !empty($_ENV['TELEGRAM_BOT_TOKEN']))
        ? "🟢 TELEGRAM_BOT_TOKEN is configured."
        : "🔴 TELEGRAM_BOT_TOKEN is NOT configured in .env!";
    echo "✅ Telegram Webhook entry point is active.\n" . $token_status;
    exit;
}

// Handle webhook calls (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Instantiate the controller and handle the request
    $controller = new \App\Controllers\Telegram\WebhookController();
    $controller->handle();
} else {
    // Respond to other methods (e.g., PUT, DELETE) with an error
    http_response_code(405); // Method Not Allowed
    echo "Method Not Allowed";
}
