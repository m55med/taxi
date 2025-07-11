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
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE status = 'active' ORDER BY username ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGrantedBonuses() {
        $stmt = $this->db->prepare("
            SELECT 
                umb.*,
                u.username,
                g.username as granter_name
            FROM user_monthly_bonus umb
            JOIN users u ON umb.user_id = u.id
            LEFT JOIN users g ON umb.granted_by = g.id
            ORDER BY umb.bonus_year DESC, umb.bonus_month DESC, u.username
        ");
        $stmt->execute();
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

    public function bonusExists($userId, $year, $month) {
        $stmt = $this->db->prepare("SELECT id FROM user_monthly_bonus WHERE user_id = :user_id AND bonus_year = :year AND bonus_month = :month");
        $stmt->execute([
            ':user_id' => $userId,
            ':year' => $year,
            ':month' => $month
        ]);
        return $stmt->fetch() !== false;
    }

    public function deleteBonus($id) {
        $stmt = $this->db->prepare("DELETE FROM user_monthly_bonus WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getBonusSettings() {
        // We assume there's only one row with ID 1
        $stmt = $this->db->prepare("SELECT * FROM bonus_settings WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBonusSettings($data) {
        $stmt = $this->db->prepare("
            UPDATE bonus_settings 
            SET 
                min_bonus_percent = :min_bonus_percent,
                max_bonus_percent = :max_bonus_percent,
                predefined_bonus_1 = :predefined_bonus_1,
                predefined_bonus_2 = :predefined_bonus_2,
                predefined_bonus_3 = :predefined_bonus_3,
                updated_by = :updated_by
            WHERE id = 1
        ");

        return $stmt->execute([
            ':min_bonus_percent' => $data['min_bonus_percent'],
            ':max_bonus_percent' => $data['max_bonus_percent'],
            ':predefined_bonus_1' => $data['predefined_bonus_1'],
            ':predefined_bonus_2' => $data['predefined_bonus_2'],
            ':predefined_bonus_3' => $data['predefined_bonus_3'],
            ':updated_by' => $_SESSION['user_id']
        ]);
    }
} 