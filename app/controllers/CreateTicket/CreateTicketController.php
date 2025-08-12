<?php

namespace App\Controllers\CreateTicket;

use App\Core\Controller;
use App\Core\Auth;

class CreateTicketController extends Controller
{
    private $createTicketModel;

    public function __construct()
    {
        parent::__construct();
        $this->createTicketModel = new \App\Models\create_ticket\CreateTicketModel();
    }

    public function index()
    {
        // Dummy data for now, will be replaced by model calls
        $data = [
            'countries' => $this->createTicketModel->getCountries(),
            'platforms' => $this->createTicketModel->getPlatforms(),
            'categories' => $this->createTicketModel->getCategories(),
            'marketers' => $this->createTicketModel->getMarketers(),
            'title' => 'Create New Ticket'
        ];

        $this->view('create_ticket/index', $data);
    }

    public function v2()
    {
        // Data for the V2 form
        $data = [
            'countries' => $this->createTicketModel->getCountries(),
            'platforms' => $this->createTicketModel->getPlatforms(),
            'categories' => $this->createTicketModel->getCategories(),
            'marketers' => $this->createTicketModel->getMarketers(),
            'title' => 'Create New Ticket V2'
        ];

        $this->view('create_ticket/v2', $data);
    }

    public function fetch_trengo_ticket($ticketId)
    {
        // It's better to store the token in an environment variable or a config file
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjQ1OGE3ZDEyMTg4NjY4OGM2ZmY4MDY5NzgyMjBmNWU1YWVkMjRiYmVhZDZjNmM0N2FkNDg1NzMzMTU3ZmU3OTVjZWE5YTIxYmIxMGVlOTYiLCJpYXQiOjE3NTQ5ODY0NjQsIm5iZiI6MTc1NDk4NjQ2NCwiZXhwIjo0ODc5MTI0MDY0LCJzdWIiOiI3MjQ3MTgiLCJzY29wZXMiOltdLCJhZ2VuY3lfaWQiOjIyNTU1fQ.jtA3Qa3ubVnUb8tgd0d0I24oE1gPMFeZEmPzylqt04fZywBbQcIombLQjU9o5nOMSyCa6iXUN4yqke3WbX_9TA';
        $url = "https://app.trengo.com/api/v2/tickets/{$ticketId}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $this->sendJsonResponse(['success' => false, 'message' => "Failed to fetch data from Trengo. Status code: {$httpCode}"], $httpCode);
            return;
        }

        $this->sendJsonResponse(['success' => true, 'data' => json_decode($response)]);
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = $this->createTicketModel->getSubcategories($categoryId);
        $this->sendJsonResponse($subcategories);
    }

    public function getCodes($subcategoryId)
    {
        $codes = $this->createTicketModel->getCodes($subcategoryId);
        $this->sendJsonResponse($codes);
    }

    public function getAvailableCoupons($countryId)
    {
        $coupons = $this->createTicketModel->getAvailableCoupons($countryId);
        $this->sendJsonResponse($coupons);
    }

    public function checkTicketExists($ticketNumber)
    {
        $ticket = $this->createTicketModel->findByTicketNumber($ticketNumber);
        if ($ticket) {
            $this->sendJsonResponse(['exists' => true, 'ticket_id' => $ticket['id']]);
        } else {
            $this->sendJsonResponse(['exists' => false]);
        }
    }

    public function holdCoupon()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $couponId = $input['coupon_id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (empty($couponId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Coupon ID is required.'], 400);
            return;
        }

        $result = $this->createTicketModel->holdCouponById($couponId, $userId);
        $this->sendJsonResponse($result, $result['success'] ? 200 : 400);
    }
    
    public function releaseCoupon()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $couponId = $input['coupon_id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (empty($couponId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Coupon ID is required.'], 400);
            return;
        }

        $success = $this->createTicketModel->releaseCoupon($couponId, $userId);
        
        if ($success) {
            $this->sendJsonResponse(['success' => true]);
        } else {
            // It might fail if the coupon was not held by this user, which is not a critical error client-side.
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to release coupon.'], 400);
        }
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (empty($data['ticket_number']) || empty($data['platform_id']) || empty($data['category_id'])) {
             $this->sendJsonResponse(['success' => false, 'message' => 'Please fill all required fields.'], 400);
             return;
        }

        // VIP Marketer validation
        if (!empty($data['is_vip']) && empty($data['marketer_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Please select a marketer for VIP tickets.'], 400);
            return;
        }

        $data['user_id'] = $_SESSION['user_id'];
        $teamLeaderId = $this->createTicketModel->findTeamLeaderByUserId($data['user_id']);

        if (empty($teamLeaderId)) {
             $this->sendJsonResponse(['success' => false, 'message' => 'User is not assigned to a team with a leader.'], 400);
             return;
        }
        $data['team_leader_id'] = $teamLeaderId;


        $result = $this->createTicketModel->createTicketDetails($data);

        if ($result['success']) {
            if (!empty($data['call_log_id'])) {
                $callLogModel = $this->model('call_log/CallLogModel');
                $callLogModel->linkTicketDetail($data['call_log_id'], $result['ticket_detail_id']);
            }
            $this->sendJsonResponse(['success' => true, 'message' => 'Ticket details saved successfully!', 'ticket_id' => $result['ticket_id']]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
} 