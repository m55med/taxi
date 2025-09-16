<?php

namespace App\Models\Discussion;

use PDO;
use PDOException;
use App\Core\Model;
use App\Models\Notifications\Notification;

class Discussion extends Model
{
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
    }

    /**
     * Get team member IDs for a given team leader
     *
     * @param int $teamLeaderId The user ID of the team leader
     * @return array Array of team member user IDs
     */
    public function getTeamMemberIds($teamLeaderId)
    {
        try {
            $sql = "
                SELECT tm.user_id
                FROM team_members tm
                INNER JOIN teams t ON tm.team_id = t.id
                WHERE t.team_leader_id = ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$teamLeaderId]);

            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $result ?: [];
        } catch (PDOException $e) {
            error_log('Error getting team member IDs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches all discussions relevant to a specific user, with contextual information.
     * - Admins/Developers/QM see all discussions.
     * - Team Leaders see discussions related to their team members.
     * - Agents see discussions on reviews of their work (ticket details/calls).
     */
    public function getDiscussionsForUser($userId, $role)
    {
        $baseQuery = "
            SELECT 
                d.id, d.discussable_type, d.discussable_id, d.reason, d.notes, d.status, d.created_at,
                opener.username AS opener_name,
                d.opened_by,
                COALESCE(last_reply.username, 'N/A') as last_replier_name,
                COALESCE(last_reply.created_at, d.created_at) as last_activity_at,
                t.ticket_number,
                td.ticket_id,
                r.rating as review_score,
                reviewer.username as reviewer_name,
                -- Simplify the reviewable_type for easier use in the frontend
                CASE 
                    WHEN r.reviewable_type LIKE '%TicketDetail' THEN 'ticket_detail'
                    WHEN r.reviewable_type LIKE '%DriverCall' THEN 'driver_call'
                    ELSE r.reviewable_type
                END as reviewable_type_simple,
                dc.driver_id,
                (
                    SELECT COUNT(*) 
                    FROM discussion_replies dr 
                    WHERE dr.discussion_id = d.id 
                      AND dr.user_id != ?
                      AND dr.id > (
                          SELECT COALESCE(MAX(udrs.last_read_reply_id), 0)
                          FROM user_discussion_read_status udrs
                          WHERE udrs.user_id = ? AND udrs.discussion_id = d.id
                      )
                ) AS unread_count
            FROM discussions d
            JOIN users opener ON d.opened_by = opener.id
            LEFT JOIN (
                SELECT 
                    dr.discussion_id, 
                    u.username, 
                    dr.created_at
                FROM discussion_replies dr
                JOIN users u ON dr.user_id = u.id
                WHERE dr.id IN (
                    SELECT MAX(id) 
                    FROM discussion_replies 
                    GROUP BY discussion_id
                )
            ) AS last_reply ON d.id = last_reply.discussion_id
            LEFT JOIN reviews r ON d.discussable_id = r.id AND d.discussable_type LIKE '%Review'
            LEFT JOIN users reviewer ON r.reviewed_by = reviewer.id
            LEFT JOIN ticket_details td ON r.reviewable_id = td.id AND r.reviewable_type LIKE '%TicketDetail'
            LEFT JOIN tickets t ON td.ticket_id = t.id
            LEFT JOIN driver_calls dc on r.reviewable_id = dc.id and r.reviewable_type LIKE '%DriverCall'
        ";
        

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        $conditions = [];
        $params = [$userId, $userId];

        if ($role !== 'admin' && $role !== 'developer' && $role !== 'quality_control' && $role !== 'Team_leader') {
            // Agent should see discussions they opened, discussions on their work (tickets/calls), 
            // or discussions they have replied to.
            $conditions[] = "(
                d.opened_by = ? 
                OR t.created_by = ? 
                OR dc.call_by = ?
                OR EXISTS (
                    SELECT 1 
                    FROM discussion_replies dr 
                    WHERE dr.discussion_id = d.id AND dr.user_id = ?
                )
            )";
            array_push($params, $userId, $userId, $userId, $userId);
        } elseif ($role === 'Team_leader') {
            // Team Leader sees discussions for their team members
            $teamMembers = $this->getTeamMemberIds($userId); // This method must exist and return an array of user IDs
            if (!empty($teamMembers)) {
                $teamMemberCount = count($teamMembers);
                $placeholders = implode(',', array_fill(0, $teamMemberCount, '?'));
                
                // Build the condition with separate placeholder sets for each column
                $conditions[] = "(d.opened_by IN ($placeholders) OR t.created_by IN ($placeholders) OR dc.call_by IN ($placeholders))";
                
                // The parameters must be repeated for each placeholder set
                $team_params = array_merge($teamMembers, $teamMembers, $teamMembers);
                $params = array_merge($params, $team_params);
            } else {
                // If team leader has no members, they can only see discussions they opened.
                $conditions[] = "d.opened_by = ?";
                $params[] = $userId;
            }
        }

        $sql = $baseQuery;
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY last_activity_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all replies in one go for efficiency
        $discussionIds = array_column($discussions, 'id');
        if (!empty($discussionIds)) {
            $replies = $this->getRepliesForDiscussions($discussionIds);
            $repliesByDiscussion = [];
            foreach ($replies as $reply) {
                $repliesByDiscussion[$reply['discussion_id']][] = $reply;
            }
            foreach ($discussions as &$discussion) {
                $discussion['replies'] = $repliesByDiscussion[$discussion['id']] ?? [];
            }
        }

        return $discussions;
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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
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

    public function addReply($discussionId, $userId, $message, $isSystemMessage = false)
    {
        try {
            $this->db->beginTransaction();

            // Allow adding system messages even to closed discussions
            if (!$isSystemMessage) {
                $status_stmt = $this->db->prepare("SELECT status FROM discussions WHERE id = :id");
                $status_stmt->execute([':id' => $discussionId]);
                if ($status_stmt->fetchColumn() !== 'open') {
                    error_log("Attempted to reply to a closed discussion (ID: $discussionId)");
                    $this->db->rollBack();
                    return false;
                }
            }

            $sql = "INSERT INTO discussion_replies (discussion_id, user_id, message) VALUES (:discussion_id, :user_id, :message)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':discussion_id' => $discussionId,
                ':user_id' => $userId,
                ':message' => $message
            ]);
            $replyId = $this->db->lastInsertId();

            if (!$replyId) {
                throw new PDOException("Failed to get last insert ID for new reply.");
            }

            // --- Notification Logic ---
            if (!$isSystemMessage) {
                 $this->notifyUsersOnNewReply($discussionId, $userId, $message);
            }

            $this->db->commit();

            // Fetch the newly created reply to return it
            $fetch_sql = "SELECT dr.*, u.username 
                          FROM discussion_replies dr
                          JOIN users u ON dr.user_id = u.id
                          WHERE dr.id = :reply_id";
            $fetch_stmt = $this->db->prepare($fetch_sql);
            $fetch_stmt->execute([':reply_id' => $replyId]);
            return $fetch_stmt->fetch(PDO::FETCH_ASSOC);

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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        if ($result) {

            return convert_dates_for_display($result, ['created_at', 'updated_at']);

        }


        return $result;
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
    
    /**
     * Reopens a discussion by setting its status to 'open'.
     * This operation is transactional, ensuring that both the status update
     * and the creation of a system message reply occur successfully.
     *
     * @param int $discussionId The ID of the discussion to reopen.
     * @param int $userId The ID of the user reopening the discussion.
     * @return bool True on success, false on failure.
     */
    public function reopenDiscussion($discussionId, $userId)
    {
        $this->db->beginTransaction();
        try {
            // Step 1: Update discussion status from 'closed' to 'open'
            $sql_update = "UPDATE discussions SET status = 'open' WHERE id = :id AND status = 'closed'";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->execute([':id' => $discussionId]);

            // If no rows were affected, it means the discussion was not closed or doesn't exist.
            if ($stmt_update->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // Step 2: Add a system message reply indicating the discussion was reopened
            $message = "This discussion was reopened by a moderator.";
            $sql_insert_reply = "INSERT INTO discussion_replies (discussion_id, user_id, message) VALUES (:discussion_id, :user_id, :message)";
            $stmt_insert_reply = $this->db->prepare($sql_insert_reply);
            $stmt_insert_reply->execute([
                ':discussion_id' => $discussionId,
                ':user_id' => $userId,
                ':message' => $message
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in reopenDiscussion: " . $e->getMessage());
            return false;
        }
    }

    public function markRepliesAsRead($discussionId, $userId) {
        // This is a placeholder for the actual implementation
        return true;
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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
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
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (PDOException $e) {
            error_log("Error in getRepliesForDiscussions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches the details of an item that can be discussed.
     *
     * @param string $type The type of the discussable item (e.g., 'review').
     * @param int $id The ID of the discussable item.
     * @return array|false The item details or false if not found.
     */
    public function getDiscussableItemDetails(string $type, int $id)
    {
        if ($type === 'review') {
            $sql = "SELECT r.*, u.username as reviewer_name
                    FROM reviews r
                    JOIN users u ON r.reviewed_by = u.id
                    WHERE r.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        // Add other discussable types here in the future
        
        return false;
    }

    public function markDiscussionAsRead($discussionId, $userId)
    {
        try {
            // Find the ID of the latest reply in this discussion.
            $latestReplyStmt = $this->db->prepare("SELECT MAX(id) FROM discussion_replies WHERE discussion_id = ?");
            $latestReplyStmt->execute([$discussionId]);
            $latestReplyId = $latestReplyStmt->fetchColumn();

            // If there are no replies, there's nothing to mark as read.
            if (!$latestReplyId) {
                return true; 
            }

            // Use INSERT...ON DUPLICATE KEY UPDATE to efficiently update or create the read status.
            $sql = "
                INSERT INTO user_discussion_read_status (user_id, discussion_id, last_read_reply_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE last_read_reply_id = VALUES(last_read_reply_id)
            ";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId, $discussionId, $latestReplyId]);
            
        } catch (PDOException $e) {
            error_log("Error in markDiscussionAsRead: " . $e->getMessage());
            return false;
        }
    }
} 