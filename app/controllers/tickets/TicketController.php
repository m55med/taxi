<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Tickets\Category;
use App\Models\Tickets\Platform;
use App\Models\Admin\Team;
use App\Models\Admin\Coupon;

class TicketController extends Controller
{
    private $ticketModel;

    public function __construct()
    {
        parent::__construct();
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->ticketModel = $this->model('tickets/Ticket');
    }

    public function index()
    {
        // Load necessary data for the create/update form
        $countryModel = $this->model('admin/Country');
        $categoryModel = $this->model('tickets/Category');
        $platformModel = $this->model('tickets/Platform');
        $teamModel = $this->model('admin/Team');
        
        $data = [
            'countries' => $countryModel->getAll(),
            'categories' => $categoryModel->getAll(),
            'platforms' => $platformModel->getAll(),
            'team_leaders' => $teamModel->getAllTeamLeaders(),
            'pageTitle' => 'Create Ticket'
        ];

        $this->view('tickets/index', $data);
    }
    
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tickets'); // Or to a relevant page
            return;
        }

        $ticket_id = $_POST['ticket_id'] ?? null;

        if (empty($ticket_id)) {
            die("Ticket ID is missing.");
        }

        $ticket = $this->ticketModel->findById($ticket_id);
        if (!$ticket) {
            die('Ticket not found.');
        }

        // Prepare data for adding a ticket detail
        // This should come from the form POST data
        $postData = [
            'country_id' => $_POST['country_id'],
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'genders' => $_POST['genders'],
            'ride_type' => $_POST['ride_type'],
            'car_type' => $_POST['car_type'],
            'ticket_source' => $_POST['ticket_source'],
            'sub_source' => $_POST['sub_source'],
            'pickup_point' => $_POST['pickup_point'],
            'destination' => $_POST['destination'],
            'notes' => $_POST['notes'] ?? '',
        ];

        // Add the new detail
        $result = $this->ticketModel->addTicketDetail($ticket_id, $postData);

        if ($result) {
            // Handle coupon additions
            if (!empty($_POST['coupons'])) {
                $this->ticketModel->updateTicketCoupons($ticket_id, $result, $_POST['coupons']);
            }
            redirect('tickets/details/' . $ticket_id);
        } else {
            die('Failed to update ticket.');
        }
    }

    public function details($id)
    {
        if (empty($id)) {
            redirect('errors/notfound');
        }

        $ticket = $this->ticketModel->findById($id);

        if (!$ticket) {
             die('Ticket not found.');
        }

        // Get the full history of the ticket
        $ticketHistory = $this->ticketModel->getTicketHistory($id);

        // For each history item, get its specific coupons
        foreach ($ticketHistory as $key => $historyItem) {
            $ticketHistory[$key]['coupons'] = $this->ticketModel->getCouponsForTicketDetail($historyItem['id']);
        }

        // Manually fetch related tickets here to bypass model issue
        $relatedTickets = [];
        if (!empty($ticket['phone'])) {
            $relatedTickets = $this->ticketModel->findByPhone($ticket['phone'], $id);
        }

        $ticket['coupons'] = $this->ticketModel->getTicketCoupons($id);
        $reviews = $this->ticketModel->getReviews($id);
        $discussions = $this->ticketModel->getDiscussions($id);
        
        $data = [
            'page_main_title' => 'Ticket Details',
            'ticket' => $ticket, // This is the latest version
            'ticketHistory' => $ticketHistory, // This is the full history
            'relatedTickets' => $relatedTickets,
            'reviews' => $reviews,
            'discussions' => $discussions,
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['role']
            ]
        ];

        $this->view('tickets/details', $data);
    }

    public function addReview($ticketId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':ticket_id' => $ticketId,
            ':reviewed_by' => $_SESSION['user_id'],
            ':review_result' => $_POST['review_result'],
            ':review_notes' => trim($_POST['review_notes'])
        ];

        if ($this->ticketModel->addReview($data)) {
            $_SESSION['success_message'] = 'Review added successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to add review.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function addDiscussion($ticketId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':ticket_id' => $ticketId,
            ':opened_by' => $_SESSION['user_id'],
            ':reason' => trim($_POST['reason']),
            ':notes' => trim($_POST['notes'])
        ];

        if ($this->ticketModel->addDiscussion($data)) {
            $_SESSION['success_message'] = 'Discussion started successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to start discussion.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function addObjection($ticketId, $discussionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             redirect('tickets/details/' . $ticketId);
        }
        
        $ticket = $this->ticketModel->findById($ticketId);
        if ($_SESSION['user_id'] != $ticket['created_by'] && !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':discussion_id' => $discussionId,
            ':objection_text' => trim($_POST['objection_text']),
            ':replied_to_user_id' => $_POST['replied_to_user_id'],
            ':replied_by_agent_id' => $_SESSION['user_id']
        ];

        if ($this->ticketModel->addObjection($data)) {
            $_SESSION['success_message'] = 'Reply added successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to add reply.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function closeDiscussion($ticketId, $discussionId)
    {
        if (!in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        if ($this->ticketModel->closeDiscussion($discussionId)) {
            $_SESSION['success_message'] = 'Discussion closed successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to close discussion.';
        }
        redirect('tickets/details/' . $ticketId);
    }
} 