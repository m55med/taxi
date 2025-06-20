<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class UsersController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('user/User');
    }

    public function index() {
        $this->users();
    }

    public function users() {
        // التحقق من صلاحيات المدير
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'developer'])) {
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