<?php

namespace App\Models\knowledge_base;

use App\Core\Database;
use PDO;

class KnowledgeBaseModel
{
    private $db;
    private $purifier;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Get all knowledge base articles with details of the author and linked ticket code.
     */
    public function getAll()
    {
        $sql = "
            SELECT 
                kb.id,
                kb.title,
                kb.updated_at,
                u.username AS author_name,
                tc.name AS ticket_code_name
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.updated_by = u.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            ORDER BY kb.updated_at DESC
        ";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    /**
     * Find a single article by its ID.
     */
    public function findById($id)
    {
        $sql = "
            SELECT 
                kb.*,
                u_created.username AS created_by_name,
                u_updated.username AS updated_by_name,
                tc.name AS ticket_code_name
            FROM knowledge_base kb
            LEFT JOIN users u_created ON kb.created_by = u_created.id
            LEFT JOIN users u_updated ON kb.updated_by = u_updated.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            WHERE kb.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find an article by its associated Ticket Code ID.
     */
    public function findByTicketCodeId($ticket_code_id)
    {
        $sql = "SELECT id, title FROM knowledge_base WHERE ticket_code_id = :ticket_code_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_code_id' => $ticket_code_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new knowledge base article.
     */
    public function create($data)
    {
        // Sanitize the content to prevent XSS
        $clean_content = $this->purifier->purify($data['content']);

        $sql = "
            INSERT INTO knowledge_base (title, content, ticket_code_id, created_by, updated_by)
            VALUES (:title, :content, :ticket_code_id, :created_by, :updated_by)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':content' => $clean_content,
            ':ticket_code_id' => $data['ticket_code_id'] ?: null,
            ':created_by' => $_SESSION['user_id'],
            ':updated_by' => $_SESSION['user_id']
        ]);
    }

    /**
     * Update an existing knowledge base article.
     */
    public function update($id, $data)
    {
        // Sanitize the content to prevent XSS
        $clean_content = $this->purifier->purify($data['content']);

        $sql = "
            UPDATE knowledge_base
            SET 
                title = :title,
                content = :content,
                ticket_code_id = :ticket_code_id,
                updated_by = :user_id
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':content' => $clean_content,
            ':ticket_code_id' => $data['ticket_code_id'] ?: null,
            ':user_id' => $_SESSION['user_id']
        ]);
    }

    /**
     * Delete an article by its ID.
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM knowledge_base WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Search for articles using FULLTEXT search on title and content.
     */
    public function search($query)
    {
        $sql = "
            SELECT 
                kb.id,
                kb.title,
                kb.updated_at,
                u.username AS author_name,
                tc.name AS ticket_code_name,
                MATCH(kb.title, kb.content) AGAINST(:query1 IN BOOLEAN MODE) as relevance
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.updated_by = u.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            WHERE MATCH(kb.title, kb.content) AGAINST(:query2 IN BOOLEAN MODE)
            ORDER BY relevance DESC
        ";
        $stmt = $this->db->prepare($sql);
        // Using boolean mode allows for more complex queries, like adding '+' for required words
        $stmt->execute([
            ':query1' => $query . '*',
            ':query2' => $query . '*'
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all ticket codes for the dropdown in create/edit forms.
     */
    public function getAllTicketCodes()
    {
        $sql = "SELECT id, name FROM ticket_codes ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} 