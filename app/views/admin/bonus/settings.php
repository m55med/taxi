<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
// Prepare flash message data for Alpine.js
$flashMessage = null;
if (isset($_SESSION['bonus_settings_message'])) {
    $flashMessage = [
        'message' => $_SESSION['bonus_settings_message'],
        'type' => $_SESSION['bonus_settings_message_type'] ?? 'success'
    ];
    unset($_SESSION['bonus_settings_message']);
    unset($_SESSION['bonus_settings_message_type']);
}
?>

<div class="container mx-auto px-4 py-8" x-data="settingsPage(<?= htmlspecialchars(json_encode($flashMessage), ENT_QUOTES) ?>)" x-init="init()">
    
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

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Bonus System Settings</h1>
        <a href="<?= URLROOT ?>/admin/bonus" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
            Back to Bonus Granting
        </a>
    </div>

    <?php // flash('bonus_settings_message'); // Handled by Alpine.js toast ?>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <form action="<?= URLROOT ?>/admin/bonus/updateSettings" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Admin Settings -->
                <div class="col-span-1 md:col-span-2 border-b pb-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Admin Controls</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="min_bonus_percent" class="block text-gray-700 text-sm font-bold mb-2">Minimum Bonus %</label>
                            <input type="number" step="0.01" id="min_bonus_percent" name="min_bonus_percent" value="<?= htmlspecialchars($data['settings']['min_bonus_percent'] ?? 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div>
                            <label for="max_bonus_percent" class="block text-gray-700 text-sm font-bold mb-2">Maximum Bonus %</label>
                            <input type="number" step="0.01" id="max_bonus_percent" name="max_bonus_percent" value="<?= htmlspecialchars($data['settings']['max_bonus_percent'] ?? 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                    </div>
                </div>

                <!-- Predefined Values for Other Roles -->
                <div class="col-span-1 md:col-span-2">
                     <h2 class="text-xl font-semibold text-gray-700 mb-4">Predefined Bonus Buttons (for Team Leaders, etc.)</h2>
                     <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="predefined_bonus_1" class="block text-gray-700 text-sm font-bold mb-2">Predefined Value 1 (%)</label>
                            <input type="number" step="0.01" id="predefined_bonus_1" name="predefined_bonus_1" value="<?= htmlspecialchars($data['settings']['predefined_bonus_1'] ?? 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                         <div>
                            <label for="predefined_bonus_2" class="block text-gray-700 text-sm font-bold mb-2">Predefined Value 2 (%)</label>
                            <input type="number" step="0.01" id="predefined_bonus_2" name="predefined_bonus_2" value="<?= htmlspecialchars($data['settings']['predefined_bonus_2'] ?? 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                         <div>
                            <label for="predefined_bonus_3" class="block text-gray-700 text-sm font-bold mb-2">Predefined Value 3 (%)</label>
                            <input type="number" step="0.01" id="predefined_bonus_3" name="predefined_bonus_3" value="<?= htmlspecialchars($data['settings']['predefined_bonus_3'] ?? 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                     </div>
                </div>

            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300 ease-in-out">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function settingsPage(flashMessage) {
    return {
        toast: {
            show: false,
            message: '',
            type: 'success'
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
            if (flashMessage && flashMessage.message) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        }
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 