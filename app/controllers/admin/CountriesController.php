<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Country;
use App\Core\Auth;
use App\Core\Controller;

class CountriesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $countryModel = new Country();
        $countries = $countryModel->getAll();
        
        $data = [
            'countries' => $countries,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/countries/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $countryModel = new Country();
                if ($countryModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة الدولة بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن الدولة موجودة بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم الدولة لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/countries');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $countryModel = new Country();
            if ($countryModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف الدولة بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف الدولة.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/countries');
        exit;
    }
}
