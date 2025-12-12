<?php



namespace App\Models\User;



use App\Core\Database;

use PDO;

use PDOException;

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';



class User

{

    private $db;



    public function __construct()

    {

        $this->db = Database::getInstance();

    }



    public function isEmailExists($email)

    {

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");

        $stmt->execute([':email' => $email]);

        return $stmt->fetchColumn() > 0;

    }



    public function isUsernameExists($username)

    {

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");

        $stmt->execute([':username' => $username]);

        return $stmt->fetchColumn() > 0;

    }



    public function register($data)

    {

        try {

            // التحقق من وجود البريد الإلكتروني

            if ($this->isEmailExists($data['email'])) {

                return ['status' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل'];

            }



            // التحقق من وجود اسم المستخدم

            if ($this->isUsernameExists($data['username'])) {

                return ['status' => false, 'message' => 'اسم المستخدم مستخدم بالفعل'];

            }



            // إضافة المستخدم الجديد

            $stmt = $this->db->prepare("INSERT INTO users (username, name, email, password, status, role_id) VALUES (:username, :name, :email, :password, :status, :role_id)");

            $result = $stmt->execute([

                ':username' => $data['username'],

                ':name' => $data['name'],

                ':email' => $data['email'],

                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),

                ':status' => $data['status'] ?? 'pending',

                ':role_id' => $data['role_id'] ?? 3

            ]);



            if ($result) {

                return ['status' => true, 'message' => 'تم التسجيل بنجاح'];

            } else {

                return ['status' => false, 'message' => 'حدث خطأ أثناء التسجيل'];

            }

        } catch (PDOException $e) {

            error_log("Registration error: " . $e->getMessage());

            return ['status' => false, 'message' => 'حدث خطأ أثناء التسجيل'];

        }

    }



    public function login($username, $password)

    {

        $sql = "SELECT u.*, r.name as role_name FROM users u 

                LEFT JOIN roles r ON u.role_id = r.id 

                WHERE u.username = ?";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_OBJ);



        if ($user && password_verify($password, $user->password)) {

            if ($user->status === 'banned') {

                return ['status' => false, 'message' => 'Your account has been banned.'];

            }

            if ($user->status === 'pending') {

                return ['status' => false, 'message' => 'Your account is pending review.'];

            }



            // Update online status

            $this->updateOnlineStatus($user->id, 1);

            

            return ['status' => true, 'user' => $user];

        }



        return ['status' => false, 'message' => 'Invalid username or password.'];

    }



    public function setOffline($userId)

    {

        try {

            $stmt = $this->db->prepare("UPDATE users SET is_online = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id");

            $result = $stmt->execute([':id' => $userId]);



            // التحقق من نجاح التحديث

            if ($result && $stmt->rowCount() > 0) {

                return true;

            }

            return false;

        } catch (PDOException $e) {

            error_log("Database error in setOffline: " . $e->getMessage());

            return false;

        }

    }



    // إضافة مستخدم جديد

    public function createUser($userData)

