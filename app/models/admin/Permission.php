<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionClass;
use ReflectionMethod;

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
                $addStmt = $this->db->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
                foreach ($toAdd as $key) {
                    $description = $this->createFriendlyPermissionName($key);
                    $addStmt->execute([$key, $description]);
                }
            }
            
            // Remove old ones
            if (!empty($toRemove)) {
                // Use a subquery to avoid deadlock issues and use permission_key
                $removeStmt = $this->db->prepare("DELETE FROM permissions WHERE permission_key IN (" . implode(',', array_fill(0, count($toRemove), '?')) . ")");
                $removeStmt->execute($toRemove);
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

            // Construct FQCN (Fully Qualified Class Name) in a robust way
            $filePath = $file->getRealPath();
            $classPath = str_replace([$basePath . DIRECTORY_SEPARATOR, '.php'], '', $filePath);
            $namespace = 'App\\Controllers\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $classPath);
            
            if (in_array($namespace, self::$excludedClasses) || !class_exists($namespace)) {
                continue;
            }

            try {
                $reflector = new ReflectionClass($namespace);
                if ($reflector->isAbstract()) continue;

                $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    // Skip constructors, magic methods, and methods from the parent Controller class
                    if ($method->isConstructor() || str_starts_with($method->getName(), '__') || $method->getDeclaringClass()->getName() !== $reflector->getName()) {
                        continue;
                    }

                    // Format: ControllerName/methodName (e.g., "Users/index", "Calls/create")
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
        return $this->db->query("SELECT * FROM permissions ORDER BY permission_key ASC")->fetchAll(PDO::FETCH_ASSOC);
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
} 