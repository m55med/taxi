<?php

namespace App\Controllers\Admin;

use App\Models\Admin\TicketCategory;
use App\Core\Auth;
use App\Core\Controller;

class TicketCategoriesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $ticketCategoryModel = new TicketCategory();
        $ticket_categories = $ticketCategoryModel->getAll();
        
        $data = [
            'ticket_categories' => $ticket_categories,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/ticket_categories/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $ticketCategoryModel = new TicketCategory();
                if ($ticketCategoryModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة التصنيف بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن التصنيف موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم التصنيف لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_categories');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketCategoryModel = new TicketCategory();
            if ($ticketCategoryModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف التصنيف بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف التصنيف.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_categories');
        exit;
    }
} 