<?php

namespace App\Core;

class Auth
{
    /**
     * Ensures session is started.
     */
    private static function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Checks if a user is logged in.
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Gets the current user's ID.
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Gets the current user's role name.
     * @return string|null
     */
    public static function getUserRole(): ?string
    {
        self::startSession();
        return $_SESSION['role'] ?? null;
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
     * Checks if the current user has a specific permission.
     * This is a more flexible system than role-checking alone.
     *
     * @param string $permission The permission to check.
     * @return bool
     */
    public static function hasPermission(string $permission): bool
    {
        self::startSession();

        // Rule #1: Admin has all permissions.
        if (self::getUserRole() === 'admin') {
            return true;
        }

        // Rule #2: Check if the permissions array exists in the session and if the permission is in it.
        $userPermissions = $_SESSION['permissions'] ?? [];
        return in_array($permission, $userPermissions);
    }

    /**
     * If the user is not logged in, redirects to the login page.
     */
    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            // You can store the intended URL in session to redirect back after login
            // $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_PATH . '/auth/login');
            exit;
        }
    }

    /**
     * Requires a specific role to access a page. Shows 403 error if not met.
     * @param string $role
     */
    public static function requireRole(string $role)
    {
        self::requireLogin(); // A user must be logged in to have a role.
        if (!self::hasRole($role)) {
            http_response_code(403);
            // Ensure this path is correct
            require_once APPROOT . '/../app/views/errors/403.php';
            exit;
        }
    }
    
    /**
     * A specific check for admin, which might have more uses.
     */
    public static function checkAdmin()
    {
        self::requireRole('admin');
    }
}