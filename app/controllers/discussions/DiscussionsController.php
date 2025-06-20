<?php

namespace App\Controllers\Discussions;

use App\Core\Controller;
use App\Core\Auth;

class DiscussionsController extends Controller {
    private $discussionModel;

    public function __construct() {
        parent::__construct();
        Auth::requireLogin(); // Ensure user is logged in
        $this->discussionModel = $this->model('Discussion');
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        $discussions = $this->discussionModel->getDiscussionsForUser($userId, $role);
        
        $data = [
            'page_main_title' => 'مناقشاتي',
            'discussions' => $discussions
        ];

        $this->view('discussions/index', $data);
    }
} 