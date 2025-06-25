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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

            if (!empty($name) && $categoryId) {
                $ticketSubCategoryModel = new TicketSubCategory();
                if ($ticketSubCategoryModel->create($name, $categoryId)) {
                    $_SESSION['ticket_subcategory_message'] = 'Subcategory created successfully.';
                    $_SESSION['ticket_subcategory_message_type'] = 'success';
                } else {
                    $_SESSION['ticket_subcategory_message'] = 'Error creating subcategory.';
                    $_SESSION['ticket_subcategory_message_type'] = 'error';
                }
            } else {
                $_SESSION['ticket_subcategory_message'] = 'Subcategory name and category are required.';
                $_SESSION['ticket_subcategory_message_type'] = 'error';
            }
        }
        header("Location: " . BASE_PATH . "/admin/ticket_subcategories");
        exit();
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketSubCategoryModel = new TicketSubCategory();
            if ($ticketSubCategoryModel->delete($id)) {
                $_SESSION['ticket_subcategory_message'] = 'Subcategory deleted successfully.';
                $_SESSION['ticket_subcategory_message_type'] = 'success';
            } else {
                $_SESSION['ticket_subcategory_message'] = 'Error deleting subcategory. It might be in use.';
                $_SESSION['ticket_subcategory_message_type'] = 'error';
            }
        }
        header("Location: " . BASE_PATH . "/admin/ticket_subcategories");
        exit();
    }
}
