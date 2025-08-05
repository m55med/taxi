<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Tickets\Category;
use App\Models\Tickets\Platform;
use App\Models\Admin\Team;
use App\Models\Admin\Coupon;
use App\Models\Discussion\Discussion;
use App\Models\Review\Review;

class TicketController extends Controller
{
    private $ticketModel;
    private $discussionModel;
    private $reviewModel;

    public function __construct()
    {
        parent::__construct();
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->ticketModel = $this->model('Tickets/Ticket');
        $this->discussionModel = $this->model('Discussion/Discussion');
        $this->reviewModel = $this->model('Review/Review');
    }

    public function show($id = null)
    {
        if (empty($id)) {
            // If no ID is provided, show the search page
            $this->view('tickets/search', ['page_main_title' => 'Search for a Ticket']);
            return;
        }

        $ticket = $this->ticketModel->findById($id);

        if (!$ticket) {
            // If ticket not found, redirect back to search with an error
            $_SESSION['error_message'] = "Ticket with ID #{$id} not found.";
            redirect('tickets/view');
        }

        // Get the full history of the ticket
        $ticketHistory = $this->ticketModel->getTicketHistory($id);

        // Manually fetch marketer name for VIP tickets for each history item
        foreach ($ticketHistory as $key => $historyItem) {
            if (!empty($historyItem['is_vip'])) {
                $marketer_info = $this->ticketModel->getVipMarketerForDetail($historyItem['id']);
                $ticketHistory[$key]['marketer_name'] = $marketer_info ? $marketer_info['username'] : null;
            } else {
                $ticketHistory[$key]['marketer_name'] = null;
            }
        }

        // Extract all history IDs to fetch reviews in one query
        $historyIds = array_map(function ($item) {
            return $item['id'];
        }, $ticketHistory);

        // This array will hold all reviews from all history versions of the ticket.
        $all_reviews = [];
        if (!empty($historyIds)) {
            // Fetch all reviews for all history items at once.
            $all_reviews = $this->reviewModel->getReviewsForHistory($historyIds);
        }

        // NEW EFFICIENT LOGIC
        $reviewIds = !empty($all_reviews) ? array_map(fn($r) => $r['id'], $all_reviews) : [];
        $all_discussions = !empty($reviewIds) ? $this->discussionModel->getDiscussionsForReviews($reviewIds) : [];

        $discussionIds = !empty($all_discussions) ? array_map(fn($d) => $d['id'], $all_discussions) : [];
        $all_replies = !empty($discussionIds) ? $this->discussionModel->getRepliesForDiscussions($discussionIds) : [];

        // Group replies by discussion ID
        $repliesByDiscussionId = [];
        foreach ($all_replies as $reply) {
            $repliesByDiscussionId[$reply['discussion_id']][] = $reply;
        }

        // Attach replies to discussions
        foreach ($all_discussions as $key => $discussion) {
            $all_discussions[$key]['replies'] = $repliesByDiscussionId[$discussion['id']] ?? [];
        }

        // Group discussions by review ID
        $discussionsByReviewId = [];
        foreach ($all_discussions as $discussion) {
            $discussionsByReviewId[$discussion['discussable_id']][] = $discussion;
        }

        // Attach discussions to reviews
        foreach ($all_reviews as $key => $review) {
            $all_reviews[$key]['discussions'] = $discussionsByReviewId[$review['id']] ?? [];
        }

        // Group reviews by history ID
        $reviewsByHistoryId = [];
        foreach ($all_reviews as $review) {
            $reviewsByHistoryId[$review['reviewable_id']][] = $review;
        }

        // Attach coupons and the structured reviews to each history item
        foreach ($ticketHistory as $key => $historyItem) {
            $historyId = $historyItem['id'];
            $ticketHistory[$key]['coupons'] = $this->ticketModel->getCouponsForTicketDetail($historyId);
            $ticketHistory[$key]['reviews'] = $reviewsByHistoryId[$historyId] ?? [];
        }

        // Manually fetch global ticket discussions (not tied to a review)
        $ticketDiscussions = $this->discussionModel->getDiscussions('ticket', $id);
        $ticketDiscussionIds = !empty($ticketDiscussions) ? array_map(fn($d) => $d['id'], $ticketDiscussions) : [];
        $ticketReplies = !empty($ticketDiscussionIds) ? $this->discussionModel->getRepliesForDiscussions($ticketDiscussionIds) : [];

        $repliesByTicketDiscussionId = [];
        foreach ($ticketReplies as $reply) {
            $repliesByTicketDiscussionId[$reply['discussion_id']][] = $reply;
        }
        foreach ($ticketDiscussions as $key => $discussion) {
            $ticketDiscussions[$key]['replies'] = $repliesByTicketDiscussionId[$discussion['id']] ?? [];
        }

        // Manually fetch related tickets
        $relatedTickets = [];
        if (!empty($ticket['phone'])) {
            $relatedTickets = $this->ticketModel->findByPhone($ticket['phone'], $id);
        }

        // Load categories for the review form partial
        $categoryModel = $this->model('Tickets/Category');
        $ticket_categories = $categoryModel->getAll();

        $data = [
            'page_main_title' => 'تفاصيل التذكرة',
            'ticket' => $ticket,
            'ticketHistory' => $ticketHistory,
            'relatedTickets' => $relatedTickets,
            'ticketDiscussions' => $ticketDiscussions, // Pass the fully-loaded ticket discussions
            'ticket_categories' => $ticket_categories, // Pass categories for review partial
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['user']['role_name'] ?? 'default_role'
            ]

        ];

