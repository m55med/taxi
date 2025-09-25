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
use App\Models\Listings\ListingModel;

class TicketController extends Controller
{
    private $ticketModel;
    private $discussionModel;
    private $reviewModel;
    private $listingModel;

    public function __construct()
    {
        parent::__construct();
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->ticketModel = $this->model('Tickets/Ticket');
        $this->discussionModel = $this->model('Discussion/Discussion');
        $this->reviewModel = $this->model('Review/Review');
        $this->listingModel = $this->model('Listings/ListingModel');
    }

    public function show($id = null)
    {
        if (empty($id)) {
            // If no ID is provided, show the search page
            $recentLogs = $this->listingModel->getTicketLogs(null, 10); // Get recent logs
            $this->view('tickets/search', [
                'page_main_title' => 'Search for a Ticket',
                'recent_logs' => $recentLogs
            ]);
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
                $ticketHistory[$key]['marketer_name'] = $marketer_info ? $marketer_info['name'] : null;
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
            'listingModel' => $this->listingModel, // For logs functionality
            'currentUser' => [
                'id' => $_SESSION['user_id'] ?? null,
                'role' => $_SESSION['user']['role_name'] ?? $_SESSION['role_name'] ?? 'default_role'
            ]

        ];

        // Debug: طباعة معلومات debug إذا كان مطلوباً
        if (isset($_GET['debug_team_leader']) && $_GET['debug_team_leader'] == '1') {
            $data['debug_team_leader_info'] = [
                'session_user_id' => $_SESSION['user_id'] ?? 'not set',
                'session_role_name' => $_SESSION['role_name'] ?? 'not set',
                'session_user_role_name' => $_SESSION['user']['role_name'] ?? 'not set',
                'current_user_team_id' => \App\Models\Admin\TeamMember::getCurrentTeamIdForUser($_SESSION['user_id'] ?? 0),
                'first_history_item_team_id' => $ticketHistory[0]['team_id_at_action'] ?? 'not set'
            ];
        }

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
        if ($_SESSION['user_id'] != $ticket['created_by'] && !in_array($_SESSION['role_name'], ['Quality', 'Team_leader', 'admin', 'developer'])) {
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
        if (!in_array($_SESSION['role_name'], ['Quality', 'Team_leader', 'admin', 'developer'])) {
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

    public function edit($detailId)
    {

        $ticketDetail = $this->ticketModel->findDetailById($detailId);
        if (!$ticketDetail) {
            redirect('tickets/view');
            return;
        }

        // التحقق من الصلاحيات قبل عرض صفحة التعديل
        $userId = Auth::getUserId() ?? 0;
        $userRole = Auth::getUserRole() ?? 'guest';

        // يمكن لأي شخص تعديل التذاكر التي أنشأها
        // ولكن التذاكر التي لم ينشأها يمكن تعديلها من الادمن أو مسؤول الجودة فقط
        if ($ticketDetail['edited_by'] != $userId) {
            // إذا لم يكن هو المنشئ الأصلي
            if (!in_array($userRole, ['admin', 'quality_manager', 'Quality'])) {
                // التحقق من صلاحيات التيم ليدر
                $canEditAsTeamLeader = false;
                if ($userRole === 'Team_leader') {
                    // الحصول على team_id للتيم ليدر الحالي
                    $currentUserTeamId = \App\Models\Admin\TeamMember::getCurrentTeamIdForUser($userId);
                    // التحقق من أن team_id_at_action للتذكرة يطابق team_id للتيم ليدر
                    if ($currentUserTeamId && isset($ticketDetail['team_id_at_action']) && $ticketDetail['team_id_at_action'] == $currentUserTeamId) {
                        $canEditAsTeamLeader = true;
                    }
                }

                if (!$canEditAsTeamLeader) {
                    $_SESSION['error_message'] = 'You do not have permission to edit this ticket. Only the original creator, admin, quality manager, or team leader of the assigned team can edit it.';
                    redirect('tickets/view/' . $ticketDetail['ticket_id']);
                }
            }
        }

        $categoryModel = $this->model('Tickets/Category');
        $platformModel = $this->model('Tickets/Platform');
        $teamModel = $this->model('Admin/Team');
        $countryModel = $this->model('Admin/Country');

        $data = [
            'page_main_title' => 'Edit Ticket Details',
            'ticket' => $ticketDetail, // Pass the specific detail
            'categories' => $categoryModel->getAll(),
            'platforms' => $platformModel->getAll(),
            'team_leaders' => $teamModel->getAllTeamLeaders(),
            'countries' => $countryModel->getAll(),
            'listingModel' => $this->listingModel, // For logs functionality
            'user_can_edit' => true, // تم التحقق من الصلاحيات بالفعل
            'user_role' => $userRole,
            'current_user_id' => $userId
        ];

        $this->view('tickets/edit', $data);
    }

    public function update($detailId)
    {
        $ticketId = $this->ticketModel->getTicketIdFromDetailId($detailId);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            // Get current ticket details before updating
            $currentDetails = $this->ticketModel->findDetailById($detailId);
    
            if (!$currentDetails) {
                $_SESSION['error_message'] = '❌ التذكرة غير موجودة أو تم حذفها.';
                redirect('tickets/view/' . $ticketId);
            }
    
            $userId = Auth::getUserId() ?? 0; // Default to 0 if no user is logged in
            $userRole = Auth::getUserRole() ?? 'guest';
    
    
            // التحقق من الصلاحيات
            if ($currentDetails['edited_by'] != $userId) {
                if (!in_array(strtolower($userRole), ['admin', 'quality_manager', 'quality'])) {
                    // التحقق من صلاحيات التيم ليدر
                    $canEditAsTeamLeader = false;
                    if ($userRole === 'Team_leader') {
                        // الحصول على team_id للتيم ليدر الحالي
                        $currentUserTeamId = \App\Models\Admin\TeamMember::getCurrentTeamIdForUser($userId);
                        // التحقق من أن team_id_at_action للتذكرة يطابق team_id للتيم ليدر
                        if ($currentUserTeamId && isset($currentDetails['team_id_at_action']) && $currentDetails['team_id_at_action'] == $currentUserTeamId) {
                            $canEditAsTeamLeader = true;
                        }
                    }

                    if (!$canEditAsTeamLeader) {
                        $_SESSION['error_message'] = '🚫 ليس لديك صلاحية تعديل هذه التذكرة. يمكن فقط للمنشئ الأصلي أو المدير أو مسؤول الجودة أو قائد الفريق المسؤول تعديلها.';
                        redirect('tickets/view/' . $ticketId);
                    }
                }
            }
    
            $data = $_POST;
    
    
            // Ensure all required fields are present
            if (empty($data['platform_id']) || empty($data['category_id']) || empty($data['subcategory_id']) || empty($data['code_id'])) {
                $_SESSION['error_message'] = '⚠️ يرجى ملء جميع الحقول المطلوبة (المنصة، الفئة، الفئة الفرعية، الكود) قبل الحفظ.';
                redirect('tickets/view/' . $ticketId);
                return;
            }
    
        
            $this->logTicketChanges($detailId, $currentDetails, $data, $userId);
    
            // UPDATE the existing ticket detail instead of creating a new one
           

            $updateResult = $this->ticketModel->updateTicketDetail($detailId, $data, $userId);

            // Handle the result and redirect with detailed messages
            if ($updateResult) {
                $_SESSION['success_message'] = '✅ تم تحديث تفاصيل التذكرة بنجاح! تم حفظ جميع التغييرات.';
            } else {
                $_SESSION['error_message'] = '❌ فشل في تحديث تفاصيل التذكرة. يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.';
                error_log("Ticket update failed for detail ID: $detailId, User: $userId");
            }

            // Redirect back to ticket view
            redirect('tickets/view/' . $ticketId);
        } else {
            redirect('tickets/view/' . $ticketId);
        }
    }
    

    /**
     * Log changes made to ticket details
     */
    private function logTicketChanges($detailId, $currentDetails, $newData, $userId)
    {
        error_log("LOGGING TICKET CHANGES: Detail ID: $detailId, User: $userId");

        $fieldsToTrack = [
            'platform_id' => 'Platform',
            'phone' => 'Phone',
            'category_id' => 'Category',
            'subcategory_id' => 'Subcategory',
            'code_id' => 'Code',
            'country_id' => 'Country',
            'is_vip' => 'VIP Status',
            'notes' => 'Notes'
        ];

        foreach ($fieldsToTrack as $field => $fieldName) {
            $oldValue = $currentDetails[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            // Convert checkbox values
            if ($field === 'is_vip') {
                $oldValue = $oldValue ? 'Yes' : 'No';
                $newValue = isset($newData[$field]) ? 'Yes' : 'No';
            }

            // Convert IDs to readable names for better logging
            if (in_array($field, ['platform_id', 'category_id', 'subcategory_id', 'code_id', 'country_id'])) {
                $oldValue = $this->getReadableValue($field, $oldValue, $currentDetails);
                $newValue = $this->getReadableValue($field, $newValue, $newData);
            }

            // Only log if there's a change
            if ($oldValue != $newValue) {
                $logResult = $this->ticketModel->logEdit($detailId, $userId, $fieldName, $oldValue, $newValue);
            }
        }
    }

    /**
     * Get readable value for IDs (convert ID to name)
     */
    private function getReadableValue($field, $value, $data)
    {
        if (empty($value)) return 'N/A';

        switch ($field) {
            case 'platform_id':
                return $data['platform_name'] ?? "Platform ID: $value";
            case 'category_id':
                return $data['category_name'] ?? "Category ID: $value";
            case 'subcategory_id':
                return $data['subcategory_name'] ?? "Subcategory ID: $value";
            case 'code_id':
                return $data['code_name'] ?? "Code ID: $value";
            case 'country_id':
                return $data['country_name'] ?? "Country ID: $value";
            default:
                return $value;
        }
    }

    /**
     * Show edit logs for a ticket (admin only)
     */
    public function editLogs($ticketId)
{
    // Check if user is logged in
    if (!Auth::isLoggedIn()) {
        $_SESSION['error_message'] = 'Please log in to access this page.';
        redirect('login');
        return;
    }

    // Debug: Check current user role
    $currentRole = Auth::getUserRole();
    error_log("Edit Logs Access - User Role: " . ($currentRole ?? 'null'));
    
    // Check if user is admin or developer
    if (!Auth::hasAnyRole(['admin', 'developer'])) {
        $_SESSION['error_message'] = "Access denied. Admin privileges required. Your role: " . ($currentRole ?? 'unknown');
        redirect('tickets/view/' . $ticketId);
        return;
    }

    // First try to find ticket by ID
    $ticket = $this->ticketModel->findById($ticketId);
    $actualTicketId = $ticketId;
    
    // If not found, maybe the ID is a ticket detail ID, try to get ticket from detail
    if (!$ticket) {
        $actualTicketId = $this->ticketModel->getTicketIdFromDetailId($ticketId);
        if ($actualTicketId) {
            $ticket = $this->ticketModel->findById($actualTicketId);
        }
    }
    
    if (!$ticket) {
        $_SESSION['error_message'] = "❌ التذكرة رقم #{$ticketId} غير موجودة أو تم حذفها.";
        redirect('tickets/view');
        return;
    }

    $editLogs = $this->ticketModel->getAllEditLogsForTicket($actualTicketId);

    // ✅ تحويل created_at من UTC إلى Cairo + 12 ساعة format
    foreach ($editLogs as &$log) {
        if (!empty($log['created_at'])) {
            try {
                $utc = new \DateTimeImmutable($log['created_at'], new \DateTimeZone('UTC'));
                $cairoTime = $utc->setTimezone(new \DateTimeZone('Africa/Cairo'));
                $log['created_at_formatted'] = $cairoTime->format('Y-m-d h:i A');
            } catch (\Exception $e) {
                $log['created_at_formatted'] = $log['created_at']; // fallback
            }
        } else {
            $log['created_at_formatted'] = null;
        }
    }

    // Debug: Log successful access
    error_log("Edit Logs Access SUCCESS - Input ID: $ticketId, Actual Ticket ID: $actualTicketId, User Role: $currentRole, Logs Count: " . count($editLogs));

    $data = [
        'page_main_title' => 'Edit Logs',
        'ticket' => $ticket,
        'editLogs' => $editLogs,
        'listingModel' => $this->listingModel // For additional logs functionality
    ];

    $this->view('tickets/edit_logs', $data);
}

}