    {

        $sql = "INSERT INTO users (username, name, email, password, role_id, status) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([

            $userData['username'],

            $userData['name'],

            $userData['email'],

            password_hash($userData['password'], PASSWORD_DEFAULT),

            $userData['role_id'],

            $userData['status']

        ]);



        if ($success) {

            return $this->db->lastInsertId();

        }

        return false;

    }



    // تحديث حالة المستخدم

    public function updateUserStatus($userId, $status)

    {

        try {

            $sql = "UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            $result = $stmt->execute([$status, $userId]);

            return ['status' => $result, 'message' => $result ? 'تم تحديث الحالة بنجاح' : 'فشل تحديث الحالة'];

        } catch (PDOException $e) {

            error_log("Error updating user status: " . $e->getMessage());

            return ['status' => false, 'message' => 'حدث خطأ أثناء تحديث الحالة'];

        }

    }



    // حذف مستخدم

    public function deleteUser($userId)

    {

        $sql = "DELETE FROM users WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$userId]);

    }



    // الحصول على جميع المستخدمين مع الفلترة

    public function getAllUsers($filters = [])

    {

        try {

            $sql = "SELECT DISTINCT

                        u.id,

                        u.username,

                        u.name,

                        u.email,

                        u.status,

                        u.is_online,

                        u.role_id,

                        u.updated_at,

                        r.name as role_name

                    FROM users u 

                    LEFT JOIN roles r ON u.role_id = r.id";



            $params = [];



            if (!empty($filters)) {

                $sql .= " WHERE 1=1";



                if (!empty($filters['search'])) {

                    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.name LIKE ?)";

                    $searchTerm = "%{$filters['search']}%";

                    $params[] = $searchTerm;

                    $params[] = $searchTerm;

                    $params[] = $searchTerm;

                }



                if (!empty($filters['role'])) {

                    $sql .= " AND r.name = ?";

                    $params[] = $filters['role'];

                }



                if (!empty($filters['status'])) {

                    $sql .= " AND u.status = ?";

                    $params[] = $filters['status'];

                }

            }



            $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";



            $stmt = $this->db->prepare($sql);

            $stmt->execute($params);

            $users = $stmt->fetchAll(PDO::FETCH_OBJ);



            // Remove duplicates by ID (extra safety measure)
            $uniqueUsers = [];
            $seenIds = [];
            foreach ($users as $user) {
                $userId = is_object($user) ? $user->id : (is_array($user) ? $user['id'] : null);
                if ($userId && !in_array($userId, $seenIds)) {
                    $uniqueUsers[] = $user;
                    $seenIds[] = $userId;
                } else if ($userId) {
                    error_log("Duplicate user found in getAllUsers: ID " . $userId);
                }
            }
            $users = $uniqueUsers;
            
            // Log total unique users for debugging
            error_log("getAllUsers - Total unique users: " . count($users));



            // التأكد من أن جميع الحقول موجودة

            foreach ($users as &$user) {

                $user->role_name = $user->role_name ?? 'مستخدم';

                $user->role_id = $user->role_id ?? 3; // افتراضي للمستخدم العادي

                $user->status = $user->status ?? 'pending';

                $user->is_online = $user->is_online ?? 0;

                $user->updated_at = $user->updated_at ?? date('Y-m-d H:i:s');

            }



            return $users;

        } catch (PDOException $e) {

            error_log("Error fetching users: " . $e->getMessage());

            return [];

        }

    }



    // الحصول على جميع الأدوار

    public function getRoles()

    {

        try {

            $sql = "SELECT id, name FROM roles ORDER BY id ASC";

            $stmt = $this->db->prepare($sql);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            // تحويل التواريخ للعرض بالتوقيت المحلي


            return convert_dates_for_display($results, ['created_at', 'updated_at']);

        } catch (PDOException $e) {

            error_log("Error fetching roles: " . $e->getMessage());

            return [];

        }

    }



    // إضافة دور جديد

    public function addRole($roleName)

    {

        $sql = "INSERT INTO roles (name) VALUES (?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$roleName]);

    }



    // إحصائيات المستخدمين

    public function countUsers()

    {

        try {

            $sql = "SELECT COUNT(*) FROM users";

            return $this->db->query($sql)->fetchColumn();

        } catch (PDOException $e) {

            error_log("Error counting users: " . $e->getMessage());

            return 0;

        }

    }



    public function countUsersByStatus($status)

    {

        try {

            $sql = "SELECT COUNT(*) FROM users WHERE status = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([$status]);

            return $stmt->fetchColumn();

        } catch (PDOException $e) {

            error_log("Error counting users by status: " . $e->getMessage());

            return 0;

        }

    }



    public function countOnlineUsers()

    {

        try {

            $sql = "SELECT COUNT(*) FROM users WHERE is_online = 1";

            return $this->db->query($sql)->fetchColumn();

        } catch (PDOException $e) {

            error_log("Error counting online users: " . $e->getMessage());

            return 0;

        }

    }



    public function logout($userId)

    {

        $this->setOffline($userId);

    }



    public function updateOnlineStatus($userId, $status)

    {

        try {

            // تحديث وقت النشاط

            $sql = "UPDATE users SET is_online = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([$status, $userId]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {

            error_log("Error updating online status: " . $e->getMessage());

            return false;

        }

    }



    public function updateUserRole($userId, $roleId)

    {

        try {

            $sql = "UPDATE users SET role_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([$roleId, $userId]);

        } catch (PDOException $e) {

            error_log("Error updating user role: " . $e->getMessage());

            return false;

        }

    }



    public function getUserById($id)

    {

        try {

            $sql = "SELECT 

                        u.*, 

                        r.name as role_name 

                    FROM users u 

                    LEFT JOIN roles r ON u.role_id = r.id 

                    WHERE u.id = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([$id]);

            $user = $stmt->fetch(PDO::FETCH_OBJ);



            if ($user) {

                // التأكد من أن جميع الحقول المطلوبة موجودة

                $user->role_name = $user->role_name ?? 'مستخدم';

                $user->status = $user->status ?? 'pending';

            }



            return $user;

        } catch (PDOException $e) {

            error_log("Error fetching user by ID: " . $e->getMessage());

            return null;

        }

    }



    public function updateUser($id, $data)

    {

        try {

            // Check if the email is being changed to one that already belongs to another user.

            if (!empty($data['email'])) {

                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");

                $stmt->execute([':email' => $data['email'], ':id' => $id]);

                if ($stmt->fetch()) {

                    return ['status' => false, 'message' => 'This email address is already in use by another account.'];

                }

            }



            $fields = [];

            $params = [':id' => $id];



            if (!empty($data['name'])) {

                $fields[] = 'name = :name';

                $params[':name'] = $data['name'];

            }

            if (!empty($data['email'])) {

                $fields[] = 'email = :email';

                $params[':email'] = $data['email'];

            }

            if (!empty($data['password'])) {

                $fields[] = 'password = :password';

                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            }

            if (isset($data['role_id'])) {

                $fields[] = 'role_id = :role_id';

                $params[':role_id'] = $data['role_id'];

            }

            if (!empty($data['status'])) {

                $fields[] = 'status = :status';

                $params[':status'] = $data['status'];

            }



            if (empty($fields)) {

                return ['status' => true, 'message' => 'No changes were made.'];

            }



            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $result = $stmt->execute($params);



            if ($result) {

                // Return success and the updated name

                $response = ['status' => true, 'message' => 'User updated successfully.'];

                if (isset($data['name'])) {

                    $response['name'] = $data['name'];

                }

                return $response;

            } else {

                return ['status' => false, 'message' => 'Failed to update user due to a database error.'];

            }

        } catch (PDOException $e) {

            error_log("User update error: " . $e->getMessage());

            return ['status' => false, 'message' => 'An internal error occurred. Please try again later.'];

        }

    }



    public function changePassword($userId, $currentPassword, $newPassword)

    {

        try {

            $user = $this->getById($userId);

            if (!$user) {

                return ['success' => false, 'message' => 'المستخدم غير موجود'];

            }



            // التحقق من كلمة المرور الحالية

            if (!password_verify($currentPassword, $user['password'])) {

                return ['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'];

            }



            // تحديث كلمة المرور

            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([$newHashedPassword, $userId]);



            return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];

        } catch (PDOException $e) {

            error_log("Error changing password: " . $e->getMessage());

            return ['success' => false, 'message' => 'حدث خطأ أثناء تغيير كلمة المرور'];

        }

    }



    public function getById($id)

    {

        return $this->getUserById($id);

    }



    public function getActiveStaff()

    {

        // Adjust the role IDs (e.g., 1 for admin, 2 for staff) as per your `roles` table

        $stmt = $this->db->query("SELECT id, username FROM users WHERE status = 'active' AND role_id IN (1, 2, 4, 5, 6, 7)");

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

    }

    

    public function getActiveUsers() {
        $query = "SELECT id, name FROM users ORDER BY name ASC";
        error_log("Executing query: " . $query);
        $stmt = $this->db->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched users: " . print_r($users, true));
        return $users;
    }

    

    public function getAvailableForTeamLeadership($excludeTeamId = null)

    {

        $sql = "SELECT u.id, u.username

                FROM users u

                INNER JOIN roles r ON u.role_id = r.id

                LEFT JOIN teams t ON u.id = t.team_leader_id

                WHERE u.status = 'active'

                  AND r.name = 'Team_leader'

                  AND (t.id IS NULL OR t.id = :excludeTeamId)

                ORDER BY u.username ASC";

        

        $stmt = $this->db->prepare($sql);

        $stmt->execute([':excludeTeamId' => $excludeTeamId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

    }

    



    /**

     * Get all permissions for a specific user based on their role.

     * @param int $userId

     * @return array

     */

    public function getUserPermissions(int $userId): array

    {

        // First, get the user's role ID

        $user = $this->getById($userId);

        if (!$user || !isset($user->role_id)) {

            return []; // No user or role found, return no permissions

        }

        $roleId = $user->role_id;



        // Get permissions assigned directly to the user

        $userPermissionsSql = "SELECT p.permission_key 

                               FROM user_permissions up

                               JOIN permissions p ON up.permission_id = p.id

                               WHERE up.user_id = :user_id";

        

        $stmtUser = $this->db->prepare($userPermissionsSql);

        $stmtUser->execute([':user_id' => $userId]);

        $userPermissions = $stmtUser->fetchAll(PDO::FETCH_COLUMN, 0);



        // Get permissions assigned to the user's role

        $rolePermissions = $this->getRolePermissions($roleId);



        // Merge and return unique permissions

        return array_unique(array_merge($userPermissions, $rolePermissions));

    }



    public function getUsersByRole(int $roleId)

    {

        $stmt = $this->db->prepare("SELECT 

            u.id, 

            u.username, 

            u.name,

            u.email, 

            u.status,

            u.created_at,

            u.updated_at,

            r.name as role_name 

            FROM users u 

            LEFT JOIN roles r ON u.role_id = r.id 

            WHERE u.role_id = :role_id 

            ORDER BY u.created_at DESC");

        $stmt->execute([':role_id' => $roleId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);

    }

    

    public function setForceLogout(int $userId, bool $status): bool

    {

        try {

            $stmt = $this->db->prepare("UPDATE users SET force_logout = :status WHERE id = :id");

            return $stmt->execute([':status' => $status ? 1 : 0, ':id' => $userId]);

        } catch (PDOException $e) {

            error_log("Error in setForceLogout: " . $e->getMessage());

            return false;

        }

    }



    /**

     * Updates a user's password based on their email address.

     *

     * @param string $email

     * @param string $newPassword

     * @return bool

     */

    public function updatePasswordByEmail(string $email, string $newPassword): bool

    {

        try {

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE email = :email";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([

                ':password' => $hashedPassword,

                ':email' => $email

            ]);

        } catch (PDOException $e) {

            error_log("Error updating password by email: " . $e->getMessage());

            return false;

        }

    }



    public function getRolePermissions(int $roleId): array

    {

        $sql = "SELECT p.permission_key

                FROM role_permissions rp

                JOIN permissions p ON rp.permission_id = p.id

                WHERE rp.role_id = ?";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    }



    public function findUserById($id)

    {

        return $this->getUserById($id);

    }

    

    public function findByUsername($username)

    {

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");

        $stmt->execute([$username]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        if ($result) {


            return convert_dates_for_display($result, ['created_at', 'updated_at']);


        }



        return $result;

    }



    

    public function getUserStats() {

        try {

            $stats = [];



            // Total users

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");

            $stmt->execute();

            $stats['total_users'] = $stmt->fetchColumn();



            // Online users

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE is_online = 1");

            $stmt->execute();

            $stats['online_users'] = $stmt->fetchColumn();



            // Status breakdown

            $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM users GROUP BY status");

            $stmt->execute();

            $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            

            $stats['active_users'] = $statusCounts['active'] ?? 0;

            $stats['banned_users'] = $statusCounts['banned'] ?? 0;

            $stats['pending_users'] = $statusCounts['pending'] ?? 0;



            return $stats;

        } catch (PDOException $e) {

            error_log("Error in getUserStats: " . $e->getMessage());

            return [

                'total_users' => 0,

                'online_users' => 0,

                'active_users' => 0,

                'banned_users' => 0,

                'pending_users' => 0,

            ];

        }

    }

    

    public function getAllAgentsDetails()

    {

        try {

            $sql = "

                SELECT

                    u.id as user_id,

                    u.name,

                    a.id as agent_id,

                    a.phone,

                    a.state AS address,

                    a.latitude,

                    a.longitude,

                    a.map_url AS google_map_url,

                    a.is_online_only,

                    wh.day_of_week,

                    wh.start_time,

                    wh.end_time,

                    wh.is_closed

                FROM

                    users u

                JOIN

                    roles r ON u.role_id = r.id

                JOIN

                    agents a ON u.id = a.user_id

                LEFT JOIN

                    working_hours wh ON a.id = wh.agent_id

                WHERE

                    r.name = 'marketer'

                ORDER BY

                    u.id, wh.id

            ";



            $stmt = $this->db->prepare($sql);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $agents = [];

            foreach ($results as $row) {

                $agentId = $row['agent_id'];

                if (!isset($agents[$agentId])) {

                    $agents[$agentId] = [

                        'name' => $row['name'],

                        'coordinates' => [

                            'latitude' => $row['latitude'],

                            'longitude' => $row['longitude']

                        ],

                        'google_map_url' => $row['google_map_url'],

                        'phone' => $row['phone'],

                        'service_type' => $row['is_online_only'] ? 'اونلاين فقط' : 'نقاط شحن',

                        'address' => $row['address'],

                        'working_hours' => []

                    ];

                }



                if ($row['day_of_week']) {

                    $day = strtolower($row['day_of_week']);

                    $startTime = $row['start_time'] ? date('h:i A', strtotime($row['start_time'])) : '';

                    $endTime = $row['end_time'] ? date('h:i A', strtotime($row['end_time'])) : '';

                    

                    $agents[$agentId]['working_hours'][$day] = $row['is_closed']

                        ? 'مغلق'

                        : 'مفتوح من ' . $startTime . ' إلى ' . $endTime;

                }

            }



            // Fill in missing days for working_hours

            $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

            foreach ($agents as &$agent) {

                $existingDays = array_keys($agent['working_hours']);

                foreach ($daysOfWeek as $day) {

                    if (!in_array($day, $existingDays)) {

                        $agent['working_hours'][$day] = 'غير محدد';

                    }

                }

            }



            return array_values($agents); // Re-index the array



        } catch (PDOException $e) {

            error_log("Error fetching agent details: " . $e->getMessage());

            return [];

        }

    }

    /**
     * Get users by a list of role names.
     *
     * @param array $roles An array of role names (e.g., ['agent', 'Team_leader'])
     * @return array An array of user objects
     */
    public function getUsersByRoles(array $roles)
    {
        if (empty($roles)) {
            return [];
        }

        try {
            // Create placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($roles), '?'));

            $sql = "SELECT u.id, u.name, u.username
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE r.name IN ($placeholders)
                    ORDER BY u.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($roles);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);

        } catch (PDOException $e) {
            error_log("Error in getUsersByRoles: " . $e->getMessage());
            return [];
        }
    }
}