<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class BonusModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGrantedBonuses() {
        $stmt = $this->db->query("
            SELECT 
                umb.*,
                u.username,
                g.username as granter_name
            FROM user_monthly_bonus umb
            JOIN users u ON umb.user_id = u.id
            LEFT JOIN users g ON umb.granted_by = g.id
            ORDER BY umb.bonus_year DESC, umb.bonus_month DESC, u.username
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBonus($data) {
        $stmt = $this->db->prepare("
            INSERT INTO user_monthly_bonus (user_id, bonus_percent, bonus_year, bonus_month, reason, granted_by)
            VALUES (:user_id, :bonus_percent, :bonus_year, :bonus_month, :reason, :granted_by)
        ");

        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':bonus_percent' => $data['bonus_percent'],
            ':bonus_year' => $data['bonus_year'],
            ':bonus_month' => $data['bonus_month'],
            ':reason' => $data['reason'],
            ':granted_by' => $data['granted_by']
        ]);
    }
} 