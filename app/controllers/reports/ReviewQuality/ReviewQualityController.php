<?php

namespace App\Controllers\Reports\ReviewQuality;

use App\Core\Controller;

class ReviewQualityController extends Controller
{
    private $qualityModel;

    public function __construct()
    {
        parent::__construct();
        $this->qualityModel = $this->model('Reports/ReviewQuality/ReviewQualityReport');
    }

    public function index()
    {
        $filters = [
            'agent_id' => $_GET['agent_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $summary = $this->qualityModel->getQualitySummary($filters);

        $data = [
            'title' => 'تقرير جودة المراجعات',
            'summary' => $summary,
            'agents' => $this->qualityModel->getAgents(),
            'filters' => $filters
        ];

        $this->view('reports/ReviewQuality/index', $data);
    }
}