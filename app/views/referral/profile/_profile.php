<div x-data="{ isOnlineOnly: <?php echo (isset($data['agentProfile']['is_online_only']) && $data['agentProfile']['is_online_only']) ? 'true' : 'false'; ?> }">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">تعديل الملف الشخصي</h2>
    
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <form id="agentProfileForm" action="<?php echo URLROOT; ?>/referral/saveAgentProfile" method="POST" class="space-y-8">
        
        <!-- Basic Information Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-6">المعلومات الأساسية</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">الدولة / المحافظة</label>
                    <input type="text" name="state" id="state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($data['agentProfile']['state'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف للتواصل</label>
                    <input type="text" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($data['agentProfile']['phone'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <!-- Location Settings Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-6">
                <h3 class="text-lg font-semibold text-gray-800">إعدادات الموقع</h3>
                <div class="flex items-center">
                    <input type="hidden" name="is_online_only" value="0">
                    <input id="is_online_only" name="is_online_only" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" x-model="isOnlineOnly" <?php echo !empty($data['agentProfile']['is_online_only']) ? 'checked' : ''; ?>>
                    <label for="is_online_only" class="mr-2 text-sm text-gray-900">أعمل عبر الإنترنت فقط</label>
                </div>
            </div>
            <div class="space-y-6" x-show="!isOnlineOnly" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2">
                <div>
                    <label for="google_map_url" class="block text-sm font-medium text-gray-700 mb-1">رابط الموقع على خرائط جوجل (اختياري)</label>
                    <input type="url" name="google_map_url" id="google_map_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://maps.app.goo.gl/..." value="<?php echo htmlspecialchars($data['agentProfile']['map_url'] ?? '') ?>">
                    <p class="mt-2 text-xs text-gray-500">سيتم استخراج الإحداثيات من الرابط عند الحفظ. هذا الحقل سيتجاوز التحديد التلقائي للموقع.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">الموقع الجغرافي (يتم تحديثه تلقائياً)</label>
                    <div id="geo-status" class="mt-2 text-sm text-gray-500 bg-gray-50 p-3 rounded-md border border-gray-200">جاري تحديد موقعك...</div>
                    <input type="hidden" name="latitude" id="latitude" value="<?php echo htmlspecialchars($data['agentProfile']['latitude'] ?? '') ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?php echo htmlspecialchars($data['agentProfile']['longitude'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Working Hours Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-6">أوقات العمل</h3>
            <div class="space-y-4">
                <?php
                $working_hours_data = $data['working_hours'];
                include APPROOT . '/views/referral/profile/_working_hours.php';
                ?>
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-300 transition-colors">
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const geoStatus = document.getElementById('geo-status');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    function updateLocation(lat, lon) {
        if (!latitudeInput || !longitudeInput || !geoStatus) return;
        latitudeInput.value = lat;
        longitudeInput.value = lon;
        const mapLink = `https://www.google.com/maps?q=${lat},${lon}`;
        geoStatus.innerHTML = `تم تحديد موقعك بنجاح. <a href="${mapLink}" target="_blank" class="text-blue-500 hover:underline">عرض على الخريطة</a>`;
    }

    function showExistingLocation() {
        if (!latitudeInput || !longitudeInput) return;
        const lat = latitudeInput.value;
        const lon = longitudeInput.value;
        if (lat && lon) {
            updateLocation(lat, lon);
        } else if (geoStatus) {
             geoStatus.textContent = "لم يتم تحديد موقع بعد. سيتم تحديده تلقائياً عند الحفظ.";
        }
    }
    
    // Only run geo-logic if the profile form is on the page
    if (document.getElementById('agentProfileForm')) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    updateLocation(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    let errorMessage = 'لا يمكن الحصول على الموقع حالياً. ';
                    if (geoStatus) {
                        switch(error.code) {
                            case error.PERMISSION_DENIED: errorMessage += "لقد رفضت الإذن بتحديد الموقع."; break;
                            case error.POSITION_UNAVAILABLE: errorMessage += "معلومات الموقع غير متاحة."; break;
                            case error.TIMEOUT: errorMessage += "انتهت مهلة طلب تحديد الموقع."; break;
                            default: errorMessage += "حدث خطأ غير معروف."; break;
                        }
                        geoStatus.textContent = errorMessage;
                    }
                    showExistingLocation();
                }
            );
        } else {
            if (geoStatus) geoStatus.textContent = "المتصفح لا يدعم تحديد الموقع الجغرافي.";
            showExistingLocation();
        }
    }
});
</script> 