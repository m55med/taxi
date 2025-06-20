<?php

namespace App\Core;

use App\Core\Auth;
use App\Models\Admin\Permission;

class Controller
{
    public function __construct()
    {
        // Constructor is now empty, enforcement is done in App.php
    }

    protected function model($model, $data = [])
    {
        // Convert file path to class name, ensuring PascalCase for namespaces
        $parts = explode('/', $model);
        $classNameParts = array_map(function($part) {
            // Converts snake_case and kebab-case to PascalCase
            return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $part)));
        }, $parts);
        $className = 'App\\Models\\' . implode('\\', $classNameParts);
        
        if (class_exists($className, true)) {
            return new $className($data);
        }

        // Fallback for debugging, should not be reached with a correct autoloader setup
        error_log("Model class not found by autoloader: " . $className);
        return null;
    }

    protected function view($view, $data = [])
    {
        $viewPath = '../app/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            die('View does not exist');
        }
    }

    protected function sendJsonResponse($data, $statusCode = 200)
    {
        // Clean any previous output buffer to prevent corrupting the JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        // Ensure Arabic characters are encoded correctly
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

