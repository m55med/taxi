<?php

namespace App\Models\Assignment;

use App\Core\Database;
use PDO;
use PDOException;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Assignment
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAssignmentsReport($filters = [])
    {
        try {
            $sql = "SELECT 
                        a.*,
                        d.name as driver_name,
                        d.phone as driver_phone,
                        u1.username as from_user,
                        u2.username as to_user
                    FROM driver_assignments a
                    LEFT JOIN drivers d ON a.driver_id = d.id
                    LEFT JOIN users u1 ON a.from_user_id = u1.id
                    LEFT JOIN users u2 ON a.to_user_id = u2.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['from_user_id'])) {
                $sql .= " AND a.from_user_id = ?";
                $params[] = $filters['from_user_id'];
            }

            if (!empty($filters['to_user_id'])) {
                $sql .= " AND a.to_user_id = ?";
                $params[] = $filters['to_user_id'];
            }

            if (isset($filters['is_seen'])) {
                $sql .= " AND a.is_seen = ?";
                $params[] = $filters['is_seen'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(a.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(a.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY a.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // تحويل التواريخ للعرض بالتوقيت المحلي

            return convert_dates_for_display($results, ['created_at', 'updated_at']);
        } catch (PDOException $e) {
            error_log("Error in getAssignmentsReport: " . $e->getMessage());
            return [];
        }
    }
} 