<?php

namespace App\Models\Discussion;

use App\Core\Database;
use App\Models\Notifications\Notification;
use PDO;
use PDOException;

class Discussion
{
    private $db;
    private $notificationModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notificationModel = new Notification();
    }

    /**
     * Fetches all discussions relevant to a specific user, with contextual information.
     * - Admins/Developers/QM see all discussions.
     * - Team Leaders see discussions related to their team members.
     * - Agents see discussions on reviews of their work (ticket details/calls).
     */
    public function getDiscussionsForUser($userId, $role)
    {
        $logFile = APPROOT . '/logs/discussion_reply_debug.log';
        $log_entry = "========== [" . date('Y-m-d H:i:s') . "] getDiscussionsForUser Attempt ==========\n";
        $log_entry .= "User ID: $userId, Role: $role\n";

        // Base query to get discussions and join with the user who opened it.
        $sql = "
            SELECT 
                d.id, d.reason, d.notes, d.status, d.created_at,
                d.discussable_type, d.discussable_id,
                opener.id as opener_id,
                opener.username as opener_name,
                
                -- Context-specific fields, populated based on the type of discussion
                COALESCE(t_from_review.ticket_number, t_direct.ticket_number) as ticket_number,
                COALESCE(td.ticket_id, t_direct.id) as ticket_id,
                dr.name as driver_name,
                dc.driver_id as driver_id,
                creator.username as created_by_name -- The agent whose work is being discussed

            FROM discussions d
            JOIN users opener ON d.opened_by = opener.id
            
            -- Join to find the related review, if any
            LEFT JOIN reviews r ON d.discussable_type = 'App\\\\Models\\\\Review\\\\Review' AND d.discussable_id = r.id
            
            -- Join for Ticket-related context
            LEFT JOIN ticket_details td ON r.reviewable_type IN ('ticket_detail', 'App\\\\Models\\\\Tickets\\\\TicketDetail') AND r.reviewable_id = td.id
            LEFT JOIN tickets t_from_review ON td.ticket_id = t_from_review.id
            
            -- Join for Call-related context
            LEFT JOIN driver_calls dc ON r.reviewable_type IN ('driver_call', 'App\\\\Models\\\\Call\\\\DriverCall') AND r.reviewable_id = dc.id
            LEFT JOIN drivers dr ON dc.driver_id = dr.id

            -- Join for direct Ticket discussions
            LEFT JOIN tickets t_direct ON d.discussable_type = 'App\\\\Models\\\\Tickets\\\\Ticket' AND d.discussable_id = t_direct.id

            -- Join to find the agent who did the work (the creator of the reviewed item)
            LEFT JOIN users creator ON creator.id = (
                CASE
                    WHEN td.edited_by IS NOT NULL THEN td.edited_by
                    WHEN dc.call_by IS NOT NULL THEN dc.call_by
                    WHEN t_direct.created_by IS NOT NULL THEN t_direct.created_by
                    ELSE NULL
                END
            )
        ";

        $params = [];
        $conditions = [];

        // Role-based filtering
        if (!in_array($role, ['admin', 'developer', 'quality_manager'])) {
            if ($role === 'Team_leader') {
                // Team leaders see discussions related to their team members' work OR opened by their team members
                $team_members_subquery = "SELECT user_id FROM team_members WHERE team_id IN (SELECT team_id FROM team_members WHERE user_id = :user_id)";
                $conditions[] = "creator.id IN ($team_members_subquery) OR d.opened_by IN ($team_members_subquery)";
                $params[':user_id'] = $userId;
            } else { // Agent or any other non-admin role
                // Agents see discussions about their work OR discussions they opened themselves.
                $conditions[] = "creator.id = :creator_id OR d.opened_by = :opener_id";
                $params[':creator_id'] = $userId;
                $params[':opener_id'] = $userId;
            }
        }
        // Admins, QMs, Devs see everything, so no conditions are added.

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" OR ", $conditions);
        }

        $sql .= " ORDER BY d.status ASC, d.created_at DESC";

        $log_entry .= "Final SQL Query:\n" . $sql . "\n";
        $log_entry .= "Parameters: " . json_encode($params) . "\n";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $log_entry .= "Query executed successfully. Number of discussions found: " . count($discussions) . "\n";

            // For each discussion, fetch its replies
            foreach ($discussions as $key => $discussion) {
                $discussions[$key]['replies'] = $this->getReplies($discussion['id']);
            }

            file_put_contents($logFile, $log_entry . "========== End of Attempt ==========\n\n", FILE_APPEND);
            return $discussions;

        } catch (PDOException $e) {
            $log_entry .= "!!!!!!!!!! EXCEPTION CAUGHT !!!!!!!!!!\n";
            $log_entry .= "Exception Message: " . $e->getMessage() . "\n";
            error_log("Error in getDiscussionsForUser: " . $e->getMessage());
            file_put_contents($logFile, $log_entry . "========== End of Attempt (FAILED) ==========\n\n", FILE_APPEND);
            return [];
        }
    }

    public function getDiscussions($discussable_type, $discussable_id)
    {
        $sql = "SELECT d.*, u.username as opener_name 
                FROM discussions d
                JOIN users u ON d.opened_by = u.id
                WHERE d.discussable_type = :discussable_type 
                AND d.discussable_id = :discussable_id
                ORDER BY d.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':discussable_type' => $discussable_type,
                ':discussable_id' => $discussable_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDiscussions: " . $e->getMessage());
            return [];
        }
    }

    public function addDiscussion($type, $discussable_id, $userId, $data)
    {
        // Map simple type string to the fully qualified class name
        $typeMap = [
            'review' => 'App\\Models\\Review\\Review',
            'ticket' => 'App\\Models\\Tickets\\Ticket',
            // Add other types here as needed
        ];

        if (!array_key_exists($type, $typeMap)) {
            error_log("Invalid discussable type provided: " . $type);
            return false;
        }
        $discussable_type = $typeMap[$type];

        $sql = "INSERT INTO discussions (discussable_type, discussable_id, opened_by, reason, notes, status)
                VALUES (:discussable_type, :discussable_id, :opened_by, :reason, :notes, 'open')";
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':discussable_type' => $discussable_type,
                ':discussable_id' => $discussable_id,
                ':opened_by' => $userId,
                ':reason' => $data['reason'],
                ':notes' => $data['notes']
            ]);
            $newDiscussionId = $this->db->lastInsertId();

            if (!$newDiscussionId) {
                throw new PDOException("Failed to get last insert ID for new discussion.");
            }

            // --- Notification Logic ---
            $targetUserId = $this->getTargetUserIdForNotification($type, $discussable_id); // Use original simple type
            if ($targetUserId && $targetUserId != $userId) { // Don't notify user about their own action
                $opener = $this->getUserById($userId);
                $title = "New Discussion Opened by " . ($opener['username'] ?? 'System');
                $message = "A new discussion has been opened regarding your work. Reason: " . htmlspecialchars($data['reason']);
                $link = URLROOT . "/discussions#discussion-" . $newDiscussionId;
                $this->notificationModel->createForUser($title, $message, $targetUserId, $link);
            }
            
            $this->db->commit();
            return $newDiscussionId;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in addDiscussion: " . $e->getMessage());
            return false;
        }
    }

    public function addReply($discussionId, $userId, $message)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO discussion_replies (discussion_id, user_id, message) VALUES (:discussion_id, :user_id, :message)";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':discussion_id' => $discussionId,
                ':user_id' => $userId,
                ':message' => $message,
            ]);

            if (!$success) {
                throw new PDOException("Failed to insert the reply.");
            }
            
            // --- Notification Logic ---
            $this->notifyUsersOnNewReply($discussionId, $userId, $message);

            $this->db->commit();

            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in addReply: " . $e->getMessage());
            return false;
        }
    }

    private function notifyUsersOnNewReply($discussionId, $replierId, $message)
    {
        // Get all unique user IDs involved in this discussion (opener and all repliers)
        $sql = "
            (SELECT opened_by as user_id FROM discussions WHERE id = :discussion_id1)
            UNION
            (SELECT user_id FROM discussion_replies WHERE discussion_id = :discussion_id2)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':discussion_id1' => $discussionId, ':discussion_id2' => $discussionId]);
        $participantIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $replier = $this->getUserById($replierId);
        $title = "New Reply in Discussion";
        $body = "User '" . ($replier['username'] ?? 'Unknown') . "' replied: " . htmlspecialchars(substr($message, 0, 50)) . "...";

        foreach ($participantIds as $participantId) {
            // Don't notify the user who just wrote the reply
            if ($participantId != $replierId) {
                $this->notificationModel->createForUser($title, $body, $participantId);
            }
        }
    }

    public function getReplies($discussionId)
    {
        $sql = "SELECT dr.*, u.username 
                FROM discussion_replies dr
                JOIN users u ON dr.user_id = u.id
                WHERE dr.discussion_id = :discussion_id
                ORDER BY dr.created_at ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':discussion_id' => $discussionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getReplies: " . $e->getMessage());
            return [];
        }
    }

    private function getTargetUserIdForNotification($type, $discussable_id)
    {
        if ($type === 'review') {
            $sql = "SELECT r.reviewable_type, r.reviewable_id FROM reviews r WHERE r.id = :review_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':review_id' => $discussable_id]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$review) return null;

            if ($review['reviewable_type'] === 'ticket_detail') {
                $sql = "SELECT edited_by FROM ticket_details WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id' => $review['reviewable_id']]);
                return $stmt->fetchColumn();
            } elseif ($review['reviewable_type'] === 'driver_call') {
                $sql = "SELECT call_by FROM driver_calls WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id' => $review['reviewable_id']]);
                return $stmt->fetchColumn();
            }
        } elseif ($type === 'ticket') {
            $sql = "SELECT created_by FROM tickets WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $discussable_id]);
            return $stmt->fetchColumn();
        }
        
        return null;
    }

    private function getUserById($userId)
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function closeDiscussion($discussionId, $userId, $role)
    {
        // First, get the discussion to check for ownership
        $stmt = $this->db->prepare("SELECT opened_by FROM discussions WHERE id = :id");
        $stmt->execute([':id' => $discussionId]);
        $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$discussion) {
            return false; // Not found
        }

        // Allow closing if user is an admin or the one who opened it
        if ($role === 'admin' || $discussion['opened_by'] == $userId) {
            $update_stmt = $this->db->prepare("UPDATE discussions SET status = 'closed' WHERE id = :id");
            return $update_stmt->execute([':id' => $discussionId]);
        }

        return false; // No permission
    }

    public function getEntityForRedirect($type, $discussable_id)
    {
        if ($type === 'review') {
            // Find what the review belongs to.
            $sql = "SELECT reviewable_type, reviewable_id FROM reviews WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $discussable_id]);
            $review_parent = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($review_parent) {
                $parent_type = $review_parent['reviewable_type'];
                $parent_id = $review_parent['reviewable_id'];

                if ($parent_type === 'ticket_detail') {
                    $sql = "SELECT ticket_id FROM ticket_details WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':id' => $parent_id]);
                    $ticket_id = $stmt->fetchColumn();
                    return ['type' => 'ticket', 'id' => $ticket_id];
                } elseif ($parent_type === 'driver_call') {
                    $sql = "SELECT driver_id FROM driver_calls WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':id' => $parent_id]);
                    $driver_id = $stmt->fetchColumn();
                    return ['type' => 'driver', 'id' => $driver_id];
                }
            }
        } elseif ($type === 'driver') {
            return ['type' => 'driver', 'id' => $discussable_id];
        } elseif ($type === 'ticket') {
            return ['type' => 'ticket', 'id' => $discussable_id];
        }
        
        return null;
    }

    /**
     * Efficiently fetches all discussions for a given list of review IDs.
     *
     * @param array $reviewIds An array of review IDs.
     * @return array A list of discussions.
     */
    public function getDiscussionsForReviews(array $reviewIds): array
    {
        if (empty($reviewIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
        $sql = "SELECT d.*, u.username as opener_name 
                FROM discussions d
                JOIN users u ON d.opened_by = u.id
                WHERE d.discussable_type = 'App\\\\Models\\\\Review\\\\Review' 
                AND d.discussable_id IN ($placeholders)
                ORDER BY d.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($reviewIds);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getDiscussionsForReviews: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Efficiently fetches all replies for a given list of discussion IDs.
     *
     * @param array $discussionIds An array of discussion IDs.
     * @return array A list of replies.
     */
    public function getRepliesForDiscussions(array $discussionIds): array
    {
        if (empty($discussionIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($discussionIds), '?'));
        $sql = "SELECT dr.*, u.username 
                FROM discussion_replies dr
                JOIN users u ON dr.user_id = u.id
                WHERE dr.discussion_id IN ($placeholders)
                ORDER BY dr.created_at ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($discussionIds);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRepliesForDiscussions: " . $e->getMessage());
            return [];
        }
    }
} 