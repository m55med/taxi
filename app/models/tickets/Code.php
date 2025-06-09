<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

class Code extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Finds all codes for a given subcategory ID.
     *
     * @param int $subcategoryId
     * @return array
     */
    public function getBySubcategoryId($subcategoryId)
    {
        $stmt = $this->db->prepare("SELECT id, name FROM ticket_codes WHERE subcategory_id = ? ORDER BY name ASC");
        $stmt->execute([$subcategoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 