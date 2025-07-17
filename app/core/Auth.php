<?php

namespace App\Core;

class Auth
{
    /**
     * Get a value from the authenticated user's session data.
     *
     * @param string|null $key The key of the user data to retrieve (e.g., 'id', 'role_name').
     *                         If null, returns the entire user data array.
     * @return mixed|null The requested user data or null if not found.
     */
    public static function user($key = null)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            return null;
        }

        if (is_null($key)) {
            return $_SESSION['user'];
        }

        return $_SESSION['user'][$key] ?? null;
    }

    /**
     * Checks if a user is logged in.
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    /**
     * Gets the current user's ID.
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        return self::user('id');
    }

    /**
     * Gets the current user's role name.
     * @return string|null
     */
    public static function getUserRole(): ?string
    {
        return self::user('role_name');
    }

    /**
     * Checks if the current user has a specific role.
     * @param string $role The role name to check (e.g., 'admin', 'marketer').
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        return self::getUserRole() === $role;
    }
    
    /**
     * Checks if the currently logged-in user has a specific permission.
     *
     * @param string $permission The permission key to check for.
     * @return bool True if the user has the permission, false otherwise.
     */
    public static function hasPermission($permission): bool
    {
        // Admins and developers have all permissions implicitly.
        $userRole = self::getUserRole();

        if ($userRole && in_array($userRole, ['admin', 'developer'])) {
            return true;
        }

        $permissions = self::user('permissions') ?? [];
        
        if (empty($permissions) || !is_array($permissions)) {
            return false;
        }
        
        return in_array($permission, $permissions, true);
    }
    
    /**
     * If the user is not logged in, redirects to the login page.
     */
    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Requires a specific role to access a page. Shows 403 error if not met.
     * @param string|array $roles
     */
    public static function requireRole($roles)
    {
        self::requireLogin();

        if (is_string($roles)) {
            $roles = [$roles];
        }

        $userRole = self::getUserRole();

        if (is_null($userRole) || !in_array($userRole, $roles, true)) {
            http_response_code(403);
            $data['debug_info'] = [
                'required_roles' => $roles,
                'user_role' => $userRole ?? 'Not Set',
                'session_user_data' => $_SESSION['user'] ?? 'User session not set'
            ];
            require_once APPROOT . '/views/errors/403.php';
            exit;
        }
    }
    
    /**
     * A specific check for admin and developer roles.
     */
    public static function checkAdmin()
    {
        self::requireRole(['admin', 'developer']);
    }
}