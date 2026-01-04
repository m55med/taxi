<?php

namespace App\Controllers;

use App\Core\Controller;

class HelpController extends Controller
{
    public function index()
    {
        $data = [
            'page_title' => 'Help & Support',
            'page_main_title' => 'Help & Support Center'
        ];

        $this->view('help/index', $data);
    }

    public function submitFeedback()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('help');
            return;
        }

        $feedback = [
            'type' => $_POST['feedback_type'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
            'priority' => $_POST['priority'] ?? 'medium',
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user']['name'] ?? 'Anonymous',
            'user_email' => $_SESSION['user']['email'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Here you can save to database, send email, etc.
        // For now, we'll just log it
        error_log('New feedback received: ' . json_encode($feedback));

        flash('success', 'Thank you for your feedback! We will review it shortly.');
        redirect('help');
    }
}
