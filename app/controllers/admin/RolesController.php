<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Role;
use App\Core\Auth;
use App\Core\Controller;

class RolesController extends Controller {

    private $roleModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->roleModel = new Role();
    }

    public function index() {
        $data = [
            'roles' => $this->roleModel->getAll()
        ];
        $this->view('admin/roles/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                flash('role_message', 'Role name cannot be empty.', 'error');
            } elseif ($this->roleModel->findByName($name)) {
                flash('role_message', 'Role name already exists.', 'error');
            } else {
                if ($this->roleModel->create($name)) {
                    flash('role_message', 'Role added successfully.');
                } else {
                    flash('role_message', 'Failed to add role.', 'error');
                }
            }
        }
        redirect('/admin/roles');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                flash('role_message', 'Role name cannot be empty.', 'error');
                redirect('/admin/roles');
            }

            $existingRole = $this->roleModel->findByName($name);
            if ($existingRole && $existingRole['id'] != $id) {
                flash('role_message', 'Another role with this name already exists.', 'error');
            } else {
                if ($this->roleModel->update($id, $name)) {
                    flash('role_message', 'Role updated successfully.');
                } else {
                    flash('role_message', 'Failed to update role.', 'error');
                }
            }
        }
        redirect('/admin/roles');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id == 1) {
                flash('role_message', 'The Administrator role cannot be deleted.', 'error');
                redirect('/admin/roles');
            }

            if ($this->roleModel->delete($id)) {
                flash('role_message', 'Role deleted successfully.');
            } else {
                flash('role_message', 'Failed to delete role. It may be in use.', 'error');
            }
        }
        redirect('/admin/roles');
    }
} 