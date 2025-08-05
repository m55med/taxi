<?php
$flashMessage = null;
if (isset($_SESSION['coupon_message'])) {
    $flashMessage = [
        'message' => $_SESSION['coupon_message'],
        'type' => $_SESSION['coupon_message_type'] ?? 'success'
    ];
    unset($_SESSION['coupon_message'], $_SESSION['coupon_message_type']);
}
?>
   
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<!-- Flash Message Toast -->
<div class="toast-container fixed top-5 right-5">
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

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($page_main_title); ?></h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">Total Coupons</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-green-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">Used Coupons</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['used']) ?></p>
        </div>
        <div class="bg-yellow-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">Available Coupons</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['unused']) ?></p>
        </div>
    </div>
    
    <!-- Collapsible Add Coupon Section -->
    <div class="mb-8 bg-white rounded-lg shadow-md border border-gray-200" x-data="{ open: false }">
        <div class="p-5 cursor-pointer flex justify-between items-center" @click="open = !open">
            <h2 class="text-xl font-semibold text-gray-700">Add New Coupons</h2>
            <i class="fas fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
        </div>
        <div x-show="open" x-collapse x-cloak>
            <form action="<?= URLROOT ?>/admin/coupons" method="POST" class="p-5 border-t">
                <input type="hidden" name="action" value="add_bulk">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Codes Textarea -->
                    <div class="md:col-span-3">
                        <label for="codes" class="block mb-2 text-sm font-medium text-gray-900">Coupon Codes</label>
                        <textarea name="codes" id="codes" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Place each coupon on a new line..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">You can paste a list of codes separated by a new line, space, or comma.</p>
                    </div>
                    <!-- Value -->
                    <div>
                        <label for="value" class="block mb-2 text-sm font-medium text-gray-900">Coupon Value</label>
                        <input type="number" step="0.01" name="value" id="value" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                    </div>
                    <!-- Country -->
                    <div>
                        <label for="country_id" class="block mb-2 text-sm font-medium text-gray-900">Country (Optional)</label>
                        <select name="country_id" id="country_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Submit -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <i class="fas fa-plus mr-2"></i>Add Coupons
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons List Section -->
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Coupons List</h2>

        <!-- Filters & Actions -->
        <div class="mb-6">
            <!-- Filter Form -->
            <form action="" method="GET" class="flex flex-wrap items-center gap-4 mb-4">
                 <div class="flex-grow">
                    <label for="search" class="sr-only">Search Coupon</label>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search by code..." class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-grow">
                     <label for="filter_country_id" class="sr-only">Country</label>
                    <select name="country_id" id="filter_country_id" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Countries</option>
                         <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>" <?= (isset($filters['country_id']) && $filters['country_id'] == $country['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-grow">
                    <label for="is_used" class="sr-only">Usage Status</label>
                    <select name="is_used" id="is_used" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="0" <?= (isset($filters['is_used']) && $filters['is_used'] === '0') ? 'selected' : '' ?>>Unused</option>
                        <option value="1" <?= (isset($filters['is_used']) && $filters['is_used'] === '1') ? 'selected' : '' ?>>Used</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                     <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Filter</button>
                     <a href="<?= URLROOT ?>/admin/coupons" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300 text-sm font-medium">Clear</a>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4">
                <!-- Bulk Actions Dropdown -->
                <div class="relative" x-show="selectedCoupons.length > 0" x-cloak>
                    <button @click="actionOpen = !actionOpen" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Actions <span x-text="`(${selectedCoupons.length})`" class="ml-1"></span>
                        <i class="fas fa-chevron-down -mr-1 ml-2 h-5 w-5 transition-transform" :class="{'rotate-180': actionOpen}"></i>
                    </button>
                    <div x-show="actionOpen" @click.away="actionOpen = false" x-collapse class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                        <div class="py-1">
                            <button @click="confirmBulkDelete()" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-trash text-red-500 w-5 text-center mr-2"></i> Delete Selected
                            </button>
                            <button @click="copySelected()" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-copy text-blue-500 w-5 text-center mr-2"></i> Copy Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Button -->
                <div>
                    <button @click="exportModal.open = true" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Table Form for Bulk Actions -->
        <form action="<?= URLROOT ?>/admin/coupons" method="POST" id="bulkActionForm">
            <input type="hidden" name="action" id="bulkActionInput" value="">
            <template x-for="couponId in selectedCoupons" :key="couponId">
                <input type="hidden" name="coupon_ids[]" :value="couponId">
            </template>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="p-4">
                            <div class="flex items-center">
                                <input type="checkbox" @click="toggleAll($event.target.checked)" :checked="allVisibleUnusedCoupons.length > 0 && selectedCoupons.length === allVisibleUnusedCoupons.length" :disabled="allVisibleUnusedCoupons.length === 0" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3">Code</th>
                        <th scope="col" class="px-4 py-3">Value</th>
                        <th scope="col" class="px-4 py-3">Country</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3">Created Date</th>
                        <th scope="col" class="px-4 py-3">Usage Info</th>
                        <th scope="col" class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="8" class="px-6 py-4 text-center">No coupons match the criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $coupon): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="w-4 p-4">
                                <?php if (!$coupon['is_used']): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" :value="<?= $coupon['id'] ?>" x-model="selectedCoupons" data-code="<?= htmlspecialchars($coupon['code']) ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 font-mono">
                                <span class="mr-2"><?= htmlspecialchars($coupon['code']) ?></span>
                                <button type="button" @click="copyToClipboard('<?= htmlspecialchars($coupon['code']) ?>')" class="text-gray-400 hover:text-blue-600"><i class="far fa-copy"></i></button>
                            </td>
                            <td class="px-4 py-4 font-semibold"><?= htmlspecialchars($coupon['value']) ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars($coupon['country_name'] ?? 'N/A') ?></td>
                            <td class="px-4 py-4">
                                <?php if ($coupon['is_used']): ?>
                                    <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Used</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Unused</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4"><?= date('Y-m-d', strtotime($coupon['created_at'])) ?></td>
                            <td class="px-4 py-4">
                                <?php if ($coupon['is_used']): ?>
                                    <div class="text-xs">
                                        <p><strong>Ticket:</strong> <a href="<?= URLROOT . '/tickets/details/' . $coupon['used_in_ticket'] ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($coupon['ticket_number']) ?></a></p>
                                        <p><strong>By:</strong> <?= htmlspecialchars($coupon['used_by_username']) ?></p>
                                        <p><strong>At:</strong> <?= date('Y-m-d H:i', strtotime($coupon['used_at'])) ?></p>
                                    </div>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4">
                                <?php if (!$coupon['is_used']): ?>
                                <button type="button" @click="openEditModal(<?= htmlspecialchars(json_encode($coupon)) ?>)" class="text-blue-600 hover:text-blue-800" title="Edit Coupon"><i class="fas fa-edit"></i></button>
                                <button type="button" @click="confirmSingleDelete(<?= $coupon['id'] ?>)" class="text-red-600 hover:text-red-800 ml-2" title="Delete Coupon"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
         <?php require_once __DIR__ . '/../../includes/pagination.php'; ?>
        </form>
    </div>
