<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Permission;
use App\Models\User\User;

class PermissionsController extends Controller
{
    private $permissionModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->authorize(['admin', 'developer']);
        $this->permissionModel = $this->model('admin/Permission');
        $this->userModel = $this->model('user/User');
    }

    public function index()
    {
        // Sync permissions from files to DB on each visit
        $this->permissionModel->syncPermissions();

        $selectedRoleId = isset($_GET['role_id']) && is_numeric($_GET['role_id']) ? (int)$_GET['role_id'] : null;
        
        $users = [];
        $userPermissions = [];

        if ($selectedRoleId) {
            $users = $this->userModel->getUsersByRole($selectedRoleId);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $userPermissions[$user['id']] = $this->permissionModel->getPermissionsByUser($user['id']);
                }
            }
        }
        
        // Group permissions by a key (e.g., controller name)
        $allPermissions = $this->permissionModel->getAllPermissions();
        $groupedPermissions = [];
        foreach ($allPermissions as $permission) {
            // Group by the Controller part of the key. "Users/index" -> "Users"
            $key = $permission['permission_key'];
            $parts = explode('/', $key);
            $groupKey = $parts[0]; // The controller name is the group key
            
            if (!isset($groupedPermissions[$groupKey])) {
                $groupedPermissions[$groupKey] = [];
            }
            $groupedPermissions[$groupKey][] = $permission;
        }

        $data = [
            'page_main_title' => 'إدارة الصلاحيات',
            'roles' => $this->userModel->getRoles(),
            'users' => $users,
            'permissions' => $groupedPermissions, // Use grouped permissions
            'userPermissions' => $userPermissions,
            'selectedRoleId' => $selectedRoleId,
        ];

        $this->view('admin/permissions/index', $data);
    }
    
    public function save()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $permissionId = $_POST['permission_id'] ?? null;
        $isChecked = filter_var($_POST['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$userId || !$permissionId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing user ID or permission ID.'], 400);
            return;
        }
        
        $result = $this->permissionModel->toggleUserPermission($userId, $permissionId, $isChecked);
        
        if ($result) {
            // Signal the user's session to refresh its permissions on the next request.
            $this->signalPermissionRefresh($userId);

            $message = $isChecked ? 'تم منح الصلاحية بنجاح' : 'تم إلغاء الصلاحية بنجاح';
            $this->sendJsonResponse(['success' => true, 'message' => $message]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'فشل تحديث الصلاحية.'], 500);
        }
    }

    /**
     * Creates a signal file to notify a user's session to refresh permissions.
     * @param int $userId The ID of the user to notify.
     */
    private function signalPermissionRefresh(int $userId)
    {
        $dir = APPROOT . '/app/cache/refresh_permissions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        touch($dir . '/' . $userId);
    }
} 