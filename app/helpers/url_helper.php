<?php

// Simple URL redirect function
if (!function_exists('redirect')) {
    function redirect($location) {
        header("Location: " . rtrim(BASE_URL, '/') . '/' . ltrim($location, '/'));
        exit();
    }
}
if (!function_exists('redirectIfGuest')) {
    function redirectIfGuest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $excluded = ['/login', '/register', '/auth/login', '/auth/register'];

        foreach ($excluded as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return; // Do not redirect
            }
        }

        if (!isset($_SESSION['user'])) {
            redirect('login');
        }
    }
}
