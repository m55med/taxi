<?php

namespace App\Models\Establishments;

use App\Core\Database;
use PDO;

class EstablishmentModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new establishment
     */
    public function create($data)
    {
        $sql = "INSERT INTO establishments (
                    establishment_name, legal_name, taxpayer_number, street, house_number, 
                    postal_zip, establishment_email, establishment_phone, owner_full_name, 
                    owner_position, owner_email, owner_phone, description, 
                    establishment_logo, establishment_header_image, marketer_id
                ) VALUES (
                    :establishment_name, :legal_name, :taxpayer_number, :street, :house_number,
                    :postal_zip, :establishment_email, :establishment_phone, :owner_full_name,
                    :owner_position, :owner_email, :owner_phone, :description,
                    :establishment_logo, :establishment_header_image, :marketer_id
                )";

        $this->db->query($sql);
        
        // Bind parameters
        $this->db->bind(':establishment_name', $data['establishment_name'] ?? null);
        $this->db->bind(':legal_name', $data['legal_name'] ?? null);
        $this->db->bind(':taxpayer_number', $data['taxpayer_number'] ?? null);
        $this->db->bind(':street', $data['street'] ?? null);
        $this->db->bind(':house_number', $data['house_number'] ?? null);
        $this->db->bind(':postal_zip', $data['postal_zip'] ?? null);
        $this->db->bind(':establishment_email', $data['establishment_email'] ?? null);
        $this->db->bind(':establishment_phone', $data['establishment_phone'] ?? null);
        $this->db->bind(':owner_full_name', $data['owner_full_name'] ?? null);
        $this->db->bind(':owner_position', $data['owner_position'] ?? null);
        $this->db->bind(':owner_email', $data['owner_email'] ?? null);
        $this->db->bind(':owner_phone', $data['owner_phone'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':establishment_logo', $data['establishment_logo'] ?? null);
        $this->db->bind(':establishment_header_image', $data['establishment_header_image'] ?? null);
        $this->db->bind(':marketer_id', $data['marketer_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get establishments with pagination and optional filters
     */
    public function getEstablishments($marketerId = null, $page = 1, $limit = 25, $search = '', $filterMarketer = '', $filterContact = '', $sortBy = 'created_at', $sortOrder = 'DESC')
    {
        $offset = ($page - 1) * $limit;
        
        $whereClauses = [];
        $params = [];
        
        // Marketer filter (main constraint for marketers)
        if ($marketerId !== null) {
            $whereClauses[] = 'e.marketer_id = :marketer_id';
            $params[':marketer_id'] = $marketerId;
        }
        
        // Admin filters
        if (!empty($search)) {
            $whereClauses[] = '(e.establishment_name LIKE :search OR e.legal_name LIKE :search OR e.owner_full_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($filterMarketer)) {
            $whereClauses[] = 'e.marketer_id = :filter_marketer';
            $params[':filter_marketer'] = $filterMarketer;
        }
        
        if ($filterContact === 'with_email') {
            $whereClauses[] = 'e.establishment_email IS NOT NULL AND e.establishment_email != ""';
        } elseif ($filterContact === 'with_phone') {
            $whereClauses[] = 'e.establishment_phone IS NOT NULL AND e.establishment_phone != ""';
        } elseif ($filterContact === 'no_contact') {
            $whereClauses[] = '(e.establishment_email IS NULL OR e.establishment_email = "") AND (e.establishment_phone IS NULL OR e.establishment_phone = "")';
        }
        
        $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        // Validate sort columns
        $allowedSortColumns = ['establishment_name', 'legal_name', 'owner_full_name', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT e.*, u.name as marketer_name 
                FROM establishments e 
                LEFT JOIN users u ON e.marketer_id = u.id 
                $whereClause 
                ORDER BY e.$sortBy $sortOrder 
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet(PDO::FETCH_OBJ);
    }

    /**
     * Get total count of establishments with filters
     */
    public function getTotalCount($marketerId = null, $search = '', $filterMarketer = '', $filterContact = '')
    {
        $whereClauses = [];
        $params = [];
        
        // Marketer filter (main constraint for marketers)
        if ($marketerId !== null) {
            $whereClauses[] = 'marketer_id = :marketer_id';
            $params[':marketer_id'] = $marketerId;
        }
        
        // Admin filters
        if (!empty($search)) {
            $whereClauses[] = '(establishment_name LIKE :search OR legal_name LIKE :search OR owner_full_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($filterMarketer)) {
            $whereClauses[] = 'marketer_id = :filter_marketer';
            $params[':filter_marketer'] = $filterMarketer;
        }
        
        if ($filterContact === 'with_email') {
            $whereClauses[] = 'establishment_email IS NOT NULL AND establishment_email != ""';
        } elseif ($filterContact === 'with_phone') {
            $whereClauses[] = 'establishment_phone IS NOT NULL AND establishment_phone != ""';
        } elseif ($filterContact === 'no_contact') {
            $whereClauses[] = '(establishment_email IS NULL OR establishment_email = "") AND (establishment_phone IS NULL OR establishment_phone = "")';
        }
        
        $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        $sql = "SELECT COUNT(*) as total FROM establishments $whereClause";
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $result = $this->db->single(PDO::FETCH_OBJ);
        return $result->total;
    }

    /**
     * Get summary statistics with filters
     */
    public function getSummaryStats($marketerId = null, $search = '', $filterMarketer = '', $filterContact = '')
    {
        $whereClauses = [];
        $params = [];
        
        // Marketer filter (main constraint for marketers)
        if ($marketerId !== null) {
            $whereClauses[] = 'marketer_id = :marketer_id';
            $params[':marketer_id'] = $marketerId;
        }
        
        // Admin filters
        if (!empty($search)) {
            $whereClauses[] = '(establishment_name LIKE :search OR legal_name LIKE :search OR owner_full_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($filterMarketer)) {
            $whereClauses[] = 'marketer_id = :filter_marketer';
            $params[':filter_marketer'] = $filterMarketer;
        }
        
        if ($filterContact === 'with_email') {
            $whereClauses[] = 'establishment_email IS NOT NULL AND establishment_email != ""';
        } elseif ($filterContact === 'with_phone') {
            $whereClauses[] = 'establishment_phone IS NOT NULL AND establishment_phone != ""';
        } elseif ($filterContact === 'no_contact') {
            $whereClauses[] = '(establishment_email IS NULL OR establishment_email = "") AND (establishment_phone IS NULL OR establishment_phone = "")';
        }
        
        $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN establishment_email IS NOT NULL AND establishment_email != '' THEN 1 END) as with_email,
                    COUNT(CASE WHEN establishment_phone IS NOT NULL AND establishment_phone != '' THEN 1 END) as with_phone,
                    COUNT(CASE WHEN description IS NOT NULL AND description != '' THEN 1 END) as with_description
                FROM establishments $whereClause";
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->single(PDO::FETCH_OBJ);
    }

    /**
     * Get establishment by ID
     */
    public function getById($id)
    {
        $sql = "SELECT e.*, u.name as marketer_name 
                FROM establishments e 
                LEFT JOIN users u ON e.marketer_id = u.id 
                WHERE e.id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        
        return $this->db->single(PDO::FETCH_OBJ);
    }

    /**
     * Update establishment by ID
     */
    public function update($id, $data)
    {
        $sql = "UPDATE establishments SET 
                    establishment_name = :establishment_name,
                    legal_name = :legal_name,
                    taxpayer_number = :taxpayer_number,
                    street = :street,
                    house_number = :house_number,
                    postal_zip = :postal_zip,
                    establishment_email = :establishment_email,
                    establishment_phone = :establishment_phone,
                    owner_full_name = :owner_full_name,
                    owner_position = :owner_position,
                    owner_email = :owner_email,
                    owner_phone = :owner_phone,
                    description = :description,
                    establishment_logo = :establishment_logo,
                    establishment_header_image = :establishment_header_image,
                    marketer_id = :marketer_id
                WHERE id = :id";

        $this->db->query($sql);
        
        // Bind all parameters
        $this->db->bind(':id', $id);
        $this->db->bind(':establishment_name', $data['establishment_name'] ?? null);
        $this->db->bind(':legal_name', $data['legal_name'] ?? null);
        $this->db->bind(':taxpayer_number', $data['taxpayer_number'] ?? null);
        $this->db->bind(':street', $data['street'] ?? null);
        $this->db->bind(':house_number', $data['house_number'] ?? null);
        $this->db->bind(':postal_zip', $data['postal_zip'] ?? null);
        $this->db->bind(':establishment_email', $data['establishment_email'] ?? null);
        $this->db->bind(':establishment_phone', $data['establishment_phone'] ?? null);
        $this->db->bind(':owner_full_name', $data['owner_full_name'] ?? null);
        $this->db->bind(':owner_position', $data['owner_position'] ?? null);
        $this->db->bind(':owner_email', $data['owner_email'] ?? null);
        $this->db->bind(':owner_phone', $data['owner_phone'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':establishment_logo', $data['establishment_logo'] ?? null);
        $this->db->bind(':establishment_header_image', $data['establishment_header_image'] ?? null);
        $this->db->bind(':marketer_id', $data['marketer_id'] ?? null);
        
        return $this->db->execute();
    }

    /**
     * Get all establishments for export with filters
     */
    public function getAllForExport($marketerId = null, $search = '', $filterMarketer = '', $filterContact = '')
    {
        $whereClauses = [];
        $params = [];
        
        // Marketer filter (main constraint for marketers)
        if ($marketerId !== null) {
            $whereClauses[] = 'e.marketer_id = :marketer_id';
            $params[':marketer_id'] = $marketerId;
        }
        
        // Admin filters
        if (!empty($search)) {
            $whereClauses[] = '(e.establishment_name LIKE :search OR e.legal_name LIKE :search OR e.owner_full_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($filterMarketer)) {
            $whereClauses[] = 'e.marketer_id = :filter_marketer';
            $params[':filter_marketer'] = $filterMarketer;
        }
        
        if ($filterContact === 'with_email') {
            $whereClauses[] = 'e.establishment_email IS NOT NULL AND e.establishment_email != ""';
        } elseif ($filterContact === 'with_phone') {
            $whereClauses[] = 'e.establishment_phone IS NOT NULL AND e.establishment_phone != ""';
        } elseif ($filterContact === 'no_contact') {
            $whereClauses[] = '(e.establishment_email IS NULL OR e.establishment_email = "") AND (e.establishment_phone IS NULL OR e.establishment_phone = "")';
        }
        
        $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        $sql = "SELECT 
                    e.id,
                    e.establishment_name,
                    e.legal_name,
                    e.taxpayer_number,
                    e.street,
                    e.house_number,
                    e.postal_zip,
                    e.establishment_email,
                    e.establishment_phone,
                    e.owner_full_name,
                    e.owner_position,
                    e.owner_email,
                    e.owner_phone,
                    e.description,
                    u.name as marketer_name,
                    e.created_at
                FROM establishments e 
                LEFT JOIN users u ON e.marketer_id = u.id 
                $whereClause 
                ORDER BY e.created_at DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet(PDO::FETCH_ASSOC);
    }

    /**
     * Update specific fields only
     */
    public function updateFields($id, $fields)
    {
        if (empty($fields)) {
            return false;
        }

        $setParts = [];
        foreach ($fields as $field => $value) {
            $setParts[] = "$field = :$field";
        }

        $sql = "UPDATE establishments SET " . implode(', ', $setParts) . " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        foreach ($fields as $field => $value) {
            $this->db->bind(":$field", $value);
        }
        
        return $this->db->execute();
    }

    /**
     * Delete establishment by ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM establishments WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
}
