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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(htmlspecialchars($_POST['name']));
            if (!empty($name)) {
                $ticketCategoryModel = new TicketCategory();
                if ($ticketCategoryModel->create($name)) {
                    $_SESSION['ticket_category_message'] = 'Category created successfully.';
                    $_SESSION['ticket_category_message_type'] = 'success';
                } else {
                    $_SESSION['ticket_category_message'] = 'Error creating category.';
                    $_SESSION['ticket_category_message_type'] = 'error';
                }
            } else {
                $_SESSION['ticket_category_message'] = 'Category name cannot be empty.';
                $_SESSION['ticket_category_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_categories');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketCategoryModel = new TicketCategory();
            if ($ticketCategoryModel->delete($id)) {
                $_SESSION['ticket_category_message'] = 'Category deleted successfully.';
                $_SESSION['ticket_category_message_type'] = 'success';
            } else {
                $_SESSION['ticket_category_message'] = 'Error deleting category. It might be in use.';
                $_SESSION['ticket_category_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_categories');
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(htmlspecialchars($_POST['name']));
            if (!empty($name)) {
                $ticketCategoryModel = new TicketCategory();
                if ($ticketCategoryModel->update($id, $name)) {
                    $_SESSION['ticket_category_message'] = 'Category updated successfully.';
                    $_SESSION['ticket_category_message_type'] = 'success';
                } else {
                    $_SESSION['ticket_category_message'] = 'Error updating category.';
                    $_SESSION['ticket_category_message_type'] = 'error';
                }
            } else {
                $_SESSION['ticket_category_message'] = 'Category name cannot be empty.';
                $_SESSION['ticket_category_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_categories');
    }
} 