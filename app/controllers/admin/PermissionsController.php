<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;

class PermissionsController extends Controller
{
    private $permissionModel;
    private $roleModel;

    public function __construct()
    {
        $this->permissionModel = new Permission();
        $this->roleModel = new Role();
    }

    public function index()
    {
        $roles = $this->roleModel->getAll();
        $modules = $this->permissionModel->discoverControllers();
        
        $permissions = [];
        foreach ($roles as $role) {
            $permissions[$role['id']] = $this->permissionModel->getPermissionsByRole($role['id']);
        }

        $data = [
            'page_main_title' => 'إدارة الصلاحيات',
            'roles' => $roles,
            'modules' => $modules,
            'permissions' => $permissions
        ];

        $this->view('admin/permissions/index', $data);
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permissions = $_POST['permissions'] ?? [];
            $allRoles = $this->roleModel->getAll();

            $allSuccess = true;
            foreach ($allRoles as $role) {
                $roleId = $role['id'];
                $rolePermissions = $permissions[$roleId] ?? [];
                if (!$this->permissionModel->savePermissions($roleId, $rolePermissions)) {
                    $allSuccess = false;
                }
            }

            if ($allSuccess) {
                $_SESSION['success'] = 'تم حفظ الصلاحيات بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء حفظ بعض الصلاحيات.';
            }

            header('Location: ' . BASE_PATH . '/admin/permissions');
            exit;
        }
    }
} 