<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

class Ticket extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(array $data)
    {
        $this->db->beginTransaction();

        try {
            // 1. Insert the main ticket
            $ticketSql = "INSERT INTO tickets (ticket_number, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, created_by, assigned_team_leader_id) 
                          VALUES (:ticket_number, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :created_by, :assigned_team_leader_id)";
            
            $stmt = $this->db->prepare($ticketSql);

            $params = [
                ':ticket_number'            => $data['ticket_number'],
                ':is_vip'                   => $data['is_vip'] ?? 0,
                ':platform_id'              => $data['platform_id'],
                ':phone'                    => !empty($data['phone']) ? $data['phone'] : null,
                ':category_id'              => $data['category_id'],
                ':subcategory_id'           => $data['subcategory_id'],
                ':code_id'                  => $data['code_id'],
                ':notes'                    => !empty($data['notes']) ? $data['notes'] : null,
                ':country_id'               => !empty($data['country_id']) ? $data['country_id'] : null,
                ':created_by'               => $_SESSION['user_id'], // Assuming user ID is in session
                ':assigned_team_leader_id'  => $data['assigned_team_leader_id'] // Assuming this comes from the form
            ];
            
            $stmt->execute($params);
            $ticketId = $this->db->lastInsertId();

            // 2. Handle coupons if they exist
            if (!empty($data['coupons']) && is_array($data['coupons'])) {
                $couponSql = "INSERT INTO ticket_coupons (ticket_id, coupon_id) VALUES (:ticket_id, :coupon_id)";
                $couponStmt = $this->db->prepare($couponSql);
                
                $updateUsedCouponSql = "UPDATE coupons SET is_used = 1, used_by = :user_id, used_in_ticket = :ticket_id, used_at = NOW(), used_for_phone = :used_for_phone WHERE id = :coupon_id";
                $updateStmt = $this->db->prepare($updateUsedCouponSql);

                foreach ($data['coupons'] as $couponId) {
                    if (empty($couponId)) continue;
                    
                    // Link coupon to ticket
                    $couponStmt->execute([':ticket_id' => $ticketId, ':coupon_id' => $couponId]);
                    
                    // Mark coupon as used
                    $updateStmt->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':ticket_id' => $ticketId,
                        ':coupon_id' => $couponId,
                        ':used_for_phone' => !empty($data['phone']) ? $data['phone'] : null
                    ]);
                }
            }

            $this->db->commit();
            return $ticketId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log error for debugging
            error_log("Ticket creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function update(array $data)
    {
        $this->db->beginTransaction();

        try {
            // 1. Update the main ticket details
            $ticketSql = "UPDATE tickets SET
                            is_vip = :is_vip,
                            platform_id = :platform_id,
                            phone = :phone,
                            category_id = :category_id,
                            subcategory_id = :subcategory_id,
                            code_id = :code_id,
                            notes = :notes,
                            country_id = :country_id
                          WHERE ticket_number = :ticket_number";
            
            $stmt = $this->db->prepare($ticketSql);

            $params = [
                ':ticket_number'    => $data['ticket_number'],
                ':is_vip'           => $data['is_vip'] ?? 0,
                ':platform_id'      => $data['platform_id'],
                ':phone'            => !empty($data['phone']) ? $data['phone'] : null,
                ':category_id'      => $data['category_id'],
                ':subcategory_id'   => $data['subcategory_id'],
                ':code_id'          => $data['code_id'],
                ':notes'            => !empty($data['notes']) ? $data['notes'] : null,
                ':country_id'       => !empty($data['country_id']) ? $data['country_id'] : null,
            ];
            
            $stmt->execute($params);

            // Fetch ticket details to get ID and existing coupons
            $ticket = $this->findByTicketNumber($data['ticket_number']);
            if (!$ticket) {
                // Should not happen in update flow, but as a safeguard
                $this->db->rollBack();
                return false;
            }
            $ticketId = $ticket['id'];

            // 2. Handle newly added coupons
            $existingCouponIdsOnTicket = array_column($ticket['coupons'], 'id');
            $submittedCouponIds = !empty($data['coupons']) ? array_map('intval', array_filter($data['coupons'])) : [];
            
            $couponsToAdd = array_diff($submittedCouponIds, $existingCouponIdsOnTicket);
            
            if (!empty($couponsToAdd)) {
                $couponSql = "INSERT INTO ticket_coupons (ticket_id, coupon_id) VALUES (:ticket_id, :coupon_id)";
                $couponStmt = $this->db->prepare($couponSql);
                
                $updateUsedCouponSql = "UPDATE coupons SET is_used = 1, used_by = :user_id, used_in_ticket = :ticket_id, used_at = NOW(), used_for_phone = :used_for_phone WHERE id = :coupon_id";
                $updateStmt = $this->db->prepare($updateUsedCouponSql);

                foreach ($couponsToAdd as $couponId) {
                    // Link coupon to ticket
                    $couponStmt->execute([':ticket_id' => $ticketId, ':coupon_id' => $couponId]);
                    
                    // Mark coupon as used
                    $updateStmt->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':ticket_id' => $ticketId,
                        ':coupon_id' => $couponId,
                        ':used_for_phone' => !empty($data['phone']) ? $data['phone'] : null
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log error for debugging
            error_log("Ticket update failed: " . $e->getMessage());
            return false;
        }
    }

    public function findByTicketNumber(string $ticketNumber)
    {
        $sql = "SELECT t.*, p.name as platform_name, c.name as category_name, sc.name as subcategory_name, co.name as code_name, u_creator.username as creator_name, u_leader.username as leader_name
                FROM tickets t
                LEFT JOIN platforms p ON t.platform_id = p.id
                LEFT JOIN ticket_categories c ON t.category_id = c.id
                LEFT JOIN ticket_subcategories sc ON t.subcategory_id = sc.id
                LEFT JOIN ticket_codes co ON t.code_id = co.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_leader ON t.assigned_team_leader_id = u_leader.id
                WHERE t.ticket_number = :ticket_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_number' => $ticketNumber]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Fetch associated coupons for the ticket
            $couponSql = "SELECT c.id, c.code, c.value 
                          FROM coupons c
                          JOIN ticket_coupons tc ON c.id = tc.coupon_id
                          WHERE tc.ticket_id = ?";
            
            $couponStmt = $this->db->prepare($couponSql);
            $couponStmt->execute([$ticket['id']]);
            $ticket['coupons'] = $couponStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $ticket ?: null;
    }

    public function findById(int $id)
    {
        $sql = "SELECT t.*, 
                       p.name as platform_name, 
                       cat.name as category_name, 
                       sc.name as subcategory_name, 
                       cod.name as code_name, 
                       u_creator.username as creator_username, 
                       u_leader.username as leader_username,
                       cnt.name as country_name
                FROM tickets t
                LEFT JOIN platforms p ON t.platform_id = p.id
                LEFT JOIN ticket_categories cat ON t.category_id = cat.id
                LEFT JOIN ticket_subcategories sc ON t.subcategory_id = sc.id
                LEFT JOIN ticket_codes cod ON t.code_id = cod.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_leader ON t.assigned_team_leader_id = u_leader.id
                LEFT JOIN countries cnt ON t.country_id = cnt.id
                WHERE t.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Fetch associated coupons for the ticket
            $couponSql = "SELECT c.id, c.code, c.value 
                          FROM coupons c
                          JOIN ticket_coupons tc ON c.id = tc.coupon_id
                          WHERE tc.ticket_id = ?";
            
            $couponStmt = $this->db->prepare($couponSql);
            $couponStmt->execute([$ticket['id']]);
            $ticket['coupons'] = $couponStmt->fetchAll(PDO::FETCH_ASSOC);
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

        $sql = "SELECT id, ticket_number, created_at 
                FROM tickets 
                WHERE phone = :phone AND id != :exclude_id
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':phone' => $phone,
            ':exclude_id' => $excludeTicketId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 