<?php

namespace App\Models\Admin;

use App\Core\Model;
use PDO;

class TelegramSetting extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Get admin users who are not yet linked in telegram_links.
     * @return array
     */
    public function getAdminUsers(): array
    {
        // This query now excludes users who are already in the telegram_links table.
        $sql = "
            SELECT id, username 
            FROM users 
            WHERE role_id = 1 AND id NOT IN (SELECT user_id FROM telegram_links)
            ORDER BY username ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all Telegram link settings.
     * @return array
     */
    public function getAllSettings(): array
    {
        // Join with users table to get the username for display
        $sql = "
            SELECT tl.id, tl.user_id, tl.telegram_user_id, tl.telegram_chat_id, u.username 
            FROM telegram_links tl
            JOIN users u ON tl.user_id = u.id
            ORDER BY u.username
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a link already exists for the given user or telegram user.
     * @param int $userId The system user ID.
     * @param int $telegramUserId The Telegram user ID.
     * @return bool
     */
    public function isLinkExist(int $userId, int $telegramUserId): bool
    {
        // We no longer check for telegram_chat_id, as multiple admins can be in the same group.
        // A system user or a telegram user should only be linked once.
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM telegram_links WHERE user_id = ? OR telegram_user_id = ?"
        );
        $stmt->execute([$userId, $telegramUserId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Add a new Telegram link setting.
     * @param int $userId The system user ID.
     * @param int $telegramUserId The Telegram user ID.
     * @param int $telegramChatId The Telegram chat/group ID.
     * @return bool
     */
    public function addSetting(int $userId, int $telegramUserId, int $telegramChatId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO telegram_links (user_id, telegram_user_id, telegram_chat_id) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$userId, $telegramUserId, $telegramChatId]);
    }

    /**
     * Delete a Telegram link setting.
     * @param int $id The ID of the link to delete.
     * @return bool
     */
    public function deleteSetting(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM telegram_links WHERE id = ?");
        return $stmt->execute([$id]);
    }
} 