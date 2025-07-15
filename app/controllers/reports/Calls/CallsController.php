<?php

namespace App\Controllers\Reports\Calls;

use App\Core\Controller;

class CallsController extends Controller
{
    private $callsReportModel;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->callsReportModel = $this->model('reports/Calls/CallsReport');
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $stats = $this->callsReportModel->getCallsStats($filters);
        $totalRecords = $this->callsReportModel->countCalls($filters);
        $totalPages = ceil($totalRecords / $limit);

        $calls = $this->callsReportModel->getPaginatedCalls($limit, $offset, $filters);

        $data = array_merge($stats, [
            'calls' => $calls,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords
            ],
            'filters' => $filters
        ]);

        $this->view('reports/Calls/index', $data);
    }
}