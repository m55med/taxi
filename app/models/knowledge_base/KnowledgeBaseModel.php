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

        // Allow essential tags for a rich text editor, including images from URLs.
        $config->set('HTML.Allowed', 'h1,h2,h3,p,b,strong,i,em,u,s,a[href|title],ul,ol,li,blockquote,pre,code,img[src|alt|style],span[style]');
        
        // Allow specific CSS properties that Quill might use for formatting.
        $config->set('CSS.AllowedProperties', 'text-align, direction');

        // Ensure that only http and https image sources are allowed.
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);

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
                tc.name AS ticket_code_name,
                kf.name AS folder_name,
                kf.color AS folder_color,
                kf.icon AS folder_icon
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.updated_by = u.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            LEFT JOIN knowledge_base_folders kf ON kb.folder_id = kf.id
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
                tc.name AS ticket_code_name,
                kf.name AS folder_name,
                kf.color AS folder_color,
                kf.icon AS folder_icon
            FROM knowledge_base kb
            LEFT JOIN users u_created ON kb.created_by = u_created.id
            LEFT JOIN users u_updated ON kb.updated_by = u_updated.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            LEFT JOIN knowledge_base_folders kf ON kb.folder_id = kf.id
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
            INSERT INTO knowledge_base (title, content, ticket_code_id, folder_id, created_by, updated_by)
            VALUES (:title, :content, :ticket_code_id, :folder_id, :created_by, :updated_by)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':content' => $clean_content,
            ':ticket_code_id' => $data['ticket_code_id'] ?: null,
            ':folder_id' => $data['folder_id'] ?: null,
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
                folder_id = :folder_id,
                updated_by = :user_id
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':content' => $clean_content,
            ':ticket_code_id' => $data['ticket_code_id'] ?: null,
            ':folder_id' => $data['folder_id'] ?: null,
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

    /**
     * Get all folders for the dropdown in create/edit forms.
     */
    public function getAllFolders()
    {
        $sql = "SELECT id, name, color, icon FROM knowledge_base_folders WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all folders with article count for the main page.
     */
    public function getFoldersWithCount()
    {
        $sql = "
            SELECT
                kf.id,
                kf.name,
                kf.color,
                kf.icon,
                kf.description,
                COUNT(kb.id) as article_count
            FROM knowledge_base_folders kf
            LEFT JOIN knowledge_base kb ON kf.id = kb.folder_id
            WHERE kf.is_active = 1
            GROUP BY kf.id, kf.name, kf.color, kf.icon, kf.description
            ORDER BY kf.name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get articles by folder ID.
     */
    public function getArticlesByFolder($folderId)
    {
        $sql = "
            SELECT
                kb.id,
                kb.title,
                kb.updated_at,
                u.username AS author_name,
                tc.name AS ticket_code_name,
                kf.name AS folder_name,
                kf.color AS folder_color,
                kf.icon AS folder_icon
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.updated_by = u.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            LEFT JOIN knowledge_base_folders kf ON kb.folder_id = kf.id
            WHERE kb.folder_id = :folder_id
            ORDER BY kb.updated_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':folder_id' => $folderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search articles with folder filter.
     */
    public function searchWithFolder($query, $folderId = null)
    {
        $whereClause = "";
        $params = [
            ':query1' => $query . '*',
            ':query2' => $query . '*'
        ];

        if ($folderId) {
            $whereClause = "AND kb.folder_id = :folder_id";
            $params[':folder_id'] = $folderId;
        }

        $sql = "
            SELECT
                kb.id,
                kb.title,
                kb.updated_at,
                u.username AS author_name,
                tc.name AS ticket_code_name,
                kf.name AS folder_name,
                kf.color AS folder_color,
                kf.icon AS folder_icon,
                MATCH(kb.title, kb.content) AGAINST(:query1 IN BOOLEAN MODE) as relevance
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.updated_by = u.id
            LEFT JOIN ticket_codes tc ON kb.ticket_code_id = tc.id
            LEFT JOIN knowledge_base_folders kf ON kb.folder_id = kf.id
            WHERE MATCH(kb.title, kb.content) AGAINST(:query2 IN BOOLEAN MODE) {$whereClause}
            ORDER BY relevance DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get database connection (public method to access private $db)
     */
    public function getDbConnection()
    {
        return $this->db;
    }

    /**
     * Get folder by ID
     */
    public function getFolderById($folderId)
    {
        $sql = "SELECT * FROM knowledge_base_folders WHERE id = :id AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $folderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new folder
     */
    public function createFolder($data)
    {
        $sql = "
            INSERT INTO knowledge_base_folders (name, description, color, icon, created_by)
            VALUES (:name, :description, :color, :icon, :created_by)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':color' => $data['color'] ?? '#3B82F6',
            ':icon' => $data['icon'] ?? 'fas fa-folder',
            ':created_by' => $data['created_by'] ?? null
        ]);
    }

    /**
     * Update folder
     */
    public function updateFolder($folderId, $data)
    {
        $sql = "
            UPDATE knowledge_base_folders
            SET name = :name, description = :description, color = :color, icon = :icon
            WHERE id = :folder_id AND is_active = 1
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':folder_id' => $folderId,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':color' => $data['color'] ?? '#3B82F6',
            ':icon' => $data['icon'] ?? 'fas fa-folder'
        ]);
    }

    /**
     * Delete folder (soft delete)
     */
    public function deleteFolder($folderId)
    {
        try {
            $this->db->beginTransaction();

            // Get the default "General" folder ID
            $generalFolder = $this->db->prepare("SELECT id FROM knowledge_base_folders WHERE name = 'عام' LIMIT 1");
            $generalFolder->execute();
            $generalFolderId = $generalFolder->fetchColumn();

            // Move all articles from this folder to General
            $moveArticles = $this->db->prepare("UPDATE knowledge_base SET folder_id = :general_id WHERE folder_id = :folder_id");
            $moveArticles->execute([
                ':general_id' => $generalFolderId,
                ':folder_id' => $folderId
            ]);

            // Soft delete the folder
            $deleteFolder = $this->db->prepare("UPDATE knowledge_base_folders SET is_active = 0 WHERE id = :folder_id");
            $deleteFolder->execute([':folder_id' => $folderId]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

} 