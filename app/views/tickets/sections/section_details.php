<!-- Section 1: Ticket Details -->
<div class="form-section">
    <h2 class="section-title">1. تفاصيل التذكرة الأساسية</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
        <!-- Ticket Number -->
        <div class="md:col-span-2">
            <label for="ticket_number" class="block text-sm font-semibold text-gray-700 mb-2">رقم التذكرة <span class="text-red-500">*</span></label>
            <div class="flex items-center space-x-2 space-x-reverse">
                <div class="relative flex-grow">
                    <input type="text" id="ticket_number" name="ticket_number" class="form-input block w-full pr-12" placeholder="الصق أو أدخل رقم التذكرة" required>
                    <button type="button" id="paste-ticket-number" class="absolute inset-y-0 right-0 flex items-center justify-center w-12 text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-paste fa-lg"></i>
                    </button>
                </div>
                <div id="view-ticket-container" class="hidden-transition">
                    <a href="#" id="view-ticket-btn" class="btn btn-info whitespace-nowrap"><i class="fas fa-eye mr-2"></i>عرض التذكرة</a>
                </div>
            </div>
            <p id="ticket-exists-error" class="text-green-600 text-sm mt-2 hidden"><i class="fas fa-check-circle mr-1"></i> رقم التذكرة موجود. تم ملء البيانات.</p>
        </div>

        <!-- Platform -->
        <div>
            <label for="platform_id" class="block text-sm font-semibold text-gray-700 mb-2">المنصة <span class="text-red-500">*</span></label>
            <select id="platform_id" name="platform_id" class="form-select block w-full" required>
                <option value="" disabled selected>اختر المنصة</option>
                <?php foreach ($platforms as $platform): ?>
                    <option value="<?= $platform['id'] ?>"><?= htmlspecialchars($platform['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Phone Number -->
        <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">رقم الهاتف</label>
            <div class="relative">
                <input type="tel" id="phone" name="phone" class="form-input block w-full pr-12" placeholder="مثال: 05xxxxxxx">
                <button type="button" id="paste-phone-number" class="absolute inset-y-0 right-0 flex items-center justify-center w-12 text-gray-500 hover:text-indigo-600">
                    <i class="fas fa-paste fa-lg"></i>
                </button>
            </div>
        </div>

        <!-- Country -->
        <div>
            <label for="country_id" class="block text-sm font-semibold text-gray-700 mb-2">الدولة</label>
            <select id="country_id" name="country_id" class="form-select block w-full">
                <option value="" selected>اختر الدولة</option>
                <?php if (!empty($countries)): ?>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Is VIP -->
        <div class="flex items-center pt-2">
            <input type="checkbox" id="is_vip" name="is_vip" value="1" class="h-5 w-5 text-indigo-600 border-gray-400 rounded focus:ring-indigo-500">
            <label for="is_vip" class="mr-3 block text-md font-semibold text-gray-800">عميل مميز (VIP)</label>
        </div>

        <!-- Coupons Section -->
        <div class="md:col-span-2 hidden" id="coupons-section">
             <label class="block text-sm font-semibold text-gray-700 mb-2">الكوبونات</label>
            <div id="coupons-container" class="space-y-3">
                <!-- Dynamic coupons will be added here -->
            </div>
            <button type="button" id="add-coupon-btn" class="btn btn-secondary mt-3 text-sm">
                <i class="fas fa-plus ml-2"></i>إضافة كوبون
            </button>
        </div>
    </div>
</div> 