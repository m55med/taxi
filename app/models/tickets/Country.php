<?php



namespace App\Models\Tickets;



use App\Core\Model;

use PDO;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Country extends Model

{

    public function __construct()

    {

        parent::__construct();

    }



    /**

     * Get all countries from the database.

     *

     * @return array

     */

    public function getAll()

    {

        $stmt = $this->db->query("SELECT id, name FROM countries ORDER BY name ASC");

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return \convert_dates_for_display_12h($results, ['created_at', 'updated_at']);

    }



    /**

     * Find a country by its ID.

     *

     * @param int $id

     * @return mixed

     */

    public function findById($id)

    {

        $stmt = $this->db->prepare("SELECT * FROM countries WHERE id = ?");

        $stmt->execute([$id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        if ($result) {


            return convert_dates_for_display($result, ['created_at', 'updated_at']);


        }



        return $result;

    }

} 