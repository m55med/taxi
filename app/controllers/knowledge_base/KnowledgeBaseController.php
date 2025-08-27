<?php

namespace App\Controllers\knowledge_base;

use App\Core\Controller;
use App\Core\Auth;

class KnowledgeBaseController extends Controller
{
    private $kbModel;

    public function __construct()
    {
        if (!Auth::isLoggedIn()) {
            redirect('/auth/login');
        }
        $this->kbModel = $this->model('Knowledge_base/KnowledgeBaseModel');
    }

    /**
     * Main page for the knowledge base.
     * Displays a searchable list of all articles.
     */
    public function index()
    {
        $searchQuery = $_GET['q'] ?? '';
        if (!empty($searchQuery)) {
            $articles = $this->kbModel->search($searchQuery);
        } else {
            $articles = $this->kbModel->getAll();
        }

        $data = [
            'page_main_title' => 'Knowledge Base',
            'articles' => $articles,
            'searchQuery' => $searchQuery,
'can_create' => self::canPerformKBAction('create'),
            'can_edit' => self::canPerformKBAction('edit'),
            'can_delete' => self::canPerformKBAction('destroy'),
            // Keep is_admin for backward compatibility
            'is_admin' => isset($_SESSION['user']['role_name']) && in_array($_SESSION['user']['role_name'], ['admin', 'developer']),
        ];

        $this->view('knowledge_base/index', $data);
    }

    /**
     * Display a single knowledge base article.
     */
    public function show($id)
    {
        $article = $this->kbModel->findById($id);
        if (!$article) {
            redirect('errors/404');
        }

        $data = [
            'page_main_title' => $article['title'],
            'article' => $article,
            'can_edit' => self::canPerformKBAction('edit'),
            'can_delete' => self::canPerformKBAction('destroy')
        ];

        $this->view('knowledge_base/show', $data);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        $this->requireKnowledgeBasePermission('create');
        $data = [
            'page_main_title' => 'Add New Article',
            'ticket_codes' => $this->kbModel->getAllTicketCodes(),
            'article' => [
                'id' => null,
                'title' => '',
                'content' => '',
                'ticket_code_id' => null
            ]
        ];
        $this->view('knowledge_base/create', $data);
    }

    /**
     * Store a new article in the database.
     */
    public function store()
    {
        $this->requireKnowledgeBasePermission('store');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        $data = [
            'title' => isset($_POST['title']) ? trim($_POST['title']) : '',
            'content' => isset($_POST['content']) ? trim($_POST['content']) : '',
            'ticket_code_id' => isset($_POST['ticket_code_id']) ? $_POST['ticket_code_id'] : null
        ];

        // Basic validation
        if (empty($data['title']) || empty($data['content'])) {
            flash('kb_message', 'Title and Content are required.', 'error');
            redirect('/knowledge_base/create');
            return;
        }

        if ($this->kbModel->create($data)) {
            flash('kb_message', 'Article created successfully.', 'success');
            redirect('/knowledge_base');
        } else {
            flash('kb_message', 'Failed to create article.', 'error');
            redirect('/knowledge_base/create');
        }
    }

    /**
     * Show the form for editing an article.
     */
    public function edit($id)
    {
        $this->requireKnowledgeBasePermission('edit');
        $article = $this->kbModel->findById($id);
        if (!$article) {
            redirect('/knowledge_base');
        }

        $data = [
            'page_main_title' => 'Edit Article',
            'ticket_codes' => $this->kbModel->getAllTicketCodes(),
            'article' => $article,
            'can_delete' => self::canPerformKBAction('destroy')
        ];
        $this->view('knowledge_base/edit', $data);
    }

    /**
     * Update an existing article in the database.
     */
    public function update($id)
    {
        $this->requireKnowledgeBasePermission('update');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        $data = [
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'ticket_code_id' => $_POST['ticket_code_id']
        ];

        if (empty($data['title']) || empty($data['content'])) {
            flash('kb_message', 'Title and Content are required.', 'error');
            redirect('/knowledge_base/edit/' . $id);
        }

        if ($this->kbModel->update($id, $data)) {
            flash('kb_message', 'Article updated successfully.', 'success');
            redirect('/knowledge_base');
        } else {
            flash('kb_message', 'Failed to update article.', 'error');
            redirect('/knowledge_base/edit/' . $id);
        }
    }

    /**
     * Delete an article.
     */
    public function destroy()
    {
        $this->requireKnowledgeBasePermission('destroy');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        $id = $_POST['id'];
        if ($this->kbModel->delete($id)) {
            flash('kb_message', 'Article deleted successfully.', 'success');
        } else {
            flash('kb_message', 'Failed to delete article.', 'error');
        }
        redirect('/knowledge_base');
    }

    /**
     * API endpoint to find an article by ticket code ID.
     * This will be used by the Create Ticket page.
     */
    public function findByCode($ticketCodeId)
    {
        $article = $this->kbModel->findByTicketCodeId($ticketCodeId);
        $this->sendJsonResponse($article ?: []);
    }

    /**
     * API endpoint for live search functionality.
     */
    public function searchApi()
    {
        $searchQuery = $_GET['q'] ?? '';

        if (empty($searchQuery)) {
            $articles = $this->kbModel->getAll();
        } else {
            $articles = $this->kbModel->search($searchQuery);
        }

        $this->sendJsonResponse($articles);
    }

    /**
     * Helper function to check for admin privileges.
     * @deprecated Use requireKnowledgeBasePermission instead
     */
    private function requireAdmin()
    {
        Auth::checkAdmin();
    }

    /**
     * Helper function to check for Knowledge Base permissions.
     * Admins and developers have all permissions, others need specific permissions.
     */
    private function requireKnowledgeBasePermission($action)
    {
        // Admin and developer roles have full access (preserve existing behavior)
        if (Auth::hasRole('admin') || Auth::hasRole('developer')) {
            return;
        }

        // For other users, check specific permission directly from database
        $permissionKey = 'KnowledgeBase/' . $action;
        if (!self::hasPermissionFromDB($permissionKey)) {
            http_response_code(403);
            require_once APPROOT . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Helper function to check if current user can perform KB actions.
     * Used in views to show/hide buttons.
     */
    public static function canPerformKBAction($action)
    {
        // Admin and developer roles have full access
        if (Auth::hasRole('admin') || Auth::hasRole('developer')) {
            return true;
        }

        // For other users, check permission directly from database to avoid session cache issues
        $permissionKey = 'KnowledgeBase/' . $action;
        return self::hasPermissionFromDB($permissionKey);
    }

    /**
     * Check permission directly from database (bypasses session cache)
     */
    private static function hasPermissionFromDB($permissionKey)
    {
        $userId = Auth::getUserId();
        if (!$userId) {
            return false;
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = ? AND p.permission_key = ?
        ");
        $stmt->execute([$userId, $permissionKey]);
        return $stmt->fetchColumn() > 0;
    }
}