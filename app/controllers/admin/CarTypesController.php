<?php

namespace App\Controllers\Admin;

use App\Models\Admin\CarType;
use App\Core\Auth;
use App\Core\Controller;

class CarTypesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $carTypeModel = new CarType();
        $car_types = $carTypeModel->getAll();
        
        $data = [
            'car_types' => $car_types,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/car_types/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $carTypeModel = new CarType();
                if ($carTypeModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة نوع السيارة بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن نوع السيارة موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم نوع السيارة لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/car_types');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $carTypeModel = new CarType();
            if ($carTypeModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف نوع السيارة بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف نوع السيارة.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/car_types');
        exit;
    }
} 