</div>

<!-- Modals Container -->
<div id="modals-container">
    <!-- Edit Modal -->
    <div x-show="editModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="editModal.open = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="editModal.open = false">
            <h3 class="text-lg font-semibold mb-4">Edit Coupon</h3>
            <form @submit.prevent="submitEditForm($event.target)">
                 <input type="hidden" name="action" value="update">
                 <input type="hidden" name="id" :value="editModal.coupon.id">
                
                 <div class="mb-4">
                    <label for="edit_code" class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" id="edit_code" x-model="editModal.coupon.code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                 <div class="mb-4">
                    <label for="edit_value" class="block text-sm font-medium text-gray-700">Value</label>
                    <input type="number" step="0.01" name="value" id="edit_value" x-model="editModal.coupon.value" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="mb-4">
                    <label for="edit_country_id" class="block text-sm font-medium text-gray-700">Country</label>
                     <select name="country_id" id="edit_country_id" x-model="editModal.coupon.country_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Countries</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                        <?php endforeach; ?>
                     </select>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="editModal.open = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Modal -->
    <div x-show="exportModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="exportModal.open = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="exportModal.open = false">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-800">Export Coupons</h3>
                <button @click="exportModal.open = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-6">Data will be exported based on the currently applied filters.</p>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Choose export format:</label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" @click="exportModal.type = 'excel'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportModal.type === 'excel', 'bg-white hover:bg-gray-50 border-gray-300': exportModal.type !== 'excel' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                        <i class="fas fa-file-excel text-3xl mb-2" :class="exportModal.type === 'excel' ? 'text-white' : 'text-green-500'"></i>
                        <span class="font-semibold text-sm">Excel</span>
                    </button>
                    <button type="button" @click="exportModal.type = 'txt'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportModal.type === 'txt', 'bg-white hover:bg-gray-50 border-gray-300': exportModal.type !== 'txt' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                        <i class="fas fa-file-alt text-3xl mb-2" :class="exportModal.type === 'txt' ? 'text-white' : 'text-gray-500'"></i>
                        <span class="font-semibold text-sm">Text</span>
                    </button>
                     <button type="button" @click="exportModal.type = 'json'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportModal.type === 'json', 'bg-white hover:bg-gray-50 border-gray-300': exportModal.type !== 'json' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                        <i class="fas fa-file-code text-3xl mb-2" :class="exportModal.type === 'json' ? 'text-white' : 'text-blue-500'"></i>
                        <span class="font-semibold text-sm">JSON</span>
                    </button>
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-4 border-t">
                <button type="button" @click="exportModal.open = false" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-semibold text-sm">Cancel</button>
                <button type="button" @click="submitExport()" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-sm flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export Now
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="deleteModal.open = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="deleteModal.open = false">
             <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
            <p class="mb-6" x-text="deleteModal.message"></p>
            <div class="flex justify-end space-x-4">
                <button @click="deleteModal.open = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancel</button>
                <button @click="processDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
