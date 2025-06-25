<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;
use PDOException;

class Ticket extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createTicket(array $data, $userId)
    {
        $this->db->beginTransaction();

        try {
            // Step 1: Create the main ticket record
            $ticketSql = "INSERT INTO tickets (ticket_number, created_by) VALUES (:ticket_number, :created_by)";
            $stmt = $this->db->prepare($ticketSql);
            $stmt->execute([':ticket_number' => $data['ticket_number'], ':created_by' => $userId]);
            $ticketId = $this->db->lastInsertId();

            // Step 2: Add the first detail record, which also handles coupons
            $this->addTicketDetail($ticketId, $data, $userId);

            $this->db->commit();
            return $ticketId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addTicketDetail($ticketId, array $data, $userId)
    {
        // This function now assumes it is called within a transaction (from store() in controller)
        try {
            $detailSql = "INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, edited_by)
                          VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :edited_by)";
            
            $stmt = $this->db->prepare($detailSql);
            $teamLeaderId = $this->getTeamLeaderForUser($userId);
            $params = [
                ':ticket_id' => $ticketId,
                ':is_vip' => $data['is_vip'] ?? 0,
                ':platform_id' => $data['platform_id'],
                ':phone' => !empty($data['phone']) ? $data['phone'] : null,
                ':category_id' => $data['category_id'],
                ':subcategory_id' => $data['subcategory_id'],
                ':code_id' => $data['code_id'],
                ':notes' => !empty($data['notes']) ? $data['notes'] : null,
                ':country_id' => !empty($data['country_id']) ? $data['country_id'] : null,
                ':assigned_team_leader_id' => $teamLeaderId,
                ':edited_by' => $userId
            ];
            $stmt->execute($params);
            $detailId = $this->db->lastInsertId();

            // Sync coupons to this new detail ID
            if (isset($data['coupons'])) {
                $this->syncCoupons($ticketId, $detailId, $data['coupons']);
            }

            return $detailId;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function findTicketByNumber(string $ticketNumber)
    {
        $sql = "SELECT 
                    t.id, t.ticket_number, t.created_by, t.created_at,
                    td.*, -- Select all from ticket_details
                    p.name as platform_name, 
                    c.name as category_name, 
                    sc.name as subcategory_name, 
                    co.name as code_name, 
                    u_creator.username as creator_name, 
                    u_editor.username as editor_name, -- Changed from leader to editor
                    u_leader.username as leader_name
                FROM tickets t
                -- Join with the latest detail record
                JOIN ticket_details td ON t.id = td.ticket_id AND td.id = (
                    SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id
                )
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN ticket_categories c ON td.category_id = c.id
                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
                LEFT JOIN ticket_codes co ON td.code_id = co.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_editor ON td.edited_by = u_editor.id -- The user who made the last change
                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id
                WHERE t.ticket_number = :ticket_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_number' => $ticketNumber]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            $ticket['coupons'] = $this->getTicketCoupons($ticket['id']);
        }

        return $ticket ?: null;
    }

    public function findTicketByNumberOrPhone(string $searchTerm)
    {
        // First, try to find by ticket number, as it's unique and faster.
        $ticket = $this->findTicketByNumber($searchTerm);
        if ($ticket) {
            return $ticket;
        }

        // If not found, search by phone number in all ticket_details.
        // We want the ticket that had this phone number most recently.
        $sql = "SELECT t.id
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                WHERE td.phone = :phone
                ORDER BY td.created_at DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':phone' => $searchTerm]);
        $ticketId = $stmt->fetchColumn();

        // If we found a ticket ID, use findById to get all its details.
        if ($ticketId) {
            return $this->findById($ticketId);
        }

        return null; // Return null if nothing is found.
    }

    public function getTeamLeaderForUser($userId)
    {
        $sql = "SELECT t.team_leader_id 
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function syncCoupons($ticketId, $detailId, array $couponIds) {
        try {
            // Unmark coupons that are associated with the ticket but are not in the new list
            $currentCouponsSql = "SELECT coupon_id FROM ticket_coupons WHERE ticket_id = ?";
            $stmt = $this->db->prepare($currentCouponsSql);
            $stmt->execute([$ticketId]);
            $allTicketCouponIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $couponsToUnmark = array_diff($allTicketCouponIds, $couponIds);

            if (!empty($couponsToUnmark)) {
                $unmarkPlaceholders = implode(',', array_fill(0, count($couponsToUnmark), '?'));
                $unmarkSql = "UPDATE coupons SET is_used = 0, used_by = NULL, used_in_ticket = NULL, used_at = NULL, used_for_phone = NULL WHERE id IN ($unmarkPlaceholders)";
                $stmt = $this->db->prepare($unmarkSql);
                $stmt->execute(array_values($couponsToUnmark));
            }
            
            // Delete all of the ticket's existing coupon associations
            $deleteSql = "DELETE FROM ticket_coupons WHERE ticket_id = ?";
            $stmt = $this->db->prepare($deleteSql);
            $stmt->execute([$ticketId]);

            // Re-insert associations for all coupons in the current submission
            if (!empty($couponIds)) {
                $phoneSql = "SELECT phone FROM ticket_details WHERE id = ?";
                $phoneStmt = $this->db->prepare($phoneSql);
                $phoneStmt->execute([$detailId]);
                $phone = $phoneStmt->fetchColumn();
                $userId = $_SESSION['user_id'];
                
                $couponSql = "INSERT INTO ticket_coupons (ticket_id, ticket_detail_id, coupon_id) VALUES (?, ?, ?)";
                $couponStmt = $this->db->prepare($couponSql);
                
                $updateUsedCouponSql = "UPDATE coupons SET is_used = 1, used_by = ?, used_in_ticket = ?, used_at = NOW(), used_for_phone = ? WHERE id = ?";
                $updateStmt = $this->db->prepare($updateUsedCouponSql);

                foreach ($couponIds as $couponId) {
                    if (empty($couponId)) continue;
                    $couponStmt->execute([$ticketId, $detailId, $couponId]);
                    $updateStmt->execute([$userId, $ticketId, $phone, $couponId]);
                }
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function findById(int $id)
    {
        // This function now gets the latest version of the ticket
        $sql = "SELECT 
                    t.id, t.ticket_number, t.created_by, t.created_at,
                    td.*,
                    p.name as platform_name, 
                    cat.name as category_name, 
                    sc.name as subcategory_name, 
                    cod.name as code_name, 
                    u_creator.username as creator_username,
                    u_editor.username as editor_username, 
                    u_leader.username as leader_username,
                    cnt.name as country_name
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id AND td.id = (
                    SELECT MAX(id) FROM ticket_details WHERE ticket_id = t.id
                )
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN ticket_categories cat ON td.category_id = cat.id
                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
                LEFT JOIN ticket_codes cod ON td.code_id = cod.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_editor ON td.edited_by = u_editor.id
                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id
                LEFT JOIN countries cnt ON td.country_id = cnt.id
                WHERE t.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Fetch all coupons ever associated with the ticket for the top-level display
            $ticket['coupons'] = $this->getTicketCoupons($ticket['id']);
        }

        return $ticket ?: null;
    }

    /**
     * Find all tickets associated with a given phone number, excluding a specific ticket ID.
     */
    public function findByPhone(string $phone, int $excludeTicketId)
    {
        if (empty($phone)) {
            return [];
        }

        // This query now needs to check the ticket_details table.
        // We find the ticket_ids that have the given phone number in any of their details.
        $sql = "SELECT DISTINCT t.id, t.ticket_number, t.created_at
                FROM tickets t
                JOIN ticket_details td ON t.id = td.ticket_id
                WHERE td.phone = :phone AND t.id != :exclude_id
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':phone' => $phone,
            ':exclude_id' => $excludeTicketId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketHistory($ticketId)
    {
        $sql = "SELECT 
                    td.*,
                    p.name as platform_name, 
                    c.name as category_name, 
                    sc.name as subcategory_name, 
                    co.name as code_name,
                    u_editor.username as editor_name,
                    u_leader.username as leader_name
                FROM ticket_details td
                LEFT JOIN platforms p ON td.platform_id = p.id
                LEFT JOIN ticket_categories c ON td.category_id = c.id
                LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
                LEFT JOIN ticket_codes co ON td.code_id = co.id
                LEFT JOIN users u_editor ON td.edited_by = u_editor.id
                LEFT JOIN users u_leader ON td.assigned_team_leader_id = u_leader.id
                WHERE td.ticket_id = :ticket_id
                ORDER BY td.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketCoupons($ticketId)
    {
        // This now gets ALL coupons ever associated with the ticket.
        // It's used for the main display area.
        $sql = "SELECT c.id, c.code, c.value, c.is_used, c.used_at, tc.ticket_detail_id
                FROM ticket_coupons tc
                JOIN coupons c ON tc.coupon_id = c.id
                WHERE tc.ticket_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCouponsForTicketDetail($detailId)
    {
        // This gets only the coupons for a SPECIFIC version of the ticket.
        $sql = "SELECT c.id, c.code, c.value
                FROM ticket_coupons tc
                JOIN coupons c ON tc.coupon_id = c.id
                WHERE tc.ticket_detail_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$detailId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedTickets($phone, $currentTicketId)
    {
        if (empty($phone) || $currentTicketId === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("SELECT id, ticket_number, created_at FROM tickets WHERE phone = :phone AND id != :current_id ORDER BY created_at DESC");
            $stmt->execute([':phone' => $phone, ':current_id' => $currentTicketId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting related tickets: " . $e->getMessage());
            return [];
        }
    }

    public function getReviews($ticketId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT tr.*, u.username as reviewer_username
                FROM ticket_reviews tr
                JOIN users u ON tr.reviewed_by = u.id
                WHERE tr.ticket_id = :ticket_id
                ORDER BY tr.reviewed_at DESC
            ");
            $stmt->execute([':ticket_id' => $ticketId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getReviews: " . $e->getMessage());
            return [];
        }
    }

    public function getDiscussions($ticketId)
    {
         try {
            $stmt = $this->db->prepare("
                SELECT td.*, u.username as opener_username
                FROM ticket_discussions td
                JOIN users u ON td.opened_by = u.id
                WHERE td.ticket_id = :ticket_id
                ORDER BY td.created_at DESC
            ");
            $stmt->execute([':ticket_id' => $ticketId]);
            $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($discussions as &$discussion) {
                $discussion['objections'] = $this->getObjections($discussion['id']);
            }

            return $discussions;
        } catch (PDOException $e) {
            error_log("Error in getDiscussions: " . $e->getMessage());
            return [];
        }
    }

    public function getObjections($discussionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT tob.*, u_replier.username as replier_username, u_replied_to.username as replied_to_username
                FROM ticket_objections tob
                JOIN users u_replier ON tob.replied_by_agent_id = u_replier.id
                JOIN users u_replied_to ON tob.replied_to_user_id = u_replied_to.id
                WHERE tob.discussion_id = :discussion_id
                ORDER BY tob.created_at ASC
            ");
            $stmt->execute([':discussion_id' => $discussionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getObjections: " . $e->getMessage());
            return [];
        }
    }

    public function addReview($data) {
        try {
            $sql = "INSERT INTO ticket_reviews (ticket_id, reviewed_by, review_result, review_notes) VALUES (:ticket_id, :reviewed_by, :review_result, :review_notes)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error adding review: " . $e->getMessage());
            return false;
        }
    }

    public function addDiscussion($data) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO ticket_discussions (ticket_id, opened_by, reason, notes) VALUES (:ticket_id, :opened_by, :reason, :notes)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);

            $discussionId = $this->db->lastInsertId();

            // Potentially update ticket status here
            $updateSql = "UPDATE tickets SET status = 'under_discussion' WHERE id = :ticket_id";
            // Note: 'status' column is not in the tickets table schema provided. This would need to be added.
            // For now, we will skip this part.
            // $updateStmt = $this->db->prepare($updateSql);
            // $updateStmt->execute([':ticket_id' => $data['ticket_id']]);

            $this->db->commit();
            return $discussionId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error adding discussion: " . $e->getMessage());
            return false;
        }
    }

    public function addObjection($data) {
        try {
            $sql = "INSERT INTO ticket_objections (discussion_id, objection_text, replied_to_user_id, replied_by_agent_id) VALUES (:discussion_id, :objection_text, :replied_to_user_id, :replied_by_agent_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error adding objection: " . $e->getMessage());
            return false;
        }
    }

    public function closeDiscussion($discussionId) {
        $sql = "UPDATE ticket_discussions SET status = 'closed', updated_at = NOW() WHERE id = :discussion_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':discussion_id' => $discussionId]);
    }
} 