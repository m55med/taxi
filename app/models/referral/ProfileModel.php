<?php

namespace App\Models\Referral;

use App\Core\Database;
use PDO;

class ProfileModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds an agent's profile by their user ID.
     *
     * @param int $userId The ID of the logged-in user.
     * @return array|false The agent's data or false if not found.
     */
    public function getAgentByUserId(int $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM agents WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all agents and joins their corresponding user information.
     *
     * @return array A list of all agents with their user details.
     */
    public function getAllAgentsWithUsers(): array
    {
        $sql = "SELECT 
                    u.id,
                    u.username,
                    u.email,
                    r.name as role_name,
                    a.id as agent_id,
                    a.state,
                    a.phone,
                    a.is_online_only,
                    a.map_url,
                    a.latitude,
                    a.longitude,
                    COALESCE(vs.total_visits, 0) as total_visits,
                    COALESCE(vs.total_registrations, 0) as total_registrations,
                    IF(COALESCE(vs.total_visits, 0) > 0, (COALESCE(vs.total_registrations, 0) / vs.total_visits) * 100, 0) AS conversion_rate
                FROM users u
                LEFT JOIN agents a ON u.id = a.user_id
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN (
                    SELECT
                        affiliate_user_id,
                        COUNT(id) AS total_visits,
                        SUM(CASE WHEN registration_status = 'successful' THEN 1 ELSE 0 END) AS total_registrations
                    FROM referral_visits
                    GROUP BY affiliate_user_id
                ) AS vs ON u.id = vs.affiliate_user_id
                WHERE r.name IN ('marketer', 'Team_leader')
                ORDER BY u.username ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    /**
     * Creates or updates an agent's profile.
     *
     * @param array $data The data for the agent.
     * @return bool True on success, false on failure.
     */
    public function createOrUpdateAgent(array $data): bool
    {
        // Check if agent already exists
        $agent = $this->getAgentByUserId($data['user_id']);

        if ($agent) {
            // Update existing agent
            $sql = "UPDATE agents 
                    SET state = :state, phone = :phone, is_online_only = :is_online_only, 
                        latitude = :latitude, longitude = :longitude, map_url = :map_url
                    WHERE user_id = :user_id";
        } else {
            // Insert new agent
            $sql = "INSERT INTO agents (user_id, state, phone, is_online_only, latitude, longitude, map_url) 
                    VALUES (:user_id, :state, :phone, :is_online_only, :latitude, :longitude, :map_url)";
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $data['user_id'],
                ':state' => $data['state'],
                ':phone' => $data['phone'],
                ':is_online_only' => $data['is_online_only'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':map_url' => $data['map_url']
            ]);
        } catch (\PDOException $e) {
            error_log("Agent Profile Error: " . $e->getMessage());
            return false;
        }
    }

    public function getWorkingHoursByAgentId(int $agentId): array
    {
        $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $workingHours = [];

        // Initialize with default values for all days
        foreach ($daysOfWeek as $day) {
            $workingHours[$day] = [
                'day_of_week' => $day,
                'start_time' => null,
                'end_time' => null,
                'is_closed' => true // Default to closed
            ];
        }

        // Fetch existing records from the database
        $stmt = $this->db->prepare("SELECT day_of_week, start_time, end_time, is_closed FROM working_hours WHERE agent_id = ?");
        $stmt->execute([$agentId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Overwrite defaults with database values
        foreach ($results as $row) {
            $day = $row['day_of_week'];
            if (isset($workingHours[$day])) {
                $workingHours[$day]['start_time'] = $row['start_time'];
                $workingHours[$day]['end_time'] = $row['end_time'];
                $workingHours[$day]['is_closed'] = (bool)$row['is_closed'];
            }
        }

        return $workingHours;
    }

    public function saveWorkingHours(int $agentId, array $hoursData): bool
    {
        $sql = "INSERT INTO working_hours (agent_id, day_of_week, start_time, end_time, is_closed)
                VALUES (:agent_id, :day_of_week, :start_time, :end_time, :is_closed)
                ON DUPLICATE KEY UPDATE 
                start_time = VALUES(start_time), 
                end_time = VALUES(end_time), 
                is_closed = VALUES(is_closed)";
        
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare($sql);

            foreach ($hoursData as $day => $times) {
                $is_closed = isset($times['is_closed']) ? 1 : 0;
                $stmt->execute([
                    ':agent_id' => $agentId,
                    ':day_of_week' => $day,
                    ':start_time' => !$is_closed ? $times['start_time'] : null,
                    ':end_time' => !$is_closed ? $times['end_time'] : null,
                    ':is_closed' => $is_closed
                ]);
            }

            return $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Working Hours Save Error: " . $e->getMessage());
            return false;
        }
    }
} 