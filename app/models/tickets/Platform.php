<?php



namespace App\Models\Tickets;



use PDO;

use App\Core\Model;



class Platform extends Model

{

    public function __construct()

    {

        parent::__construct();

    }



    public function getAll(): array

    {

        $this->query("SELECT id, name FROM platforms ORDER BY name ASC");

        return $this->resultSet();

    }

} 