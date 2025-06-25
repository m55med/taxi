<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Tickets\Category;
use App\Models\Tickets\Platform;
use App\Models\Admin\Team;
use App\Models\Admin\Coupon;

class TicketsController extends Controller
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

    public function details($id)
    {
        if (empty($id)) {
            redirect('ticket');
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
            'page_main_title' => 'تفاصيل التذكرة',
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

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendJsonResponse(['success' => false, 'message' => 'Invalid JSON input.'], 400);
        }

        // Basic validation
        $required_fields = ['ticket_number', 'platform_id', 'category_id', 'subcategory_id', 'code_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return $this->sendJsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
            }
        }
    
        try {
            // Re-check if ticket exists to decide between create and update
            $existingTicket = $this->ticketModel->findTicketByNumber($data['ticket_number']);
            $userId = $_SESSION['user_id'];
            
            if ($existingTicket) {
                // Update logic: Add a new detail record to the existing ticket
                $this->ticketModel->addTicketDetail($existingTicket['id'], $data, $userId);
                $ticketId = $existingTicket['id']; // Use the existing ticket ID
                $message = 'Ticket updated successfully.';
            } else {
                // Create logic
                $ticketId = $this->ticketModel->createTicket($data, $userId);
                $message = 'Ticket created successfully.';
            }

            if ($ticketId) {
                // Handle coupons (clear existing and add new ones)
                if (isset($data['coupons'])) {
                    $this->ticketModel->syncCoupons($ticketId, $data['coupons']);
                }
                return $this->sendJsonResponse(['success' => true, 'message' => $message, 'ticket_id' => $ticketId]);
            } else {
                return $this->sendJsonResponse(['success' => false, 'message' => 'Failed to save ticket.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error in TicketsController::store(): " . $e->getMessage());
            // Send detailed error message for debugging
            return $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
            // Set success message
            $_SESSION['success_message'] = 'تمت إضافة المراجعة بنجاح.';
        } else {
            // Set error message
            $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة المراجعة.';
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
            $_SESSION['success_message'] = 'تم فتح المناقشة بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء فتح المناقشة.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function addObjection($ticketId, $discussionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             redirect('tickets/details/' . $ticketId);
        }
        
        $ticket = $this->ticketModel->findById($ticketId);
        // User must be the ticket creator, or a manager/leader to add an objection/reply.
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
            $_SESSION['success_message'] = 'تمت إضافة الرد بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة الرد.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function closeDiscussion($ticketId, $discussionId)
    {
        // Add authorization check to ensure only specific roles can close discussions
        if (!in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            // Or use $this->authorize([...]) if you have it set up
            redirect('tickets/details/' . $ticketId);
        }

        if ($this->ticketModel->closeDiscussion($discussionId)) {
            $_SESSION['success_message'] = 'تم إغلاق المناقشة بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء إغلاق المناقشة.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function search()
    {
        if (empty($_POST['ticket_number'])) {
            redirect('ticket'); // Redirect to create page if search is empty
        }

        $searchTerm = trim($_POST['ticket_number']);
        $ticket = $this->ticketModel->findTicketByNumberOrPhone($searchTerm);

        if ($ticket) {
            redirect('tickets/details/' . $ticket['id']);
        } else {
            // Set a flash message to inform the user
            $_SESSION['error_message'] = "لم يتم العثور على تذكرة أو عميل بالبيانات: " . htmlspecialchars($searchTerm);
            // Redirect to the create page so they can create it if they want
            redirect('ticket');
        }
    }

    public function checkTicket($ticketNumber = '')
    {
        header('Content-Type: application/json');
        
        if (empty($ticketNumber)) {
            echo json_encode(['exists' => false]);
            return;
        }

        $ticket = $this->ticketModel->findTicketByNumber(trim($ticketNumber));

        if ($ticket) {
            echo json_encode(['exists' => true, 'id' => $ticket['id']]);
        } else {
            echo json_encode(['exists' => false]);
        }
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
            'pageTitle' => 'Tickets'
        ];

        $this->view('tickets/index', $data);
    }
} 