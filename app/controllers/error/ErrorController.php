<?php

namespace App\Controllers\error;

use App\Core\Controller;

class ErrorController extends Controller
{
    /**
     * Shows the 404 Not Found page.
     */
    public function notFound()
    {
        $this->view('errors/404', ['page_title' => 'Page Not Found']);
    }
} 