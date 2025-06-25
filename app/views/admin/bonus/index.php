<?php
$flashMessage = null;
if (isset($_SESSION['bonus_message'])) {
    // Note: The 'type' is now hardcoded to what we set in the controller
    // e.g., flash('bonus_message', 'My message', 'error')
    $flashMessage = [
        'message' => $_SESSION['bonus_message'],
        'type' => $_SESSION['bonus_message_type'] ?? 'success' 
    ];
    unset($_SESSION['bonus_message']);
    unset($_SESSION['bonus_message_type']);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Monthly Bonuses</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .toast-container { z-index: 1000; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100" x-data="bonusPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()" x-cloak>
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <!-- Flash Message Toast -->
    <div class="toast-container fixed top-5 right-5 flex items-center justify-center">
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="p-4 rounded-lg shadow-lg text-white font-semibold"
             :class="{ 'bg-green-500': toast.type === 'success', 'bg-red-500': toast.type === 'error' }">
            <p x-text="toast.message"></p>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="modal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @keydown.escape.window="modal.show = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="modal.show = false">
            <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
            <p class="mb-6">Are you sure you want to delete the bonus for <strong x-text="modal.username"></strong>? This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button @click="modal.show = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancel</button>
                <form :action="modal.deleteUrl" method="POST">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Monthly Employee Bonuses</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Grant Bonus Form -->
            <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Grant Bonus Form</h2>
                <form action="<?= BASE_PATH ?>/admin/bonus/grant" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="user_id" id="user_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select an employee...</option>
                                <?php foreach($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bonus_month" class="block text-sm font-medium text-gray-700">Bonus Month</label>
                            <input type="month" name="bonus_month" id="bonus_month" required value="<?= date('Y-m') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="bonus_percent" class="block text-sm font-medium text-gray-700">Bonus Percentage (%)</label>
                            <input type="number" step="0.01" name="bonus_percent" id="bonus_percent" required placeholder="e.g., 5.5" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                            <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Grant Bonus
                    </button>
                </form>
            </div>

            <!-- Granted Bonuses List -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Granted Bonus History</h2>
                <div class="max-h-[500px] overflow-y-auto border rounded-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Granted By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             <?php if (empty($data['bonuses'])): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No bonuses have been granted yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['bonuses'] as $bonus): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900"><?= htmlspecialchars($bonus['username']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-700"><?= $bonus['bonus_year'] . '-' . str_pad($bonus['bonus_month'], 2, '0', STR_PAD_LEFT) ?></td>
                                        <td class="px-4 py-2 text-sm text-green-600 font-bold"><?= $bonus['bonus_percent'] ?>%</td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($bonus['reason'] ?: 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-500"><?= htmlspecialchars($bonus['granter_name'] ?: 'System') ?></td>
                                        <td class="px-4 py-2 text-sm">
                                            <button @click="openModal(<?= $bonus['id'] ?>, '<?= htmlspecialchars($bonus['username']) ?>')" class="text-red-600 hover:text-red-900 transition-colors" title="Delete Bonus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
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
    
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
    function bonusPage(flashMessage) {
        return {
            toast: {
                show: false,
                message: '',
                type: 'success',
            },
            modal: {
                show: false,
                deleteUrl: '',
                username: ''
            },
            openModal(id, username) {
                this.modal.deleteUrl = `<?= BASE_PATH ?>/admin/bonus/delete/${id}`;
                this.modal.username = username;
                this.modal.show = true;
            },
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 5000);
            },
            init() {
                if (flashMessage) {
                    this.showToast(flashMessage.message, flashMessage.type);
                }
            }
        }
    }
    </script>
</body>
</html> 