        $this->view('tickets/view', $data);
    }

    public function getCouponsByCountry()
    {
        header('Content-Type: application/json');
        $debug = [];

        try {
            if (!isset($_SESSION['user_id'])) {
                throw new \Exception('User not authenticated.');
            }
            $debug['auth_check'] = 'Passed for user_id: ' . $_SESSION['user_id'];

            if (!isset($_GET['country_id']) || empty($_GET['country_id'])) {
                throw new \Exception('Country ID is required.');
            }
            $countryId = (int) $_GET['country_id'];
            $debug['country_id'] = $countryId;

            $excludeIds = [];
            if (!empty($_GET['exclude_ids'])) {
                $excludeIds = array_map('intval', explode(',', $_GET['exclude_ids']));
            }
            $debug['exclude_ids'] = $excludeIds;

            $couponModel = $this->model('admin/Coupon');

            if (!is_object($couponModel)) {
                throw new \Exception('Coupon model is not a valid object.');
            }
            $debug['model_check'] = 'Coupon model is a valid object.';

            $result = $couponModel->getAvailableByCountry($countryId, $_SESSION['user_id'], $excludeIds);

            $coupons = $result['coupons'];
            $debug = array_merge($debug, $result['debug']);

            $debug['final_coupons_count'] = count($coupons);

            echo json_encode(['success' => true, 'coupons' => $coupons, 'debug' => $debug]);

        } catch (\Exception $e) {
            $debug['EXCEPTION_MESSAGE'] = $e->getMessage();
            $debug['EXCEPTION_TRACE'] = $e->getTraceAsString();
            echo json_encode(['success' => false, 'message' => 'An error occurred.', 'coupons' => [], 'debug' => $debug]);
        }
    }

    public function holdCoupon()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $couponModel = $this->model('admin/Coupon');

        if (!isset($data['coupon_id'])) {
            echo json_encode(['success' => false, 'message' => 'Coupon ID is required.']);
            return;
        }

        $couponId = (int) $data['coupon_id'];
        $userId = $_SESSION['user_id'];

        if ($couponModel->hold($couponId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Coupon held successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Coupon is already held by another user or is invalid.']);
        }
    }

    public function releaseCoupon()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $couponModel = $this->model('admin/Coupon');

        if (!isset($data['coupon_id'])) {
            http_response_code(204);
            return;
        }

        $couponId = (int) $data['coupon_id'];
        $userId = $_SESSION['user_id'];

        $couponModel->release($couponId, $userId);

        http_response_code(204); // No Content
    }

    public function releaseAllCoupons()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $couponModel = $this->model('admin/Coupon');
            $couponModel->releaseAllForUser($userId);
        }
        http_response_code(204); // No Content
    }

    public function store()
    {
        header('Content-Type: application/json');

        // 1. Get and Validate Input
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
            return;
        }

        $required_fields = ['ticket_number', 'platform_id', 'category_id', 'subcategory_id', 'code_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                return;
            }
        }

        try {
            $userId = $_SESSION['user_id'];
            $ticketId = null;
            $ticketDetailId = null;
            $message = '';

            // 2. Determine if it's a Create or Update operation
            $existingTicket = $this->ticketModel->findTicketByNumber($data['ticket_number']);

            if ($existingTicket) {
                // UPDATE LOGIC
                $ticketId = $existingTicket['id'];
                $ticketDetailId = $this->ticketModel->addTicketDetail($ticketId, $data, $userId);
                $message = 'Ticket updated successfully.';
            } else {
                // CREATE LOGIC
                $newTicketData = $this->ticketModel->createTicket($data, $userId);
                if ($newTicketData) {
                    $ticketId = $newTicketData['ticket_id'];
                    $ticketDetailId = $newTicketData['ticket_detail_id'];
                    $message = 'Ticket created successfully.';
                }
            }

            // 3. Sync Coupons if a detail record was created/added
            if ($ticketDetailId) {
                if (isset($data['coupons']) && is_array($data['coupons'])) {
                    $this->ticketModel->syncCoupons($ticketId, $ticketDetailId, $data['coupons']);
                }
                echo json_encode(['success' => true, 'message' => $message, 'ticket_id' => $ticketId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save ticket details.']);
            }
        } catch (\Exception $e) {
            error_log("Critical Error in TicketsController::store(): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'A critical server error occurred.']);
        }
    }

    public function addObjection($ticketId, $discussionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($_SESSION['role_name'], ['agent', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $ticket = $this->ticketModel->findById($ticketId);
        // User must be the ticket creator, or a manager/leader to add an objection/reply.
        if ($_SESSION['user_id'] != $ticket['created_by'] && !in_array($_SESSION['role_name'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
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
        if (!in_array($_SESSION['role_name'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
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
        if (empty($_POST['search_term'])) {
            redirect('tickets/view'); // Redirect if search is empty
        }

        $searchTerm = trim($_POST['search_term']);
        $ticket = $this->ticketModel->findTicketByNumberOrPhone($searchTerm);

        if ($ticket) {
            redirect('tickets/view/' . $ticket['id']);
        } else {
            // Set a flash message to inform the user
            $_SESSION['error_message'] = "No ticket or customer found for: " . htmlspecialchars($searchTerm);
            // Redirect back to the search page
            redirect('tickets/view');
        }
    }

    public function ajaxSearch()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
            echo json_encode([]);
            return;
        }

        $searchTerm = trim($_GET['term']);
        $suggestions = $this->ticketModel->getSuggestions($searchTerm);

        echo json_encode($suggestions);
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
        $countryModel = $this->model('Admin/Country');
        $categoryModel = $this->model('Tickets/Category');
        $platformModel = $this->model('Tickets/Platform');
        $teamModel = $this->model('Admin/Team');

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
