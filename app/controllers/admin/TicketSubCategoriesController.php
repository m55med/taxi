<?php

namespace App\Controllers\Admin;

use App\Models\Admin\TicketSubCategory;
use App\Models\Admin\TicketCategory;
use App\Core\Auth;
use App\Core\Controller;

class TicketSubCategoriesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $ticketSubCategoryModel = new TicketSubCategory();
        $ticketCategoryModel = new TicketCategory();

        $ticket_subcategories = $ticketSubCategoryModel->getAll();
        $ticket_categories = $ticketCategoryModel->getAll();
        
        $data = [
            'ticket_subcategories' => $ticket_subcategories,
            'ticket_categories' => $ticket_categories,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/ticket_subcategories/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['category_id'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            
            if (!empty($name) && $category_id) {
                $ticketSubCategoryModel = new TicketSubCategory();
                if ($ticketSubCategoryModel->create($name, $category_id)) {
                    $_SESSION['message'] = 'تمت إضافة التصنيف الفرعي بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن التصنيف الفرعي موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'البيانات المدخلة غير صالحة.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_subcategories');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketSubCategoryModel = new TicketSubCategory();
            if ($ticketSubCategoryModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف التصنيف الفرعي بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف التصنيف الفرعي.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_subcategories');
        exit;
    }
}
