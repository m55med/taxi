<?php

namespace App\Models\Tickets;

use PDO;
use App\Core\Model;

class Category extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM ticket_categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 