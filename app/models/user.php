<?php

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
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, status, role_id) VALUES (:username, :email, :password, :status, :role_id)");
            $result = $stmt->execute([
                ':username' => $data['username'],
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
    public function createUser($userData) {
        $sql = "INSERT INTO users (username, email, password, role_id, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userData['username'],
            $userData['email'],
            $userData['password'],
            $userData['role_id'],
            $userData['status']
        ]);
    }

    // تحديث حالة المستخدم
    public function updateUserStatus($userId, $status) {
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
    public function deleteUser($userId) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    // الحصول على جميع المستخدمين مع الفلترة
    public function getAllUsers($filters = []) {
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
    public function getRoles() {
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
    public function addRole($roleName) {
        $sql = "INSERT INTO roles (name) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roleName]);
    }

    // إحصائيات المستخدمين
    public function countUsers() {
        try {
            $sql = "SELECT COUNT(*) FROM users";
            return $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }

    public function countUsersByStatus($status) {
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

    public function countOnlineUsers() {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE is_online = 1";
            return $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting online users: " . $e->getMessage());
            return 0;
        }
    }

    // تسجيل الخروج
    public function logout($userId) {
        return $this->updateOnlineStatus($userId, 0);
    }

    // تحديث حالة الاتصال
    private function updateOnlineStatus($userId, $status) {
        $sql = "UPDATE users SET is_online = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $userId]);
    }

    // تحديث دور المستخدم
    public function updateUserRole($userId, $roleId) {
        try {
            $sql = "UPDATE users SET role_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$roleId, $userId]);
            return ['status' => $result, 'message' => $result ? 'تم تحديث الدور بنجاح' : 'فشل تحديث الدور'];
        } catch (PDOException $e) {
            error_log("Error updating user role: " . $e->getMessage());
            return ['status' => false, 'message' => 'حدث خطأ أثناء تحديث الدور'];
        }
    }

    // الحصول على معلومات مستخدم محدد
    public function getUserById($id) {
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
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // التأكد من وجود جميع الحقول المطلوبة
                $user['role_name'] = $user['role_name'] ?? 'مستخدم';
                $user['role_id'] = $user['role_id'] ?? 3;
                $user['status'] = $user['status'] ?? 'pending';
                $user['is_online'] = $user['is_online'] ?? 0;
                $user['updated_at'] = $user['updated_at'] ?? date('Y-m-d H:i:s');
                return $user;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error fetching user by ID: " . $e->getMessage());
            return null;
        }
    }

    // تحديث معلومات المستخدم
    public function updateUser($id, $data) {
        try {
            $updates = [];
            $params = [];

            // تحديث اسم المستخدم إذا تم تغييره
            if (!empty($data['username'])) {
                // التحقق من وجود اسم المستخدم لمستخدم آخر
                $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$data['username'], $id]);
                if ($stmt->fetch()) {
                    return ['status' => false, 'message' => 'اسم المستخدم مستخدم بالفعل'];
                }
                $updates[] = "username = ?";
                $params[] = $data['username'];
            }

            // تحديث البريد الإلكتروني إذا تم تغييره
            if (!empty($data['email'])) {
                // التحقق من وجود البريد الإلكتروني لمستخدم آخر
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $id]);
                if ($stmt->fetch()) {
                    return ['status' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل'];
                }
                $updates[] = "email = ?";
                $params[] = $data['email'];
            }

            // تحديث كلمة المرور إذا تم تغييرها
            if (!empty($data['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // تحديث الدور إذا تم تغييره
            if (isset($data['role_id'])) {
                $updates[] = "role_id = ?";
                $params[] = $data['role_id'];
            }

            // تحديث الحالة إذا تم تغييرها
            if (isset($data['status'])) {
                $updates[] = "status = ?";
                $params[] = $data['status'];
            }

            if (empty($updates)) {
                return ['status' => false, 'message' => 'لم يتم إجراء أي تغييرات'];
            }

            // إضافة معرف المستخدم للمعلمات
            $params[] = $id;

            $sql = "UPDATE users SET " . implode(", ", $updates) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            return [
                'status' => $result,
                'message' => $result ? 'تم تحديث المستخدم بنجاح' : 'فشل تحديث المستخدم'
            ];
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return ['status' => false, 'message' => 'حدث خطأ أثناء تحديث المستخدم'];
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // التحقق من كلمة المرور الحالية
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['status' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'];
            }

            // تحديث كلمة المرور
            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $userId
            ]);

            return [
                'status' => $result,
                'message' => $result ? 'تم تغيير كلمة المرور بنجاح' : 'فشل تغيير كلمة المرور'
            ];
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            return ['status' => false, 'message' => 'حدث خطأ أثناء تغيير كلمة المرور'];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in User::getById: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveStaff() {
        try {
            $sql = "SELECT id, username FROM users WHERE status = 'active' AND role_id IN (1, 2)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active staff: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveUsers() {
        try {
            $sql = "SELECT id, username, is_online FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active users: " . $e->getMessage());
            return [];
        }
    }
}
