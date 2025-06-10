<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Platform;
use App\Core\Auth;
use App\Core\Controller;

class PlatformsController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $platformModel = new Platform();
        $platforms = $platformModel->getAll();
        
        // Pass data to the view
        $data = [
            'platforms' => $platforms,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        // Clear session messages
        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/platforms/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $platformModel = new Platform();
                if ($platformModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة المنصة بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن المنصة موجودة بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم المنصة لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/platforms');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $platformModel = new Platform();
            if ($platformModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف المنصة بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف المنصة.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/platforms');
        exit;
    }
} 