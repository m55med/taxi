<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionClass;
use ReflectionMethod;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Permission
{
    private $db;
    private static $excludedClasses = [
        // This is not a user-facing controller, safe to exclude
        'App\Controllers\Telegram\WebhookController',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Scans controller directories and populates the permissions table.
     */
    public function syncPermissions()
    {
        $discoveredPermissions = $this->discoverPermissions();

        // Start transaction
        $this->db->beginTransaction();
        try {
            // Get all existing permissions from DB
            $existingStmt = $this->db->query("SELECT permission_key FROM permissions");
            $existingPermissions = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

            // Find permissions to add
            $toAdd = array_diff($discoveredPermissions, $existingPermissions);
            
            // Find permissions to remove
            $toRemove = array_diff($existingPermissions, $discoveredPermissions);

            // Add new ones
            if (!empty($toAdd)) {
                $addStmt = $this->db->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
                foreach ($toAdd as $key) {
                    $description = $this->createFriendlyPermissionName($key);
                    $addStmt->execute([$key, $description]);
                }
            }
            
            // Remove old ones
            if (!empty($toRemove)) {
                $removeStmt = $this->db->prepare("DELETE FROM permissions WHERE permission_key IN (" . implode(',', array_fill(0, count($toRemove), '?')) . ")");
                $removeStmt->execute(array_values($toRemove));
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to sync permissions: " . $e->getMessage());
        }
    }
    
    private function discoverPermissions(): array
    {
        $permissions = [];
        $basePath = realpath(__DIR__ . '/../../controllers');
        if (!$basePath) {
            error_log("Could not resolve the base path for controllers in Permission model.");
            return [];
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->isDir() || !str_ends_with($file->getFilename(), 'Controller.php')) {
                continue;
            }

            $filePath = $file->getRealPath();
            $fileNamespace = $this->getNamespaceFromFile($filePath);
            $className = basename($file->getFilename(), '.php');

            if (!$fileNamespace) {
                // Skip files without a namespace declaration.
                continue;
            }
            
            $namespace = $fileNamespace . '\\' . $className;

            if (in_array($namespace, self::$excludedClasses) || !class_exists($namespace, true)) {
                 if (!in_array($namespace, self::$excludedClasses)) {
                    error_log("Permission sync: Skipping class that does not exist or could not be loaded: $namespace");
                }
                continue;
            }

            try {
                $reflector = new ReflectionClass($namespace);
                if ($reflector->isAbstract()) continue;

                $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    if ($method->isConstructor() || str_starts_with($method->getName(), '__') || $method->getDeclaringClass()->getName() !== $reflector->getName()) {
                        continue;
                    }

                    $controllerShortName = str_replace('Controller', '', $reflector->getShortName());
                    $permissionKey = $controllerShortName . '/' . $method->getName();
                    $permissions[] = $permissionKey;
                }
            } catch (\ReflectionException $e) {
                error_log("Reflection error for $namespace: " . $e->getMessage());
            }
        }
        return array_unique($permissions);
    }
    
    /**
     * Extracts the namespace from a PHP file.
     *
     * @param string $filePath The full path to the PHP file.
     * @return string|null The found namespace or null.
     */
    private function getNamespaceFromFile(string $filePath): ?string
    {
        $src = file_get_contents($filePath);
        $tokens = token_get_all($src);
        $namespace = '';
        $namespaceStarted = false;
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $namespaceStarted = true;
            } elseif ($namespaceStarted && is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR])) {
                $namespace .= $token[1];
            } elseif ($namespaceStarted && $token === ';') {
                break;
            }
        }
        return $namespace ?: null;
    }

    private function createFriendlyPermissionName($permissionKey) {
        $parts = explode('/', $permissionKey);
        // "Users/edit" -> "Users / Edit"
        return ucfirst($parts[0]) . ' / ' . ucfirst($parts[1] ?? 'Action');
    }

    /**
     * Gets all available permissions from the database.
     */
    public function getAllPermissions()
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions ORDER BY permission_key ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    /**
     * Gets permissions for a specific user.
     */
    public function getPermissionsByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.permission_key 
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPermissionsByRole(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Updates the permissions for a specific user.
     */
    public function updateUserPermissions(int $userId, array $permissionIds)
    {
        $this->db->beginTransaction();
        try {
            // Delete old permissions for the user
            $deleteStmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $deleteStmt->execute([$userId]);

            // Insert new permissions
            if (!empty($permissionIds)) {
                $insertStmt = $this->db->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
                foreach ($permissionIds as $permissionId) {
                    $insertStmt->execute([$userId, $permissionId]);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function toggleUserPermission(int $userId, int $permissionId, bool $grant): bool
    {
        try {
            if ($grant) {
                // Use IGNORE to prevent errors if the permission already exists.
                $stmt = $this->db->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
                return $stmt->execute([$userId, $permissionId]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?");
                return $stmt->execute([$userId, $permissionId]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function toggleRolePermission(int $roleId, int $permissionId, bool $grant): bool
    {
        try {
            if ($grant) {
                $stmt = $this->db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                return $stmt->execute([$roleId, $permissionId]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
                return $stmt->execute([$roleId, $permissionId]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function syncRolePermissions(int $roleId, array $permissionIds, bool $grant): bool
    {
        // Ensure permissionIds are all integers to prevent SQL injection.
        $permissionIds = array_filter($permissionIds, 'is_numeric');
        if (empty($permissionIds)) {
            // If we are revoking and the list is empty, there is nothing to do.
            // If we are granting and the list is empty, also nothing to do.
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
        
        try {
            if ($grant) {
                $sql = "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ";
                $values = [];
                $params = [];
                foreach ($permissionIds as $permissionId) {
                    $values[] = "(?, ?)";
                    $params[] = $roleId;
                    $params[] = (int)$permissionId;
                }
                $sql .= implode(', ', $values);
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($params);
            } else {
                $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission_id IN ($placeholders)";
                $params = array_merge([$roleId], $permissionIds);
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($params);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function syncUserPermissions(int $userId, array $permissionIds, bool $grant): bool
    {
        $permissionIds = array_filter($permissionIds, 'is_numeric');
        if (empty($permissionIds)) {
            return true;
        }
        
        $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));

        try {
            if ($grant) {
                $sql = "INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES ";
                $values = [];
                $params = [];
                foreach ($permissionIds as $permissionId) {
                    $values[] = "(?, ?)";
                    $params[] = $userId;
                    $params[] = (int)$permissionId;
                }
                $sql .= implode(', ', $values);
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($params);
            } else {
                $sql = "DELETE FROM user_permissions WHERE user_id = ? AND permission_id IN ($placeholders)";
                $params = array_merge([$userId], $permissionIds);
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($params);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
} 