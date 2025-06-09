<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 