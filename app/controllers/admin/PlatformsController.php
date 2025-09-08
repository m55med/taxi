<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

class PlatformsController extends Controller {
    private $platformModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->platformModel = $this->model('Admin/Platform');
    }

    public function index() {
        $platforms = $this->platformModel->getAll();
        
        $data = [
            'platforms' => $platforms
        ];
        
        $this->view('admin/platforms/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            
            if (!empty($name)) {
                if ($this->platformModel->findByName($name)) {
                    flash('platform_message', 'Platform with this name already exists.', 'error');
                } else if ($this->platformModel->create($name)) {
                    flash('platform_message', 'Platform added successfully.', 'success');
                } else {
                    flash('platform_message', 'An error occurred while adding the platform.', 'error');
                }
            } else {
                flash('platform_message', 'Platform name cannot be empty.', 'error');
            }
        }
        redirect('/admin/platforms');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));

            if (!empty($name) && !empty($id)) {
                $existing = $this->platformModel->findByName($name);
                if ($existing && $existing['id'] != $id) {
                    flash('platform_message', 'Another platform with this name already exists.', 'error');
                } else if ($this->platformModel->update($id, $name)) {
                    flash('platform_message', 'Platform updated successfully.', 'success');
                } else {
                    flash('platform_message', 'An error occurred or no changes were made.', 'error');
                }
            } else {
                flash('platform_message', 'Platform name cannot be empty.', 'error');
            }
        }
        redirect('/admin/platforms');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            if ($this->platformModel->delete($id)) {
                flash('platform_message', 'Platform deleted successfully.', 'success');
            } else {
                flash('platform_message', 'An error occurred while deleting the platform.', 'error');
            }
        }
        redirect('/admin/platforms');
    }
} 