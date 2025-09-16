<?php



namespace App\Models\Admin;



use App\Core\Database;

use PDO;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class PointsModel {

    private $db;



    public function __construct() {

        $this->db = Database::getInstance();

    }



    public function getAllTicketCodes() {

        $stmt = $this->db->prepare("
            SELECT 
                tc.id, 
                CONCAT(cat.name, ' -> ', sub.name, ' -> ', tc.name) as name
            FROM ticket_codes tc
            JOIN ticket_subcategories sub ON tc.subcategory_id = sub.id
            JOIN ticket_categories cat ON sub.category_id = cat.id
            ORDER BY cat.name, sub.name, tc.name ASC
        ");

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

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

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

    }



    public function getCallPoints() {

        $stmt = $this->db->prepare("SELECT * FROM call_points ORDER BY call_type, valid_from DESC");

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

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