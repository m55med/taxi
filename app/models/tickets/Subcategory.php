<?php

namespace App\Models\Tickets;

use App\Core\Model;
use PDO;

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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 