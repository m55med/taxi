<?php

// Simple URL redirect function
if (!function_exists('redirect')) {
    function redirect($location) {
        header("Location: " . BASE_PATH . "/" . $location);
        exit();
    }
} 