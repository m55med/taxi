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

    public function getPermissionsByRole($roleId)
    {
        $stmt = $this->db->prepare("SELECT permission FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function savePermissions($roleId, $permissions)
    {
        try {
            $this->db->beginTransaction();

            $deleteStmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $deleteStmt->execute([':role_id' => $roleId]);

            if (!empty($permissions)) {
                $insertStmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (:role_id, :permission)");
                foreach ($permissions as $permission) {
                    $insertStmt->execute([
                        ':role_id' => $roleId,
                        ':permission' => $permission
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Permission save error: " . $e->getMessage());
            return false;
        }
    }

    public function discoverControllers()
    {
        $modules = [];
        $controllersPath = APPROOT . '/app/controllers';

        $excludedPrefixes = [
            'App\\Controllers\\Telegram', // Internal system
        ];

        $iterator = new \DirectoryIterator($controllersPath);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()) continue;
            
            $moduleName = $fileinfo->getBasename($fileinfo->getExtension() === 'php' ? '.php' : '');
            
            // Skip non-controller files/folders
            if (!$fileinfo->isDir() && !str_ends_with($moduleName, 'Controller')) {
                continue;
            }
            
            $displayName = str_replace('Controller', '', $moduleName);
            $displayName = ucwords(str_replace(['_', '-'], ' ', $displayName));
            
            $namespaceName = str_replace(' ', '', $displayName);
            $permissionString = 'App\\Controllers\\' . $namespaceName;
            
            $isExcluded = false;
            foreach ($excludedPrefixes as $prefix) {
                if (str_starts_with($permissionString, $prefix)) {
                    $isExcluded = true;
                    break;
                }
            }
            if ($isExcluded) {
                continue;
            }
            
            if (!isset($modules[$permissionString])) {
                $modules[$permissionString] = [
                    'permission' => $permissionString,
                    'name' => $displayName
                ];
            }
        }

        $moduleList = array_values($modules);
        usort($moduleList, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $moduleList;
    }
} 