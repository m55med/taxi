<?php

namespace App\Models\Reports\Referrals;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class ReferralsReport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getReferrals()
    {
        $sql = "SELECT 
                    r.id, 
                    u.username as referrer_name, 
                    r.referral_code,
                    (SELECT COUNT(*) FROM drivers d WHERE d.referral_id = r.id) as registration_count,
                    r.created_at
                FROM referrals r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }
} 