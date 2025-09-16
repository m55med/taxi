<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;


// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class Restaurant
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all restaurants from the database.
     */
    public function getAll()
    {
        $sql = "SELECT * FROM restaurants ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    /**
     * Create a new restaurant record.
     */
    public function create($data)
    {
        $sql = "
            INSERT INTO restaurants 
            (name_ar, name_en, category, governorate, city, address, is_chain, num_stores, contact_name, email, phone, pdf_path, referred_by_user_id) 
            VALUES 
            (:name_ar, :name_en, :category, :governorate, :city, :address, :is_chain, :num_stores, :contact_name, :email, :phone, :pdf_path, :referred_by_user_id)
        ";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':name_ar' => $data['name_ar'],
            ':name_en' => $data['name_en'],
            ':category' => $data['category'],
            ':governorate' => $data['governorate'],
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':is_chain' => $data['is_chain'],
            ':num_stores' => $data['num_stores'],
            ':contact_name' => $data['contact_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':pdf_path' => $data['pdf_path'],
            ':referred_by_user_id' => $data['referred_by_user_id'],
        ];

        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Delete a restaurant by its ID.
     */
    public function delete($id)
    {
        $sql = "DELETE FROM restaurants WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get a single restaurant by its ID.
     */
    public function getById($id)
    {
        $sql = "
            SELECT 
                r.*,
                u.username as marketer_name
            FROM 
                restaurants r
            LEFT JOIN 
                users u ON r.referred_by_user_id = u.id
            WHERE r.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        if ($result) {

            return convert_dates_for_display($result, ['created_at', 'updated_at']);

        }


        return $result;
    }

    /**
     * Update a restaurant's data.
     */
    public function update($id, $data)
    {
        $sql = "
            UPDATE restaurants SET
                name_ar = :name_ar,
                name_en = :name_en,
                category = :category,
                governorate = :governorate,
                city = :city,
                address = :address,
                is_chain = :is_chain,
                num_stores = :num_stores,
                contact_name = :contact_name,
                email = :email,
                phone = :phone,
                pdf_path = :pdf_path
            WHERE id = :id
        ";
        
        $stmt = $this->db->prepare($sql);

        $params = [
            ':id' => $id,
            ':name_ar' => $data['name_ar'],
            ':name_en' => $data['name_en'],
            ':category' => $data['category'],
            ':governorate' => $data['governorate'],
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':is_chain' => $data['is_chain'],
            ':num_stores' => $data['num_stores'],
            ':contact_name' => $data['contact_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':pdf_path' => $data['pdf_path'],
        ];

        return $stmt->execute($params);
    }

    /**
     * Update only the pdf_path for a restaurant.
     */
    public function updatePdfPath($id, $fileName)
    {
        $sql = "UPDATE restaurants SET pdf_path = :pdf_path WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':pdf_path' => $fileName, ':id' => $id]);
    }

    public function getFilteredRestaurants($filters = [], $page = 1, $limit = 25)
    {
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(r.id) FROM restaurants r LEFT JOIN users u ON r.referred_by_user_id = u.id WHERE 1=1";
        
        $sql = "
            SELECT 
                r.*,
                u.username as marketer_name
            FROM 
                restaurants r
            LEFT JOIN 
                users u ON r.referred_by_user_id = u.id
            WHERE 1=1
        ";

        $params = [];
        $whereSql = "";

        if (!empty($filters['search'])) {
            $whereSql .= " AND (r.name_en LIKE :search OR r.name_ar LIKE :search OR r.category LIKE :search OR r.city LIKE :search OR r.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['governorate'])) {
            $whereSql .= " AND r.governorate = :governorate";
            $params[':governorate'] = $filters['governorate'];
        }
        if (!empty($filters['category'])) {
            $whereSql .= " AND r.category = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['marketer'])) {
            $whereSql .= " AND r.referred_by_user_id = :marketer";
            $params[':marketer'] = $filters['marketer'];
        }
        if (!empty($filters['start_date'])) {
            $whereSql .= " AND DATE(r.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereSql .= " AND DATE(r.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        // Get total count
        $stmtCount = $this->db->prepare($countSql . $whereSql);
        $stmtCount->execute($params);
        $totalRecords = $stmtCount->fetchColumn();

        // Get paginated data
        $sql .= $whereSql . " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['data' => $data, 'total' => $totalRecords];
    }

    public function getMarketers()
    {
        $sql = "SELECT id, username FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'marketer') ORDER BY username";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // تحويل التواريخ للعرض بالتوقيت المحلي

        return convert_dates_for_display($results, ['created_at', 'updated_at']);
    }

    public function getGovernorates()
    {
        $sql = "SELECT DISTINCT governorate FROM restaurants WHERE governorate IS NOT NULL AND governorate != '' ORDER BY governorate";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM restaurants WHERE category IS NOT NULL AND category != '' ORDER BY category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStats($filters = [])
    {
        // Base query for stats is the same as for filtered restaurants
        $sql = "FROM restaurants r LEFT JOIN users u ON r.referred_by_user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (r.name_en LIKE :search OR r.name_ar LIKE :search OR r.category LIKE :search OR r.city LIKE :search OR r.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['governorate'])) {
            $sql .= " AND r.governorate = :governorate";
            $params[':governorate'] = $filters['governorate'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND r.category = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['marketer'])) {
            $sql .= " AND r.referred_by_user_id = :marketer";
            $params[':marketer'] = $filters['marketer'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(r.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(r.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        $totalRestaurantsQuery = "SELECT COUNT(DISTINCT r.id) " . $sql;
        $stmtTotal = $this->db->prepare($totalRestaurantsQuery);
        $stmtTotal->execute($params);
        $totalRestaurants = $stmtTotal->fetchColumn();

        $directRegistrationsQuery = "SELECT COUNT(DISTINCT r.id) " . $sql . " AND r.referred_by_user_id IS NULL";
        $stmtDirect = $this->db->prepare($directRegistrationsQuery);
        $stmtDirect->execute($params);
        $directRegistrations = $stmtDirect->fetchColumn();
        
        $referredRegistrations = $totalRestaurants - $directRegistrations;

        return [
            'total_restaurants' => $totalRestaurants,
            'direct_registrations' => $directRegistrations,
            'referred_registrations' => $referredRegistrations,
        ];
    }
}
