<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Services\ActiveUserService;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('User/User');
        if (!$this->userModel) { die('❌ Failed to load user model'); } // Debugging line
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // التحقق من البيانات المدخلة
            $username = trim($_POST['username'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // التحقق من صحة البيانات
            if (empty($username) || empty($name) || empty($email) || empty($password)) {
                $this->view('auth/register', ['error' => 'جميع الحقول مطلوبة']);
                return;
            }

            if (strlen($username) < 4) {
                $this->view('auth/register', ['error' => 'يجب أن يكون اسم المستخدم 4 أحرف على الأقل']);
                return;
            }

            if (strlen($password) < 6) {
                $this->view('auth/register', ['error' => 'يجب أن تكون كلمة المرور 6 أحرف على الأقل']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view('auth/register', ['error' => 'البريد الإلكتروني غير صالح']);
                return;
            }

            $data = [
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'password' => $password
            ];

            $result = $this->userModel->register($data);

            if ($result['status']) {
                $_SESSION['success'] = 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.';
                header('Location: /auth/login');
                exit();
            } else {
                $this->view('auth/register', ['error' => $result['message']]);
            }
        } else {
            $this->view('auth/register');
        }
    }

    public function login()
    {
        // إذا كان المستخدم مسجل دخوله بالفعل، قم بتوجيهه إلى لوحة التحكم
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $this->view('auth/login', ['error' => 'يرجى إدخال اسم المستخدم وكلمة المرور']);
                return;
            }

            $result = $this->userModel->login($username, $password);

            if ($result['status'] && isset($result['user'])) {
                $user = $result['user'];
                // Create a clean session
                session_regenerate_id(true);

                // Store essential user data in the session
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['role_name'] = $user->role_name;
                $_SESSION['is_online'] = true;
                $_SESSION['last_activity'] = time();

                // Record user activity to mark them as online immediately
                $activeUserService = new ActiveUserService();
                $activeUserService->recordUserActivity($user->id);

                // Fetch and store permissions in the session
                $permissions = $this->userModel->getUserPermissions($user->id);
                $_SESSION['permissions'] = $permissions;

                header('Location: ' . BASE_PATH . '/dashboard');
                exit();
            } else {
                $this->view('auth/login', ['error' => $result['message'] ?? 'Invalid username or password.']);
            }
        } else {
            // احفظ رسالة الخطأ مؤقتاً وامسحها من الجلسة
            $error = $_SESSION['error'] ?? null;
            unset($_SESSION['error']);

            $this->view('auth/login', ['error' => $error]);
        }
    }

    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // Use ActiveUserService to handle logout
            $activeUserService = new ActiveUserService();
            $activeUserService->logoutUser($userId);

            // تحرير السائقين المحجوزين
            $callModel = $this->model('Calls/Call');
            if ($callModel) {
                $callModel->releaseAllHeldDrivers($userId);
            }

            // تدمير الجلسة وإزالة جميع المتغيرات
            session_unset();
            session_destroy();
        }

        header('Location: /auth/login');
        exit();
    }

    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            // User not found, handle appropriately
            session_unset();
            session_destroy();
            header('Location: /login');
            exit();
        }

        $this->view('profile/index', ['user' => $user]);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: /profile');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $data = [
            'id' => $userId,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            // Password is optional
            'password' => $_POST['password'] ?? ''
        ];

        // Basic validation
        if (empty($data['name']) || empty($data['email'])) {
            $_SESSION['error'] = 'Name and Email are required.';
            header('Location: ' . BASE_PATH . '/profile');
            exit();
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format.';
            header('Location: ' . BASE_PATH . '/profile');
            exit();
        }

        // If password is provided, it must meet length requirements
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters long.';
            header('Location: ' . BASE_PATH . '/profile');
            exit();
        }

        $result = $this->userModel->updateUser($userId, $data);

        if ($result['status']) {
            $_SESSION['success'] = 'Profile updated successfully.';
            // Update session with new name if it exists in the model response
            if (isset($result['name'])) {
                $_SESSION['name'] = $result['name'];
            }
        } else {
            $_SESSION['error'] = $result['message'] ?? 'Failed to update profile.';
        }

        header('Location: ' . BASE_PATH . '/profile');
        exit();
    }
}