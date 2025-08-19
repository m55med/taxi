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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $subcategoryId = filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT);

            if (!empty($name) && $subcategoryId) {
                $ticketCodeModel = new TicketCode();
                if ($ticketCodeModel->create($name, $subcategoryId)) {
                    $_SESSION['ticket_code_message'] = 'Code created successfully.';
                    $_SESSION['ticket_code_message_type'] = 'success';
                } else {
                    $_SESSION['ticket_code_message'] = 'Error creating code.';
                    $_SESSION['ticket_code_message_type'] = 'error';
                }
            } else {
                $_SESSION['ticket_code_message'] = 'Code name and subcategory are required.';
                $_SESSION['ticket_code_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_codes');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketCodeModel = new TicketCode();
            if ($ticketCodeModel->delete($id)) {
                $_SESSION['ticket_code_message'] = 'Code deleted successfully.';
                $_SESSION['ticket_code_message_type'] = 'success';
            } else {
                $_SESSION['ticket_code_message'] = 'Error deleting code.';
                $_SESSION['ticket_code_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_codes');
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $subcategoryId = filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT);

            if (!empty($name) && $subcategoryId) {
                $ticketCodeModel = new TicketCode();
                if ($ticketCodeModel->update($id, $name, $subcategoryId)) {
                    $_SESSION['ticket_code_message'] = 'Code updated successfully.';
                    $_SESSION['ticket_code_message_type'] = 'success';
                } else {
                    $_SESSION['ticket_code_message'] = 'Error updating code.';
                    $_SESSION['ticket_code_message_type'] = 'error';
                }
            } else {
                $_SESSION['ticket_code_message'] = 'Code name and subcategory are required.';
                $_SESSION['ticket_code_message_type'] = 'error';
            }
        }
        redirect('admin/ticket_codes');
    }
} 