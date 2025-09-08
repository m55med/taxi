<?php
namespace App\Controllers\Reports\Notifications;

use App\Core\Controller;

class NotificationsController extends Controller
{
    private $reportModel;

    public function __construct()
    {
        $this->authorize('admin');
        $this->reportModel = $this->model('Reports/Notifications/NotificationsReportModel');
    }

    public function index()
    {
        $summary = $this->reportModel->getSummary();
        $readers = [];
        $page_title = 'Notifications Report';
        $selected_notification_id = filter_input(INPUT_GET, 'notification_id', FILTER_VALIDATE_INT);

        if ($selected_notification_id) {
            $readers = $this->reportModel->getReadersForNotification($selected_notification_id);
            $notificationTitle = $this->reportModel->getNotificationTitle($selected_notification_id);
            $page_title = 'Readers for: ' . htmlspecialchars($notificationTitle);
        }

        $this->view('reports/Notifications/index', [
            'page_main_title' => $page_title,
            'summary' => $summary,
            'readers' => $readers,
            'selected_notification' => $selected_notification_id
        ]);
    }
} 