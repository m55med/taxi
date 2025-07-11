<?php
namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class EmployeeEvaluation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new employee evaluation.
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO employee_evaluations (user_id, evaluator_id, score, comment, applicable_month, applicable_year) 
                VALUES (:user_id, :evaluator_id, :score, :comment, :applicable_month, :applicable_year)";
        
        $this->db->query($sql);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':evaluator_id', $data['evaluator_id']);
        $this->db->bind(':score', $data['score']);
        $this->db->bind(':comment', $data['comment']);
        $this->db->bind(':applicable_month', $data['month']);
        $this->db->bind(':applicable_year', $data['year']);

        return $this->db->execute();
    }

    /**
     * Find all evaluations with detailed information (user names, evaluator names).
     * @return array
     */
    public function findAllWithDetails(): array {
        $sql = "SELECT 
                    ee.id, 
                    ee.score,
                    ee.comment,
                    ee.applicable_month,
                    ee.applicable_year,
                    ee.created_at,
                    u_employee.username AS user_name,
                    u_evaluator.username AS evaluator_name
                FROM 
                    employee_evaluations ee
                JOIN 
                    users u_employee ON ee.user_id = u_employee.id
                JOIN 
                    users u_evaluator ON ee.evaluator_id = u_evaluator.id
                ORDER BY 
                    ee.applicable_year DESC, ee.applicable_month DESC, u_employee.username ASC";
        
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Delete an evaluation by its ID.
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool {
        $sql = "DELETE FROM employee_evaluations WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Find an evaluation by ID.
     * @param int $id
     * @return mixed
     */
    public function findById(int $id) {
        $this->db->query('SELECT * FROM employee_evaluations WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
} 