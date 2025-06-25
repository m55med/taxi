<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Coupon;
use App\Core\Auth;

class CouponsController extends Controller {
    private $couponModel;

    public function __construct() {
        parent::__construct();
        // Ensure user is logged in and is an admin
        Auth::checkAdmin();
        $this->couponModel = $this->model('Admin\Coupon');
    }

    public function index() {
        // Handle form submissions for add, update, delete
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add_bulk':
                        $this->addBulk();
                        break;
                    case 'update':
                        $this->update();
                        break;
                    case 'delete':
                        $this->delete();
                        break;
                    case 'bulk_delete':
                        $this->bulkDelete();
                        break;
                }
            }
        }
        
        $filters = [
            'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
            'is_used' => filter_input(INPUT_GET, 'is_used', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
            'country_id' => filter_input(INPUT_GET, 'country_id', FILTER_SANITIZE_NUMBER_INT) ?? ''
        ];

        // Handle export requests
        if (isset($_GET['export'])) {
            $this->handleExport($_GET['export'], $filters);
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $coupons_data = $this->couponModel->getCoupons($filters, $page, 20);
        $coupon_stats = $this->couponModel->getCouponStats($filters);

        $data = [
            'page_main_title' => 'Manage Coupons',
            'coupons' => $coupons_data['data'],
            'pagination' => $coupons_data,
            'stats' => $coupon_stats,
            'countries' => $this->couponModel->getCountries(),
            'filters' => $filters,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];
        
        // Clear session messages
        unset($_SESSION['message']);
        unset($_SESSION['error']);
        
        $this->view('admin/coupons/index', $data);
    }
    
    private function addBulk() {
        $codes_raw = $_POST['codes'] ?? '';
        $value = $_POST['value'] ?? 0;
        $country_id = !empty($_POST['country_id']) ? $_POST['country_id'] : null;
        
        $codes = preg_split('/\\r\\n|\\n|\\r|,|\\s+/', $codes_raw);
        $codes = array_filter(array_map('trim', $codes));

        if (empty($codes) || !is_numeric($value) || $value <= 0) {
            flash('coupon_message', 'Please enter valid codes and a value.', 'error');
            redirect('/admin/coupons');
        }

        $result = $this->couponModel->addBulkCoupons($codes, $value, $country_id);

        if ($result) {
            $message = "Successfully added {$result['added']} coupons.";
            if ($result['skipped'] > 0) {
                $message .= " Skipped {$result['skipped']} duplicate coupons.";
            }
            flash('coupon_message', $message, 'success');
        } else {
            flash('coupon_message', 'An error occurred while adding coupons.', 'error');
        }
        redirect('/admin/coupons');
    }
    
    private function update() {
        header('Content-Type: application/json');
        $id = $_POST['id'];
        $coupon = $this->couponModel->findCouponById($id);

        if (!$coupon || $coupon['is_used']) {
             echo json_encode(['success' => false, 'message' => 'Cannot edit a used or non-existent coupon.']);
             exit;
        }

        $data = [
            'id' => $id,
            'code' => trim($_POST['code']),
            'value' => $_POST['value'],
            'country_id' => !empty($_POST['country_id']) ? $_POST['country_id'] : null
        ];
        
        // Basic validation
        if (empty($data['code']) || !is_numeric($data['value']) || $data['value'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid code or value provided.']);
            exit;
        }
        
        if ($this->couponModel->updateCoupon($id, $data)) {
            flash('coupon_message', 'Coupon updated successfully.', 'success');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update coupon. The code might already exist.']);
        }
        exit;
    }

    private function delete() {
        $id = $_POST['id'];
         $coupon = $this->couponModel->findCouponById($id);

        if (!$coupon || $coupon['is_used']) {
            flash('coupon_message', 'Cannot delete a used or non-existent coupon.', 'error');
            redirect('/admin/coupons');
        }

        if ($this->couponModel->deleteCoupon($id)) {
            flash('coupon_message', 'Coupon deleted successfully.', 'success');
        } else {
            flash('coupon_message', 'Failed to delete coupon.', 'error');
        }
        redirect('/admin/coupons');
    }

    private function bulkDelete() {
        $ids = $_POST['coupon_ids'] ?? [];
        if (empty($ids)) {
            flash('coupon_message', 'No coupons selected.', 'error');
            redirect('/admin/coupons');
        }

        $deleted_count = $this->couponModel->deleteBulkCoupons($ids);

        if ($deleted_count > 0) {
            flash('coupon_message', "Successfully deleted {$deleted_count} coupons.", 'success');
        } else {
            flash('coupon_message', 'Failed to delete selected coupons. They may already be used or do not exist.', 'error');
        }
        redirect('/admin/coupons');
    }
    
    private function handleExport($type, $filters) {
        $coupons = $this->couponModel->getCouponsWithoutPagination($filters);
        
        switch ($type) {
            case 'excel':
                $this->exportExcel($coupons);
                break;
            case 'txt':
                $this->exportTxt($coupons);
                break;
            case 'json':
                $this->exportJson($coupons);
                break;
        }
        exit;
    }

    private function exportExcel($coupons) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=coupons.csv');
        
        // Add UTF-8 BOM to ensure Excel opens it correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Add headers
        fputcsv($output, [
            'Code', 'Value', 'Country', 'Status', 'Created At', 
            'Used in Ticket', 'Used By'
        ]);

        foreach ($coupons as $coupon) {
            fputcsv($output, [
                $coupon['code'],
                $coupon['value'],
                $coupon['country_name'] ?? 'N/A',
                $coupon['is_used'] ? 'Used' : 'Unused',
                $coupon['created_at'],
                $coupon['ticket_number'] ?? 'N/A',
                $coupon['used_by_username'] ?? 'N/A'
            ]);
        }

        fclose($output);
    }

    private function exportTxt($coupons) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename=coupons.txt');
        foreach ($coupons as $coupon) {
            echo $coupon['code'] . "\r\n";
        }
    }

    private function exportJson($coupons) {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=coupons.json');
        echo json_encode($coupons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} 