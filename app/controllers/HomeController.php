<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        } else {
            redirect('login');
        }
    }
} 