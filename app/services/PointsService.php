<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class PointsService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function calculateForActivity(&$activity) {
        $points = 0;
        switch ($activity->activity_type) {
            case 'Outgoing Call':
                $points = $this->getCallPoints('outgoing');
                break;
            case 'Incoming Call':
                // An incoming call activity should always get points as a call.
                $points = $this->getCallPoints('incoming');
                break;
            case 'Ticket':
                $points = $this->getTicketPoints($activity->activity_id);
                break;
        }
        
        $activity->points = $points;
    }

    private function getCallPoints($callType) {
        $stmt = $this->db->prepare("SELECT points FROM call_points WHERE call_type = :call_type AND valid_from <= NOW() AND (valid_to >= NOW() OR valid_to IS NULL) ORDER BY valid_from DESC LIMIT 1");
        $stmt->execute([':call_type' => $callType]);
        
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->points : 0;
    }

    private function getTicketPoints($ticketDetailId) {
        // First, get ticket details including platform name
        $stmt = $this->db->prepare("
            SELECT td.platform_id, p.name as platform_name, td.is_vip, td.code_id
            FROM ticket_details td
            JOIN platforms p ON td.platform_id = p.id
            WHERE td.id = :ticket_detail_id
        ");
        $stmt->execute([':ticket_detail_id' => $ticketDetailId]);
        $ticketDetail = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$ticketDetail) {
            return 0;
        }
        
        $platformName = strtolower(str_replace('_', ' ', $ticketDetail->platform_name));
        if ($platformName === 'incoming call' || $platformName === 'incoming calls') {
            return 0;
        }
        
        // get points based on code and VIP status
        $stmt = $this->db->prepare("
            SELECT points FROM ticket_code_points 
            WHERE code_id = :code_id 
            AND is_vip = :is_vip 
            AND valid_from <= NOW() AND (valid_to >= NOW() OR valid_to IS NULL) 
            ORDER BY valid_from DESC LIMIT 1
        ");
        $stmt->execute([
            ':code_id' => $ticketDetail->code_id,
            ':is_vip' => $ticketDetail->is_vip
        ]);

        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (float)$result->points : 0;
    }
} 