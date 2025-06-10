<?php

namespace App\Controllers\Admin;

use App\Models\Admin\TicketCode;
use App\Models\Admin\TicketSubCategory;
use App\Core\Auth;
use App\Core\Controller;

class TicketCodesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $ticketCodeModel = new TicketCode();
        $ticketSubCategoryModel = new TicketSubCategory();

        $ticket_codes = $ticketCodeModel->getAll();
        $ticket_subcategories = $ticketSubCategoryModel->getAll();
        
        $data = [
            'ticket_codes' => $ticket_codes,
            'ticket_subcategories' => $ticket_subcategories,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/ticket_codes/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['subcategory_id'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            $subcategory_id = filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT);
            
            if (!empty($name) && $subcategory_id) {
                $ticketCodeModel = new TicketCode();
                if ($ticketCodeModel->create($name, $subcategory_id)) {
                    $_SESSION['message'] = 'تمت إضافة الكود بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن الكود موجود بالفعل لهذا التصنيف الفرعي.';
                }
            } else {
                $_SESSION['error'] = 'البيانات المدخلة غير صالحة.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_codes');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketCodeModel = new TicketCode();
            if ($ticketCodeModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف الكود بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف الكود.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/ticket_codes');
        exit;
    }
} 