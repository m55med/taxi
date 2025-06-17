<?php

namespace App\Controllers\Reports\Teams;

use App\Core\Controller;
use App\Core\Auth;

class TeamsController extends Controller
{
    private $teamModel;

    public function __construct()
    {
        parent::__construct();
        Auth::check();

        if (!in_array($_SESSION['role'], ['admin', 'developer'])) {
            $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->teamModel = $this->model('reports/Teams/TeamsReport');
    }

    public function index()
    {
        $data = [
            'title' => 'تقرير الفرق',
            'teams' => $this->teamModel->getTeamsReport(),
        ];

        $this->view('reports/Teams/index', $data);
    }
} 