function couponsPage(flashMessage) {
    return {
        toast: { show: false, message: '', type: 'success' },
        editModal: { open: false, coupon: {} },
        exportModal: { open: false, type: 'excel' },
        deleteModal: { open: false, message: '', action: null },
        selectedCoupons: [],
        actionOpen: false,

        get allVisibleUnusedCoupons() { return <?= json_encode(array_column(array_filter($coupons, fn($c) => !$c['is_used']), 'id')) ?>; },
        
        init() {
            if (flashMessage) {
                this.showToast(flashMessage.message, flashMessage.type);
            }
        },
        
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 4000);
        },

        toggleAll(checked) {
            this.selectedCoupons = checked ? this.allVisibleUnusedCoupons : [];
        },

        openEditModal(coupon) {
            this.editModal.coupon = { ...coupon };
            this.editModal.open = true;
        },

        submitEditForm(formElement) {
            // We can use FormData for simpler submission
            const formData = new FormData(formElement);
            fetch('<?= URLROOT ?>/admin/coupons', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Just reload for simplicity, or we could update the table row
                    window.location.reload(); 
                } else {
                    this.showToast(data.message || 'Failed to update coupon.', 'error');
                }
            })
            .catch(() => {
                this.showToast('An error occurred.', 'error');
            })
            .finally(() => {
                this.editModal.open = false;
            });
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('Code copied to clipboard!');
            }).catch(err => {
                this.showToast('Failed to copy code.', 'error');
            });
        },

        copySelected() {
            const codes = this.selectedCoupons.map(id => {
                const checkbox = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                return checkbox ? checkbox.dataset.code : null;
            }).filter(Boolean);
            
            if (codes.length > 0) {
                this.copyToClipboard(codes.join('\n'));
            }
        },

        confirmSingleDelete(id) {
            this.deleteModal.message = 'Are you sure you want to delete this coupon? This action cannot be undone.';
            this.deleteModal.action = () => {
                const form = document.getElementById('bulkActionForm');
                // Clear any existing bulk selections to avoid conflict
                form.querySelectorAll('input[name="coupon_ids[]"]').forEach(el => el.remove());

                // Set action and add the single ID
                form.querySelector('#bulkActionInput').value = 'delete';
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);
                
                form.submit();
            };
            this.deleteModal.open = true;
        },

        confirmBulkDelete() {
            if (this.selectedCoupons.length === 0) {
                this.showToast('Please select at least one coupon.', 'error');
                return;
            }
            this.deleteModal.message = `Are you sure you want to delete the ${this.selectedCoupons.length} selected coupons? This action cannot be undone.`;
            this.deleteModal.action = () => {
                document.getElementById('bulkActionInput').value = 'bulk_delete';
                document.getElementById('bulkActionForm').submit();
            };
            this.deleteModal.open = true;
        },
        
        processDelete() {
            if (this.deleteModal.action) {
                this.deleteModal.action();
            }
            this.deleteModal.open = false;
        },

        submitExport() {
            const url = new URL(window.location.href);
            // Remove existing 'export' param to avoid conflicts if user changes format
            url.searchParams.delete('export');
            url.searchParams.set('export', this.exportModal.type);
            window.location.href = url.toString();
            this.exportModal.open = false;
        }
    }
}
</script>

</body>
</html> 