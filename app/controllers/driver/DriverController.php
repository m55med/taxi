<?php

namespace App\Controllers\Driver;

use App\Core\Controller;
use App\Models\Review\Review;
use Exception;

class DriverController extends Controller
{
    private $driverModel;
    private $discussionModel;
    private $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->driverModel = $this->model('driver/Driver');
        $this->discussionModel = $this->model('discussion/Discussion');
        $this->reviewModel = $this->model('review/Review');
    }

    public function update()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة', 405);
            }

            if (empty($_POST['driver_id']) || empty(trim($_POST['name']))) {
                throw new Exception('البيانات المطلوبة غير مكتملة', 400);
            }

            $result = $this->driverModel->update([
                'id' => $_POST['driver_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'nationality' => trim($_POST['nationality'] ?? ''),
                'data_source' => trim($_POST['data_source'] ?? ''),
                'app_status' => trim($_POST['app_status'] ?? 'active')
            ]);

            if (!$result) {
                throw new Exception('فشل في تحديث البيانات من جهة الخادم', 500);
            }

            // جلب بيانات السائق المحدثة لإرجاعها
            $updatedDriver = $this->driverModel->getById($_POST['driver_id']);
            if (!$updatedDriver) {
                 throw new Exception('فشل في استرداد بيانات السائق المحدثة', 500);
            }

            $this->sendJsonResponse(['success' => true, 'message' => 'تم تحديث البيانات بنجاح', 'driver' => $updatedDriver]);

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
                throw new Exception('طريقة طلب غير صحيحة', 405);
            }

            if (empty($_POST['driver_id'])) {
                throw new Exception('معرف السائق مطلوب', 400);
            }

            $driverId = $_POST['driver_id'];
            $documents = $_POST['documents'] ?? [];
            $documentNotes = $_POST['document_notes'] ?? [];

            $result = $this->driverModel->updateDocuments($driverId, $documents, $documentNotes);

            if (!$result) {
                throw new Exception('فشل تحديث المستندات في قاعدة البيانات', 500);
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'تم تحديث المستندات بنجاح'
            ]);

        } catch (Exception $e) {
            error_log("Driver documents update error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    // public function updateStatus()
    // {
    //     header('Content-Type: application/json');
    //     $data = json_decode(file_get_contents('php://input'), true);

    //     if (!$this->isAjax() || !$this->isAuthenticated()) {
    //         http_response_code(401);
    //         echo json_encode(['error' => 'Unauthorized']);
    //         return;
    //     }

    //     if (!isset($data['driver_id']) || !isset($data['status'])) {
    //         http_response_code(400);
    //         echo json_encode(['error' => 'Invalid input']);
    //         return;
    //     }

    //     try {
    //         $result = $this->driverModel->updateStatus($data['driver_id'], $data['status']);
    //         if ($result) {
    //             echo json_encode(['success' => true]);
    //         } else {
    //             http_response_code(500);
    //             echo json_encode(['error' => 'Failed to update status']);
    //         }
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['error' => $e->getMessage()]);
    //     }
    // }

    public function assign()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة', 405);
            }

            if (empty($_POST['driver_id']) || empty($_POST['to_user_id'])) {
                throw new Exception('البيانات المطلوبة للتحويل غير مكتملة', 400);
            }

            $driverId = $_POST['driver_id'];
            $fromUserId = $_SESSION['user_id'];
            $toUserId = $_POST['to_user_id'];
            $note = trim($_POST['note'] ?? '');

            $result = $this->driverModel->assignDriver($driverId, $fromUserId, $toUserId, $note);

            if ($result) {
                // Release the hold on the driver so they are available for the new user
                $callModel = $this->model('Calls/Call');
                if ($callModel) {
                    $callModel->releaseDriverHold($driverId);
                }

                // Since this is an AJAX request from the call center, we don't redirect.
                // The frontend will handle the redirect.
                if (isset($_SESSION['locked_driver_id']) && $_SESSION['locked_driver_id'] == $driverId) {
                    unset($_SESSION['locked_driver_id']);
                }
                $this->sendJsonResponse(['success' => true, 'message' => 'تم تحويل السائق بنجاح.']);
            } else {
                throw new Exception('فشل تحويل السائق في قاعدة البيانات.', 500);
            }
        } catch (Exception $e) {
            error_log("Driver assignment error: " . $e->getMessage());
            $this->sendJsonResponse(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    public function details($id)
    {
        if (empty($id)) {
            redirect('errors/notfound');
        }

        $driver = $this->driverModel->getById($id);

        if (!$driver) {
            redirect('errors/notfound');
        }

        $callHistory = $this->driverModel->getCallHistory($id);
        $assignmentHistory = $this->driverModel->getAssignmentHistory($id);
        $assignableUsers = $this->driverModel->getAssignableUsers(); // Fetch users for the form

        // Get driver-level discussions
        $driverDiscussions = $this->discussionModel->getDiscussions('App\\\\Models\\\\Driver\\\\Driver', $id);

        // Efficiently fetch reviews, discussions, and replies for all calls
        $callIds = array_map(fn($call) => $call['id'], $callHistory);
        
        $allReviews = !empty($callIds) ? $this->reviewModel->getReviewsForMultipleItems('driver_call', $callIds) : [];
        
        $reviewIds = !empty($allReviews) ? array_map(fn($r) => $r['id'], $allReviews) : [];
        $allDiscussions = !empty($reviewIds) ? $this->discussionModel->getDiscussionsForReviews($reviewIds) : [];

        $discussionIds = !empty($allDiscussions) ? array_map(fn($d) => $d['id'], $allDiscussions) : [];
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

        // Group reviews by call (reviewable) ID
        $reviewsByCallId = [];
        foreach ($allReviews as $review) {
            $review['discussions'] = $discussionsByReviewId[$review['id']] ?? [];
            $reviewsByCallId[$review['reviewable_id']][] = $review;
        }
        
        // Attach the fully-loaded reviews to each call in the history
        foreach ($callHistory as $key => $call) {
            $callHistory[$key]['reviews'] = $reviewsByCallId[$call['id']] ?? [];
        }

        $data = [
            'page_main_title' => 'تفاصيل السائق',
            'driver' => $driver,
            'callHistory' => $callHistory,
            'assignmentHistory' => $assignmentHistory,
            'assignableUsers' => $assignableUsers,
            'discussions' => $driverDiscussions, // Renamed for clarity
            'currentUser' => ['id' => $_SESSION['user_id'], 'role' => $_SESSION['role']]
        ];

        $this->view('drivers/details', $data);
    }

    public function search()
    {
        // We expect a 'q' query parameter, e.g., /drivers/search?q=123
        $query = $_GET['q'] ?? '';

        if (empty($query) || !is_string($query)) {
            $this->sendJsonResponse([]);
            return;
        }

        $drivers = $this->driverModel->searchByPhone(trim($query));
        $this->sendJsonResponse($drivers);
    }
}