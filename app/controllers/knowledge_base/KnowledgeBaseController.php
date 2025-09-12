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

        $folderId = $_GET['folder'] ?? null;

        if (!empty($searchQuery)) {

            if ($folderId) {

                $articles = $this->kbModel->searchWithFolder($searchQuery, $folderId);

            } else {

                $articles = $this->kbModel->search($searchQuery);

            }

        } elseif ($folderId) {

            $articles = $this->kbModel->getArticlesByFolder($folderId);

        } else {

            $articles = $this->kbModel->getAll();

        }

        $data = [

            'page_main_title' => 'Knowledge Base',

            'articles' => $articles,

            'folders' => $this->kbModel->getFoldersWithCount(),

            'searchQuery' => $searchQuery,

            'selectedFolder' => $folderId,

            'can_create' => self::canPerformKBAction('create'),

            'can_edit' => self::canPerformKBAction('edit'),

            'can_delete' => self::canPerformKBAction('destroy'),

            // Keep is_admin for backward compatibility

            'is_admin' => isset($_SESSION['user']['role_name']) && in_array($_SESSION['user']['role_name'], ['admin', 'developer']),

        ];



        $this->view('knowledge_base/index', $data);

    }

    /**
     * Display articles by folder ID.
     */
    public function folder($folderId)
    {
        $articles = $this->kbModel->getArticlesByFolder($folderId);
        $folders = $this->kbModel->getFoldersWithCount();

        $data = [
            'page_main_title' => 'Knowledge Base',
            'articles' => $articles,
            'folders' => $folders,
            'searchQuery' => '',
            'selectedFolder' => $folderId,
            'can_create' => self::canPerformKBAction('create'),
            'can_edit' => self::canPerformKBAction('edit'),
            'can_delete' => self::canPerformKBAction('destroy'),
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

            'folders' => $this->kbModel->getAllFolders(),

            'article' => [

                'id' => null,

                'title' => '',

                'content' => '',

                'ticket_code_id' => null,

                'folder_id' => null

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

            'ticket_code_id' => isset($_POST['ticket_code_id']) ? $_POST['ticket_code_id'] : null,

            'folder_id' => isset($_POST['folder_id']) ? $_POST['folder_id'] : null

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

            'folders' => $this->kbModel->getAllFolders(),

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

            'ticket_code_id' => $_POST['ticket_code_id'],

            'folder_id' => $_POST['folder_id'] ?? null

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

        $folderId = $_GET['folder'] ?? null;

        if (empty($searchQuery)) {

            if ($folderId) {

                $articles = $this->kbModel->getArticlesByFolder($folderId);

            } else {

                $articles = $this->kbModel->getAll();

            }

        } else {

            if ($folderId) {

                $articles = $this->kbModel->searchWithFolder($searchQuery, $folderId);

            } else {

                $articles = $this->kbModel->search($searchQuery);

            }

        }

        $this->sendJsonResponse($articles);

    }

    // ===== FOLDER MANAGEMENT METHODS =====

    /**
     * Show create folder form
     */
    public function createFolder()
    {
        $this->requireKnowledgeBasePermission('create');

        $data = [
            'page_main_title' => 'Create New Folder',
            'folder' => [
                'id' => null,
                'name' => '',
                'description' => '',
                'color' => '#3B82F6',
                'icon' => 'fas fa-folder'
            ]
        ];

        $this->view('knowledge_base/folders/create', $data);
    }

    /**
     * Store new folder
     */
    public function storeFolder()
    {
        $this->requireKnowledgeBasePermission('create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'color' => $_POST['color'] ?? '#3B82F6',
            'icon' => $_POST['icon'] ?? 'fas fa-folder',
            'created_by' => $_SESSION['user_id']
        ];

        if (empty($data['name'])) {
            flash('kb_message', 'Folder name is required.', 'error');
            redirect('/knowledge_base/folders/create');
            return;
        }

        // Create folder using the model method
        if ($this->kbModel->createFolder($data)) {
            flash('kb_message', 'Folder created successfully.', 'success');
            redirect('/knowledge_base');
        } else {
            flash('kb_message', 'Failed to create folder.', 'error');
            redirect('/knowledge_base/folders/create');
        }
    }

    /**
     * Show edit folder form
     */
    public function editFolder($folderId)
    {
        $this->requireKnowledgeBasePermission('edit');

        // Get folder details using the model method
        $folder = $this->kbModel->getFolderById($folderId);

        if (!$folder) {
            redirect('/knowledge_base');
        }

        $data = [
            'page_main_title' => 'Edit Folder',
            'folder' => $folder
        ];

        $this->view('knowledge_base/folders/edit', $data);
    }

    /**
     * Update folder
     */
    public function updateFolder($folderId)
    {
        $this->requireKnowledgeBasePermission('edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'color' => $_POST['color'] ?? '#3B82F6',
            'icon' => $_POST['icon'] ?? 'fas fa-folder'
        ];

        if (empty($data['name'])) {
            flash('kb_message', 'Folder name is required.', 'error');
            redirect('/knowledge_base/folders/edit/' . $folderId);
            return;
        }

        // Update folder using the model method
        if ($this->kbModel->updateFolder($folderId, $data)) {
            flash('kb_message', 'Folder updated successfully.', 'success');
            redirect('/knowledge_base');
        } else {
            flash('kb_message', 'Failed to update folder.', 'error');
            redirect('/knowledge_base/folders/edit/' . $folderId);
        }
    }

    /**
     * Show delete folder confirmation
     */
    public function deleteFolder($folderId)
    {
        $this->requireKnowledgeBasePermission('destroy');

        // Get folder details using the model method
        $folder = $this->kbModel->getFolderById($folderId);

        if (!$folder) {
            redirect('/knowledge_base');
        }

        // Get articles count in this folder using the model method
        $db = $this->kbModel->getDbConnection();
        $articlesCount = $db->prepare("SELECT COUNT(*) FROM knowledge_base WHERE folder_id = :folder_id");
        $articlesCount->execute([':folder_id' => $folderId]);
        $articlesCount = $articlesCount->fetchColumn();

        $data = [
            'page_main_title' => 'Delete Folder',
            'folder' => $folder,
            'articles_count' => $articlesCount
        ];

        $this->view('knowledge_base/folders/delete', $data);
    }

    /**
     * Destroy folder
     */
    public function destroyFolder($folderId)
    {
        $this->requireKnowledgeBasePermission('destroy');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/knowledge_base');
        }

        // Delete folder using the model method
        if ($this->kbModel->deleteFolder($folderId)) {
            flash('kb_message', 'Folder deleted successfully. All articles have been moved to General folder.', 'success');
            redirect('/knowledge_base');
        } else {
            flash('kb_message', 'Failed to delete folder.', 'error');
            redirect('/knowledge_base/folders/delete/' . $folderId);
        }
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