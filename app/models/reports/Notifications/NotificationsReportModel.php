<?php
namespace App\Models\Reports\Notifications;

use App\Core\Model;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class NotificationsReportModel extends Model {

    /**
     * Gets a summary of all notifications, including counts of sent and read.
     */
    public function getSummary() {
        $sql = "SELECT 
                    n.id, 
                    n.title,
                    n.created_at,
                    (SELECT COUNT(*) FROM user_notifications un WHERE un.notification_id = n.id) as total_sent,
                    (SELECT COUNT(*) FROM user_notifications un WHERE un.notification_id = n.id AND un.is_read = 1) as total_read
                FROM notifications n
                ORDER BY n.created_at DESC";
        $this->query($sql);
        $this->execute();
        return $this->resultSet();
    }

    /**
     * Gets a list of all users who have read a specific notification.
     */
    public function getReadersForNotification($notificationId) {
        $sql = "SELECT 
                    u.id,
                    u.username,
                    un.read_at
                FROM users u
                JOIN user_notifications un ON u.id = un.user_id
                WHERE un.notification_id = :notification_id AND un.is_read = 1
                ORDER BY un.read_at ASC";
        $this->query($sql);
        $this->bind(':notification_id', $notificationId);
        $this->execute();
        return $this->resultSet();
    }

    /**
     * Gets the title of a notification by its ID.
     */
    public function getNotificationTitle($notificationId) {
        $sql = "SELECT title FROM notifications WHERE id = :id";
        $this->query($sql);
        $this->bind(':id', $notificationId);
        $this->execute();
        $result = $this->single();
        return $result ? $result['title'] : 'Unknown Notification';
    }
} 