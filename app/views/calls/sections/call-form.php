<?php
defined('URLROOT') or exit('No direct script access allowed');

// Ensure driver is an object for consistency
$driver = $data['driver'] ?? null;
if (is_array($driver)) {
    $driver = (object) $driver;
}
$driver_id = $driver->id ?? null;

$ticket_categories = $data['ticket_categories'] ?? [];
?>

<?php if ($driver): ?>
    <!-- Call Form -->
    <div>
        <h3 class="text-xl font-semibold mb-6 text-gray-800">Log New Call</h3>
        <form id="callForm" class="space-y-6">
            <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver_id, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Call Outcome -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Call Outcome <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-4">
                    <?php 
                    $statuses = [
                        'answered' => ['icon' => 'fas fa-phone-alt', 'label' => 'Answered', 'border' => 'border-green-500', 'hover' => 'group-hover:text-green-500'],
                        'no_answer' => ['icon' => 'fas fa-phone-slash', 'label' => 'No Answer', 'border' => 'border-red-500', 'hover' => 'group-hover:text-red-500'],
                        'busy' => ['icon' => 'fas fa-phone-volume', 'label' => 'Busy', 'border' => 'border-yellow-500', 'hover' => 'group-hover:text-yellow-500'],
                        'not_available' => ['icon' => 'fas fa-user-clock', 'label' => 'Unavailable', 'border' => 'border-blue-500', 'hover' => 'group-hover:text-blue-500'],
                        'wrong_number' => ['icon' => 'fas fa-exclamation-triangle', 'label' => 'Wrong Number', 'border' => 'border-orange-500', 'hover' => 'group-hover:text-orange-500'],
                        'rescheduled' => ['icon' => 'fas fa-calendar-alt', 'label' => 'Reschedule', 'border' => 'border-purple-500', 'hover' => 'group-hover:text-purple-500'],
                    ];
                    foreach ($statuses as $status => $cfg): ?>
                        <button type="button" 
                                class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200"
                                data-status="<?= $status ?>"
                                data-border="<?= $cfg['border'] ?>">
                            <i class="<?= $cfg['icon'] ?> text-2xl text-gray-400 <?= $cfg['hover'] ?> transition-colors"></i>
                            <span class="mt-2 text-sm font-medium text-gray-600"><?= $cfg['label'] ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="call_status" id="selectedCallStatus">
            </div>

            <!-- Ticket Classification -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg">
                <!-- Category Select -->
                <div>
                    <label for="ticket_category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="ticket_category_id" name="ticket_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a category...</option>
                        <?php foreach ($ticket_categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subcategory Select -->
                <div>
                    <label for="ticket_subcategory_id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                    <select id="ticket_subcategory_id" name="ticket_subcategory_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" disabled>
                        <option value="">Select a category first</option>
                    </select>
                </div>

                <!-- Code Select -->
                <div>
                    <label for="ticket_code_id" class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                    <select id="ticket_code_id" name="ticket_code_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" disabled>
                        <option value="">Select a subcategory first</option>
                    </select>
                </div>
            </div>
            <!-- End Ticket Classification -->

            <!-- Call Notes -->
            <div>
                <label for="callNotes" class="block text-sm font-medium text-gray-700 mb-2">Call Notes</label>
                <textarea id="callNotes" name="notes" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Enter call outcome and your notes here... (optional)"></textarea>
            </div>

            <!-- Next Call Time -->
            <div class="hidden" id="nextCallSection">
                <label for="nextCallTime" class="block text-sm font-medium text-gray-700 mb-2">Next Call Time</label>
                <input id="nextCallTime" type="datetime-local" name="next_call_at"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Actions -->
            <div class="pt-6 border-t border-gray-200 flex items-center gap-4">
                <button type="submit"
                    class="flex-grow bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center font-semibold"
                    disabled>
                    <i class="fas fa-save mr-2"></i>
                    <span>Save Call</span>
                </button>

                <a href="<?= URLROOT ?>/calls/skip/<?= htmlspecialchars($driver_id, ENT_QUOTES, 'UTF-8') ?>"
                    title="Skip Driver"
                    class="skip-btn inline-flex items-center justify-center h-12 w-12 rounded-lg text-gray-500 bg-gray-200 hover:bg-gray-300 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-forward"></i>
                </a>

                <button type="button" onclick="showTransferModal()" title="Transfer Driver"
                    class="flex-shrink-0 bg-gray-200 text-gray-700 w-12 h-12 flex items-center justify-center rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    console.log("✅ call-form.js loaded");

    const CallFormManager = {
        init() {
            this.form = document.getElementById('callForm');
            if (!this.form) return;

            this.driverId = this.form.querySelector('[name="driver_id"]').value;
            this.submitButton = this.form.querySelector('button[type="submit"]');

            this.bindEventListeners();
            this.initializeSelects();
        },

        bindEventListeners() {
            const statusButtons = this.form.querySelectorAll('.call-status-btn');
            statusButtons.forEach(btn => {
                btn.addEventListener('click', () => this.handleStatusClick(btn));
            });
            this.form.addEventListener('submit', (e) => this.submitForm(e));
        },

        handleStatusClick(clickedButton) {
            const status = clickedButton.dataset.status;
            this.form.querySelectorAll('.call-status-btn').forEach(btn => {
                btn.classList.remove('selected-status', 'border-green-500', 'border-red-500', 'border-yellow-500', 'border-blue-500', 'border-orange-500', 'border-purple-500');
                const selectedBorder = clickedButton.dataset.border;
                if (btn === clickedButton) {
                    btn.classList.add('selected-status', selectedBorder);
                }
            });
            this.form.querySelector('#selectedCallStatus').value = status;
            const nextCallSection = document.getElementById('nextCallSection');
            const nextCallInput = document.getElementById('nextCallTime');

            if (['no_answer', 'busy', 'not_available', 'rescheduled'].includes(status)) {
                nextCallSection.classList.remove('hidden');
                if (status !== 'rescheduled') {
                    const hoursMap = { 'no_answer': 1, 'busy': 0.5, 'not_available': 3 };
                    this.setDefaultNextCallTime(hoursMap[status]);
                }
            } else {
                nextCallSection.classList.add('hidden');
                nextCallInput.value = '';
            }
            this.validateForm();
        },

        setDefaultNextCallTime(hours) {
            const nextCallInput = document.getElementById('nextCallTime');
            const now = new Date();
            now.setTime(now.getTime() + (hours * 60 * 60 * 1000));
            nextCallInput.value = now.toISOString().slice(0, 16);
        },

        initializeSelects() {
            const categorySelect = this.form.querySelector('#ticket_category_id');
            const subcategorySelect = this.form.querySelector('#ticket_subcategory_id');
            const codeSelect = this.form.querySelector('#ticket_code_id');

            categorySelect.addEventListener('change', () => {
                this.fetchSubcategories(categorySelect.value);
                this.updateSelectOptions(subcategorySelect, [], 'Select a subcategory...');
                this.updateSelectOptions(codeSelect, [], 'Select a code...');
            });

            subcategorySelect.addEventListener('change', () => {
                this.fetchCodes(subcategorySelect.value);
                this.updateSelectOptions(codeSelect, [], 'Select a code...');
            });
        },

        async fetchSubcategories(categoryId) {
            if (!categoryId) return;
            try {
                const response = await fetch(`${URLROOT}/calls/subcategories/${categoryId}`);
                if (!response.ok) throw new Error('Server error');
                const data = await response.json();
                this.updateSelectOptions(this.form.querySelector('#ticket_subcategory_id'), data, 'Select a subcategory...');
            } catch (error) {
                console.error('Error fetching subcategories:', error);
                showToast('Failed to load subcategories.', 'error');
            }
        },

        async fetchCodes(subcategoryId) {
            if (!subcategoryId) return;
            try {
                const response = await fetch(`${URLROOT}/calls/codes/${subcategoryId}`);
                if (!response.ok) throw new Error('Server error');
                const data = await response.json();
                this.updateSelectOptions(this.form.querySelector('#ticket_code_id'), data, 'Select a code...');
            } catch (error) {
                console.error('Error fetching codes:', error);
                showToast('Failed to load codes.', 'error');
            }
        },

        updateSelectOptions(selectElement, options, placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
            options.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.id;
                opt.textContent = option.name;
                selectElement.appendChild(opt);
            });
            selectElement.disabled = options.length === 0;
        },

        validateForm() {
            const callStatus = this.form.querySelector('#selectedCallStatus').value;
            this.submitButton.disabled = !callStatus;
        },

        async submitForm(e) {
            e.preventDefault();
            this.submitButton.disabled = true;
            this.submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Saving...`;
            const formData = new FormData(this.form);
            try {
                const response = await fetch(`${URLROOT}/calls/record`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Server error');
                if (result.success) {
                    showToast('Call logged successfully. Fetching next driver...', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(result.message || 'An unexpected error occurred.');
                }
            } catch (error) {
                showToast(error.message, 'error');
                this.submitButton.disabled = false;
                this.submitButton.innerHTML = `<i class="fas fa-save mr-2"></i> Save Call`;
            }
        }
    };
    CallFormManager.init();
});
</script>
