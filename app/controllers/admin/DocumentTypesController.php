<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

class DocumentTypesController extends Controller {
    private $documentTypeModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->documentTypeModel = $this->model('Admin/DocumentType');
    }

    public function index() {
        $document_types = $this->documentTypeModel->getAll();
        
        $data = [
            'document_types' => $document_types
        ];
        
        $this->view('admin/document_types/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            
            if (!empty($name)) {
                if ($this->documentTypeModel->findByName($name)) {
                    flash('document_type_message', 'Document type with this name already exists.', 'error');
                } else if ($this->documentTypeModel->create($name)) {
                    flash('document_type_message', 'Document type added successfully.', 'success');
                } else {
                    flash('document_type_message', 'An error occurred while adding the document type.', 'error');
                }
            } else {
                flash('document_type_message', 'Document type name cannot be empty.', 'error');
            }
        }
        redirect('/admin/document_types');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));

            if (!empty($name) && !empty($id)) {
                // Check if another document type with the same name exists
                $existing = $this->documentTypeModel->findByName($name);
                if ($existing && $existing['id'] != $id) {
                    flash('document_type_message', 'Another document type with this name already exists.', 'error');
                } else if ($this->documentTypeModel->update($id, $name)) {
                    flash('document_type_message', 'Document type updated successfully.', 'success');
                } else {
                    flash('document_type_message', 'An error occurred or no changes were made.', 'error');
                }
            } else {
                flash('document_type_message', 'Document type name cannot be empty.', 'error');
            }
        }
        redirect('/admin/document_types');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            if ($this->documentTypeModel->delete($id)) {
                flash('document_type_message', 'Document type deleted successfully.', 'success');
            } else {
                flash('document_type_message', 'An error occurred while deleting the document type.', 'error');
            }
        }
        redirect('/admin/document_types');
    }
} 