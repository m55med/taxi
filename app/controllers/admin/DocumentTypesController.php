<?php

namespace App\Controllers\Admin;

use App\Models\Admin\DocumentType;
use App\Core\Auth;
use App\Core\Controller;

class DocumentTypesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $documentTypeModel = new DocumentType();
        $document_types = $documentTypeModel->getAll();
        
        $data = [
            'document_types' => $document_types,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/document_types/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim(htmlspecialchars($_POST['name']));
            
            if (!empty($name)) {
                $documentTypeModel = new DocumentType();
                if ($documentTypeModel->create($name)) {
                    $_SESSION['message'] = 'تمت إضافة نوع المستند بنجاح.';
                } else {
                    $_SESSION['error'] = 'حدث خطأ أو أن نوع المستند موجود بالفعل.';
                }
            } else {
                $_SESSION['error'] = 'اسم نوع المستند لا يمكن أن يكون فارغًا.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/document_types');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentTypeModel = new DocumentType();
            if ($documentTypeModel->delete($id)) {
                $_SESSION['message'] = 'تم حذف نوع المستند بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حذف نوع المستند.';
            }
        }
        header('Location: ' . BASE_PATH . '/admin/document_types');
        exit;
    }
} 