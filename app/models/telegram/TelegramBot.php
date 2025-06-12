<?php

namespace App\Models\Telegram;

use App\Core\Model;
use PDO;

class TelegramBot extends Model
{
    /**
     * Checks if a user is authorized to interact with the bot in a specific chat.
     *
     * @param int $telegramUserId The user's Telegram ID.
     * @param int $telegramChatId The chat's Telegram ID.
     * @return bool True if authorized, false otherwise.
     */
    public function isAuthorized(int $telegramUserId, int $telegramChatId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM telegram_links WHERE telegram_user_id = ? AND telegram_chat_id = ?"
        );
        $stmt->execute([$telegramUserId, $telegramChatId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getSystemUserId(int $telegramUserId): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id FROM telegram_links WHERE telegram_user_id = ?");
        $stmt->execute([$telegramUserId]);
        $result = $stmt->fetchColumn();
        return $result ? (int)$result : null;
    }

    public function findDriversByPhones(array $phones): array
    {
        if (empty($phones)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($phones), '?'));
        $stmt = $this->db->prepare("SELECT * FROM drivers WHERE phone IN ($placeholders)");
        $stmt->execute($phones);
        
        $driversByPhone = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $driversByPhone[$row['phone']] = $row;
        }
        return $driversByPhone;
    }
    
    public function getLastCall(int $driverId): ?array
    {
        // Changed to LEFT JOIN to ensure a call record is returned even if the user who made it has been deleted.
        $sql = "
            SELECT 
                dc.*, 
                u.username AS caller_username
            FROM driver_calls dc
            LEFT JOIN users u ON dc.call_by = u.id
            WHERE dc.driver_id = ? 
            ORDER BY dc.created_at DESC 
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$driverId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addDrivers(array $phones, int $addedBy): int
    {
        if (empty($phones)) {
            return 0;
        }

        // Added `name` to the INSERT statement, using the phone as a default name to satisfy the NOT NULL constraint.
        $sql = "INSERT INTO drivers (name, phone, data_source, main_system_status, added_by, notes) VALUES (?, ?, 'telegram', 'reconsider', ?, ?)";
        $stmt = $this->db->prepare($sql);
        $note = 'رقم هاتف مضاف بواسطة الأدمن عبر تليجرام';
        
        $count = 0;
        foreach ($phones as $phone) {
            // Pass the phone number for both name and phone fields.
            if ($stmt->execute([$phone, $phone, $addedBy, $note])) {
                $count++;
            }
        }
        return $count;
    }
} 