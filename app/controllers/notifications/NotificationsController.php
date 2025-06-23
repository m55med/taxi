<?php
namespace App\Controllers\Notifications;

use App\Core\Controller;

class NotificationsController extends Controller
{
    private $notificationModel;
    private $reportModel;

    public function __construct()
    {
        // The model is loaded into the controller.
        $this->notificationModel = $this->model('notifications/Notification');
        $this->reportModel = $this->model('reports/Notifications/NotificationsReportModel');
    }

    /**
     * Admin: Displays a list of all created notifications.
     * Accessible only by users with 'admin' role.
     */
    public function index()
    {
        $this->authorize('admin');
        $notifications = $this->notificationModel->getAll();
        $this->view('notifications/index', ['notifications' => $notifications, 'page_main_title' => 'Manage Notifications']);
    }

    /**
     * Admin: Shows the form to create a new notification.
     */
    public function create()
    {
        $this->authorize('admin');
        $this->view('notifications/create', ['page_main_title' => 'Create Notification']);
    }

    /**
     * Admin: Handles the form submission for creating a new notification.
     */
    public function store()
    {
        $this->authorize('admin');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title'] ?? '');
            // Allow basic HTML in message, but sanitize other inputs
            $message = trim($_POST['message'] ?? '');

            if (empty($title) || empty($message)) {
                $_SESSION['error'] = 'Title and message fields cannot be empty.';
                redirect('notifications/create');
                return;
            }

            if ($this->notificationModel->createAndAssign($title, $message)) {
                $_SESSION['success'] = 'Notification has been created and sent to all users.';
                redirect('notifications');
            } else {
                $_SESSION['error'] = 'Failed to create the notification. Please try again.';
                redirect('notifications/create');
            }
        } else {
            redirect('notifications');
        }
    }

    /**
     * API: Fetches mandatory unread notifications for the current user.
     * Used for the popup modal.
     */
    public function getUnread()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse([], 401);
            return;
        }
        $unread = $this->notificationModel->getMandatoryUnreadForUser($_SESSION['user_id']);
        $this->sendJsonResponse($unread);
    }
    
    /**
     * API: Fetches notifications and unread count for the navigation bar bell.
     */
    public function getNavNotifications()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJsonResponse([], 401);
            return;
        }
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getAllForUser($userId, 5); // Get latest 5 for the dropdown
        $unreadCount = $this->notificationModel->getUnreadCountForUser($userId);
        
        $this->sendJsonResponse([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * API: Marks a specific notification as read.
     */
    public function markRead()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized Access'], 401);
            return;
        }

        $notificationId = $_POST['notification_id'] ?? null;
        if (!$notificationId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Notification ID is required.'], 400);
            return;
        }

        if ($this->notificationModel->markAsRead($_SESSION['user_id'], $notificationId)) {
            $this->sendJsonResponse(['success' => true]);
        } else {
            // This is not a critical error if it was already read, so we send success.
            $this->sendJsonResponse(['success' => true, 'message' => 'Already marked as read or failed to update.']);
        }
    }
    
    /**
     * User-facing page to view a complete history of all notifications.
     */
    public function history() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        // Get all notifications for the user (e.g., up to 100)
        $allNotifications = $this->notificationModel->getAllForUser($_SESSION['user_id'], 100); 
        $this->view('notifications/history', ['notifications' => $allNotifications, 'page_main_title' => 'Notification History']);
    }

    /**
     * Admin: Shows a list of users who have read a specific notification.
     */
    public function readers($id = 0)
    {
        $this->authorize('admin');
        $id = (int)$id;
        if ($id === 0) {
            redirect('notifications');
            return;
        }
    
        $readers = $this->reportModel->getReadersForNotification($id);
        $notificationTitle = $this->reportModel->getNotificationTitle($id);
    
        $data = [
            'page_main_title' => 'Readers for: ' . htmlspecialchars($notificationTitle),
            'readers' => $readers,
            'notification_id' => $id
        ];
    
        $this->view('notifications/readers', $data);
    }
} 