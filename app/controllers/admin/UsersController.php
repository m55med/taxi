<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User\User;
use App\Models\Admin\Role;
use App\Models\Admin\Permission;
use App\Services\ActiveUserService;

class UsersController extends Controller {
    private $userModel;
    private $roleModel;
    private $permissionModel;
    private $activeUserService;

    public function __construct() {
        Auth::checkAdmin();
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
        require_once APPROOT . '/services/ActiveUserService.php';
        $this->activeUserService = new ActiveUserService();
    }

    public function index() {
        $users = $this->userModel->getAllUsers();
        $onlineUserIds = $this->activeUserService->getOnlineUserIds();
        $userStats = $this->userModel->getUserStats(); // Get user statistics

        foreach ($users as &$user) {
            $user->is_online = in_array($user->id, $onlineUserIds);
        }
        
        $data = [
            'users' => $users,
            'stats' => $userStats, // Pass stats to the view
            'title' => 'User Management'
        ];

        $this->view('admin/users/index', $data);
    }

    public function create() {
        $data = [
            'roles' => $this->roleModel->getAll(),
            'title' => 'Add New User'
        ];
        $this->view('admin/users/add', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username']),
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'], // No hashing here
                'role_id' => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
                'status' => $_POST['status']
            ];

            if ($this->userModel->isUsernameExists($data['username'])) {
                $_SESSION['user_message'] = 'Username already exists.';
                $_SESSION['user_message_type'] = 'error';
                header('Location: ' . URLROOT . '/admin/users/create'); // Redirect back to form
                exit;
            }
            
            if ($this->userModel->isEmailExists($data['email'])) {
                $_SESSION['user_message'] = 'Email already exists.';
                $_SESSION['user_message_type'] = 'error';
                header('Location: ' . URLROOT . '/admin/users/create'); // Redirect back to form
                exit;
            }

            $newUserId = $this->userModel->createUser($data);
            if ($newUserId) {
                $_SESSION['user_message'] = 'User added successfully.';
                $_SESSION['user_message_type'] = 'success';
            } else {
                $_SESSION['user_message'] = 'Failed to add user.';
                $_SESSION['user_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/users');
        exit;
    }

    public function edit($id) {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            // Handle user not found
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        $data = [
            'user' => $user,
            'roles' => $this->roleModel->getAll(),
            'title' => 'Edit User'
        ];
        $this->view('admin/users/edit', $data);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'role_id' => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
                'status' => $_POST['status']
            ];

            // Optional password update
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password']; // No hashing here
            }
            
            $result = $this->userModel->updateUser($id, $data);

            if ($result['status']) {
                $_SESSION['user_message'] = $result['message'];
                $_SESSION['user_message_type'] = 'success';
            } else {
                $_SESSION['user_message'] = $result['message'] ?? 'Failed to update user.';
                $_SESSION['user_message_type'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/users');
        exit;
    }

    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
             header('Location: ' . URLROOT . '/admin/users');
             exit;
        }
        $id = $_POST['id'];

        if ($this->userModel->deleteUser($id)) {
            $_SESSION['user_message'] = 'User deleted successfully.';
            $_SESSION['user_message_type'] = 'success';
        } else {
            $_SESSION['user_message'] = 'Failed to delete user.';
            $_SESSION['user_message_type'] = 'error';
        }
        // This should redirect to the index page which will show the flash message
        header('Location: ' . URLROOT . '/admin/users');
        exit;
    }

    public function forceLogout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $message = $_POST['message'] ?? '1'; // Default message is '1' if not provided

            if ($userId) {
                $forceLogoutDir = APPROOT . '/database/force_logout/';
                if (!is_dir($forceLogoutDir)) {
                    mkdir($forceLogoutDir, 0755, true);
                }
                
                $logoutFile = $forceLogoutDir . $userId;
                if (file_put_contents($logoutFile, $message) !== false) {
                    $this->sendJsonResponse(['status' => 'success', 'message' => 'User will be logged out on their next action.']);
                } else {
                    $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to write logout signal file.'], 500);
                }
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid user ID.'], 400);
            }
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid request.'], 400);
        }
    }
} 