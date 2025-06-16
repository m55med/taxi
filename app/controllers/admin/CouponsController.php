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
        $this->couponModel = new Coupon();
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
            'search' => $_GET['search'] ?? '',
            'is_used' => $_GET['is_used'] ?? '',
            'country_id' => $_GET['country_id'] ?? ''
        ];

        // Handle export requests
        if (isset($_GET['export'])) {
            $this->handleExport($_GET['export'], $filters);
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $coupons_data = $this->couponModel->getCoupons($filters, $page, 20);
        $coupon_stats = $this->couponModel->getCouponStats($filters);

        $data = [
            'page_main_title' => 'إدارة الكوبونات',
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
            $_SESSION['error'] = 'الرجاء إدخال أكواد وقيمة صالحة.';
            header('Location: ' . BASE_PATH . '/admin/coupons');
            exit;
        }

        $result = $this->couponModel->addBulkCoupons($codes, $value, $country_id);

        if ($result) {
            $message = "تمت الإضافة بنجاح. العدد المضاف: {$result['added']}.";
            if ($result['skipped'] > 0) {
                $message .= " العدد المستثنى (مكرر): {$result['skipped']}.";
            }
            $_SESSION['message'] = $message;
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء إضافة الكوبونات.';
        }
        header('Location: ' . BASE_PATH . '/admin/coupons');
        exit;
    }
    
    private function update() {
        $id = $_POST['id'];
        $coupon = $this->couponModel->findCouponById($id);

        if (!$coupon || $coupon['is_used']) {
             $_SESSION['error'] = 'لا يمكن تعديل كوبون مستخدم أو غير موجود.';
             header('Location: ' . BASE_PATH . '/admin/coupons');
             exit;
        }

        $data = [
            'id' => $id,
            'code' => trim($_POST['code']),
            'value' => $_POST['value'],
            'country_id' => !empty($_POST['country_id']) ? $_POST['country_id'] : null
        ];
        
        if ($this->couponModel->updateCoupon($id, $data)) {
            $_SESSION['message'] = 'تم تحديث الكوبون بنجاح.';
        } else {
            $_SESSION['error'] = 'فشل تحديث الكوبون. قد يكون الكود موجوداً مسبقاً أو أن الكوبون مستخدم.';
        }
        header('Location: ' . BASE_PATH . '/admin/coupons');
        exit;
    }

    private function delete() {
        $id = $_POST['id'];
         $coupon = $this->couponModel->findCouponById($id);

        if (!$coupon || $coupon['is_used']) {
             $_SESSION['error'] = 'لا يمكن حذف كوبون مستخدم أو غير موجود.';
             header('Location: ' . BASE_PATH . '/admin/coupons');
             exit;
        }

        if ($this->couponModel->deleteCoupon($id)) {
            $_SESSION['message'] = 'تم حذف الكوبون بنجاح.';
        } else {
            $_SESSION['error'] = 'فشل حذف الكوبون. قد يكون تم استخدامه.';
        }
        header('Location: ' . BASE_PATH . '/admin/coupons');
        exit;
    }

    private function bulkDelete() {
        $ids = $_POST['coupon_ids'] ?? [];
        if (empty($ids)) {
            $_SESSION['error'] = 'لم يتم تحديد أي كوبونات.';
            header('Location: ' . BASE_PATH . '/admin/coupons');
            exit;
        }

        $deleted_count = $this->couponModel->deleteBulkCoupons($ids);

        if ($deleted_count > 0) {
            $_SESSION['message'] = "تم حذف {$deleted_count} كوبون بنجاح.";
        } else {
            $_SESSION['error'] = 'فشل حذف الكوبونات المحددة. قد تكون مستخدمة أو غير موجودة.';
        }
        header('Location: ' . BASE_PATH . '/admin/coupons');
        exit;
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
            'الكود', 'القيمة', 'الدولة', 'الحالة', 'تاريخ الإنشاء', 
            'استخدم في تذكرة', 'استخدم بواسطة'
        ]);

        foreach ($coupons as $coupon) {
            fputcsv($output, [
                $coupon['code'],
                $coupon['value'],
                $coupon['country_name'] ?? 'N/A',
                $coupon['is_used'] ? 'مستخدم' : 'غير مستخدم',
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