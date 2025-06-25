<?php
// Prepare flash message
$flashMessage = null;
if (isset($_SESSION['telegram_message'])) {
    $flashMessage = [
        'message' => $_SESSION['telegram_message'],
        'type' => $_SESSION['telegram_message_type'] ?? 'success'
    ];
    unset($_SESSION['telegram_message'], $_SESSION['telegram_message_type']);
}
?>
<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<div x-data="telegramSettingsPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.500ms class="fixed top-5 right-5 z-50">
        <div class="px-6 py-3 rounded-lg shadow-md flex items-center text-white" :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
            <i class="mr-3" :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Telegram Bot Settings</h1>
            <i class="fab fa-telegram-plane text-5xl text-blue-500"></i>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Form to Add New Link -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Link</h2>
                    <form action="<?= BASE_PATH ?>/admin/telegram_settings/add" method="POST">
                        <div class="mb-4">
                            <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">System User:</label>
                            <select name="user_id" id="user_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Select a user --</option>
                                <?php foreach ($data['admin_users'] as $admin): ?>
                                    <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="telegram_user_id" class="block text-gray-700 text-sm font-bold mb-2">Telegram User ID:</label>
                            <input type="number" name="telegram_user_id" id="telegram_user_id" placeholder="e.g., 123456789" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-6">
                            <label for="telegram_chat_id" class="block text-gray-700 text-sm font-bold mb-2">Telegram Chat ID:</label>
                            <input type="number" name="telegram_chat_id" id="telegram_chat_id" placeholder="e.g., -100123456789" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <i class="fas fa-plus mr-2"></i> Add
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table of Existing Links -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Current Links</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">System User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telegram User ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chat ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['current_settings'])): ?>
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No links saved currently.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data['current_settings'] as $setting): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><i class="fas fa-user text-gray-400 mr-2"></i><?= htmlspecialchars($setting['username']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><code><?= htmlspecialchars($setting['telegram_user_id']) ?></code></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><code><?= htmlspecialchars($setting['telegram_chat_id']) ?></code></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="<?= BASE_PATH ?>/admin/telegram_settings/delete/<?= $setting['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this link?');">
                                                <button type="submit" class="text-red-600 hover:text-red-800 focus:outline-none">
                                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function telegramSettingsPage(flashMessage) {
    return {
        toast: { show: false, message: '', type: 'success' },
        init() {
            if (flashMessage) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 4000);
        }
    }
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html> 