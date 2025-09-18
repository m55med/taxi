<?php



namespace App\Models\Tickets;



use App\Core\Model;

use PDO;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Subcategory extends Model

{

    public function __construct()

    {

        parent::__construct();

    }



    /**

     * Finds all subcategories for a given category ID.

     *

     * @param int $categoryId

     * @return array

     */

    public function getByCategoryId($categoryId)

    {

        $stmt = $this->db->prepare("SELECT id, name FROM ticket_subcategories WHERE category_id = ? ORDER BY name ASC");

        $stmt->execute([$categoryId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }

} 