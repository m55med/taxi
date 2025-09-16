<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class UserDelegation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllWithDetails()
    {
        try {
            $query = "
                SELECT 
                    ud.id,
                    ud.reason,
                    ud.applicable_month,
                    ud.applicable_year,
                    ud.created_at,
                    u.username as user_name,
                    dt.name as delegation_type_name,
                    dt.percentage,
                    au.username as assigned_by_user_name
                FROM 
                    user_delegations ud
                JOIN 
                    users u ON ud.user_id = u.id
                JOIN 
                    delegation_types dt ON ud.delegation_type_id = dt.id
                JOIN
                    users au ON ud.assigned_by_user_id = au.id
                ORDER BY 
                    ud.applicable_year DESC, ud.applicable_month DESC, u.username ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (\PDOException $e) {
            // Log error
            return [];
        }
    }

    public function create($userId, $delegationTypeId, $reason, $month, $year, $assignedBy)
    {
        try {
            $query = "
                INSERT INTO user_delegations 
                    (user_id, delegation_type_id, reason, applicable_month, applicable_year, assigned_by_user_id) 
                VALUES 
                    (:user_id, :delegation_type_id, :reason, :applicable_month, :applicable_year, :assigned_by_user_id)
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':delegation_type_id', $delegationTypeId, PDO::PARAM_INT);
            $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
            $stmt->bindParam(':applicable_month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':applicable_year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':assigned_by_user_id', $assignedBy, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            // The UNIQUE constraint will throw a PDOException with code 23000
            if ($e->getCode() == '23000') {
                return 'duplicate';
            }
            // Log other errors
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $query = "DELETE FROM user_delegations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            // Log error
            return false;
        }
    }
    
    public function getDelegationForUser($userId, $month, $year)
    {
        try {
            $query = "
                SELECT dt.percentage 
                FROM user_delegations ud
                JOIN delegation_types dt ON ud.delegation_type_id = dt.id
                WHERE ud.user_id = :user_id 
                AND ud.applicable_month = :month 
                AND ud.applicable_year = :year
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            if ($result) {

                return convert_dates_for_display($result, ['created_at', 'updated_at']);

            }


            return $result;
        } catch (\PDOException $e) {
            // Log error
            return null;
        }
    }
} 