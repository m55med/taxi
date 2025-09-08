<?php
namespace App\Models\Notifications;

use App\Core\Model;
use PDO;

class Notification extends Model {
    
    public function __construct(){
        parent::__construct();
    }

    /**
     * Creates a new notification in the 'notifications' table,
     * then assigns it to every user in the 'user_notifications' table.
     * This is done in a transaction to ensure data integrity.
     */
    public function createAndAssign($title, $message) {
        $this->beginTransaction();
        try {
            // 1. Create the base notification
            $this->query("INSERT INTO notifications (title, message) VALUES (:title, :message)");
            $this->bind(':title', $title);
            $this->bind(':message', $message);
            $this->execute();
            $notificationId = $this->lastInsertId();

            // 2. Get all user IDs to send the notification to
            $this->query("SELECT id FROM users");
            $users = $this->resultSet();

            // 3. Prepare and execute batch insert for user notifications
            if ($users) {
                $this->query("INSERT INTO user_notifications (user_id, notification_id) VALUES (:user_id, :notification_id)");
                foreach ($users as $user) {
                    $this->bind(':user_id', $user['id']);
                    $this->bind(':notification_id', $notificationId);
                    $this->execute();
                }
            }
            
            $this->commit();
            return $notificationId;
        } catch (\Exception $e) {
            $this->rollBack();
            error_log('Notification Creation Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a notification and assigns it to a single user.
     * IMPORTANT: This method does NOT manage its own transaction,
     * it assumes a transaction has been started by the calling method.
     */
    public function createForUser($title, $message, $userId, $link = null) {
        try {
            // Step 1: Insert the main notification record using a direct, stateless PDO call.
            // The 'link' column is removed from the query to match the user's actual database schema.
            $sql1 = "INSERT INTO notifications (title, message) VALUES (:title, :message)";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([
                ':title' => $title,
                ':message' => $message
            ]);
            $notificationId = $this->db->lastInsertId();

            if (!$notificationId) {
                throw new \Exception("Failed to create notification, no ID was generated.");
            }

            // Step 2: Link the notification to the user using a separate, direct PDO call.
            $sql2 = "INSERT INTO user_notifications (user_id, notification_id) VALUES (:user_id, :notification_id)";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([
                ':user_id'         => $userId,
                ':notification_id' => $notificationId,
            ]);

            return $notificationId;
        } catch (\Exception $e) {
            error_log('Notification Creation Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all notifications and aggregates read/sent counts for the admin view.
     */
    public function getAll() {
        $sql = "SELECT 
                    n.*, 
                    (SELECT COUNT(*) FROM user_notifications un WHERE un.notification_id = n.id) as total_recipients, 
                    (SELECT COUNT(*) FROM user_notifications un WHERE un.notification_id = n.id AND un.is_read = 1) as total_read 
                FROM notifications n 
                ORDER BY n.created_at DESC";
        $this->query($sql);
        $this->execute();
        return $this->resultSet();
    }

    /**
     * Gets all unread notifications for a user. These are considered "mandatory"
     * and will be shown in the popup modal.
     */
    public function getMandatoryUnreadForUser($userId) {
        $sql = "SELECT n.id, n.title, n.message, n.created_at 
                FROM notifications n 
                JOIN user_notifications un ON n.id = un.notification_id 
                WHERE un.user_id = :user_id AND un.is_read = 0 
                ORDER BY n.created_at ASC";
        $this->query($sql);
        $this->bind(':user_id', $userId);
        $this->execute();
        return $this->resultSet();
    }
    
    /**
     * Gets all notifications for a given user, used for the history page and nav dropdown.
     */
    public function getAllForUser($userId, $limit = 25) {
        // Cast limit to an integer to ensure it's a number and prevent SQL injection.
        $limit = (int)$limit;

        $sql = "SELECT n.id, n.title, n.message, n.created_at, un.is_read, un.read_at 
                FROM notifications n 
                JOIN user_notifications un ON n.id = un.notification_id 
                WHERE un.user_id = :user_id 
                ORDER BY n.created_at DESC 
                LIMIT :limit"; // PDO does not reliably support binding LIMIT, so we bind it directly after casting to int.
        
        $this->query($sql);
        $this->bind(':user_id', $userId);
        $this->bind(':limit', $limit, PDO::PARAM_INT); // This should now work with the updated Model class
        $this->execute();
        return $this->resultSet();
    }
    
    /**
     * Gets the count of unread notifications for the nav bell icon.
     */
    public function getUnreadCountForUser($userId) {
        $this->query("SELECT COUNT(*) as unread_count FROM user_notifications WHERE user_id = :user_id AND is_read = 0");
        $this->bind(':user_id', $userId);
        $this->execute();
        $result = $this->single();
        return $result ? (int)$result['unread_count'] : 0;
    }

    /**
     * Marks a specific notification as read for a specific user.
     */
    public function markAsRead($userId, $notificationId) {
        $this->query("UPDATE user_notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = :user_id AND notification_id = :notification_id AND is_read = 0");
        $this->bind(':user_id', $userId);
        $this->bind(':notification_id', $notificationId);
        
        $this->execute();
        return $this->rowCount() > 0;
    }

    /**
     * Gets a single notification for a specific user.
     */
    public function getNotificationForUser($userId, $notificationId) {
        $sql = "SELECT n.id, n.title, n.message, n.created_at, un.is_read, un.read_at 
                FROM notifications n 
                JOIN user_notifications un ON n.id = un.notification_id 
                WHERE un.user_id = :user_id AND un.notification_id = :notification_id";
        $this->query($sql);
        $this->bind(':user_id', $userId);
        $this->bind(':notification_id', $notificationId);
        $this->execute();
        return $this->single();
    }
} 