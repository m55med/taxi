<?php

namespace App\Models\create_ticket;

use App\Core\Model;

class CreateTicketModel extends Model
{
    private $incomingCallPlatformId = null;

    private function getIncomingCallPlatformId()
    {
        if ($this->incomingCallPlatformId === null) {
            $this->query("SELECT id FROM platforms WHERE name = 'Incoming Call'");
            $result = $this->single();
            $this->incomingCallPlatformId = $result ? (int)$result['id'] : false;
        }
        return $this->incomingCallPlatformId;
    }

    public function getCountries()
    {
        $this->query("SELECT id, name FROM countries ORDER BY name ASC");
        return $this->resultSet();
    }

    public function getPlatforms()
    {
        $this->query("SELECT id, name FROM platforms ORDER BY name ASC");
        return $this->resultSet();
    }

    public function getCategories()
    {
        $this->query("SELECT id, name FROM ticket_categories ORDER BY name ASC");
        return $this->resultSet();
    }

    public function getSubcategories($categoryId)
    {
        $this->query("SELECT id, name FROM ticket_subcategories WHERE category_id = :category_id ORDER BY name ASC");
        $this->bind(':category_id', $categoryId);
        return $this->resultSet();
    }

    public function getCodes($subcategoryId)
    {
        $this->query("SELECT id, name FROM ticket_codes WHERE subcategory_id = :subcategory_id ORDER BY name ASC");
        $this->bind(':subcategory_id', $subcategoryId);
        return $this->resultSet();
    }

    public function getMarketers()
    {
        $this->query("SELECT u.id, u.username FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'marketer' ORDER BY u.username ASC");
        return $this->resultSet();
    }

    public function findByTicketNumber($ticketNumber)
    {
        $this->query("SELECT id FROM tickets WHERE ticket_number = :ticket_number");
        $this->bind(':ticket_number', $ticketNumber);
        return $this->single();
    }

