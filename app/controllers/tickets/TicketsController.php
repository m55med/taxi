<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;

class TicketsController extends Controller
{
    private $ticketModel;

    public function __construct()
    {
        parent::__construct();
        if (!Auth::isLoggedIn()) {
            redirect('auth/login');
        }
        $this->ticketModel = $this->model('tickets/Ticket');
    }

    public function details($id)
    {
        if (empty($id)) {
            redirect('errors/notfound');
        }

        $ticket = $this->ticketModel->getTicketDetails($id);

        if (!$ticket) {
            $this->triggerNotFound("Ticket not found.");
            return;
        }

        $ticket['coupons'] = $this->ticketModel->getTicketCoupons($id);
        $reviews = $this->ticketModel->getReviews($id);
        $discussions = $this->ticketModel->getDiscussions($id);

        $relatedTickets = $this->ticketModel->getRelatedTickets($ticket['phone'], $id);
        
        $data = [
            'page_main_title' => 'تفاصيل التذكرة',
            'ticket' => $ticket,
            'relatedTickets' => $relatedTickets,
            'reviews' => $reviews,
            'discussions' => $discussions,
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['role']
            ]
        ];

        $this->view('tickets/details', $data);
    }

    public function addReview($ticketId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':ticket_id' => $ticketId,
            ':reviewed_by' => $_SESSION['user_id'],
            ':review_result' => $_POST['review_result'],
            ':review_notes' => trim($_POST['review_notes'])
        ];

        if ($this->ticketModel->addReview($data)) {
            // Set success message
            $_SESSION['success_message'] = 'تمت إضافة المراجعة بنجاح.';
        } else {
            // Set error message
            $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة المراجعة.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function addDiscussion($ticketId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':ticket_id' => $ticketId,
            ':opened_by' => $_SESSION['user_id'],
            ':reason' => trim($_POST['reason']),
            ':notes' => trim($_POST['notes'])
        ];

        if ($this->ticketModel->addDiscussion($data)) {
            $_SESSION['success_message'] = 'تم فتح المناقشة بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء فتح المناقشة.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function addObjection($ticketId, $discussionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             redirect('tickets/details/' . $ticketId);
        }
        
        $ticket = $this->ticketModel->getTicketDetails($ticketId);
        // User must be the ticket creator, or a manager/leader to add an objection/reply.
        if ($_SESSION['user_id'] != $ticket['created_by'] && !in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            redirect('tickets/details/' . $ticketId);
        }

        $data = [
            ':discussion_id' => $discussionId,
            ':objection_text' => trim($_POST['objection_text']),
            ':replied_to_user_id' => $_POST['replied_to_user_id'],
            ':replied_by_agent_id' => $_SESSION['user_id']
        ];

        if ($this->ticketModel->addObjection($data)) {
            $_SESSION['success_message'] = 'تمت إضافة الرد بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة الرد.';
        }
        redirect('tickets/details/' . $ticketId);
    }

    public function closeDiscussion($ticketId, $discussionId)
    {
        // Add authorization check to ensure only specific roles can close discussions
        if (!in_array($_SESSION['role'], ['quality_manager', 'Team_leader', 'admin', 'developer'])) {
            // Or use $this->authorize([...]) if you have it set up
            redirect('tickets/details/' . $ticketId);
        }

        if ($this->ticketModel->closeDiscussion($discussionId)) {
            $_SESSION['success_message'] = 'تم إغلاق المناقشة بنجاح.';
        } else {
            $_SESSION['error_message'] = 'حدث خطأ أثناء إغلاق المناقشة.';
        }
        redirect('tickets/details/' . $ticketId);
    }
} 