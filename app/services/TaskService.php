<?php

namespace App\Services;

use App\Models\Task\Task;

class TaskService
{
    private $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }

    public function createTask($data, $assigneeIds, $files = [])
    {
        $taskId = $this->taskModel->create($data);
        if (!$taskId) return false;

        foreach ($assigneeIds as $userId) {
            $this->taskModel->addAssignee($taskId, $userId);
        }

        if (!empty($files)) {
            $this->handleUploads($taskId, $files);
        }

        return $taskId;
    }

    public function updateTask($id, $data, $assigneeIds, $files = [])
    {
        if (!$this->taskModel->update($id, $data)) return false;

        // Refresh assignees
        $this->taskModel->clearAssignees($id);
        foreach ($assigneeIds as $userId) {
            $this->taskModel->addAssignee($id, $userId);
        }

        // Add new files if any
        if (!empty($files) && !empty($files['name'][0])) {
            $this->handleUploads($id, $files);
        }

        return true;
    }

    private function handleUploads($taskId, $files)
    {
        $uploadDir = dirname(APPROOT) . '/public/uploads/tasks/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$key];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $newName = uniqid('task_') . '.' . $extension;
                $targetPath = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $this->taskModel->addAttachment($taskId, '/uploads/tasks/' . $newName, $name, $files['type'][$key]);
                }
            }
        }
    }

    public function listTasks($filters = [])
    {
        return $this->taskModel->getAllTasks($filters);
    }

    public function getTaskDetails($id)
    {
        return $this->taskModel->getTaskById($id);
    }

    public function getTaskById($id)
    {
        return $this->taskModel->getTaskById($id);
    }

    public function updateTaskStatus($taskId, $status)
    {
        return $this->taskModel->updateStatus($taskId, $status);
    }

    public function addComment($data)
    {
        return $this->taskModel->addComment($data);
    }

    public function getTaskComments($taskId)
    {
        return $this->taskModel->getCommentsByTaskId($taskId);
    }

    public function getPendingTasksCount($userId)
    {
        return $this->taskModel->getPendingCountForUser($userId);
    }

    public function updateTaskAssignees($id, $assigneeIds)
    {
        // Clear existing assignees
        $this->taskModel->clearAssignees($id);

        // Add new assignees
        foreach ($assigneeIds as $userId) {
            $this->taskModel->addAssignee($id, $userId);
        }

        return true;
    }

    public function deleteTask($id)
    {
        return $this->taskModel->deleteTask($id);
    }
}
