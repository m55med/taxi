<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User\User;
use App\Models\Admin\Role;

class UsersController extends Controller {
    private $userModel;
    private $roleModel;

    public function __construct() {
        Auth::checkAdmin();
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function index() {
        $users = $this->userModel->getAllUsers();
        
        $data = [
            'users' => $users,
            'title' => 'User Management'
        ];

        $this->view('admin/users/index', $data);
    }

    public function add() {
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
                'email' => trim($_POST['email']),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role_id' => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
                'status' => $_POST['status']
            ];

            if ($this->userModel->isUsernameExists($data['username'])) {
                $_SESSION['user_message'] = 'Username already exists.';
                $_SESSION['user_message_type'] = 'error';
            } elseif ($this->userModel->isEmailExists($data['email'])) {
                $_SESSION['user_message'] = 'Email already exists.';
                $_SESSION['user_message_type'] = 'error';
            } elseif ($this->userModel->createUser($data)) {
                $_SESSION['user_message'] = 'User added successfully.';
                $_SESSION['user_message_type'] = 'success';
            } else {
                $_SESSION['user_message'] = 'Failed to add user.';
                $_SESSION['user_message_type'] = 'error';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/users');
        exit;
    }

    public function edit($id) {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            // Handle user not found
            header('Location: ' . BASE_PATH . '/admin/users');
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
                'id' => $id,
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'role_id' => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
                'status' => $_POST['status']
            ];

            // Optional password update
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            if ($this->userModel->updateUser($id, $data)) {
                $_SESSION['user_message'] = 'User updated successfully.';
                $_SESSION['user_message_type'] = 'success';
            } else {
                $_SESSION['user_message'] = 'Failed to update user.';
                $_SESSION['user_message_type'] = 'error';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/users');
        exit;
    }

    public function delete($id) {
        if ($this->userModel->deleteUser($id)) {
            $_SESSION['user_message'] = 'User deleted successfully.';
            $_SESSION['user_message_type'] = 'success';
        } else {
            $_SESSION['user_message'] = 'Failed to delete user.';
            $_SESSION['user_message_type'] = 'error';
        }
        header('Location: ' . BASE_PATH . '/admin/users');
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