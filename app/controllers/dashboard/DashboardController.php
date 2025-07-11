<?php

namespace App\Controllers\Dashboard;

use App\Core\Controller;

class DashboardController extends Controller
{
    private $userModel;
    private $dashboardModel;
    private $driverModel;

    public function __construct()
    {
        // Require login for all methods in this controller
        \App\Core\Auth::requireLogin();
        
        parent::__construct();
        $this->userModel = $this->model('user/User');
        $this->driverModel = $this->model('driver/Driver');
        $this->dashboardModel = $this->model('dashboard/Dashboard');
    }

    public function index()
    {
        // Release any drivers that have been on hold for more than 5 minutes
        // $this->driverModel->releaseHeldDrivers();

        // Check for login is now in the constructor

        // Handle Date Filtering
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        
        $quickFilterDates = [
            'today' => date('Y-m-d'),
            'last7days' => date('Y-m-d', strtotime('-6 days')),
            'last30days' => date('Y-m-d', strtotime('-29 days')),
        ];

        // Fetch all dashboard data using the new model method
        $dashboardData = $this->dashboardModel->getDashboardData($startDate, $endDate);

        $data = [
            'title' => 'Dashboard',
            'dashboardData' => $dashboardData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'quickFilterDates' => $quickFilterDates,
        ];
        
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $userId = $_POST['user_id'] ?? null;
            $message = trim($_POST['message'] ?? '');

            if (!$userId || $userId == $_SESSION['user_id']) {
                echo json_encode(['status' => false, 'message' => 'طلب غير صالح.']);
                exit;
            }
            
            $logoutDir = APPROOT . '/database/force_logout';
            if (!is_dir($logoutDir)) {
                mkdir($logoutDir, 0755, true);
            }

            $adminUsername = $_SESSION['username'] ?? 'Admin';
            $fullMessage = $message . ' (بواسطة: ' . $adminUsername . ')';

            $forceFile = $logoutDir . '/' . $userId;
            if (file_put_contents($forceFile, $fullMessage)) {
                echo json_encode(['status' => true, 'message' => 'سيتم إجبار المستخدم على تسجيل الخروج في طلبه التالي.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'حدث خطأ أثناء محاولة إجبار المستخدم على الخروج.']);
            }
            exit;
        }

        // The GET request part remains for backward compatibility or direct access, though it won't have a custom message
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
        if (file_put_contents($forceFile, '1')) { // "1" as a default message
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
