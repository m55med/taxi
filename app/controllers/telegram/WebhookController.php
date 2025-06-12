<?php

namespace App\Controllers\Telegram;

use App\Models\Telegram\TelegramBot;

class WebhookController
{
    private $bot_token;
    private $input;
    private $model;

    public function __construct()
    {
        $this->bot_token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $this->input = file_get_contents('php://input');
        $this->model = new TelegramBot();
    }

    /**
     * Main handler for the Telegram webhook.
     */
    public function handle(): void
    {
        $this->forwardToDebugServer();
        $this->writeToLog();
        
        if (empty($this->bot_token)) {
            error_log("FATAL: TELEGRAM_BOT_TOKEN is not set.");
            exit;
        }

        $data = json_decode($this->input, true);

        if (isset($data['callback_query'])) {
            $this->handleCallbackQuery($data['callback_query']);
            return;
        }

        if (isset($data['message'])) {
            $this->handleMessage($data['message']);
            return;
        }

        http_response_code(200); // Acknowledge other types of updates
    }

    private function handleMessage(array $message): void
    {
        $chat_id = $message['chat']['id'];
        $user_id = $message['from']['id'];
        $text = trim($message['text'] ?? '');

        if ($text === '/setup') {
            $this->handleSetupCommand($chat_id, $user_id, $message['from']);
            return;
        }

        if (!$this->model->isAuthorized($user_id, $chat_id)) {
            exit; // Ignore silently
        }
        
        // --- Start of number processing logic (Line-by-Line approach) ---

        // 1. Split the text into lines. This is the most reliable delimiter,
        //    treating each line as a potential single phone number.
        $lines = preg_split('/\r\n|\r|\n/', $text);

        $phoneNumbers = [];
        foreach ($lines as $line) {
            // 2. For each line, remove all non-digit characters.
            //    This correctly handles spaces or any other symbols within a number
            //    e.g., "968 7741-5059" becomes "96877415059".
            $cleanedNumber = preg_replace('/[^0-9]/', '', $line);

            // 3. Only process if the cleaned line results in a non-empty string
            //    with a plausible length for a phone number.
            if (!empty($cleanedNumber) && strlen($cleanedNumber) >= 9 && strlen($cleanedNumber) <= 15) {
                if (!in_array($cleanedNumber, $phoneNumbers)) {
                    $phoneNumbers[] = $cleanedNumber;
                }
            }
        }
        // --- End of number processing logic ---

        if (!empty($phoneNumbers)) {
            $this->handlePhoneNumbers($phoneNumbers, $chat_id);
        } else {
            // $this->sendMessage($chat_id, "أهلاً! لم أجد أي أرقام هواتف في رسالتك. يمكنك إرسال قائمة بأرقام الهواتف للبحث عنها.");
        }
    }

    private function handlePhoneNumbers(array $phoneNumbers, int $chat_id): void
    {
        $foundDrivers = $this->model->findDriversByPhones($phoneNumbers);
        $foundReplies = [];
        $notFoundPhones = [];

        foreach ($phoneNumbers as $phone) {
            if (isset($foundDrivers[$phone])) {
                $driver = $foundDrivers[$phone];
                $lastCall = $this->model->getLastCall($driver['id']);
                
                $callDetails = "  <b>آخر مكالمة:</b> لا توجد مكالمات مسجلة.";
                if ($lastCall) {
                    $callStatus = $lastCall['call_status'] ?? 'غير محدد';
                    $caller = $lastCall['caller_username'] ?? 'غير معروف';
                    $callNotes = !empty($lastCall['notes']) ? htmlspecialchars($lastCall['notes']) : 'لا توجد ملاحظات';
                    
                    $callDetails = "  <b>آخر مكالمة:</b>\n";
                    $callDetails .= "    - <b>الحالة:</b> {$callStatus}\n";
                    $callDetails .= "    - <b>بواسطة:</b> {$caller}\n";
                    $callDetails .= "    - <b>ملاحظات:</b> {$callNotes}";
                }

                $foundReplies[] = "📞 <code>{$phone}</code>\n<b>  حالة السائق:</b> {$driver['main_system_status']}\n{$callDetails}";

            } else {
                $notFoundPhones[] = $phone;
            }
        }

        $finalReply = "<b>--- 🔍 نتائج البحث ---</b>\n\n";
        if (!empty($foundReplies)) {
            $finalReply .= implode("\n\n", $foundReplies);
        } else {
            $finalReply .= "لم يتم العثور على أي من الأرقام المرسلة في قاعدة البيانات.";
        }

        $buttons = null;
        if (!empty($notFoundPhones)) {
            $finalReply .= "\n\n-------------------------\n";
            $finalReply .= "⚠️ <b>أرقام غير موجودة:</b>\n" . implode(', ', array_map(fn($p) => "<code>$p</code>", $notFoundPhones));
            
            // Chunk the not-found phones into groups of 4 to avoid hitting Telegram's
            // 64-byte callback_data limit.
            $chunks = array_chunk($notFoundPhones, 4);
            $buttonRows = [];
            $batchNum = 1;
            foreach ($chunks as $chunk) {
                $callback_data = 'add_drivers|' . implode('|', $chunk);
                $buttonText = (count($chunks) > 1) 
                    ? '➕ إضافة دفعة ' . $batchNum++ . ' (' . count($chunk) . ' أرقام)'
                    : '➕ إضافة الأرقام الجديدة (' . count($chunk) . ' أرقام)';
                    
                // Each button is in its own row for clarity.
                $buttonRows[] = [['text' => $buttonText, 'callback_data' => $callback_data]];
            }

            if (!empty($buttonRows)) {
                $buttons = ['inline_keyboard' => $buttonRows];
            }
        }

        $this->sendMessage($chat_id, $finalReply, $buttons);
    }
    
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $callback_data = $callbackQuery['data'];
        $chat_id = $callbackQuery['message']['chat']['id'];
        $telegram_user_id = $callbackQuery['from']['id'];

