<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['user'])) {
            redirect('dashboard');
        } else {
            redirect('login');
        }
    }
}