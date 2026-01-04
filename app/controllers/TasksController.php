<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\TaskService;
use App\Models\User\User;



class TasksController extends Controller
{
    private $taskService;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        // Permission check can be added here if needed, e.g., $this->authorize('Tasks/index');
        $this->taskService = new TaskService();
        $this->userModel = $this->model('User/User');
    }

    public function index()
    {
        $role = $_SESSION['user']['role_name'] ?? 'agent';
        $userId = $_SESSION['user_id'];

        $filters = [];
        // Show all tasks for admin, developer, Team_leader, and Quality
        if (!in_array($role, ['admin', 'developer', 'Team_leader', 'Quality'])) {
            $filters['user_id'] = $userId;
        }

        // Apply filters from GET
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['project'])) $filters['project'] = $_GET['project'];
        if ($role === 'admin' || $role === 'developer') {
            if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        }

        $tasks = $this->taskService->listTasks($filters);
        $users = ($role === 'admin' || $role === 'developer') ? $this->userModel->getAllUsers() : [];

        $this->view('tasks/index', [
            'tasks' => $tasks,
            'role' => $role,
            'users' => $users,
            'filters' => $filters
        ]);
    }

    public function create()
    {
        // Only admin/developer/team leader can create tasks
        $role = $_SESSION['user']['role_name'] ?? 'agent';
        if (!in_array($role, ['admin', 'developer', 'Team_leader'])) {
            flash('error', 'You do not have permission to create tasks.');
            redirect('tasks');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskData = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'deadline' => $_POST['deadline'],
                'source' => trim($_POST['source']),
                'frequency' => $_POST['frequency'],
                'indicator' => trim($_POST['indicator']),
                'goal' => trim($_POST['goal']),
                'project' => trim($_POST['project']),
                'created_by' => $_SESSION['user_id'],
                'status' => 'pending'
            ];

            $assigneeIds = $_POST['assignee_ids'] ?? [];
            $files = $_FILES['attachments'] ?? [];

            if (empty($taskData['title']) || empty($assigneeIds)) {
                flash('error', 'Please provide a title and at least one assignee.');
                redirect('tasks/create');
                return;
            }

            if ($this->taskService->createTask($taskData, $assigneeIds, $files)) {
                flash('success', 'Task created and assigned successfully.');
                redirect('tasks');
            } else {
                flash('error', 'Failed to create task.');
                redirect('tasks/create');
            }
        } else {
            $users = $this->userModel->getAllUsers();
            $this->view('tasks/create', [
                'users' => $users
            ]);
        }
    }

    public function edit($id)
    {
        $task = $this->taskService->getTaskDetails($id);
        if (!$task) {
            flash('error', 'Task not found.');
            redirect('tasks');
            return;
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user']['role_name'] ?? 'agent';

        // Check permission: admin/developer or creator
        if ($role !== 'admin' && $role !== 'developer' && $task['created_by'] != $userId) {
            flash('error', 'You do not have permission to edit this task.');
            redirect('tasks/show/' . $id);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskData = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'deadline' => $_POST['deadline'],
                'source' => trim($_POST['source']),
                'frequency' => $_POST['frequency'],
                'indicator' => trim($_POST['indicator']),
                'goal' => trim($_POST['goal']),
                'project' => trim($_POST['project'])
            ];

            $assigneeIds = $_POST['assignee_ids'] ?? [];
            $files = $_FILES['attachments'] ?? [];

            if (empty($taskData['title']) || empty($assigneeIds)) {
                flash('error', 'Please provide a title and at least one assignee.');
                redirect('tasks/edit/' . $id);
                return;
            }

            if ($this->taskService->updateTask($id, $taskData, $assigneeIds, $files)) {
                flash('success', 'Task updated successfully.');
                redirect('tasks/show/' . $id);
            } else {
                flash('error', 'Failed to update task.');
                redirect('tasks/edit/' . $id);
            }
        } else {
            $users = $this->userModel->getAllUsers();
            $this->view('tasks/edit', [
                'task' => $task,
                'users' => $users
            ]);
        }
    }

    public function show($id)
    {
        $task = $this->taskService->getTaskDetails($id);
        if (!$task) {
            flash('error', 'Task not found.');
            redirect('tasks');
            return;
        }

        // Fetch comments
        $task['comments'] = $this->taskService->getTaskComments($id);

        // Check permission: admin/creator/assignee
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user']['role_name'] ?? 'agent';
        $isAssignee = false;
        foreach($task['assignees'] as $assignee) {
            if ($assignee['id'] == $userId) {
                $isAssignee = true;
                break;
            }
        }

        if ($role !== 'admin' && $role !== 'developer' && $task['created_by'] != $userId && !$isAssignee) {
            flash('error', 'Access denied.');
            redirect('tasks');
            return;
        }

        $users = $this->userModel->getAllUsers();

        $this->view('tasks/show', [
            'task' => $task,
            'role' => $role,
            'current_user_id' => $userId,
            'is_assignee' => $isAssignee,
            'users' => $users
        ]);
    }

    public function update_status()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'];
            $status = $_POST['status'];

            // Get current task to check its status
            $task = $this->taskService->getTaskById($taskId);
            if (!$task) {
                flash('error', 'Task not found.');
                redirect('tasks/show/' . $taskId);
                return;
            }

            // Check if task is completed and user is not admin/developer
            if ($task['status'] === 'completed' && !in_array(Auth::getUserRole(), ['admin', 'developer'])) {
                flash('error', 'You do not have permission to reopen completed tasks.');
                redirect('tasks/show/' . $taskId);
                return;
            }

            if ($this->taskService->updateTaskStatus($taskId, $status)) {
                flash('success', 'Status updated successfully.');
            } else {
                flash('error', 'Failed to update status.');
            }
            redirect('tasks/show/' . $taskId);
        }
    }

    public function add_comment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'task_id' => $_POST['task_id'],
                'user_id' => $_SESSION['user_id'],
                'comment' => trim($_POST['comment']),
                'is_completion_notice' => isset($_POST['is_completion_notice']) ? 1 : 0
            ];

            if (empty($data['comment'])) {
                flash('error', 'Comment cannot be empty.');
                redirect('tasks/show/' . $data['task_id']);
                return;
            }

            if ($this->taskService->addComment($data)) {
                flash('success', 'Comment added successfully.');
            } else {
                flash('error', 'Failed to add comment.');
            }
            redirect('tasks/show/' . $data['task_id']);
        }
    }

    public function update_assignees($id)
    {
        $task = $this->taskService->getTaskDetails($id);
        if (!$task) {
            flash('error', 'Task not found.');
            redirect('tasks');
            return;
        }

        $role = $_SESSION['user']['role_name'] ?? 'agent';

        // Only Team_leader and Quality can update assignees
        if (!in_array($role, ['Team_leader', 'Quality'])) {
            flash('error', 'You do not have permission to update task assignees.');
            redirect('tasks');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assigneeIds = $_POST['assignee_ids'] ?? [];

            if (empty($assigneeIds)) {
                flash('error', 'Please select at least one assignee.');
                redirect('tasks/show/' . $id);
                return;
            }

            if ($this->taskService->updateTaskAssignees($id, $assigneeIds)) {
                flash('success', 'Task assignees updated successfully.');
            } else {
                flash('error', 'Failed to update task assignees.');
            }
            redirect('tasks/show/' . $id);
        }
    }

    public function deleteAttachment($attachmentId)
    {
        // Check if request is AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            // Get attachment info to verify ownership and get file path
            $taskModel = $this->model('Task/Task');
            $attachments = $taskModel->getAttachmentsByIds([$attachmentId]);

            if (empty($attachments)) {
                echo json_encode(['success' => false, 'message' => 'Attachment not found']);
                return;
            }

            $attachment = $attachments[0];

            // Delete from database
            if ($taskModel->deleteAttachment($attachmentId)) {
                // Delete physical file
                $filePath = dirname(APPROOT) . '/public/uploads/tasks/' . basename($attachment['file_path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                echo json_encode(['success' => true, 'message' => 'Attachment deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete attachment from database']);
            }
        } catch (Exception $e) {
            error_log('Delete attachment error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the attachment']);
        }
    }

    public function delete($id)
    {
        $task = $this->taskService->getTaskDetails($id);
        if (!$task) {
            flash('error', 'Task not found.');
            redirect('tasks');
            return;
        }

        // Check permissions - only admin and developer can delete tasks
        if (!in_array($_SESSION['user']['role_name'] ?? '', ['admin', 'developer'])) {
            flash('error', 'You do not have permission to delete tasks.');
            redirect('tasks/show/' . $id);
            return;
        }

        // Delete attachments first
        if (!empty($task['attachments'])) {
            $taskModel = $this->model('Task/Task');
            foreach ($task['attachments'] as $attachment) {
                // Delete from database
                $taskModel->deleteAttachment($attachment['id']);

                // Delete physical file
                $filePath = dirname(APPROOT) . '/public/uploads/tasks/' . basename($attachment['file_path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // Delete task (this will also delete assignees and comments due to foreign keys)
        if ($this->taskService->deleteTask($id)) {
            flash('success', 'Task deleted successfully.');
            redirect('tasks');
        } else {
            flash('error', 'Failed to delete task.');
            redirect('tasks/show/' . $id);
        }
    }
}
