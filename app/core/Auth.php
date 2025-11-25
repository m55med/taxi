<?php



namespace App\Core;

use App\Models\Token\Token;

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

        $userRole = self::getUserRole();

        if ($userRole === null) {

            return false;

        }

        return strtolower($userRole) === strtolower($role);

    }



    /**

     * Checks if the current user has any of the specified roles.

     *

     * @param array $roles An array of role names to check.

     * @return bool

     */

    public static function hasAnyRole(array $roles): bool

    {

        $userRole = self::getUserRole();

        if ($userRole === null) {

            return false;

        }

        

        $userRoleLower = strtolower($userRole);

        $rolesLower = array_map('strtolower', $roles);

        

        return in_array($userRoleLower, $rolesLower, true);

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

     * A new, unified method to check for access based on roles or direct permissions.

     *

     * @param array $roles An array of role names that are allowed access.

     * @param string $permissionNeeded The specific permission key required for this route.

     */

    public static function requireAccess(array $roles, string $permissionNeeded)

    {

        self::requireLogin();



        $userRole = self::getUserRole();

        $userPermissions = self::user('permissions') ?? [];





        // 1. Admins and developers always have access.

        if ($userRole && self::hasAnyRole(['admin', 'developer'])) {

            return;

        }

        

        // 2. Check if the user's role is in the allowed list for the route.

        if ($userRole && self::hasAnyRole($roles)) {

            return;

        }



        // 3. Check if the user has the specific permission required for the route.

        if (self::hasPermission($permissionNeeded)) {

            return;

        }



        // If all checks fail, deny access.

        // The check below is intentionally redundant for debugging.

        // A warning will be triggered if headers are already sent by the <pre> block.

        if (!headers_sent()) {

            http_response_code(403);

        }

        $data['debug_info'] = [

            'required_roles' => $roles,

            'user_role' => $userRole ?? 'Not Set',

            'required_permission' => $permissionNeeded,

            'user_permissions' => self::user('permissions') ?? [],

            'session_user_data' => $_SESSION['user'] ?? 'User session not set'

        ];

        require_once APPROOT . '/views/errors/403.php';

        exit;

    }



    /**

     * Requires a specific role to access a page. Shows 403 error if not met.

     * @param string|array $roles

     * @deprecated Use requireAccess instead for more fine-grained control.

     */

    public static function requireRole($roles)

    {

        self::requireLogin();



        if (is_string($roles)) {

            $roles = [$roles];

        }



        $userRole = self::getUserRole();

        

        // This is a simplified check. For full functionality, we should also check permissions here.

        // However, since this method is deprecated, we will keep it simple.

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

    /**
     * التحقق من صحة التوكن
     * التوكن صالح إذا: NOW() - last_activity < expires_after_minutes
     *
     * @param string $token التوكن المراد التحقق منه
     * @return bool
     */
    public static function isTokenValid(string $token): bool
    {
        $tokenModel = new Token();
        return $tokenModel->isTokenValid($token);
    }

    /**
     * تحديث آخر نشاط للتوكن
     *
     * @param string $token التوكن المراد تحديث نشاطه
     * @return bool
     */
    public static function updateTokenActivity(string $token): bool
    {
        $tokenModel = new Token();
        return $tokenModel->updateTokenActivity($token);
    }

    /**
     * Update current user's token activity (called on every authenticated request)
     */
    public static function updateCurrentUserTokenActivity(): void
    {
        if (self::isLoggedIn() && isset($_SESSION['user']['current_token'])) {
            self::updateTokenActivity($_SESSION['user']['current_token']);
        }
    }

}