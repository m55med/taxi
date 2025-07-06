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

    public function getSubcategoriesByCategoryId(int $categoryId): array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM ticket_subcategories WHERE category_id = :category_id ORDER BY name ASC");
        $stmt->execute([':category_id' => $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCodesBySubcategoryId(int $subcategoryId): array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM ticket_codes WHERE subcategory_id = :subcategory_id ORDER BY name ASC");
        $stmt->execute([':subcategory_id' => $subcategoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Efficiently fetches all categories with their nested subcategories and codes.
     * This method avoids the N+1 query problem by fetching all items at once and
     * assembling them in PHP.
     *
     * @return array The nested structure of categories.
     */
    public function getAllCategoriesWithSubcategoriesAndCodes(): array
    {
        try {
            // 1. Fetch all data in three separate queries
            $categories = $this->db->query("SELECT id, name FROM ticket_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $subcategories = $this->db->query("SELECT id, name, category_id FROM ticket_subcategories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $codes = $this->db->query("SELECT id, name, subcategory_id FROM ticket_codes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

            // 2. Index data for efficient lookup
            $indexedCategories = [];
            foreach ($categories as $category) {
                $category['subcategories'] = [];
                $indexedCategories[$category['id']] = $category;
            }

            $indexedSubcategories = [];
            foreach ($subcategories as $subcategory) {
                $subcategory['codes'] = [];
                $indexedSubcategories[$subcategory['id']] = $subcategory;
            }

            // 3. Assemble the nested structure
            // Add codes to their subcategories
            foreach ($codes as $code) {
                if (isset($indexedSubcategories[$code['subcategory_id']])) {
                    $indexedSubcategories[$code['subcategory_id']]['codes'][] = $code;
                }
            }

            // Add subcategories (now with codes) to their categories
            foreach ($indexedSubcategories as $subcategory) {
                if (isset($indexedCategories[$subcategory['category_id']])) {
                    $indexedCategories[$subcategory['category_id']]['subcategories'][] = $subcategory;
                }
            }
            
            // Return the final list of categories, resetting array keys
            return array_values($indexedCategories);

        } catch (\PDOException $e) {
            error_log('Error fetching category structure: ' . $e->getMessage());
            return [];
        }
    }
} 