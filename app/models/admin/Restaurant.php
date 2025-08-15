<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new restaurant record.
     */
    public function create($data)
    {
        $sql = "
            INSERT INTO restaurants 
            (name_ar, name_en, category, governorate, city, address, is_chain, num_stores, contact_name, email, phone, pdf_path) 
            VALUES 
            (:name_ar, :name_en, :category, :governorate, :city, :address, :is_chain, :num_stores, :contact_name, :email, :phone, :pdf_path)
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
        $sql = "SELECT * FROM restaurants WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
