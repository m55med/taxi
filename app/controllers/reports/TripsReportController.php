<?php

namespace App\Controllers\Reports;

use App\Core\Controller;

class TripsReportController extends Controller
{
    /**
     * Display the trips report page and handle filtering.
     */
    public function index()
    {
        // Authenticate and authorize user (assuming you have a session-based login)
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('/login');
        // }

        $filters = $_GET ?? [];
        
        // Load the model
        $tripsReportModel = $this->model('reports/TripsReportModel');
        
        // Get dashboard data from the model
        $dashboardData = $tripsReportModel->getDashboardData($filters);

        // Pass data to the view
        $this->view('reports/trips', [
            'dashboard' => $dashboardData,
            'filters' => $filters // Pass filters back to the view to populate form fields
        ]);
    }
} 