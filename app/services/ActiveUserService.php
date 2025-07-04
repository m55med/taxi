<?php

namespace App\Services;

use PDO;

class ActiveUserService
{
    private $cacheFile;
    private $onlineThreshold; // in seconds

    public function __construct()
    {
        $this->cacheFile = APPROOT . '/cache/active_users.json';
        $this->onlineThreshold = 90; // Users are considered online if active within the last 90 seconds
        
        // Ensure cache directory exists
        if (!file_exists(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0755, true);
        }
    }

    /**
     * Updates the last seen timestamp for a given user.
     *
     * @param int $userId The ID of the user.
     */
    public function recordUserActivity(int $userId): void
    {
        $users = $this->readActiveUsers();
        $users[$userId] = time(); // Record current timestamp
        $this->writeActiveUsers($users);
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
        if (flock($fp, LOCK_SH)) {
            $data = stream_get_contents($fp);
            flock($fp, LOCK_UN);
        } else {
            // Could not get a lock, return empty array
            $data = null;
        }
        fclose($fp);

        return $data ? json_decode($data, true) : [];
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
            $filteredUsers = array_filter($users, function($timestamp) use ($now) {
                // Keep users seen within the last 24 hours
                return ($now - $timestamp) < (24 * 60 * 60); 
            });

            fwrite($fp, json_encode($filteredUsers));
            flock($fp, LOCK_UN);
        }
        // If lock fails, data is not written.
        fclose($fp);
    }
} 