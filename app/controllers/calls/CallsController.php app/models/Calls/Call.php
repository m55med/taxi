<?php

class CallsController
{
    public function __construct()
    {
        // ... model loading
    }

    public function index()
    {
        if (isset($_SESSION['locked_driver_id'])) {
            $this->callsModel->releaseDriverHold($_SESSION['locked_driver_id']);
            unset($_SESSION['locked_driver_id']);
        }

        $driver = null;
        $phone = filter_input(INPUT_GET, 'phone', FILTER_SANITIZE_NUMBER_INT);
        $phone = $phone ? ltrim($phone, '0') : null;

        if (!empty($phone)) {
            $driver = $this->callsModel->findAndLockDriverByPhone($phone);
        } else {
            // The skipping logic is now handled by the database query, no session needed.
            $driver = $this->callsModel->findAndLockNextDriver();
        }

        // ... The rest of the data preparation logic remains the same
    }

    public function skip($driverId = null)
    {
        if ($driverId && isset($_SESSION['user_id'])) {
            $this->callsModel->releaseDriverHold($driverId);
            // Snooze the driver for 5 minutes for the current user
            $this->callsModel->snoozeDriver($driverId, $_SESSION['user_id'], 5);
        }
        header("Location: " . BASE_PATH . "/calls");
        exit();
    }
} 