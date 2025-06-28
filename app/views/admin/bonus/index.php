<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
// Prepare flash message data for Alpine.js
$flashMessage = null;
if (isset($_SESSION['bonus_message'])) {
    $flashMessage = [
        'message' => $_SESSION['bonus_message'],
        // The type is set in the controller, e.g., flash('bonus_message', 'My message', 'error')
        'type' => $_SESSION['bonus_message_type'] ?? 'success'
    ];
    unset($_SESSION['bonus_message']);
    unset($_SESSION['bonus_message_type']);
}
?>

<div class="container mx-auto px-4 py-8" x-data="bonusPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Grant Monthly Bonus</h1>
        <?php if ($data['is_admin']) : ?>
            <a href="<?= URLROOT ?>/admin/bonus/settings" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                Bonus Settings
            </a>
        <?php endif; ?>
    </div>

    <?php // flash('bonus_message'); // This is now handled by the Alpine.js toast component ?>

    <!-- Delete Confirmation Modal -->
    <div x-show="modal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @keydown.escape.window="modal.show = false" style="display: none;">
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

    <!-- Flash Message Toast -->
    <div class="fixed top-20 right-5 z-[100]">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             @click.away="toast.show = false"
             class="p-4 rounded-lg shadow-lg text-white font-semibold flex items-center space-x-3"
             :class="{ 'bg-green-500': toast.type === 'success', 'bg-red-500': toast.type === 'error' }"
             style="display: none;">
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Grant Bonus Form Section -->
        <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md h-fit">
             <h2 class="text-xl font-bold mb-4 border-b pb-2">Grant Bonus Form</h2>
            <form action="<?= URLROOT ?>/admin/bonus/grant" method="POST">
                <input type="hidden" name="bonus_percent" x-model="bonusPercent">

                <div class="space-y-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Employee:</label>
                        <select id="user_id" name="user_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($data['users'] as $user) : ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="bonus_month" class="block text-sm font-medium text-gray-700">Bonus Month:</label>
                        <input type="month" id="bonus_month" name="bonus_month" value="<?= date('Y-m') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bonus Percentage (%):</label>
                        <?php if ($data['is_admin']) : ?>
                            <input type="number" step="0.01" x-model="bonusPercent"
                                   placeholder="Min: <?= $data['settings']['min_bonus_percent'] ?? 0 ?>%, Max: <?= $data['settings']['max_bonus_percent'] ?? 100 ?>%"
                                   min="<?= $data['settings']['min_bonus_percent'] ?? 0 ?>"
                                   max="<?= $data['settings']['max_bonus_percent'] ?? 100 ?>"
                                   class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm"
                                   required>
                        <?php else : ?>
                            <div class="flex space-x-2 mt-1">
                                <?php
                                $predefined = [
                                    $data['settings']['predefined_bonus_1'] ?? 5,
                                    $data['settings']['predefined_bonus_2'] ?? 10,
                                    $data['settings']['predefined_bonus_3'] ?? 15
                                ];
                                foreach ($predefined as $value) :
                                    if ($value > 0) : // Only show buttons for valid percentages
                                ?>
                                    <button type="button" @click="setBonus(<?= $value ?>)"
                                            :class="{ 'bg-blue-600 text-white': bonusPercent == <?= $value ?>, 'bg-gray-200 hover:bg-gray-300 text-gray-800': bonusPercent != <?= $value ?> }"
                                            class="font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                                        <?= $value ?>%
                                    </button>
                                <?php endif; endforeach; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-2" x-show="bonusPercent">Selected: <span x-text="bonusPercent + '%'"></span></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <button type="submit" class="mt-4 w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                        Grant Bonus
                    </button>
                </div>
            </form>
        </div>

        <!-- Granted Bonuses List Section -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">Granted Bonus History</h2>
            <div class="max-h-[600px] overflow-y-auto border rounded-md">
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
                        <?php if (empty($data['bonuses'])) : ?>
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500">No bonuses have been granted yet.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data['bonuses'] as $bonus) : ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900"><?= htmlspecialchars($bonus['username']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= $bonus['bonus_year'] . '-' . str_pad($bonus['bonus_month'], 2, '0', STR_PAD_LEFT) ?></td>
                                    <td class="px-4 py-2 text-sm text-green-600 font-bold"><?= htmlspecialchars($bonus['bonus_percent']) ?>%</td>
                                    <td class="px-4 py-2 text-sm text-gray-600" title="<?= htmlspecialchars($bonus['reason']) ?>"><?= htmlspecialchars(mb_strimwidth($bonus['reason'], 0, 20, "...")) ?: 'N/A' ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-500"><?= htmlspecialchars($bonus['granter_name'] ?: 'System') ?></td>
                                    <td class="px-4 py-2 text-sm">
                                         <?php if ($data['is_admin']) : // Only admins can delete ?>
                                            <button @click="openModal(<?= $bonus['id'] ?>, '<?= htmlspecialchars($bonus['username']) ?>')" class="text-red-600 hover:text-red-900 transition-colors" title="Delete Bonus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
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

<script>
function bonusPage(flashMessage) {
    return {
        bonusPercent: '',
        modal: {
            show: false,
            deleteUrl: '',
            username: ''
        },
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        setBonus(value) {
            this.bonusPercent = value;
        },
        openModal(id, username) {
            this.modal.deleteUrl = `<?= URLROOT ?>/admin/bonus/delete/${id}`;
            this.modal.username = username;
            this.modal.show = true;
        },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 5000); // Hide after 5 seconds
        },
        init() {
            // If a flash message was passed from PHP, show it
            if (flashMessage && flashMessage.message) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        }
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>