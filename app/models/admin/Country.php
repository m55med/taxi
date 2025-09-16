<?php



namespace App\Models\Admin;



use App\Core\Database;

use PDO;

use PDOException;



class Country

{

    private $db;



    public function __construct()

    {

        $this->db = Database::getInstance();

    }



    public function getAll()

    {

        try {

            $stmt = $this->db->prepare("SELECT * FROM countries ORDER BY name ASC");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (PDOException $e) {

            return [];

        }

    }



    public function create($name)

    {

        try {

            $stmt = $this->db->prepare("INSERT INTO countries (name) VALUES (:name)");

            $stmt->bindParam(':name', $name);

            return $stmt->execute();

        } catch (PDOException $e) {

            return false;

        }

    }



    public function delete($id)

    {

        // Check for dependencies before deleting
        $dependencies = $this->getCountryUsage($id);
        if (!empty($dependencies)) {
            // Construct a detailed message
            $messages = [];
            foreach ($dependencies as $table => $count) {
                $tableName = str_replace('_', ' ', rtrim($table, 's')); // a simple pluralization
                $messages[] = "{$count} {$tableName}(s)";
            }
            // Instead of returning false, we return a reason
            return "Cannot delete country. It is in use by " . implode(', ', $messages) . ".";

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

        }

        try {
            $stmt = $this->db->prepare("DELETE FROM countries WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true; // Success
            }
            return "Database error during deletion."; // Generic database error
        } catch (PDOException $e) {
            // Log the actual error for debugging
            error_log("Country delete error: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    private function getCountryUsage($countryId) {
        $tablesToCheck = [
            'ticket_details' => 'country_id',
            'coupons' => 'country_id',
            // Add other tables here if they reference countries
        ];
        
        $usage = [];

        foreach ($tablesToCheck as $table => $column) {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :country_id");
                $stmt->execute([':country_id' => $countryId]);
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    $usage[$table] = $count;
                }
            } catch (PDOException $e) {
                error_log("Dependency check error on table {$table}: " . $e->getMessage());
                // If a check fails, we should probably assume there's a dependency to be safe
                $usage[$table] = 'an unknown number of records due to a check error';
            }
        }

        return $usage;
    }
}