        // Acknowledge the button press immediately
        $this->answerCallbackQuery($callbackQuery['id']);

        list($command, $data) = explode('|', $callback_data, 2);

        if ($command === 'add_drivers') {
            $phonesToAdd = explode('|', $data);
            $systemUserId = $this->model->getSystemUserId($telegram_user_id);

            if ($systemUserId) {
                $addedCount = $this->model->addDrivers($phonesToAdd, $systemUserId);
                if ($addedCount > 0) {
                    $this->sendMessage($chat_id, "✅ تمت إضافة {$addedCount} أرقام جديدة بنجاح:\n" . implode(', ', $phonesToAdd));
                }
            } else {
                $this->sendMessage($chat_id, "❌ خطأ: حسابك في تليجرام غير مرتبط بحساب أدمن في النظام.");
            }
        }
    }

    /**
     * Handles the public /setup command.
     */
    private function handleSetupCommand(int $chat_id, int $user_id, array $from): void
    {
        $username = $from['username'] ?? 'N/A';
        $full_name = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
        $reply = "<b>👋 Setup Info Received</b>\n\n<b><u>👤 User Details:</u></b>\n<b>ID:</b> <code>{$user_id}</code>\n<b>Full Name:</b> {$full_name}\n<b>Username:</b> @{$username}\n\n<b><u>💬 Chat Details:</u></b>\n<b>ID:</b> <code>{$chat_id}</code>\n";
        $this->sendMessage($chat_id, $reply);
    }
    
    
    // --- Helper Functions ---
    private function sendMessage(int $chat_id, string $text, array $reply_markup = null): void
    {
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
        $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($reply_markup) {
            $data['reply_markup'] = json_encode($reply_markup);
        }
        $options = ['http' => ['header'  => "Content-type: application/x-www-form-urlencoded\r\n", 'method'  => 'POST', 'content' => http_build_query($data), 'ignore_errors' => true]];
        @file_get_contents($url, false, stream_context_create($options));
    }
    
    private function forwardToDebugServer(): void
    {
        if (empty($this->input)) return;
        $options = [
            'http' => [
                'method' => 'POST', 'header' => "Content-Type: application/json\r\n",
                'content' => $this->input, 'timeout' => 2, 'ignore_errors' => true,
            ],
        ];
        @file_get_contents('http://localhost:5000', false, stream_context_create($options));
    }

    private function writeToLog(): void
    {
        if (empty($this->input)) return;
        $logFile = __DIR__ . '/../../../public/telegram/telegram_webhook.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp]\n{$this->input}\n\n", FILE_APPEND);
    }

    private function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        $url = "https://api.telegram.org/bot{$this->bot_token}/answerCallbackQuery";
        $data = ['callback_query_id' => $callbackQueryId, 'text' => $text];
        $options = ['http' => ['ignore_errors' => true]];
        @file_get_contents($url . '?' . http_build_query($data), false, stream_context_create($options));
    }
}