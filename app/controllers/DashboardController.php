<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        }

    public function index()
    {
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        // إذا كان المستخدم مدير، قم بتحميل إحصائيات المستخدمين
        $data = [
            'title' => 'لوحة التحكم'
        ];

        if ($_SESSION['role'] === 'admin') {
            $data['quickStats'] = [
                'total_users' => $this->userModel->countUsers(),
                'active_users' => $this->userModel->countUsersByStatus('active'),
                'online_users' => $this->userModel->countOnlineUsers(),
                'blocked_users' => $this->userModel->countUsersByStatus('banned')
            ];
        }

        $this->view('dashboard/index', $data);
    }

    public function users()
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // جلب قائمة المستخدمين والأدوار
        $users = $this->userModel->getAllUsers();
        $roles = $this->userModel->getRoles(); // إضافة استدعاء دالة جلب الأدوار
        
        $data = [
            'users' => $users,
            'roles' => $roles,
            'title' => 'إدارة المستخدمين'
        ];

        $this->view('admin/users/index', $data);
    }

    public function addUser()
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // جلب قائمة الأدوار
        $roles = $this->userModel->getRoles();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // معالجة إضافة المستخدم
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role_id = $_POST['role_id'] ?? 3;
            $status = $_POST['status'] ?? 'pending';

            $result = $this->userModel->register([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role_id' => $role_id,
                'status' => $status
            ]);

            if ($result['status']) {
                $_SESSION['success'] = 'تم إضافة المستخدم بنجاح';
                header('Location: ' . BASE_PATH . '/dashboard/users');
                exit;
            } else {
                $data = [
                    'error' => $result['message'],
                    'roles' => $roles,
                    'title' => 'إضافة مستخدم جديد'
                ];
                $this->view('admin/users/sections/addUser', $data);
            }
        } else {
            $data = [
                'roles' => $roles,
                'title' => 'إضافة مستخدم جديد'
            ];
            $this->view('admin/users/sections/addUser', $data);
        }
    }

    public function editUser($id = null)
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        if ($id === null) {
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        // جلب قائمة الأدوار
        $roles = $this->userModel->getRoles();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // التحقق مما إذا كان المستخدم يحاول تعديل نفسه
            if ($id == $_SESSION['user_id']) {
                // السماح فقط بتغيير كلمة المرور
                $data = [
                    'password' => $_POST['password'] ?? ''
                ];
            } else {
                // تعديل جميع البيانات للمستخدمين الآخرين
                $data = [
                    'username' => trim($_POST['username'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'role_id' => $_POST['role_id'] ?? null,
                    'status' => $_POST['status'] ?? null
                ];
            }

            $result = $this->userModel->updateUser($id, $data);

            if ($result['status']) {
                $_SESSION['success'] = 'تم تحديث المستخدم بنجاح';
                header('Location: ' . BASE_PATH . '/dashboard/users');
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
            }
        }

        // جلب بيانات المستخدم
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'المستخدم غير موجود';
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        $data = [
            'user' => $user,
            'roles' => $roles,
            'title' => 'تعديل المستخدم'
        ];
        
        $this->view('admin/users/sections/editUser', $data);
    }

    public function deleteUser($id = null)
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        if ($id === null) {
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        // لا يمكن للمدير حذف نفسه
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'لا يمكنك حذف حسابك الخاص';
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        $result = $this->userModel->deleteUser($id);
        if ($result['status']) {
            $_SESSION['success'] = 'تم حذف المستخدم بنجاح';
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . BASE_PATH . '/dashboard/users');
        exit;
    }

    public function forceLogout($id = null)
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        if ($id === null) {
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        // لا يمكن للمدير إجبار نفسه على الخروج
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'لا يمكنك إجبار نفسك على تسجيل الخروج';
            header('Location: ' . BASE_PATH . '/dashboard/users');
            exit;
        }

        // إنشاء مجلد force_logout إذا لم يكن موجودًا
        $logoutDir = APPROOT . '/database/force_logout';
        if (!is_dir($logoutDir)) {
            mkdir($logoutDir, 0755, true);
        }

        // إنشاء ملف الإشارة لإجبار تسجيل الخروج
        $forceFile = $logoutDir . '/' . $id;
        if (file_put_contents($forceFile, '1')) {
            $_SESSION['success'] = 'سيتم إجبار المستخدم على تسجيل الخروج في طلبه التالي.';
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء محاولة إجبار المستخدم على الخروج.';
        }

        header('Location: ' . BASE_PATH . '/dashboard/users');
        exit;
    }

    public function updateUserRole($userId, $roleId)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => false, 'message' => 'غير مصرح لك بهذا الإجراء']);
            return;
        }

        // التحقق من أن المستخدم لا يحاول تغيير دوره
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['status' => false, 'message' => 'لا يمكنك تغيير دورك']);
            return;
        }

        $userModel = new User();
        $result = $userModel->updateUserRole($userId, $roleId);
        echo json_encode($result);
    }

    public function updateUserStatus($userId, $status)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => false, 'message' => 'غير مصرح لك بهذا الإجراء']);
            return;
        }

        // التحقق من أن المستخدم لا يحاول تغيير حالته
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['status' => false, 'message' => 'لا يمكنك تغيير حالتك']);
            return;
        }

        // التحقق من صحة الحالة
        $validStatuses = ['active', 'banned', 'pending'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['status' => false, 'message' => 'حالة غير صالحة']);
            return;
        }

        $userModel = new User();
        $result = $userModel->updateUserStatus($userId, $status);
        echo json_encode($result);
    }

    public function changePassword()
    {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // التحقق من تطابق كلمة المرور الجديدة
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'كلمة المرور الجديدة غير متطابقة';
                header('Location: ' . BASE_PATH . '/dashboard/changePassword');
                exit;
            }

            // التحقق من كلمة المرور الحالية وتحديث كلمة المرور
            $result = $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);

            if ($result['status']) {
                $_SESSION['success'] = 'تم تغيير كلمة المرور بنجاح';
                header('Location: ' . BASE_PATH . '/dashboard/users');
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
                header('Location: ' . BASE_PATH . '/dashboard/changePassword');
                exit;
            }
        }

        $this->view('admin/users/sections/changePassword', ['title' => 'تغيير كلمة المرور']);
    }
}
