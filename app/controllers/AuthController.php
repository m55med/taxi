<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // التحقق من البيانات المدخلة
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // التحقق من صحة البيانات
            if (empty($username) || empty($email) || empty($password)) {
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
                'email' => $email,
                'password' => $password
            ];

            $result = $this->userModel->register($data);

            if ($result['status']) {
                $_SESSION['success'] = 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.';
                header('Location: ' . BASE_PATH . '/auth/login');
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
            header('Location: ' . BASE_PATH . '/dashboard');
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

            if (!isset($result['error'])) {
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['role'] = $result['role'];
                $_SESSION['role_id'] = $result['role_id'];
                $_SESSION['is_online'] = true;
                
                header('Location: ' . BASE_PATH . '/dashboard');
                exit();
            } else {
                $this->view('auth/login', ['error' => $result['error']]);
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
            
            // تحديث حالة المستخدم إلى غير متصل
            $this->userModel->logout($userId);
            
            // تحرير السائقين المحجوزين
            $callModel = $this->model('call/Calls');
            $callModel->releaseAllHeldDrivers($userId);
            
            // تدمير الجلسة وإزالة جميع المتغيرات
            session_unset();
            session_destroy();
        }
        
        header('Location: ' . BASE_PATH . '/auth/login');
        exit();
    }
}
