<?php
class AdminController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->users();
    }

    public function users() {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // جلب قائمة المستخدمين
        $users = $this->userModel->getAllUsers();
        
        $data = [
            'users' => $users,
            'title' => 'إدارة المستخدمين'
        ];

        $this->view('admin/users', $data);
    }
} 