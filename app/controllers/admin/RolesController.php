<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Role;
use App\Core\Auth;
use App\Core\Controller;

class RolesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $roleModel = new Role();
        $roles = $roleModel->getAll();
        
        $data = [
            'roles' => $roles,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/roles/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $roleModel = new Role();
                if ($roleModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة الدور بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن الدور موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم الدور لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/roles');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Avoid deleting essential roles, e.g., admin (id=1)
            if ($id == 1) {
                $_SESSION['error'] = 'لا يمكن حذف دور المدير الأساسي.';
                header('Location: ' . BASE_PATH . '/admin/roles');
                exit;
            }

            $roleModel = new Role();
            if ($roleModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف الدور بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف الدور. قد يكون الدور مستخدمًا حاليًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/roles');
        exit;
    }
} 