<?php

namespace App\Models\Task;

use App\Core\Database;
use PDO;

class Task
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO tasks (title, description, deadline, source, status, frequency, indicator, goal, project, created_by) 
                VALUES (:title, :description, :deadline, :source, :status, :frequency, :indicator, :goal, :project, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':deadline', $data['deadline']);
        $stmt->bindValue(':source', $data['source']);
        $stmt->bindValue(':status', $data['status'] ?? 'pending');
        $stmt->bindValue(':frequency', $data['frequency'] ?? 'once');
        $stmt->bindValue(':indicator', $data['indicator']);
        $stmt->bindValue(':goal', $data['goal']);
        $stmt->bindValue(':project', $data['project']);
        $stmt->bindValue(':created_by', $data['created_by']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addAssignee($taskId, $userId)
    {
        $sql = "INSERT IGNORE INTO task_assignees (task_id, user_id) VALUES (:task_id, :user_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    public function addAttachment($taskId, $filePath, $fileName, $fileType)
    {
        $sql = "INSERT INTO task_attachments (task_id, file_path, file_name, file_type) 
                VALUES (:task_id, :file_path, :file_name, :file_type)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        $stmt->bindValue(':file_path', $filePath);
        $stmt->bindValue(':file_name', $fileName);
        $stmt->bindValue(':file_type', $fileType);
        return $stmt->execute();
    }

    public function getAllTasks($filters = [])
    {
        $sql = "SELECT t.*, u.username as creator_name, 
                (SELECT GROUP_CONCAT(users.username) FROM task_assignees JOIN users ON task_assignees.user_id = users.id WHERE task_assignees.task_id = t.id) as assignees
                FROM tasks t
                JOIN users u ON t.created_by = u.id";
        
        $where = [];
        $params = [];

        if (isset($filters['user_id'])) {
            $sql .= " JOIN task_assignees ta ON t.id = ta.task_id";
            $where[] = "ta.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (isset($filters['status'])) {
            $where[] = "t.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTaskById($id)
    {
        $sql = "SELECT t.*, u.username as creator_name FROM tasks t JOIN users u ON t.created_by = u.id WHERE t.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            $task['assignees'] = $this->getAssignees($id);
            $task['attachments'] = $this->getAttachments($id);
        }
        return $task;
    }

    public function getAssignees($taskId)
    {
        $sql = "SELECT u.id, u.username, u.name FROM task_assignees ta JOIN users u ON ta.user_id = u.id WHERE ta.task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttachments($taskId)
    {
        $sql = "SELECT * FROM task_attachments WHERE task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttachmentsByIds($attachmentIds)
    {
        if (empty($attachmentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($attachmentIds), '?'));
        $sql = "SELECT * FROM task_attachments WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($attachmentIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($taskId, $status)
    {
        $sql = "UPDATE tasks SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $taskId);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tasks SET 
                title = :title, 
                description = :description, 
                deadline = :deadline, 
                source = :source, 
                frequency = :frequency, 
                indicator = :indicator, 
                goal = :goal, 
                project = :project 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':deadline', $data['deadline']);
        $stmt->bindValue(':source', $data['source']);
        $stmt->bindValue(':frequency', $data['frequency']);
        $stmt->bindValue(':indicator', $data['indicator']);
        $stmt->bindValue(':goal', $data['goal']);
        $stmt->bindValue(':project', $data['project']);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    public function clearAssignees($taskId)
    {
        $sql = "DELETE FROM task_assignees WHERE task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        return $stmt->execute();
    }

    public function deleteAttachment($id)
    {
        $sql = "DELETE FROM task_attachments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function addComment($data)
    {
        $sql = "INSERT INTO task_comments (task_id, user_id, comment, is_completion_notice) 
                VALUES (:task_id, :user_id, :comment, :is_completion_notice)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $data['task_id']);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':comment', $data['comment']);
        $stmt->bindValue(':is_completion_notice', $data['is_completion_notice'] ?? 0);
        return $stmt->execute();
    }

    public function getCommentsByTaskId($taskId)
    {
        $sql = "SELECT tc.*, u.username, u.name 
                FROM task_comments tc 
                JOIN users u ON tc.user_id = u.id 
                WHERE tc.task_id = :task_id 
                ORDER BY tc.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $taskId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingCountForUser($userId)
    {
        $sql = "SELECT COUNT(*) FROM tasks t
                JOIN task_assignees ta ON t.id = ta.task_id
                WHERE ta.user_id = :user_id AND t.status IN ('pending', 'in_progress')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function deleteTask($id)
    {
        // Delete task comments
        $sql = "DELETE FROM task_comments WHERE task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $id);
        $stmt->execute();

        // Delete task assignees
        $sql = "DELETE FROM task_assignees WHERE task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $id);
        $stmt->execute();

        // Delete task attachments (files are deleted in controller)
        $sql = "DELETE FROM task_attachments WHERE task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':task_id', $id);
        $stmt->execute();

        // Delete task
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
