<?php

namespace App\Controllers\Documentation;

use App\Core\Controller;

class DocumentationController extends Controller
{
    /**
     * Show the documentation page.
     */
    public function index()
    {
        $this->view('documentation/index', ['title' => 'System Documentation']);
    }
} 