    public function getAvailableCoupons($countryId)
    {
        $this->query("SELECT id, code, value FROM coupons 
                      WHERE country_id = :country_id 
                      AND is_used = 0 
                      AND (held_by IS NULL OR held_at < NOW() - INTERVAL 5 MINUTE)");
        $this->bind(':country_id', $countryId);
        return $this->resultSet();
    }

    public function holdCouponById($couponId, $userId) {
        $this->beginTransaction();
        try {
            // Find the specific coupon if available
            $this->query("SELECT id, code, value FROM coupons 
                          WHERE id = :id
                          AND is_used = 0 
                          AND (held_by IS NULL OR held_at < NOW() - INTERVAL 5 MINUTE)
                          LIMIT 1 FOR UPDATE");
            $this->bind(':id', $couponId);
            $coupon = $this->single();

            if (!$coupon) {
                $this->rollBack();
                return ['success' => false, 'message' => 'Coupon not found, already used, or currently held by another user.'];
            }

            // Hold the coupon for the current user
            $this->query("UPDATE coupons SET held_by = :user_id, held_at = NOW() WHERE id = :coupon_id");
            $this->bind(':user_id', $userId);
            $this->bind(':coupon_id', $coupon['id']);
            $this->execute();
            
            $this->commit();
            return ['success' => true, 'coupon' => $coupon];

        } catch (\Exception $e) {
            $this->rollBack();
            error_log('Coupon hold error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'A server error occurred while holding the coupon.'];
        }
    }

    public function releaseCoupon($couponId, $userId) {
        try {
            $this->query("UPDATE coupons SET held_by = NULL, held_at = NULL WHERE id = :coupon_id AND held_by = :user_id");
            $this->bind(':coupon_id', $couponId);
            $this->bind(':user_id', $userId);
            return $this->execute();
        } catch (\Exception $e) {
            error_log('Coupon release error: ' . $e->getMessage());
            return false;
        }
    }

    public function createTicketDetails($data) {
        $this->beginTransaction();
        try {
            // Step 1: Find or create ticket
            $ticketId = $this->findOrCreateTicket($data);

            // Step 2: Create ticket details record
            $ticketDetailId = $this->createTicketDetailEntry($ticketId, $data);

            // Step 3: Assign marketer if it's a VIP ticket
            if (!empty($data['is_vip']) && !empty($data['marketer_id'])) {
                $this->query("INSERT INTO ticket_vip_assignments (ticket_detail_id, marketer_id) VALUES (:ticket_detail_id, :marketer_id)");
                $this->bind(':ticket_detail_id', $ticketDetailId);
                $this->bind(':marketer_id', $data['marketer_id']);
                $this->execute();
            }

            // Step 4: Handle Incoming Call logging
            if (!empty($data['platform_id'])) {
                $this->query("SELECT name FROM platforms WHERE id = :platform_id");
                $this->bind(':platform_id', $data['platform_id']);
                $platform = $this->single();

                // Corrected condition to match the actual database value 'incoming_calls'
                if ($platform && trim($platform['name']) === 'incoming_calls') {
                    if (empty($data['phone'])) {
                        throw new \Exception('Phone number is required for an incoming call ticket.');
                    }
                    $this->logIncomingCall($ticketDetailId, $data);
                }
            }
            
            // Step 5: Process coupons
            if (!empty($data['coupons'])) {
                $this->processCoupons($ticketId, $ticketDetailId, $data);
            }

            $this->commit();
            return ['success' => true, 'ticket_id' => $ticketId, 'ticket_detail_id' => $ticketDetailId];

        } catch (\Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function findOrCreateTicket($data) {
        $existingTicket = $this->findByTicketNumber($data['ticket_number']);
        if ($existingTicket) {
            return $existingTicket['id'];
        }
        $this->query("INSERT INTO tickets (ticket_number, created_by, created_at) VALUES (:ticket_number, :user_id, NOW())");
        $this->bind(':ticket_number', $data['ticket_number']);
        $this->bind(':user_id', $data['user_id']);
        $this->execute();
        return $this->lastInsertId();
    }

    private function createTicketDetailEntry($ticketId, $data) {
        $this->query("INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, edited_by)
                      VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :edited_by)");
        $this->bind(':ticket_id', $ticketId);
        $this->bind(':is_vip', (bool)($data['is_vip'] ?? false));
        $this->bind(':platform_id', $data['platform_id']);
        $this->bind(':phone', $data['phone']);
        $this->bind(':category_id', $data['category_id']);
        $this->bind(':subcategory_id', $data['subcategory_id']);
        $this->bind(':code_id', $data['code_id']);
        $this->bind(':notes', $data['notes']);
        $this->bind(':country_id', $data['country_id']);
        $this->bind(':assigned_team_leader_id', $data['team_leader_id']);
        $this->bind(':edited_by', $data['user_id']);
        $this->execute();
        return $this->lastInsertId();
    }

    private function logIncomingCall($ticketDetailId, $data) {
        $this->query("INSERT INTO incoming_calls (caller_phone_number, call_received_by, linked_ticket_detail_id, status)
                      VALUES (:caller_phone_number, :call_received_by, :linked_ticket_detail_id, 'answered')");
        $this->bind(':caller_phone_number', $data['phone']);
        $this->bind(':call_received_by', $data['user_id']);
        $this->bind(':linked_ticket_detail_id', $ticketDetailId);
        $this->execute();
    }

    private function processCoupons($ticketId, $ticketDetailId, $data) {
        foreach ($data['coupons'] as $couponId) {
            $this->query("SELECT id FROM coupons WHERE id = :id AND held_by = :user_id AND is_used = 0");
            $this->bind(':id', $couponId);
            $this->bind(':user_id', $data['user_id']);
            $coupon = $this->single();

            if (!$coupon) {
                throw new \Exception("Coupon with ID {$couponId} is no longer valid or held by the user.");
            }

            $this->query("UPDATE coupons SET is_used = 1, used_by = :user_id, used_in_ticket = :ticket_id, used_at = NOW(), held_by = NULL, held_at = NULL, used_for_phone = :phone WHERE id = :id");
            $this->bind(':user_id', $data['user_id']);
            $this->bind(':ticket_id', $ticketId);
            $this->bind(':phone', $data['phone']);
            $this->bind(':id', $couponId);
            $this->execute();

            $this->query("INSERT INTO ticket_coupons (ticket_id, ticket_detail_id, coupon_id) VALUES (:ticket_id, :ticket_detail_id, :coupon_id)");
            $this->bind(':ticket_id', $ticketId);
            $this->bind(':ticket_detail_id', $ticketDetailId);
            $this->bind(':coupon_id', $couponId);
            $this->execute();
        }
    }

    public function findTeamLeaderByUserId($userId)
    {
        $this->query("SELECT T.team_leader_id
                      FROM teams T
                      JOIN team_members TM ON T.id = TM.team_id
                      WHERE TM.user_id = :user_id");
        $this->bind(':user_id', $userId);
        $result = $this->single();
        return $result ? $result['team_leader_id'] : null;
    }
} 