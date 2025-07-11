<?php

namespace App\Models\User;

use App\Core\Database;
use PDO;
use PDOException;

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
        $sql = "SELECT u.*, r.name as role FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'banned') {
                return ['error' => 'تم حظر حسابك'];
            } elseif ($user['status'] === 'pending') {
                return ['error' => 'حسابك قيد المراجعة'];
            }

            // تحديث حالة الاتصال
            $this->updateOnlineStatus($user['id'], 1);

            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'role_id' => $user['role_id'],
                'status' => $user['status']
            ];
        }

        return ['error' => 'اسم المستخدم أو كلمة المرور غير صحيحة'];
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
        $sql = "INSERT INTO users (username, email, password, role_id, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            $userData['username'],
            $userData['email'],
            $userData['password'],
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
            $sql = "SELECT 
                        u.id,
                        u.username,
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
                    $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
                    $searchTerm = "%{$filters['search']}%";
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

            $sql .= " ORDER BY u.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // التأكد من أن جميع الحقول موجودة
            foreach ($users as &$user) {
                $user['role_name'] = $user['role_name'] ?? 'مستخدم';
                $user['role_id'] = $user['role_id'] ?? 3; // افتراضي للمستخدم العادي
                $user['status'] = $user['status'] ?? 'pending';
                $user['is_online'] = $user['is_online'] ?? 0;
                $user['updated_at'] = $user['updated_at'] ?? date('Y-m-d H:i:s');
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // التأكد من أن جميع الحقول المطلوبة موجودة
                $user['role_name'] = $user['role_name'] ?? 'مستخدم';
                $user['status'] = $user['status'] ?? 'pending';
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
            // Check if email already exists for another user
            if (isset($data['email'])) {
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
            // Only update password if a new one is provided
            if (!empty($data['password'])) {
                $fields[] = 'password = :password';
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            // The original method allowed updating these, keeping them for compatibility
            if (isset($data['role_id'])) {
                $fields[] = 'role_id = :role_id';
                $params[':role_id'] = $data['role_id'];
            }
            if (isset($data['status'])) {
                $fields[] = 'status = :status';
                $params[':status'] = $data['status'];
            }
            if (isset($data['force_logout'])) {
                $fields[] = 'force_logout = :force_logout';
                $params[':force_logout'] = $data['force_logout'];
            }


            if (empty($fields)) {
                return ['status' => true, 'message' => 'No changes were made.']; 
            }

            $fields[] = 'updated_at = CURRENT_TIMESTAMP';

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Return the updated name for session update
                return ['status' => true, 'message' => 'Profile updated successfully.', 'name' => $data['name']];
            } else {
                return ['status' => false, 'message' => 'Failed to update profile.'];
            }

        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return ['status' => false, 'message' => 'A database error occurred.'];
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
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveStaff()
    {
        // Adjust the role IDs (e.g., 1 for admin, 2 for staff) as per your `roles` table
        $stmt = $this->db->query("SELECT id, username FROM users WHERE status = 'active' AND role_id IN (1, 2, 4, 5, 6, 7)");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActiveUsers() {
        $stmt = $this->db->query("SELECT * FROM users WHERE status = 'active' ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAvailableForTeamLeadership()
    {
        // Get users who are active and not already leading a team
        $sql = "SELECT u.id, u.username 
                FROM users u
                LEFT JOIN teams t ON u.id = t.team_leader_id
                WHERE u.status = 'active' AND t.id IS NULL
                ORDER BY u.username ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all permissions for a specific user based on their role.
     * @param int $userId
     * @return array
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = "SELECT p.permission_key 
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function getUsersByRole(int $roleId)
    {
        $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}