<?php

namespace App\Models\Token;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Token Model - للتعامل مع جدول user_tokens
 * يدير إنشاء وتحقق وإدارة التوكنات الخاصة بالمستخدمين
 */
class Token extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * إنشاء توكن جديد للمستخدم
     * يحذف التوكنات القديمة أولاً
     */
    public function createToken(int $userId, int $expiresAfterMinutes = 30): ?string
    {
        $this->db->beginTransaction();

        try {
            // حذف التوكنات القديمة للمستخدم
            $this->deleteUserTokens($userId);
            // إنشاء توكن جديد
            $token = bin2hex(random_bytes(16)); // توكن عشوائي 32 حرف
            $lastActivity = date('Y-m-d H:i:s');

            $sql = "INSERT INTO user_tokens (user_id, token, last_activity, expires_after_minutes, created_at)
                    VALUES (:user_id, :token, :last_activity, :expires_after_minutes, :created_at)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':last_activity' => $lastActivity,
                ':expires_after_minutes' => $expiresAfterMinutes,
                ':created_at' => $lastActivity
            ]);

            if ($result) {
                $this->db->commit();
                return $token;
            }

            $this->db->rollBack();
            return null;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating token for user {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * التحقق من صحة التوكن
     * التوكن صالح إذا: NOW() - last_activity < expires_after_minutes
     */
    public function isTokenValid(string $token): bool
    {
        try {
            $sql = "SELECT user_id, last_activity, expires_after_minutes
                    FROM user_tokens
                    WHERE token = :token
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return false;
            }

            // حساب الفرق بين الوقت الحالي وآخر نشاط
            $lastActivity = strtotime($result['last_activity']);
            $currentTime = time();
            $differenceMinutes = ($currentTime - $lastActivity) / 60;

            return $differenceMinutes < $result['expires_after_minutes'];

        } catch (PDOException $e) {
            error_log("Error validating token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تحديث آخر نشاط للتوكن
     */
    public function updateTokenActivity(string $token): bool
    {
        try {
            $sql = "UPDATE user_tokens
                    SET last_activity = :last_activity
                    WHERE token = :token";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':token' => $token,
                ':last_activity' => date('Y-m-d H:i:s')
            ]);

        } catch (PDOException $e) {
            error_log("Error updating token activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف جميع التوكنات لمستخدم معين
     */
    public function deleteUserTokens(int $userId): bool
    {
        try {
            $sql = "DELETE FROM user_tokens WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);

        } catch (PDOException $e) {
            error_log("Error deleting user tokens for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على التوكن الحالي للمستخدم
     */
    public function getUserToken(int $userId): ?string
    {
        try {
            $sql = "SELECT token FROM user_tokens
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['token'] : null;

        } catch (PDOException $e) {
            error_log("Error getting user token for user {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على معلومات التوكن
     */
    public function getTokenInfo(string $token): ?array
    {
        try {
            $sql = "SELECT ut.*, u.username, u.name
                    FROM user_tokens ut
                    JOIN users u ON ut.user_id = u.id
                    WHERE ut.token = :token
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;

        } catch (PDOException $e) {
            error_log("Error getting token info: " . $e->getMessage());
            return null;
        }
    }

    /**
     * حذف التوكنات المنتهية الصلاحية
     */
    public function deleteExpiredTokens(): int
    {
        try {
            // حذف التوكنات التي مر عليها أكثر من expires_after_minutes
            $sql = "DELETE FROM user_tokens
                    WHERE TIMESTAMPDIFF(MINUTE, last_activity, NOW()) >= expires_after_minutes";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->rowCount();

        } catch (PDOException $e) {
            error_log("Error deleting expired tokens: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * الحصول على التوكنات النشطة للمستخدم (للإدارة)
     */
    public function getUserActiveTokens(int $userId): array
    {
        try {
            $sql = "SELECT token, last_activity, expires_after_minutes, created_at
                    FROM user_tokens
                    WHERE user_id = :user_id
                    AND TIMESTAMPDIFF(MINUTE, last_activity, NOW()) < expires_after_minutes
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getting user active tokens for user {$userId}: " . $e->getMessage());
            return [];
        }
    }
}
