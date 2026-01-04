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
        Auth::requireRole(['admin', 'developer']);
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
        require_once APPROOT . '/Services/ActiveUserService.php';
        $this->activeUserService = new ActiveUserService();
    }

    public function index() {
        // Cleanup inactive users first to ensure accurate count
        $this->activeUserService->cleanupInactiveUsers();
        
        // Get all users and online user IDs
        $users = $this->userModel->getAllUsers();
        
        // Remove duplicates by ID (extra safety check)
        $uniqueUsers = [];
        $seenIds = [];
        foreach ($users as $user) {
            $userId = is_object($user) ? $user->id : $user['id'];
            if (!in_array($userId, $seenIds)) {
                $uniqueUsers[] = $user;
                $seenIds[] = $userId;
            } else {
                error_log("Duplicate user found: ID " . $userId);
            }
        }
        $users = $uniqueUsers;
        
        $onlineUserIds = $this->activeUserService->getOnlineUserIds();
        
        // Get user statistics
        $userStats = $this->userModel->getUserStats();

        // Mark users as online/offline based on ActiveUserService
        foreach ($users as &$user) {
            $user->is_online = in_array($user->id, $onlineUserIds);
        }
        
        // IMPORTANT: Count actual online users from the users array (not from onlineUserIds)
        // This ensures consistency between the stats card and the table
        // Some users in onlineUserIds might not be in the users array (deleted, banned, etc.)
        $actualOnlineCount = 0;
        foreach ($users as $user) {
            if (!empty($user->is_online)) {
                $actualOnlineCount++;
            }
        }
        
        // Debug: Log the counts for troubleshooting
        error_log("UsersController - Online User IDs from ActiveUserService: " . count($onlineUserIds));
        error_log("UsersController - Users in array: " . count($users));
        error_log("UsersController - Actual Online Count (from users array): " . $actualOnlineCount);
        error_log("UsersController - Database Online Count (from getUserStats): " . ($userStats['online_users'] ?? 'N/A'));
        
        // Override with the actual count from users array
        $userStats['online_users'] = $actualOnlineCount;
        
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

    /**
     * Display the VIP Users management page
     */
    public function addVip() {
        // Get VIP users (role_id = 11)
        $vipUsers = $this->userModel->getUsersByRole(11);
        
        $data = [
            'vip_users' => $vipUsers,
            'title' => 'VIP Users Management'
        ];

        $this->view('admin/users/add_vip', $data);
    }

    /**
     * Store a new VIP user with random email and password
     */
    public function storeVip() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $_SESSION['vip_user_message'] = 'الاسم مطلوب.';
                $_SESSION['vip_user_message_class'] = 'error';
                header('Location: ' . URLROOT . '/admin/users/vip');
                exit;
            }

            // Generate random email and password
            $randomEmail = $this->generateRandomEmail($name);
            $randomPassword = $this->generateRandomPassword();
            $username = $this->generateUsername($name);

            $data = [
                'username' => $username,
                'name' => $name,
                'email' => $randomEmail,
                'password' => $randomPassword, 
                'role_id' => 11, // VIP role
                'status' => 'active'
            ];

            // Check if username exists
            if ($this->userModel->isUsernameExists($data['username'])) {
                // Add random numbers to make it unique
                $data['username'] = $username . '_' . rand(100, 999);
            }

            $newUserId = $this->userModel->createUser($data);
            if ($newUserId) {
                $_SESSION['vip_user_message'] = 'تم إضافة المستخدم VIP بنجاح. البريد الإلكتروني: ' . $randomEmail . ' | كلمة المرور: ' . $randomPassword;
                $_SESSION['vip_user_message_class'] = 'success';
            } else {
                $_SESSION['vip_user_message'] = 'فشل في إضافة المستخدم VIP.';
                $_SESSION['vip_user_message_class'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/users/vip');
        exit;
    }

    /**
     * Delete a VIP user
     */
    public function deleteVip($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify that user has role_id = 11 (VIP)
            $user = $this->userModel->getUserById($id);
            if (!$user || $user->role_id != 11) {
                $_SESSION['vip_user_message'] = 'مستخدم غير صحيح أو ليس من نوع VIP.';
                $_SESSION['vip_user_message_class'] = 'error';
                header('Location: ' . URLROOT . '/admin/users/vip');
                exit;
            }

            if ($this->userModel->deleteUser($id)) {
                $_SESSION['vip_user_message'] = 'تم حذف المستخدم VIP بنجاح.';
                $_SESSION['vip_user_message_class'] = 'success';
            } else {
                $_SESSION['vip_user_message'] = 'فشل في حذف المستخدم VIP.';
                $_SESSION['vip_user_message_class'] = 'error';
            }
        }
        header('Location: ' . URLROOT . '/admin/users/vip');
        exit;
    }

    /**
     * Generate a random email based on username
     */
    private function generateRandomEmail($name) {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com'];
        $domain = $domains[array_rand($domains)];
        $randomNumber = rand(100, 9999);
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', transliterator_transliterate('Any-Latin; Latin-ASCII', $name));
        return strtolower($cleanName . $randomNumber . '@' . $domain);
    }

    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Generate username from name
     */
    private function generateUsername($name) {
        // Remove special characters and spaces, convert to lowercase
        $username = preg_replace('/[^a-zA-Z0-9]/', '', transliterator_transliterate('Any-Latin; Latin-ASCII', $name));
        return strtolower($username);
    }

    public function changePassword($id) {
        // Get user info
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect('admin/users');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            // Validation
            if (empty($newPassword)) {
                flash('error', 'Password is required.');
                redirect('admin/users/change_password/' . $id);
                return;
            }

            if (strlen($newPassword) < 6) {
                flash('error', 'Password must be at least 6 characters long.');
                redirect('admin/users/change_password/' . $id);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                flash('error', 'Passwords do not match.');
                redirect('admin/users/change_password/' . $id);
                return;
            }

            // Update password
            if ($this->userModel->updatePasswordByEmail($user->email, $newPassword)) {
                flash('success', 'Password updated successfully.');
                redirect('admin/users');
            } else {
                flash('error', 'Failed to update password.');
                redirect('admin/users/change_password/' . $id);
            }
        } else {
            // Show form
            $data = [
                'page_main_title' => 'Change User Password',
                'user' => $user
            ];

            $this->view('admin/users/change_password', $data);
        }
    }
} 