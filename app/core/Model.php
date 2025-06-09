<?php

namespace App\Core;

use PDO;

class Model
{
    protected $db;

    public function __construct()
    {
        // The getInstance method no longer needs a config parameter.
        $this->db = Database::getInstance();
    }
}
