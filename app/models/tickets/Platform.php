<?php







namespace App\Models\Tickets;







use PDO;



use App\Core\Model;








// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Platform extends Model



{



    public function __construct()



    {



        parent::__construct();



    }







    public function getAll(): array



    {



        $this->query("SELECT id, name FROM platforms ORDER BY name ASC");

        $results = $this->resultSet();

        // تحويل التواريخ للعرض بتنسيق 12 ساعة + توقيت القاهرة

        return convert_dates_for_display_12h($results, ['created_at', 'updated_at']);



    }



} 