<!-- Section 1: Core Ticket Details -->
<div class="form-section">
    <h2 class="section-title">1. Core Ticket Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
        <!-- Ticket Number -->
        <div class="md:col-span-2">
            <label for="ticket_number" class="block text-sm font-semibold text-gray-700 mb-2">Ticket Number <span class="text-red-500">*</span></label>
            <div class="flex items-center space-x-2">
                <div class="relative flex-grow">
                    <input type="text" id="ticket_number" name="ticket_number" class="form-input block w-full pl-12" placeholder="Paste or enter ticket number" required>
                    <button type="button" id="paste-ticket-number" class="absolute inset-y-0 left-0 flex items-center justify-center w-12 text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-paste fa-lg"></i>
                    </button>
                </div>
                <div id="view-ticket-container" class="hidden-transition">
                    <a href="#" id="view-ticket-btn" class="btn btn-info whitespace-nowrap"><i class="fas fa-eye mr-2"></i>View Ticket</a>
                </div>
            </div>
            <p id="ticket-exists-error" class="text-green-600 text-sm mt-2 hidden"><i class="fas fa-check-circle mr-1"></i> Ticket number exists. Data has been pre-filled.</p>
        </div>

        <!-- Platform -->
        <div>
            <label for="platform_id" class="block text-sm font-semibold text-gray-700 mb-2">Platform <span class="text-red-500">*</span></label>
            <select id="platform_id" name="platform_id" class="form-select block w-full" required>
                <option value="" disabled selected>Select a platform</option>
                <?php foreach (($data['platforms'] ?? []) as $platform): ?>
                    <option value="<?= $platform['id'] ?>"><?= htmlspecialchars($platform['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Phone Number -->
        <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
            <div class="relative">
                <input type="tel" id="phone" name="phone" class="form-input block w-full pl-12" placeholder="e.g., 05xxxxxxxx">
                <button type="button" id="paste-phone-number" class="absolute inset-y-0 left-0 flex items-center justify-center w-12 text-gray-500 hover:text-indigo-600">
                    <i class="fas fa-paste fa-lg"></i>
                </button>
            </div>
        </div>

        <!-- Country -->
        <div>
            <label for="country_id" class="block text-sm font-semibold text-gray-700 mb-2">Country</label>
            <select id="country_id" name="country_id" class="form-select block w-full">
                <option value="" selected>Select a country</option>
                <?php if (!empty($data['countries'])): ?>
                    <?php foreach ($data['countries'] as $country): ?>
                        <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Is VIP -->
        <div class="flex items-center pt-2">
            <input type="checkbox" id="is_vip" name="is_vip" value="1" class="h-5 w-5 text-indigo-600 border-gray-400 rounded focus:ring-indigo-500">
            <label for="is_vip" class="ml-3 block text-md font-semibold text-gray-800">VIP Customer</label>
        </div>

        <!-- Coupons Section -->
        <div class="md:col-span-2 hidden" id="coupons-section">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Coupons</label>
            
            <div id="coupons-container" class="space-y-3">
                <!-- Dynamic coupons will be added here -->
            </div>

            <button type="button" id="add-coupon-btn"
                class="inline-flex items-center px-4 py-2 mt-3 border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors duration-200 rounded-md text-sm font-medium shadow-sm">
                <i class="fas fa-plus mr-2"></i>Add Coupon
            </button>
        </div>
    </div>
</div> 