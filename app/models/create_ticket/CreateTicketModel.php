<?php

namespace App\Models\create_ticket;

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

use App\Core\Model;

use App\Models\Admin\TeamMember;

// Import DateTimeHelper
use App\Helpers\DateTimeHelper;





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

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getPlatforms()

    {

        $this->query("SELECT id, name FROM platforms ORDER BY name ASC");

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getCategories()

    {

        $this->query("SELECT id, name FROM ticket_categories ORDER BY name ASC");

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getSubcategories($categoryId)

    {

        $this->query("SELECT id, name FROM ticket_subcategories WHERE category_id = :category_id ORDER BY name ASC");

        $this->bind(':category_id', $categoryId);

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getCodes($subcategoryId)

    {

        $this->query("SELECT id, name FROM ticket_codes WHERE subcategory_id = :subcategory_id ORDER BY name ASC");

        $this->bind(':subcategory_id', $subcategoryId);

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getAllClassifications()

    {

        $this->query("

            SELECT

                c.id as category_id, c.name as category_name,

                s.id as subcategory_id, s.name as subcategory_name,

                co.id as code_id, co.name as code_name

            FROM ticket_categories c

            LEFT JOIN ticket_subcategories s ON c.id = s.category_id

            LEFT JOIN ticket_codes co ON s.id = co.subcategory_id

            ORDER BY c.name, s.name, co.name

        ");

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function getMarketers()

    {

        $this->query("SELECT u.id, u.name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'VIP' ORDER BY u.username ASC");

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function findByTicketNumber($ticketNumber)

    {

        $this->query("SELECT id FROM tickets WHERE ticket_number = :ticket_number");

        $this->bind(':ticket_number', $ticketNumber);

        $result = $this->single();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        if ($result) {

            return \convert_dates_for_display_12h($result, ['created_at', 'updated_at']);

        }

        return $result;

    }



    public function getAvailableCoupons($countryId)

    {

        // حساب الوقت المسموح للاحتفاظ بالكوبون (5 دقائق)
        $fiveMinutesAgo = DateTimeHelper::getCurrentUTC();
        $fiveMinutesAgoTimestamp = date('Y-m-d H:i:s', strtotime($fiveMinutesAgo . ' - 5 minutes'));

        $this->query("SELECT id, code, value FROM coupons

                      WHERE country_id = :country_id

                      AND is_used = 0

                      AND (held_by IS NULL OR held_at < :five_minutes_ago)");
        
        $this->bind(':five_minutes_ago', $fiveMinutesAgoTimestamp);

        $this->bind(':country_id', $countryId);

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    public function holdCouponById($couponId, $userId) {

        $this->beginTransaction();

        try {

            // Find the specific coupon if available

            // حساب الوقت المسموح للاحتفاظ بالكوبون (5 دقائق)
            $fiveMinutesAgo = DateTimeHelper::getCurrentUTC();
            $fiveMinutesAgoTimestamp = date('Y-m-d H:i:s', strtotime($fiveMinutesAgo . ' - 5 minutes'));

            $this->query("SELECT id, code, value FROM coupons 

                          WHERE id = :id

                          AND is_used = 0 

                          AND (held_by IS NULL OR held_at < :five_minutes_ago)

                          LIMIT 1 FOR UPDATE");
                          
            $this->bind(':five_minutes_ago', $fiveMinutesAgoTimestamp);

            $this->bind(':id', $couponId);

            $coupon = $this->single();



            if (!$coupon) {

                $this->rollBack();

                return ['success' => false, 'message' => 'Coupon not found, already used, or currently held by another user.'];

            }



            // Hold the coupon for the current user

            $utcTimestamp = DateTimeHelper::getCurrentUTC();
            $this->query("UPDATE coupons SET held_by = :user_id, held_at = :held_at WHERE id = :coupon_id");
            $this->bind(':held_at', $utcTimestamp);

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

            // Get the user's current team ID at the time of action

            $teamIdAtAction = TeamMember::getCurrentTeamIdForUser($data['user_id']);



            // Step 1: Find or create ticket

            $ticketId = $this->findOrCreateTicket($data);



            // Step 2: Create ticket details record

            $ticketDetailId = $this->createTicketDetailEntry($ticketId, $data, $teamIdAtAction);



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

                    $this->logIncomingCall($ticketDetailId, $data, $teamIdAtAction);

                } else if (!empty($data['phone'])) {

                    // This is an outgoing call made while creating the ticket

                    $this->logOutgoingCallForTicket($ticketDetailId, $data, $teamIdAtAction);

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

        // Apply Cairo 00:00–06:00 exception: store -1d else now (all in UTC) for create_ticket
        $utcTimestamp = \getCurrentUTCWithCustomerException();
        
        $this->query("INSERT INTO tickets (ticket_number, created_by, created_at) VALUES (:ticket_number, :user_id, :created_at)");

        $this->bind(':ticket_number', $data['ticket_number']);

        $this->bind(':user_id', $data['user_id']);
        
        $this->bind(':created_at', $utcTimestamp);

        $this->execute();

        return $this->lastInsertId();

    }



    private function createTicketDetailEntry($ticketId, $data, $teamId) {
        // Apply Cairo 00:00–06:00 exception for details created via create_ticket
        $utcTimestamp = \getCurrentUTCWithCustomerException();

        $this->query("INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, created_by, edited_by, team_id_at_action, created_at, updated_at)

                      VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :created_by, :edited_by, :team_id_at_action, :created_at, :updated_at)");

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

        $this->bind(':created_by', $data['user_id']);

        $this->bind(':edited_by', $data['user_id']);

        $this->bind(':team_id_at_action', $teamId);

        $this->bind(':created_at', $utcTimestamp);

        $this->bind(':updated_at', $utcTimestamp);

        $this->execute();

        return $this->lastInsertId();

    }



    private function logOutgoingCallForTicket($ticketDetailId, $data, $teamId) {

        // First, find the driver_id based on the phone number

        $this->query("SELECT id FROM drivers WHERE phone = :phone LIMIT 1");

        $this->bind(':phone', $data['phone']);

        $driver = $this->single();

        $driverId = $driver ? $driver['id'] : null;



        // If a driver exists, log the call. If not, we can't log an outgoing call.

        if ($driverId) {

            $this->query("INSERT INTO driver_calls (driver_id, call_by, call_status, notes, ticket_category_id, ticket_subcategory_id, ticket_code_id, team_id_at_action)

                        VALUES (:driver_id, :call_by, 'answered', :notes, :ticket_category_id, :ticket_subcategory_id, :ticket_code_id, :team_id_at_action)");

            $this->bind(':driver_id', $driverId);

            $this->bind(':call_by', $data['user_id']);

            $this->bind(':notes', "Call logged during ticket creation: " . ($data['notes'] ?? ''));

            $this->bind(':ticket_category_id', $data['category_id']);

            $this->bind(':ticket_subcategory_id', $data['subcategory_id']);

            $this->bind(':ticket_code_id', $data['code_id']);

            $this->bind(':team_id_at_action', $teamId);

            $this->execute();

        }

    }



    private function logIncomingCall($ticketDetailId, $data, $teamId) {

        $this->query("INSERT INTO incoming_calls (caller_phone_number, call_received_by, linked_ticket_detail_id, status, team_id_at_action)

                      VALUES (:caller_phone_number, :call_received_by, :linked_ticket_detail_id, 'answered', :team_id_at_action)");

        $this->bind(':caller_phone_number', $data['phone']);

        $this->bind(':call_received_by', $data['user_id']);

        $this->bind(':linked_ticket_detail_id', $ticketDetailId);

        $this->bind(':team_id_at_action', $teamId);

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



            $utcTimestamp = DateTimeHelper::getCurrentUTC();
            $this->query("UPDATE coupons SET is_used = 1, used_by = :user_id, used_in_ticket = :ticket_id, used_at = :used_at, held_by = NULL, held_at = NULL, used_for_phone = :phone WHERE id = :id");
            $this->bind(':used_at', $utcTimestamp);

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