<?php

namespace App\Controllers\Driver;

// A test comment to see if any edit can be applied
use App\Core\Controller;
use App\Core\Database;
use App\Models\Review\Review;
use Exception;

class DriverController extends Controller
{
    private $driverModel;
    private $discussionModel;
    private $reviewModel;
    private $documentModel;

    public function __construct()
    {
        parent::__construct();
        $this->driverModel = $this->model('Driver/Driver');
        $this->discussionModel = $this->model('Discussion/Discussion');
        $this->reviewModel = $this->model('Review/Review');
        $this->documentModel = $this->model('Document/Document');
    }

    public function index()
    {
        redirect('listings/calls');
    }

    public function update()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method', 405);
            }

            $driverId = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
            if (!$driverId) {
                throw new Exception('Required driver_id is missing', 400);
            }

            // Collect all data from POST
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
                'gender' => $_POST['gender'] ?? '',
                'country_id' => filter_input(INPUT_POST, 'country_id', FILTER_VALIDATE_INT) ?: null,
                'app_status' => $_POST['app_status'] ?? 'inactive',
                'car_type_id' => filter_input(INPUT_POST, 'car_type_id', FILTER_VALIDATE_INT) ?: null,
                'notes' => $_POST['notes'] ?? ''
            ];

            $hasManyTrips = filter_var($_POST['has_many_trips'] ?? 0, FILTER_VALIDATE_BOOLEAN);

            // Execute updates as separate operations
            $coreInfoSuccess = $this->driverModel->updateCoreInfo($driverId, $data);
            if (!$coreInfoSuccess) {
                throw new Exception('Failed to update core driver information.', 500);
            }

            $tripAttrSuccess = $this->driverModel->updateTripAttribute($driverId, $hasManyTrips);
            if (!$tripAttrSuccess) {
                throw new Exception('Failed to update driver trip attribute.', 500);
            }

            // Fetch fully updated driver data to return
            $updatedDriver = $this->driverModel->getById($driverId);
            if (!$updatedDriver) {
                throw new Exception('Failed to retrieve updated driver data', 500);
            }

            ob_clean();

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Data updated successfully',
                'driver' => $updatedDriver
            ]);

        } catch (Exception $e) {
            error_log("Driver update error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    public function updateDocuments()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method', 405);
            }

            if (empty($_POST['driver_id'])) {
                throw new Exception('Driver ID is required', 400);
            }

            $driverId = $_POST['driver_id'];
            $documents = $_POST['documents'] ?? [];
            $documentNotes = $_POST['document_notes'] ?? [];

            $result = $this->driverModel->updateDocuments($driverId, $documents, $documentNotes);

            if (!$result) {
                throw new Exception('Failed to update documents in the database', 500);
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Documents updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("Driver documents update error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    public function assign()
    {
        // Check if it's an AJAX request, which is what the call center transfer modal uses
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method', 405);
            }

            if (empty($_POST['driver_id']) || empty($_POST['to_user_id'])) {
                throw new Exception('Required data for assignment is missing', 400);
            }

            $driverId = $_POST['driver_id'];
            $fromUserId = $_SESSION['user_id'];
            $toUserId = $_POST['to_user_id'];
            $note = trim($_POST['note'] ?? '');

            $result = $this->driverModel->assignDriver($driverId, $fromUserId, $toUserId, $note);

            if ($result) {
                // This logic is common for both AJAX and non-AJAX responses
                $callModel = $this->model('Call/Call');
                if ($callModel) {
                    $callModel->releaseDriverHold($driverId);
                }
                if (isset($_SESSION['locked_driver_id']) && $_SESSION['locked_driver_id'] == $driverId) {
                    unset($_SESSION['locked_driver_id']);
                }

                if ($isAjax) {
                    // For the call center, send a JSON response
                    $this->sendJsonResponse(['success' => true, 'message' => 'Driver transferred successfully.']);
                } else {
                    // For other forms, use a flash message and redirect
                    flash('driver_assignment_success', 'Driver assigned successfully.');
                    redirect('drivers/details/' . $driverId);
                }

            } else {
                throw new Exception('Failed to assign driver in the database.', 500);
            }
        } catch (Exception $e) {
            error_log("Driver assignment error: " . $e->getMessage());

            if ($isAjax) {
                // For AJAX, send a JSON error response
                $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
            } else {
                // For regular forms, use a flash message and redirect back
                flash('driver_assignment_error', $e->getMessage(), 'bg-red-500 text-white');
                $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'drivers';
                redirect($redirect_url);
            }
        }
    }

    public function details($id = null)
    {
        if (empty($id)) {
            redirect('listings/calls');
        }

        $driver = $this->driverModel->getById($id);

        if (!$driver) {
            redirect('errors/notfound');
        }

        $callHistory = $this->driverModel->getCallHistory($id);
        $assignmentHistory = $this->driverModel->getAssignmentHistory($id);
        $assignableUsers = $this->driverModel->getAssignableUsers(); // Fetch users for the form

        $driverDocuments = $this->documentModel->getDriverDocuments($id);
        $unassignedDocuments = $this->documentModel->getUnassignedDocumentTypes($id);

        // Get driver-level discussions
        $driverDiscussions = $this->discussionModel->getDiscussions('App\\\\Models\\\\Driver\\\\Driver', $id);

        // Efficiently fetch reviews, discussions, and replies for all calls
        $callIds = array_column($callHistory, 'id');
        $reviewsByCallId = !empty($callIds) ? $this->reviewModel->getReviewsForMultipleItems('driver_call', $callIds) : [];

        // Flatten the reviews to get all review IDs and then fetch their discussions
        $allReviewsFlat = array_merge(...array_values($reviewsByCallId));
        $reviewIds = !empty($allReviewsFlat) ? array_column($allReviewsFlat, 'id') : [];
        $allDiscussions = !empty($reviewIds) ? $this->discussionModel->getDiscussionsForReviews($reviewIds) : [];

        $discussionIds = !empty($allDiscussions) ? array_column($allDiscussions, 'id') : [];
        $allReplies = !empty($discussionIds) ? $this->discussionModel->getRepliesForDiscussions($discussionIds) : [];

        // Group replies by discussion ID
        $repliesByDiscussionId = [];
        foreach ($allReplies as $reply) {
            $repliesByDiscussionId[$reply['discussion_id']][] = $reply;
        }

        // Attach replies to discussions
        foreach ($allDiscussions as $key => $discussion) {
            $allDiscussions[$key]['replies'] = $repliesByDiscussionId[$discussion['id']] ?? [];
        }

        // Group discussions by review ID
        $discussionsByReviewId = [];
        foreach ($allDiscussions as $discussion) {
            $discussionsByReviewId[$discussion['discussable_id']][] = $discussion;
        }

        // Attach discussions to reviews within the grouped structure
        foreach ($reviewsByCallId as &$reviews) {
            foreach ($reviews as &$review) {
                $review['discussions'] = $discussionsByReviewId[$review['id']] ?? [];
            }
        }
        unset($review, $reviews); // Unset references

        // Attach the fully-loaded reviews to each call in the history
        foreach ($callHistory as $key => &$call) {
            $call['reviews'] = $reviewsByCallId[$call['id']] ?? [];
        }
        unset($call); // Unset reference

        // Load ticket categories for the review form partial
        $categoryModel = $this->model('Tickets/Category');
        $ticket_categories = $categoryModel->getAll();

        // Ensure session helper is loaded for flash messages
        require_once APPROOT . '/helpers/session_helper.php';

        $data = [
            'page_main_title' => 'Driver Details',
            'driver' => $driver,
            'callHistory' => $callHistory,
            'assignmentHistory' => $assignmentHistory,
            'assignableUsers' => $assignableUsers,
            'driverDiscussions' => $driverDiscussions,
            'callReviews' => $callHistory,
            'driverDocuments' => $driverDocuments,
            'unassignedDocuments' => $unassignedDocuments,
            'ticket_categories' => $ticket_categories, // Pass categories for review partial
            'currentUser' => [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['role_name']
            ]
        ];

        $this->view('drivers/details', $data);
    }

    public function search()
    {
        header('Content-Type: application/json');

        if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
            echo json_encode([]);
            return;
        }

        $query = trim($_GET['q']);
        $results = $this->driverModel->searchByNameOrPhone($query);

        echo json_encode($results);
    }

    public function manageDocument()
    {
        $this->authorize('Driver/manageDocument');

        $db = Database::getInstance(); // Get DB instance for transaction
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method', 405);
            }

            $driverId = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
            $action = $_POST['action'] ?? '';

            if (!$driverId || !$action) {
                throw new Exception('Missing required parameters.', 400);
            }

            $db->beginTransaction();

            if ($action === 'upsert') {
                $docTypeId = filter_input(INPUT_POST, 'doc_type_id', FILTER_VALIDATE_INT);
                if (!$docTypeId)
                    throw new Exception('Missing document type ID.', 400);
                $status = $_POST['status'] ?? 'missing';
                $note = trim($_POST['note'] ?? '');
                $this->documentModel->upsertDriverDocument($driverId, $docTypeId, $status, $note);

            } elseif ($action === 'remove') {
                $docTypeId = filter_input(INPUT_POST, 'doc_type_id', FILTER_VALIDATE_INT);
                if (!$docTypeId)
                    throw new Exception('Missing document type ID.', 400);
                $this->documentModel->removeDriverDocument($driverId, $docTypeId);

            } else {
                throw new Exception('Invalid action specified.', 400);
            }

            // After any change, update the master flag
            $this->documentModel->updateDriverMissingDocsFlag($driverId);

            $db->commit();

            // Fetch the updated list of documents to send back to the client
            $updatedDocuments = $this->documentModel->getDriverDocuments($driverId);
            $unassignedDocuments = $this->documentModel->getUnassignedDocumentTypes($driverId);
            $updatedDriver = $this->driverModel->getById($driverId);


            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Document managed successfully.',
                'documents' => $updatedDocuments,
                'unassigned' => $unassignedDocuments,
                'driver' => $updatedDriver
            ]);

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Document management error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }
}