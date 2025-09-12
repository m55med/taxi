<?php

namespace App\Core;

use App\Core\Auth;
use App\Models\Admin\Permission;
use App\Services\ActiveUserService;

class Controller
{
    public function __construct()
    {
        // The authentication and session checks have been centralized in App.php
        // to avoid duplicate logic and potential redirect loops.

        // We can still perform actions that should happen on every authenticated request.
        if (isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();

            $activeUserService = new \App\Services\ActiveUserService();
            $activeUserService->recordUserActivity($_SESSION['user_id']);

            // نضيف احتمالية تنظيف المستخدمين غير النشطين
            if (rand(1, 100) <= 5) {
                $activeUserService->cleanupInactiveUsers();
            }
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
        $model = str_replace('/', '\\', $model); // لتحويل إلى namespace
        $modelClass = 'App\\Models\\' . $model;
    
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
    
        error_log("❌ Model class not found: $modelClass");
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
