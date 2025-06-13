<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

class Coupon extends Model
{
    const HOLD_DURATION_MINUTES = 5; // Hold coupon for 5 minutes

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets available coupons for a country, excluding those recently held by others.
     * Limits the result to 3 coupons per distinct value.
     */
    public function getAvailableByCountry(int $countryId, int $currentUserId, array $excludeIds = [])
    {
        // This query uses a window function (ROW_NUMBER) to rank coupons within each value group.
        // It's efficient for "get N items per group" tasks. Requires MySQL 8.0+ or MariaDB 10.2+.
        // We partition by `value` and order by RAND() to get a random selection of 3 coupons per value.
        $sql = "
            SELECT id, code, value
            FROM (
                SELECT 
                    id, code, value,
                    ROW_NUMBER() OVER(PARTITION BY `value` ORDER BY RAND()) as rn
                FROM coupons
                WHERE country_id = :countryId AND is_used = 0
                AND (held_by IS NULL OR held_by = :currentUserId OR held_at < NOW() - INTERVAL " . self::HOLD_DURATION_MINUTES . " MINUTE)
        ";

        $params = [
            ':countryId' => $countryId,
            ':currentUserId' => $currentUserId
        ];
        
        // Exclude coupon IDs that are already selected in the current form.
        if (!empty($excludeIds)) {
            // Create placeholders for the IN clause
            $excludePlaceholders = implode(',', array_map(fn($i) => ":exclude_id_$i", array_keys($excludeIds)));
            $sql .= " AND id NOT IN ($excludePlaceholders)";
            // Add the excluded IDs to the parameters array
            foreach ($excludeIds as $key => $id) {
                $params[":exclude_id_$key"] = $id;
            }
        }
        
        $sql .= "
            ) AS ranked_coupons
            WHERE rn <= 3
            ORDER BY `value` ASC, code ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Attempts to place a hold on a specific coupon for a user.
     * Returns true only if the hold was successfully acquired.
     */
    public function hold(int $couponId, int $userId): bool
    {
        $this->db->beginTransaction();

        try {
            // Step 1: Lock the specific coupon row for update to prevent race conditions.
            $stmt = $this->db->prepare("SELECT is_used, held_by, held_at FROM coupons WHERE id = :couponId FOR UPDATE");
            $stmt->execute([':couponId' => $couponId]);
            $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

            // If coupon doesn't exist, fail.
            if (!$coupon) {
                $this->db->rollBack();
                return false;
            }

            // Step 2: Check if the coupon is already permanently used.
            if ($coupon['is_used']) {
                $this->db->rollBack();
                return false;
            }

            // Step 3: Check if the coupon is currently held by another user with an active hold.
            $isHeldByOther = $coupon['held_by'] !== null && (int)$coupon['held_by'] !== $userId;
            
            if ($isHeldByOther) {
                // The coupon is held by someone else, check if their hold has expired.
                $holdTimestamp = strtotime($coupon['held_at']);
                $expirationTimestamp = time() - (self::HOLD_DURATION_MINUTES * 60);

                // If the hold time is more recent than the expiration time, the hold is active.
                if ($holdTimestamp > $expirationTimestamp) {
                    $this->db->rollBack();
                    return false; // Actively held by another user.
                }
            }

            // Step 4: If all checks pass, the coupon is available. Hold it for the current user.
            $updateStmt = $this->db->prepare("UPDATE coupons SET held_by = :userId, held_at = NOW() WHERE id = :couponId");
            $updateStmt->execute([':userId' => $userId, ':couponId' => $couponId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log the error for debugging.
            error_log("Coupon hold transaction failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Releases a hold on a specific coupon if held by the given user.
     */
    public function release(int $couponId, int $userId): bool
    {
        $sql = "UPDATE coupons SET held_by = NULL, held_at = NULL WHERE id = :couponId AND held_by = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':couponId' => $couponId, ':userId' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Releases all coupons held by a specific user.
     */
    public function releaseAllForUser(int $userId): void
    {
        $sql = "UPDATE coupons SET held_by = NULL, held_at = NULL WHERE held_by = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userId' => $userId]);
    }
} 