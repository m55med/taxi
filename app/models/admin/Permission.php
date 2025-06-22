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
        $controllers = [];
        $paths = [
            APPROOT . '/app/controllers/admin',
            APPROOT . '/app/controllers/reports',
            APPROOT . '/app/controllers',
        ];

        foreach ($paths as $path) {
            $this->recursivelyScanControllers($path, $controllers, $path);
        }

        // Insert new permissions into the database
        $stmt = $this->db->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
        foreach ($controllers as $controller) {
            $description = $this->createFriendlyPermissionName($controller);
            $stmt->execute([$controller, $description]);
        }
    }
    
    private function createFriendlyPermissionName($namespace) {
        // Remove base 'App\Controllers\' and suffix 'Controller'
        $name = preg_replace('/^App\\\\Controllers\\\\|Controller$/i', '', $namespace);
        
        // Split by backslash and capitalize each part
        $parts = array_map('ucfirst', explode('\\', $name));
        
        // Handle cases like 'Admin\Users' -> 'Admin / Users'
        // Handle 'Tickets' -> 'Tickets'
        return implode(' / ', $parts);
    }

    private function recursivelyScanControllers($dir, &$results, $basePath)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->recursivelyScanControllers($path, $results, $basePath);
            } else if (str_ends_with($file, 'Controller.php')) {
                // Construct the FQCN (Fully Qualified Class Name)
                $relativePath = str_replace(APPROOT . '/app/controllers/', '', $path);
                $classPath = str_replace('.php', '', $relativePath);
                $namespace = 'App\\Controllers\\' . str_replace('/', '\\', $classPath);

                if (in_array($namespace, self::$excludedClasses)) {
                    continue;
                }
                
                $results[] = $namespace;
            }
        }
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