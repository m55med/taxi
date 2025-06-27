<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 py-8" x-data="createTicketForm()">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Create New Ticket</h1>

        <!-- Flash Messages -->
        <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

        <form @submit.prevent="submitForm" id="createTicketForm" class="space-y-6">
            <!-- Ticket Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="ticket_number" class="block text-sm font-medium text-gray-700">Ticket Number</label>
                    <div class="relative">
                        <input type="text" id="ticket_number" x-model="formData.ticket_number" @blur="checkTicketExists" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <div id="ticket-exists-warning" class="hidden mt-1 text-sm text-blue-600">
                            Ticket already exists. <a href="#" id="view-ticket-link" target="_blank" class="font-bold underline">View Details</a>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform</label>
                    <select id="platform_id" x-model="formData.platform_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Platform</option>
                        <?php foreach ($data['platforms'] as $platform) : ?>
                            <option value="<?= htmlspecialchars($platform['id']) ?>"><?= htmlspecialchars($platform['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                    <input type="text" id="phone" x-model="formData.phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                 <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                    <select id="country_id" x-model="formData.country_id" @change="countryChanged" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Country</option>
                        <?php foreach ($data['countries'] as $country) : ?>
                            <option value="<?= htmlspecialchars($country['id']) ?>"><?= htmlspecialchars($country['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pt-6">
                    <label for="is_vip" class="flex items-center">
                        <input type="checkbox" id="is_vip" x-model="formData.is_vip" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-900">VIP Customer</span>
                    </label>
                </div>
            </div>
            
            <!-- Classification -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category_id" x-model="formData.category_id" @change="fetchSubcategories" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Category</option>
                        <?php foreach ($data['categories'] as $category) : ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategory</label>
                    <select id="subcategory_id" x-model="formData.subcategory_id" @change="fetchCodes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :disabled="subcategories.length === 0">
                        <option value="">Select Subcategory</option>
                        <template x-for="subcategory in subcategories" :key="subcategory.id">
                            <option :value="subcategory.id" x-text="subcategory.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label for="code_id" class="block text-sm font-medium text-gray-700">Code</label>
                    <select id="code_id" x-model="formData.code_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :disabled="codes.length === 0">
                        <option value="">Select Code</option>
                        <template x-for="code in codes" :key="code.id">
                            <option :value="code.id" x-text="code.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Coupons -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Coupons</label>
                <div class="mt-2 p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-2 mb-4">
                        <select x-model="couponInput" class="flex-grow border-gray-300 rounded-md shadow-sm sm:text-sm" :disabled="!formData.country_id || availableCoupons.length === 0">
                            <option value="">Select a coupon...</option>
                            <template x-for="coupon in availableCoupons" :key="coupon.id">
                                <option :value="coupon.id" x-text="`${coupon.code} (Value: ${coupon.value})`"></option>
                            </template>
                        </select>
                        <button type="button" @click="addSelectedCoupon" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50" :disabled="!couponInput">
                            <i class="fas fa-plus mr-1"></i> Add
                        </button>
                    </div>
                     <span x-show="!formData.country_id" class="text-xs text-red-500">Please select a country to see available coupons.</span>
                     <span x-show="formData.country_id && availableCoupons.length === 0" class="text-xs text-gray-500">No available coupons for the selected country.</span>

                    <ul id="coupon-list" class="space-y-2">
                        <template x-for="(coupon, index) in formData.coupons" :key="coupon.id">
                             <li class="flex items-center justify-between bg-gray-50 p-2 rounded-md">
                                <div>
                                    <span class="font-semibold text-gray-800" x-text="coupon.code"></span>
                                    <span class="text-sm text-gray-500 ml-2" x-text="'(Value: ' + coupon.value + ')'"></span>
                                </div>
                                <div class="flex items-center">
                                    <button @click.prevent="copyToClipboard(coupon.code)" type="button" class="text-gray-400 hover:text-gray-600 mr-2">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button @click.prevent="removeCoupon(index)" type="button" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="notes" x-model="formData.notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const URLROOT = '<?= URLROOT ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= URLROOT ?>/js/create_ticket/main.js?v=<?= time() ?>"></script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 