<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\TelegramSetting;

class TelegramSettingsController extends Controller
{
    private $telegramSettingModel;

    public function __construct()
    {
        $this->telegramSettingModel = new TelegramSetting();
    }

    /**
     * Display the Telegram settings page.
     */
    public function index()
    {
        $data = [
            'title' => 'Telegram Settings',
            'admin_users' => $this->telegramSettingModel->getAdminUsers(),
            'current_settings' => $this->telegramSettingModel->getAllSettings()
        ];
        
        // The view path assumes a 'telegram_settings' folder inside 'admin' views
        $this->view('admin/telegram_settings/index', $data);
    }

    /**
     * Save the Telegram settings from the form submission.
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . '/admin/telegram_settings');
            exit;
        }

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $telegramUserId = filter_input(INPUT_POST, 'telegram_user_id', FILTER_VALIDATE_INT);
        $telegramChatId = filter_input(INPUT_POST, 'telegram_chat_id', FILTER_VALIDATE_INT);

        if (!$userId || !$telegramUserId || !$telegramChatId) {
            $_SESSION['error'] = 'البيانات المدخلة غير صالحة. يرجى إدخال أرقام صحيحة.';
        } elseif ($this->telegramSettingModel->isLinkExist($userId, $telegramUserId)) {
            $_SESSION['error'] = 'فشل الإضافة. المستخدم أو حساب تليجرام مرتبط بالفعل.';
        } else {
            if ($this->telegramSettingModel->addSetting($userId, $telegramUserId, $telegramChatId)) {
                $_SESSION['success'] = 'تمت إضافة الربط بنجاح.';
            } else {
                $_SESSION['error'] = 'حدث خطأ أثناء إضافة الربط.';
            }
        }
        
        header('Location: ' . BASE_PATH . '/admin/telegram_settings');
        exit;
    }

    public function delete($id = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            exit('Method not allowed.');
        }

        $settingId = filter_var($id, FILTER_VALIDATE_INT);
        if ($settingId && $this->telegramSettingModel->deleteSetting($settingId)) {
            $_SESSION['success'] = 'تم حذف الربط بنجاح.';
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء حذف الربط أو أن المعرف غير صالح.';
        }

        header('Location: ' . BASE_PATH . '/admin/telegram_settings');
        exit;
    }
} 