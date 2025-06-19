<?php

namespace App\Controllers\Trips;

use App\Core\Controller;
use App\Models\Trips\TripModel;

class TripsController extends Controller
{
    /**
     * Display the trips upload page.
     */
    public function upload()
    {
        // Simply load the view
        $this->view('trips/index');
    }

    /**
     * Process the uploaded trips file chunk via API.
     */
    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->sendJsonResponse(['error' => 'Method not allowed.'], 405);
        }

        $postData = file_get_contents('php://input');
        $chunkData = json_decode($postData, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($chunkData['trips']) || !is_array($chunkData['trips'])) {
            return $this->sendJsonResponse(['error' => 'Invalid JSON data provided.'], 400);
        }

        try {
            $tripModel = $this->model('trips/TripModel');
            $stats = $tripModel->processChunk($chunkData['trips']);
            return $this->sendJsonResponse(['status' => 'success', 'stats' => $stats]);
        } catch (\Exception $e) {
            error_log('TripsController Processing Error: ' . $e->getMessage());
            return $this->sendJsonResponse(['error' => 'An internal server error occurred during chunk processing.'], 500);
        }
    }
} 