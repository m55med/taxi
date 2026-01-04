<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Admin\Permission;
use App\Models\User\User;

class PermissionsController extends Controller
{
    private $permissionModel;
    private $userModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        // Use the new simplified authorization check
        \App\Core\Auth::checkAdmin();
        $this->permissionModel = $this->model('Admin/Permission');
        $this->userModel = $this->model('User/User');
        $this->db = Database::getInstance();
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data);
        return; // To ensure no other output is sent
    }

    public function index()
    {
    // مزامنة الصلاحيات من الملفات للقاعدة
    $this->permissionModel->syncPermissions();

    // إنشاء permissions للمهام يدوياً إذا لم تكن موجودة
    $this->ensureTaskPermissionsExist();

    $selectedRoleId = isset($_GET['role_id']) && is_numeric($_GET['role_id']) ? (int)$_GET['role_id'] : null;
    
    $users = [];
    $userPermissions = [];
    $rolePermissions = [];

    if ($selectedRoleId) {
        $users = $this->userModel->getUsersByRole($selectedRoleId);

        if (!empty($users)) {
            foreach ($users as $user) {
                // دعم object أو array
                $userId = is_object($user) ? $user->id : $user['id'];
                $userPermissions[$userId] = $this->permissionModel->getPermissionsByUser($userId);
            }
        }

        $rolePermissions = $this->permissionModel->getPermissionsByRole($selectedRoleId);
    }

    // جلب كل الصلاحيات وترتيبها
    $allPermissions = $this->permissionModel->getAllPermissions();
    $groupedPermissions = [];

    foreach ($allPermissions as $permission) {
        // دعم object أو array
        $permissionKey = is_object($permission) ? $permission->permission_key : $permission['permission_key'];
        $parts = explode('/', $permissionKey);
        $groupKey = $parts[0];

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
        'permissions' => $groupedPermissions,
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

    /**
     * Ensures that task-related permissions exist in the database
     */
    private function ensureTaskPermissionsExist()
    {
        // First sync all permissions from controllers
        $this->permissionModel->syncPermissions();

        $taskPermissions = [
            'Tasks/index' => 'View all tasks',
            'Tasks/create' => 'Create new tasks',
            'Tasks/show' => 'View task details',
            'Tasks/edit' => 'Edit tasks',
            'Tasks/update_status' => 'Update task status',
            'Tasks/add_comment' => 'Add comments to tasks',
            'Tasks/update_assignees' => 'Update task assignees'
        ];

        foreach ($taskPermissions as $permissionKey => $description) {
            // Check if permission already exists
            $stmt = $this->db->prepare("SELECT id FROM permissions WHERE permission_key = ?");
            $stmt->execute([$permissionKey]);
            $exists = $stmt->fetch();

            if (!$exists) {
                // Insert the permission
                $insertStmt = $this->db->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
                $insertStmt->execute([$permissionKey, $description]);
            }
        }

        // Also ensure that default roles have access to task permissions
        $this->ensureDefaultTaskPermissionsForRoles();
    }

    /**
     * Ensures that default roles have access to task permissions
     */
    private function ensureDefaultTaskPermissionsForRoles()
    {
        $taskPermissions = ['Tasks/index', 'Tasks/create', 'Tasks/show', 'Tasks/edit', 'Tasks/update_status', 'Tasks/add_comment', 'Tasks/update_assignees'];
        $defaultRoles = ['Quality', 'team_leader']; // These roles should have task access by default

        foreach ($defaultRoles as $roleName) {
            // Get role ID
            $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->execute([$roleName]);
            $role = $roleStmt->fetch();

            if ($role) {
                $roleId = $role['id'];

                foreach ($taskPermissions as $permissionKey) {
                    // Get permission ID
                    $permStmt = $this->db->prepare("SELECT id FROM permissions WHERE permission_key = ?");
                    $permStmt->execute([$permissionKey]);
                    $permission = $permStmt->fetch();

                    if ($permission) {
                        $permissionId = $permission['id'];

                        // Check if role already has this permission
                        $checkStmt = $this->db->prepare("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?");
                        $checkStmt->execute([$roleId, $permissionId]);
                        $exists = $checkStmt->fetch();

                        if (!$exists) {
                            // Grant permission to role
                            $insertStmt = $this->db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                            $insertStmt->execute([$roleId, $permissionId]);
                        }
                    }
                }
            }
        }
    }
} 