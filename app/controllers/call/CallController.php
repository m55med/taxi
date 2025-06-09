<?php

require_once __DIR__ . '/../BaseController.php';

class CallController extends BaseController
{
    private $callsModel;

    public function __construct()
    {
        parent::__construct();
        $this->callsModel = $this->model('Calls');
    }

    public function index()
    {
        $this->view('call/index', ['pageTitle' => 'لوحة التحكم']);
    }

    public function skip($driverId = null)
    {
        if ($driverId && isset($_SESSION['user_id'])) {
            $this->callsModel->unlockDriver($driverId, $_SESSION['user_id']);
            // Add the skipped driver ID to the session to prevent immediate re-fetching
            $_SESSION['skipped_driver_id'] = $driverId;
        }
        // Redirect to the main call page to get a new driver
        header("Location: " . BASE_PATH . "/call");
        exit();
    }

    public function record()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // ... existing code ...
        }
    }
} 