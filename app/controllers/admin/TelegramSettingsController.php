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
            header('Location: ' . URLROOT . '/admin/telegram_settings');
            exit;
        }

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $telegramUserId = filter_input(INPUT_POST, 'telegram_user_id', FILTER_VALIDATE_INT);
        $telegramChatId = filter_input(INPUT_POST, 'telegram_chat_id', FILTER_VALIDATE_INT);

        if (!$userId || !$telegramUserId || !$telegramChatId) {
            $_SESSION['telegram_message'] = 'Invalid data provided. Please enter valid numbers.';
            $_SESSION['telegram_message_type'] = 'error';
        } elseif ($this->telegramSettingModel->isLinkExist($userId, $telegramUserId)) {
            $_SESSION['telegram_message'] = 'Failed to add. The user or Telegram account is already linked.';
            $_SESSION['telegram_message_type'] = 'error';
        } else {
            if ($this->telegramSettingModel->addSetting($userId, $telegramUserId, $telegramChatId)) {
                $_SESSION['telegram_message'] = 'Link added successfully.';
                $_SESSION['telegram_message_type'] = 'success';
            } else {
                $_SESSION['telegram_message'] = 'An error occurred while adding the link.';
                $_SESSION['telegram_message_type'] = 'error';
            }
        }

        header('Location: ' . URLROOT . '/admin/telegram_settings');
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
            $_SESSION['telegram_message'] = 'Link deleted successfully.';
            $_SESSION['telegram_message_type'] = 'success';
        } else {
            $_SESSION['telegram_message'] = 'An error occurred during deletion or the ID is invalid.';
            $_SESSION['telegram_message_type'] = 'error';
        }

        header('Location: ' . URLROOT . '/admin/telegram_settings');
        exit;
    }
}