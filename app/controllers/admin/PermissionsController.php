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
        // Use the new simplified authorization check
        \App\Core\Auth::checkAdmin();
        $this->permissionModel = $this->model('Admin/Permission');
        $this->userModel = $this->model('User/User');
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data);
        return; // To ensure no other output is sent
    }

    public function index()
    {
        // Sync permissions from files to DB on each visit
        $this->permissionModel->syncPermissions();

        $selectedRoleId = isset($_GET['role_id']) && is_numeric($_GET['role_id']) ? (int)$_GET['role_id'] : null;
        
        $users = [];
        $userPermissions = [];
        $rolePermissions = [];

        if ($selectedRoleId) {
            $users = $this->userModel->getUsersByRole($selectedRoleId);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $userPermissions[$user['id']] = $this->permissionModel->getPermissionsByUser($user['id']);
                }
            }
            $rolePermissions = $this->permissionModel->getPermissionsByRole($selectedRoleId);
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
        ksort($groupedPermissions);

        $data = [
            'page_main_title' => 'Manage Permissions',
            'roles' => $this->userModel->getRoles(),
            'users' => $users,
            'permissions' => $groupedPermissions, // Use grouped permissions
            'userPermissions' => $userPermissions,
            'rolePermissions' => $rolePermissions,
            'selectedRoleId' => $selectedRoleId,
        ];

        $this->view('admin/permissions/index', $data);
    }
    
    public function toggle()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $permissionId = filter_input(INPUT_POST, 'permission_id', FILTER_SANITIZE_NUMBER_INT);
        $isGranted = filter_var($_POST['grant'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$userId || !$permissionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing user ID or permission ID.']);
            return;
        }
        
        $result = $this->permissionModel->toggleUserPermission($userId, $permissionId, $isGranted);
        
        if ($result) {
            // Signal the user's session to refresh its permissions on the next request.
            $this->signalPermissionRefresh($userId);

            $message = $isGranted ? 'Permission granted successfully.' : 'Permission revoked successfully.';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update permission.']);
        }
    }

    public function toggleRolePermission()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $roleId = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
        $permissionId = filter_input(INPUT_POST, 'permission_id', FILTER_SANITIZE_NUMBER_INT);
        $isGranted = filter_var($_POST['grant'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$roleId || !$permissionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing role ID or permission ID.']);
            return;
        }
        
        $result = $this->permissionModel->toggleRolePermission($roleId, $permissionId, $isGranted);
        
        if ($result) {
            $message = $isGranted ? 'Default permission granted successfully.' : 'Default permission revoked successfully.';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update default permission.']);
        }
    }

    public function batchUpdateRolePermissions()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $roleId = filter_var($input['role_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $permissionIds = $input['permission_ids'] ?? [];
        $isGranted = filter_var($input['grant'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$roleId || !is_array($permissionIds) || empty($permissionIds)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Missing or invalid parameters.'], 400);
        }

        $result = $this->permissionModel->syncRolePermissions($roleId, $permissionIds, $isGranted);

        if ($result) {
            return $this->jsonResponse(['success' => true, 'message' => 'Permissions updated successfully.']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update permissions.'], 500);
        }
    }

    public function batchUpdateUserPermissions()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($input['user_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $permissionIds = $input['permission_ids'] ?? [];
        $isGranted = filter_var($input['grant'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$userId || !is_array($permissionIds) || empty($permissionIds)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Missing or invalid parameters.'], 400);
        }

        $result = $this->permissionModel->syncUserPermissions($userId, $permissionIds, $isGranted);

        if ($result) {
            $this->signalPermissionRefresh($userId);
            return $this->jsonResponse(['success' => true, 'message' => 'Permissions updated successfully.']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update permissions.'], 500);
        }
    }

    /**
     * Creates a signal file to notify a user's session to refresh permissions.
     * @param int $userId The ID of the user to notify.
     */
    private function signalPermissionRefresh(int $userId)
    {
        $dir = APPROOT . '/cache/refresh_permissions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        touch($dir . '/' . $userId);
    }
} 