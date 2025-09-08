<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class ActiveUserService
{
    private $db;
    private $cacheFile;
    private $onlineThreshold; // in seconds

    public function __construct()
    {
        $this->cacheFile = APPROOT . '/cache/active_users.json';
        $this->onlineThreshold = 1800; // Users are considered online if active within the last 30 minutes
        $this->db = Database::getInstance();

        // Ensure cache directory exists
        if (!file_exists(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0755, true);
        }
    }

    /**
     * Updates the last seen timestamp for a given user and marks them as online.
     *
     * @param int $userId The ID of the user.
     */
    public function recordUserActivity(int $userId): void
    {
        $users = $this->readActiveUsers();
        $users[$userId] = time(); // Record current timestamp
        $this->writeActiveUsers($users);
        $this->updateUserOnlineStatus($userId, 1);
    }

    /**
     * Marks a user as offline.
     *
     * @param int $userId The ID of the user.
     */
    public function logoutUser(int $userId): void
    {
        $users = $this->readActiveUsers();
        unset($users[$userId]);
        $this->writeActiveUsers($users);
        $this->updateUserOnlineStatus($userId, 0);
    }

    /**
     * Scans for inactive users and marks them as offline in the database.
     * This is intended to be run periodically.
     */
    public function cleanupInactiveUsers(): void
    {
        $users = $this->readActiveUsers();
        $now = time();
        $inactiveUserIds = [];

        foreach ($users as $userId => $timestamp) {
            if (($now - $timestamp) > $this->onlineThreshold) {
                $inactiveUserIds[] = $userId;
                unset($users[$userId]); // Remove from active list
            }
        }

        if (!empty($inactiveUserIds)) {
            $this->db->query('UPDATE users SET is_online = 0 WHERE id IN (' . implode(',', $inactiveUserIds) . ')');
            $this->db->execute();
            $this->writeActiveUsers($users); // Save the cleaned list
        }
    }

    /**
     * Returns an array of user IDs that are currently considered online.
     *
     * @return array An array of online user IDs.
     */
    public function getOnlineUserIds(): array
    {
        $users = $this->readActiveUsers();
        $now = time();
        $onlineUserIds = [];

        foreach ($users as $userId => $timestamp) {
            if (($now - $timestamp) < $this->onlineThreshold) {
                $onlineUserIds[] = $userId;
            }
        }

        return $onlineUserIds;
    }

    /**
     * Reads the active users data from the cache file.
     *
     * @return array The associative array of [userId => timestamp].
     */
    private function readActiveUsers(): array
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }

        $fp = fopen($this->cacheFile, 'r');
        $data = '';
        if ($fp) {
            if (flock($fp, LOCK_SH)) {
                $data = stream_get_contents($fp);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

        $decoded = json_decode($data, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return $decoded;
    }


    /**
     * Writes the active users data to the cache file.
     *
     * @param array $users The associative array of [userId => timestamp].
     */
    private function writeActiveUsers(array $users): void
    {
        $fp = fopen($this->cacheFile, 'w');
        if (flock($fp, LOCK_EX)) {
            // Clean up old entries to prevent the file from growing indefinitely
            $now = time();
            $filteredUsers = array_filter($users, function ($timestamp) use ($now) {
                // Keep users seen within the last 24 hours
                return ($now - $timestamp) < (24 * 60 * 60);
            });

            fwrite($fp, json_encode($filteredUsers));
            flock($fp, LOCK_UN);
        }
        // If lock fails, data is not written.
        fclose($fp);
    }

    /**
     * Updates the online status of a user in the database.
     *
     * @param int $userId The ID of the user.
     * @param int $status The online status (1 for online, 0 for offline).
     */
    private function updateUserOnlineStatus(int $userId, int $status): void
    {
        $this->db->query('UPDATE users SET is_online = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $userId);
        $this->db->execute();
    }
}