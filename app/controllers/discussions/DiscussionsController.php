<?php

namespace App\Controllers\Discussions;

use App\Core\Controller;
use App\Core\Auth;

class DiscussionsController extends Controller
{
    private $discussionModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin(); // Ensure user is logged in
        $this->discussionModel = $this->model('Discussion/Discussion');
    }

    public function index()
    {
        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'];

        // Get filters from the URL
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'search' => trim($_GET['search'] ?? '')
        ];

        // Fetch all discussions based on the filters
        $discussions = $this->discussionModel->getDiscussionsForUser($userId, $role, $filters);
        
        // Determine the selected discussion
        $selectedDiscussionId = $_GET['id'] ?? null;
        $selectedDiscussion = null;

        if ($selectedDiscussionId) {
            // Find the selected discussion from the fetched list
            $selectedDiscussion = array_values(array_filter($discussions, function ($d) use ($selectedDiscussionId) {
                return $d['id'] == $selectedDiscussionId;
            }))[0] ?? null;

            // If found, mark its replies as read
            if ($selectedDiscussion) {
                $this->discussionModel->markRepliesAsRead($selectedDiscussionId, $userId);
                // We need to refetch to update the unread count, or manually set it to 0
                $discussions = $this->discussionModel->getDiscussionsForUser($userId, $role, $filters);
            }
        }

        $data = [
            'page_main_title' => 'My Discussions',
            'discussions' => $discussions,
            'selectedDiscussion' => $selectedDiscussion,
            'filters' => $filters,
            'currentUser' => [
                'id' => $userId,
                'role' => $role
            ]
        ];

        $this->view('discussions/index', $data);
    }
    
    public function getDiscussionsApi()
    {
        header('Content-Type: application/json');

        $userId = Auth::getUserId();

        if (!$userId || !isset($_SESSION['user']['role_name'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'Authentication required.']);
            return;
        }

        $role = $_SESSION['user']['role_name'];
        $discussions = $this->discussionModel->getDiscussionsForUser($userId, $role);

        $currentUser = ['id' => $userId, 'role' => $role];

        echo json_encode([
            'discussions' => array_values($discussions),
            'currentUser' => $currentUser
        ]);
    }


    public function add($discussable_type, $discussable_id)
    {
        // 1. Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->authorize('Discussions/add');

            $data = [
                'reason' => trim($_POST['reason']),
                'notes' => trim($_POST['notes']),
            ];

            // Basic validation
            if (empty($data['reason']) || empty($data['notes'])) {
                flash('discussion_error', 'Reason and notes cannot be empty.', 'alert alert-danger');
                $this->safeRedirectBack($discussable_type, $discussable_id);
                return;
            }

            $userId = Auth::getUserId();
            $newDiscussionId = $this->discussionModel->addDiscussion($discussable_type, $discussable_id, $userId, $data);

            if ($newDiscussionId) {
                flash('discussion_success', 'Discussion opened successfully.', 'alert alert-success');
                redirect('discussions?id=' . $newDiscussionId);
            } else {
                flash('discussion_error', 'Failed to add discussion. A database error occurred.', 'alert alert-danger');
                $this->safeRedirectBack($discussable_type, $discussable_id);
            }
            return;
        }

        // 2. Handle GET request (displaying the form)
        $this->authorize('Discussions/add');

        $item_to_discuss = $this->discussionModel->getDiscussableItemDetails($discussable_type, $discussable_id);

        if (!$item_to_discuss) {
            flash('error', 'The item you are trying to discuss does not exist.', 'error');
            redirect('/');
            return;
        }

        $data = [
            'page_main_title' => 'Open Discussion',
            'item' => $item_to_discuss,
            'discussable_type' => $discussable_type,
            'discussable_id' => $discussable_id,
        ];

        $this->view('discussions/add', $data);
    }

    private function safeRedirectBack($type, $id)
    {
        $redirectInfo = $this->discussionModel->getEntityForRedirect($type, $id);
        if ($redirectInfo) {
            if ($redirectInfo['type'] === 'ticket') {
                redirect('tickets/view/' . $redirectInfo['id']);
                return;
            } elseif ($redirectInfo['type'] === 'driver') {
                redirect('drivers/details/' . $redirectInfo['id']);
                return;
            }
        }
        // Fallback redirect
        redirect('');
    }

    public function close($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'] ?? '';

        $canCloseRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];
        if (!in_array($role, $canCloseRoles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to close this discussion.']);
            return;
        }

        if ($this->discussionModel->closeDiscussion($id, $userId, $role)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Discussion closed successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to close discussion.']);
        }
    }

    public function reopen($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role_name'] ?? '';

        $canReopenRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];
        if (!in_array($role, $canReopenRoles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to re-open this discussion.']);
            return;
        }

        if ($this->discussionModel->reopenDiscussion($id, $userId)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Discussion re-opened successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to re-open discussion.']);
        }
    }

    public function addReply($discussionId)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reply message cannot be empty.']);
            return;
        }

        $userId = $_SESSION['user']['id'];

        $newReply = $this->discussionModel->addReply($discussionId, $userId, $message);
        
        if ($newReply) {
            http_response_code(201);
            echo json_encode(['success' => true, 'reply' => $newReply]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add reply.']);
        }
    }

    public function addReplyApi($discussionId)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Invalid request method.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Reply message cannot be empty.']);
            return;
        }

        $userId = Auth::getUserId();

        $newReply = $this->discussionModel->addReply($discussionId, $userId, $message);

        if ($newReply) {
            http_response_code(201); // Created
            echo json_encode(['success' => true, 'reply' => $newReply]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to add reply.']);
        }
    }

    public function markAsReadApi($discussionId)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = Auth::getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            return;
        }

        if ($this->discussionModel->markRepliesAsRead($discussionId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Discussion marked as read.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to mark as read.']);
        }
    }
}
