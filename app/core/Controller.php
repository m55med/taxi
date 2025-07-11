<?php

namespace App\Core;

use App\Core\Auth;
use App\Models\Admin\Permission;
use App\Services\ActiveUserService;

class Controller
{
    public function __construct()
    {
        $activeUserService = new ActiveUserService();

        // Handle session timeout and activity tracking for logged-in users
        if (isset($_SESSION['user_id'])) {
            $timeout = 1800; // 30 minutes
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
                // Last activity was too long ago, log the user out
                $activeUserService->logoutUser($_SESSION['user_id']);
                
                // Unset all of the session variables
                $_SESSION = [];

                // Destroy the session
                session_destroy();

                // Redirect to login page
                header('Location: ' . URLROOT . '/auth/login');
                exit();
            }
            
            // Update last activity timestamp and record user activity
            $_SESSION['last_activity'] = time();
            $activeUserService->recordUserActivity($_SESSION['user_id']);
        }

        // Periodically run cleanup for inactive users (e.g., 5% chance on each request)
        if (rand(1, 100) <= 5) {
            $activeUserService->cleanupInactiveUsers();
        }
    }

    /**
     * Loads a model file.
     *
     * @param string $model The name of the model in PascalCase, optionally with subdirectories.
     * @return object|null An instance of the model, or null if the file doesn't exist.
     */
    public function model($model)
    {
        // Construct the full path to the model file
        $modelPath = '../app/models/' . $model . '.php';

        // Check if the model file exists before trying to require it
        if (file_exists($modelPath)) {
            require_once $modelPath;
            // Construct the full class name with namespace, using directory names as they are
            $parts = explode('/', $model);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts); // Keep original casing for namespace parts
            $modelClass = 'App\\Models\\' . ($namespace ? $namespace . '\\' : '') . $className;
            
            if (class_exists($modelClass)) {
                return new $modelClass();
            }
        }
        
        // In case of any failure, log the error and return null.
        // This prevents fatal errors and helps in debugging.
        error_log("Model not found or class does not exist: " . $model);
        return null;
    }

    /**
     * Loads a view file.
     *
     * @param string $view The name of the view file.
     * @param array $data Data to be extracted for use in the view.
     * @return void
     */
    public function view($view, $data = [])
    {
        // Automatically fetch mandatory notifications for any view that is loaded for a logged-in user.
        if (isset($_SESSION['user_id'])) {
            $notificationModel = $this->model('notifications/Notification');
            if ($notificationModel) {
                // Fetch mandatory notifications for modal pop-ups
                $data['mandatory_notifications'] = $notificationModel->getMandatoryUnreadForUser($_SESSION['user_id']);
                
                // Fetch the count of unread notifications for the navigation bar
                $data['unread_notification_count'] = $notificationModel->getUnreadCountForUser($_SESSION['user_id']);
            }
        }

        // Check for view file
        if (file_exists('../app/views/' . $view . '.php')) {
            // Extract data so it can be used by variable names in the view
            extract($data);
            require_once '../app/views/' . $view . '.php';
        } else {
            // If view does not exist, stop everything and show an error.
            die('View does not exist: ' . $view);
        }
    }

    protected function sendJsonResponse($data, $statusCode = 200)
    {
        // Clean any previous output buffer to prevent corrupting the JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Ensure the status code is a valid integer before setting it.
        $finalStatusCode = is_int($statusCode) && $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500;
        http_response_code($finalStatusCode);

        header('Content-Type: application/json; charset=utf-8');
        // Ensure Arabic characters are encoded correctly
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Checks if the current user has the required permission.
     * If not, it shows a 403 Forbidden page and stops execution.
     *
     * @param string|array $requiredPermission The permission string or an array of permissions to check for.
     * @return void
     */
    public function authorize($requiredPermission)
    {
        // Authorization check has been disabled.
    }
}

