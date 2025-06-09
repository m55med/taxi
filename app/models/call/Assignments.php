<?php

namespace App\Models\Call;

use App\Core\Model;
use PDO;
use PDOException;

class Assignments extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function assignDriver($data)
    {
        try {
            $this->db->beginTransaction();

            // Step 1: Cancel any previous unseen assignments for this driver
            $stmt1 = $this->db->prepare("UPDATE driver_assignments SET is_seen = 1 WHERE driver_id = ? AND is_seen = 0");
            $stmt1->execute([$data['driver_id']]);

            // Step 2: Create the new assignment record
            $stmt2 = $this->db->prepare("INSERT INTO driver_assignments (driver_id, from_user_id, to_user_id, note, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt2->execute([
                $data['driver_id'],
                $data['from_user_id'],
                $data['to_user_id'],
                $data['note']
            ]);

            // Step 3: Update the driver's main system status to 'pending' for the new agent
            $this->updateDriverStatus($data['driver_id'], 'pending');

            // Step 4: Release the hold on the driver so they can be picked up by the new user
            $this->releaseDriverHold($data['driver_id']);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in assignDriver transaction: " . $e->getMessage());
            return false;
        }
    }

    public function markAssignmentAsSeen($assignmentId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE driver_assignments SET is_seen = 1 WHERE id = ?");
            return $stmt->execute([$assignmentId]);
        } catch (PDOException $e) {
            error_log("Error in markAssignmentAsSeen: " . $e->getMessage());
            return false;
        }
    }

    public function updateDriverStatus($driverId, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE drivers SET main_system_status = ? WHERE id = ?");
            return $stmt->execute([$status, $driverId]);
        } catch (PDOException $e) {
            error_log("Error in updateDriverStatus: " . $e->getMessage());
            return false;
        }
    }

    public function recordCall($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO driver_calls 
                (driver_id, call_by, call_status, notes, next_call_at, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $data['driver_id'],
                $data['user_id'],
                $data['status'],
                $data['notes'],
                $data['next_call_at']
            ]);
        } catch (PDOException $e) {
            error_log("Error in recordCall: " . $e->getMessage());
            return false;
        }
    }

    public function releaseDriverHold($driverId)
    {
        return $this->setDriverHold($driverId, false);
    }

    private function setDriverHold($driverId, $isHeld)
    {
        try {
            $stmt = $this->db->prepare("UPDATE drivers SET hold = ? WHERE id = ?");
            return $stmt->execute([$isHeld ? 1 : 0, $driverId]);
        } catch (PDOException $e) {
            error_log("Error in setDriverHold: " . $e->getMessage());
            return false;
        }
    }
} 