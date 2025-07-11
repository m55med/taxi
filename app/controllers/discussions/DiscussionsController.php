<?php

namespace App\Controllers\Discussions;

use App\Core\Controller;
use App\Core\Auth;

class DiscussionsController extends Controller {
    private $discussionModel;

    public function __construct() {
        parent::__construct();
        Auth::requireLogin(); // Ensure user is logged in
        $this->discussionModel = $this->model('discussion/Discussion');
    }

    public function index() {
        $data = [
            'page_main_title' => 'My Discussions',
        ];

        $this->view('discussions/index', $data);
    }
    
    public function getDiscussionsApi() {
        header('Content-Type: application/json');
        $userId = Auth::getUserId();
        if (!$userId) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'Authentication required.']);
            return;
        }
        
        $role = $_SESSION['role'];
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
                redirect('discussions#discussion-' . $newDiscussionId);
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

    private function safeRedirectBack($type, $id) {
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

    public function close($id) {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = Auth::getUserId();
        $role = $_SESSION['role'] ?? ''; // Make sure role exists

        // Authorization check
        $canCloseRoles = ['admin', 'quality_manager', 'Team_leader'];
        if (!in_array($role, $canCloseRoles)) {
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'You do not have permission to close this discussion.']);
            return;
        }

        if ($this->discussionModel->closeDiscussion($id, $userId, $role)) {
            http_response_code(200); // OK
            echo json_encode(['success' => true, 'message' => 'Discussion closed successfully.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Failed to close the discussion due to a server error or it might be already closed.']);
        }
    }

    public function reopen($id) {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = Auth::getUserId();
        $role = $_SESSION['role'] ?? '';

        // Allow reopening for a broader set of authorized roles, not just admin
        $canReopenRoles = ['admin', 'quality_manager', 'Team_leader', 'developer'];
        if (!in_array($role, $canReopenRoles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to reopen this discussion.']);
            return;
        }

        if ($this->discussionModel->reopenDiscussion($id, $userId)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Discussion reopened successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to reopen the discussion.']);
        }
    }

    public function addReply($discussionId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discussions');
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            flash('discussion_error', 'Reply message cannot be empty.', 'alert alert-danger');
            redirect('discussions#discussion-' . $discussionId);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        if ($this->discussionModel->addReply($discussionId, $userId, $message)) {
            flash('discussion_success', 'Reply added successfully.', 'alert alert-success');
        } else {
            flash('discussion_error', 'Failed to add reply.', 'alert alert-danger');
        }

        redirect('discussions#discussion-' . $discussionId);
    }

    public function addReplyApi($discussionId) {
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

    public function markAsReadApi($discussionId) {
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