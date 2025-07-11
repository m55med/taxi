<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class PointsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllTicketCodes() {
        $stmt = $this->db->prepare("SELECT id, name FROM ticket_codes ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketCodePoints() {
        $stmt = $this->db->prepare("
            SELECT 
                tcp.*,
                tc.name as code_name
            FROM ticket_code_points tcp
            JOIN ticket_codes tc ON tcp.code_id = tc.id
            ORDER BY tcp.valid_from DESC, tc.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCallPoints() {
        $stmt = $this->db->prepare("SELECT * FROM call_points ORDER BY call_type, valid_from DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTicketCodePoint($data) {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_code_points (code_id, is_vip, points, valid_from, valid_to) 
            VALUES (:code_id, :is_vip, :points, :valid_from, NULL)
        ");

        return $stmt->execute([
            ':code_id' => $data['code_id'],
            ':is_vip' => $data['is_vip'],
            ':points' => $data['points'],
            ':valid_from' => $data['valid_from']
        ]);
    }

    public function addCallPoint($data) {
        $stmt = $this->db->prepare("
            INSERT INTO call_points (points, call_type, valid_from, valid_to) 
            VALUES (:points, :call_type, :valid_from, NULL)
        ");
        
        return $stmt->execute([
            ':points' => $data['points'],
            ':call_type' => $data['call_type'],
            ':valid_from' => $data['valid_from']
        ]);
    }

    /**
     * Ends a previous point rule by setting its 'valid_to' date.
     * This is to ensure that only one rule is active at a time for a given item.
     */
    public function endPreviousPointRule($table, $conditions, $new_rule_start_date) {
        $where_clauses = [];
        $params = [':valid_to' => $new_rule_start_date];
        foreach($conditions as $key => $value) {
            $where_clauses[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) . " AND valid_to IS NULL" : "WHERE valid_to IS NULL";

        $stmt = $this->db->prepare("
            UPDATE $table 
            SET valid_to = :valid_to 
            $where_sql
        ");

        return $stmt->execute($params);
    }
} 