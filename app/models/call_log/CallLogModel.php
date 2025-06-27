<?php

namespace App\Models\call_log;

use App\Core\Model;
use PDO;

class CallLogModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function startCall(string $phoneNumber, int $userId): ?int
    {
        $sql = "INSERT INTO incoming_calls (caller_phone_number, call_received_by) VALUES (:phone, :user_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':phone' => $phoneNumber,
            ':user_id' => $userId
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function linkTicketDetail(int $callLogId, int $ticketDetailId)
    {
        $sql = "UPDATE incoming_calls SET linked_ticket_detail_id = :ticket_detail_id WHERE id = :call_log_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ticket_detail_id' => $ticketDetailId,
            ':call_log_id' => $callLogId
        ]);
    }
    
    public function getPlatformByName(string $name)
    {
        $stmt = $this->db->prepare("SELECT id FROM platforms WHERE name = :name");
        $stmt->execute([':name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 