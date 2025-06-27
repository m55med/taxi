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
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        $discussions = $this->discussionModel->getDiscussionsForUser($userId, $role);
        
        $open_discussions_count = 0;
        foreach($discussions as $d) {
            if ($d['status'] === 'open') {
                $open_discussions_count++;
            }
        }
        
        $data = [
            'page_main_title' => 'My Discussions',
            'discussions' => $discussions,
            'open_discussions_count' => $open_discussions_count,
            'currentUser' => ['id' => $userId, 'role' => $role]
        ];

        $this->view('discussions/index', $data);
    }

    public function add($type, $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discussions'); // Or show an error
            return;
        }

        $data = [
            'reason' => trim($_POST['reason']),
            'notes' => trim($_POST['notes']),
        ];

        // Basic validation
        if (empty($data['reason']) || empty($data['notes'])) {
            flash('discussion_error', 'Reason and notes cannot be empty.', 'alert alert-danger');
            $this->safeRedirectBack($type, $id);
            return;
        }

        $userId = $_SESSION['user_id'];
        $newDiscussionId = $this->discussionModel->addDiscussion($type, $id, $userId, $data);

        if ($newDiscussionId) {
            flash('discussion_success', 'Discussion opened successfully.', 'alert alert-success');
            // Redirect to the discussions page, anchoring to the new discussion
            redirect('discussions#discussion-' . $newDiscussionId);
        } else {
            flash('discussion_error', 'Failed to add discussion. A database error occurred.', 'alert alert-danger');
            $this->safeRedirectBack($type, $id);
        }
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discussions');
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        if ($this->discussionModel->closeDiscussion($id, $userId, $role)) {
            flash('discussion_success', 'Discussion closed successfully.', 'alert alert-success');
        } else {
            flash('discussion_error', 'Failed to close discussion or no permission.', 'alert alert-danger');
        }

        redirect('discussions');
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
} 