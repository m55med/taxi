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
        $this->kbModel = $this->model('knowledge_base/KnowledgeBaseModel');
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
            'is_admin' => in_array($_SESSION['role'], ['admin', 'developer'])
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
            'article' => $article
        ];

        $this->view('knowledge_base/show', $data);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        $this->requireAdmin();
        $data = [
            'page_main_title' => 'Add New Article',
            'ticket_codes' => $this->kbModel->getAllTicketCodes(),
            'article' => [
                'id' => null, 'title' => '', 'content' => '', 'ticket_code_id' => null
            ]
        ];
        $this->view('knowledge_base/create', $data);
    }

    /**
     * Store a new article in the database.
     */
    public function store()
    {
        $this->requireAdmin();

        $log_file = APPROOT . '/logs/kb_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] --- KB Store method initiated ---\n", FILE_APPEND);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            file_put_contents($log_file, "[$timestamp] Request method was not POST. Redirecting.\n", FILE_APPEND);
            redirect('/knowledge_base');
        }

        file_put_contents($log_file, "[$timestamp] POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

        $data = [
            'title' => isset($_POST['title']) ? trim($_POST['title']) : '',
            'content' => isset($_POST['content']) ? trim($_POST['content']) : '',
            'ticket_code_id' => isset($_POST['ticket_code_id']) ? $_POST['ticket_code_id'] : null
        ];
        
        // Basic validation
        if (empty($data['title']) || empty($data['content'])) {
            file_put_contents($log_file, "[$timestamp] Validation failed: Title or content empty. Redirecting back.\n", FILE_APPEND);
            flash('kb_message', 'Title and Content are required.', 'error');
            redirect('/knowledge_base/create');
            return;
        }

        file_put_contents($log_file, "[$timestamp] Data validated. Attempting to create article with data: " . print_r($data, true) . "\n", FILE_APPEND);

        if ($this->kbModel->create($data)) {
            file_put_contents($log_file, "[$timestamp] Model create() returned true. Success.\n", FILE_APPEND);
            flash('kb_message', 'Article created successfully.', 'success');
            redirect('/knowledge_base');
        } else {
            file_put_contents($log_file, "[$timestamp] Model create() returned false. Failure.\n", FILE_APPEND);
            flash('kb_message', 'Failed to create article.', 'error');
            redirect('/knowledge_base/create');
        }
    }

    /**
     * Show the form for editing an article.
     */
    public function edit($id)
    {
        $this->requireAdmin();
        $article = $this->kbModel->findById($id);
        if (!$article) {
            redirect('/knowledge_base');
        }

        $data = [
            'page_main_title' => 'Edit Article',
            'ticket_codes' => $this->kbModel->getAllTicketCodes(),
            'article' => $article
        ];
        $this->view('knowledge_base/edit', $data);
    }

    /**
     * Update an existing article in the database.
     */
    public function update($id)
    {
        $this->requireAdmin();
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
    public function destroy() {
        $this->requireAdmin();
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
    public function findByCode($ticketCodeId) {
        $article = $this->kbModel->findByTicketCodeId($ticketCodeId);
        $this->sendJsonResponse($article ?: []);
    }
    
    /**
     * Helper function to check for admin privileges.
     */
    private function requireAdmin()
    {
        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            redirect('/unauthorized');
        }
    }
} 