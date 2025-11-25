<?php

namespace App\Models\TokenManagement;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Token Management Model - لإدارة التوكنات
 * يتعامل مع عرض وإدارة التوكنات مع الفلاتر
 */
class TokenManagement extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * جلب جميع التوكنات مع معلومات المستخدم والفريق
     */
    public function getAllTokens($filters = [])
    {
        try {
            $sql = "SELECT
                        ut.id,
                        ut.token,
                        ut.user_id,
                        ut.last_activity,
                        ut.expires_after_minutes,
                        ut.created_at,
                        u.name as user_name,
                        u.username as user_username,
                        t.id as team_id,
                        t.name as team_name,
                        tl.name as team_leader_name
                    FROM user_tokens ut
                    JOIN users u ON ut.user_id = u.id
                    LEFT JOIN team_members tm ON u.id = tm.user_id
                    LEFT JOIN teams t ON tm.team_id = t.id
                    LEFT JOIN users tl ON t.team_leader_id = tl.id
                    WHERE 1=1";

            $params = [];

            // فلترة حسب المستخدم
            if (!empty($filters['user_id'])) {
                $sql .= " AND ut.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }

            // فلترة حسب الفريق
            if (!empty($filters['team_id'])) {
                $sql .= " AND t.id = :team_id";
                $params[':team_id'] = $filters['team_id'];
            }

            // فلترة حسب الحالة
            if (!empty($filters['status'])) {
                // استخدام UTC time لأن البيانات محفوظة بـ UTC
                $currentTime = gmdate('Y-m-d H:i:s');
                switch ($filters['status']) {
                    case 'active':
                        // التوكنات النشطة: last_activity + expires_after_minutes > NOW
                        $sql .= " AND TIMESTAMPADD(MINUTE, ut.expires_after_minutes, ut.last_activity) > :current_time";
                        $params[':current_time'] = $currentTime;
                        break;
                    case 'expired':
                        // التوكنات المنتهية: last_activity + expires_after_minutes <= NOW
                        $sql .= " AND TIMESTAMPADD(MINUTE, ut.expires_after_minutes, ut.last_activity) <= :current_time";
                        $params[':current_time'] = $currentTime;
                        break;
                }
            }

            // فلترة حسب تاريخ الإنشاء
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(ut.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(ut.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $sql .= " ORDER BY ut.created_at DESC";

            // إضافة pagination إذا كان مطلوباً
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }

            if (!empty($filters['offset'])) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = (int)$filters['offset'];
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // إضافة معلومات الحالة المحسوبة ديناميكياً
            foreach ($results as &$result) {
                $result['status'] = $this->calculateTokenStatus(
                    $result['last_activity'],
                    $result['expires_after_minutes']
                );
            }

            return $results;

        } catch (PDOException $e) {
            error_log("Error getting tokens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * حساب حالة التوكن ديناميكياً
     */
    private function calculateTokenStatus($lastActivity, $expiresAfterMinutes)
    {
        // البيانات محفوظة بتوقيت UTC في قاعدة البيانات
        // لذا نحسب الصلاحية بتوقيت UTC
        $utcTimezone = new \DateTimeZone('UTC');
        $currentTime = new \DateTime('now', $utcTimezone);
        $lastActivityTime = new \DateTime($lastActivity, $utcTimezone);

        // إضافة دقائق الصلاحية
        $expiryTime = clone $lastActivityTime;
        $expiryTime->add(new \DateInterval('PT' . $expiresAfterMinutes . 'M'));

        if ($expiryTime > $currentTime) {
            return 'active'; // صالح
        } else {
            return 'expired'; // منتهي
        }
    }

    /**
     * إلغاء توكن (soft delete - تحديث last_activity لتاريخ قديم)
     */
    public function revokeToken($tokenId)
    {
        try {
            // جعل التوكن منتهي فوراً بتحديث last_activity لتاريخ قديم جداً
            $sql = "UPDATE user_tokens
                    SET last_activity = '2000-01-01 00:00:00'
                    WHERE id = :token_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':token_id' => $tokenId]);

        } catch (PDOException $e) {
            error_log("Error revoking token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف توكن نهائياً
     */
    public function deleteToken($tokenId)
    {
        try {
            $sql = "DELETE FROM user_tokens WHERE id = :token_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':token_id' => $tokenId]);

        } catch (PDOException $e) {
            error_log("Error deleting token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب قائمة المستخدمين للفلاتر
     */
    public function getUsersList()
    {
        try {
            $sql = "SELECT DISTINCT u.id, u.name, u.username
                    FROM users u
                    WHERE u.status = 'active'
                    ORDER BY u.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getting users list: " . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب قائمة الفرق للفلاتر
     */
    public function getTeamsList()
    {
        try {
            $sql = "SELECT DISTINCT t.id, t.name
                    FROM teams t
                    JOIN team_members tm ON t.id = tm.team_id
                    ORDER BY t.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getting teams list: " . $e->getMessage());
            return [];
        }
    }

    /**
     * إحصائيات التوكنات
     */
    public function getTokenStats($filters = [])
    {
        try {
            $stats = [];

            // بناء الاستعلام الأساسي
            $baseSql = "FROM user_tokens ut
                       JOIN users u ON ut.user_id = u.id
                       LEFT JOIN team_members tm ON u.id = tm.user_id
                       LEFT JOIN teams t ON tm.team_id = t.id";

            $conditions = [];
            $params = [];

            // فلترة حسب المستخدم
            if (!empty($filters['user_id'])) {
                $conditions[] = "ut.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }

            // فلترة حسب الفريق
            if (!empty($filters['team_id'])) {
                $conditions[] = "t.id = :team_id";
                $params[':team_id'] = $filters['team_id'];
            }

            // فلترة حسب تاريخ الإنشاء
            if (!empty($filters['date_from'])) {
                $conditions[] = "DATE(ut.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $conditions[] = "DATE(ut.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            // إضافة الشروط للاستعلام
            $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

            // إجمالي التوكنات حسب الفلاتر
            $stmt = $this->db->prepare("SELECT COUNT(*) " . $baseSql . $whereClause);
            $stmt->execute($params);
            $stats['total'] = (int)$stmt->fetchColumn();

            // التوكنات النشطة والمنتهية حسب الفلاتر
            // استخدام UTC time لأن البيانات محفوظة بـ UTC
            $currentTime = gmdate('Y-m-d H:i:s');
            $stmt = $this->db->prepare("
                SELECT
                    SUM(CASE WHEN TIMESTAMPADD(MINUTE, ut.expires_after_minutes, ut.last_activity) > ? THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN TIMESTAMPADD(MINUTE, ut.expires_after_minutes, ut.last_activity) <= ? THEN 1 ELSE 0 END) as expired
                " . $baseSql . $whereClause
            );

            $allParams = array_merge([$currentTime, $currentTime], $params);
            $stmt->execute($allParams);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active'] = (int)($result['active'] ?? 0);
            $stats['expired'] = (int)($result['expired'] ?? 0);

            // التوكنات المستخدمة اليوم حسب الفلاتر
            $today = date('Y-m-d');
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                " . $baseSql . $whereClause . " AND DATE(ut.last_activity) = :today_date"
            );

            $paramsWithDate = array_merge($params, [':today_date' => $today]);
            $stmt->execute($paramsWithDate);
            $stats['used_today'] = (int)$stmt->fetchColumn();

            return $stats;

        } catch (PDOException $e) {
            error_log("Error getting token stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'used_today' => 0
            ];
        }
    }
}
