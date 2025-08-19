<div x-data="{ isOnlineOnly: <?php echo (isset($agentProfile['is_online_only']) && $agentProfile['is_online_only']) ? 'true' : 'false'; ?> }">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">الملف الشخصي</h2>
        <a href="<?= URLROOT ?>/referral/editProfile/<?= $user->id ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            عرض وتعديل كامل
        </a>
    </div>
    
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <form id="agentProfileForm" action="<?php echo URLROOT; ?>/referral/saveAgentProfile" method="POST" class="space-y-8">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user->id) ?>">

        <!-- Basic Information Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-6">المعلومات الأساسية</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1">الدولة / المحافظة</label>
                    <input type="text" name="state" id="state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($agentProfile['state'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف للتواصل</label>
                    <input type="text" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($agentProfile['phone'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <!-- Location Settings Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-6">
                <h3 class="text-lg font-semibold text-gray-800">إعدادات الموقع</h3>
                <div class="flex items-center">
                    <input type="hidden" name="is_online_only" value="0">
                    <input id="is_online_only" name="is_online_only" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" x-model="isOnlineOnly" <?php echo !empty($agentProfile['is_online_only']) ? 'checked' : ''; ?>>
                    <label for="is_online_only" class="mr-2 text-sm text-gray-900">أعمل عبر الإنترنت فقط</label>
                </div>
            </div>
            <div class="space-y-6" x-show="!isOnlineOnly" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2">
                
                <div>
                    <label for="google_map_url" class="block text-sm font-medium text-gray-700 mb-1">
                        رابط الموقع على خرائط جوجل (الطريقة اليدوية)
                    </label>
                    <input type="url" name="google_map_url" id="google_map_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://maps.app.goo.gl/..." value="<?php echo htmlspecialchars($agentProfile['map_url'] ?? '') ?>">
                    <p class="mt-2 text-xs text-gray-500">
                        الصق رابطًا هنا لتحديد موقعك يدويًا. هذا الحقل له الأولوية على التحديد التلقائي.
                    </p>
                </div>

                <div class="border-t border-gray-200 my-4"></div>

                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">
                        الموقع الجغرافي (تحديد تلقائي)
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" id="getLocationBtn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-4 focus:ring-gray-300 transition-colors text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block ml-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                            تحديد موقعي الحالي
                        </button>
                         <button type="button" id="deleteLocationBtn" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                            حذف الموقع
                        </button>
                    </div>
                    <div id="geo-status" class="mt-3 text-sm text-gray-500 bg-gray-50 p-3 rounded-md border border-gray-200 min-h-[40px]"></div>
                    <input type="hidden" name="latitude" id="latitude" value="<?php echo htmlspecialchars($agentProfile['latitude'] ?? '') ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?php echo htmlspecialchars($agentProfile['longitude'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Working Hours Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-6">أوقات العمل</h3>
            <div class="space-y-4">
                <?php
                $working_hours_data = $working_hours;
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
    const getLocationBtn = document.getElementById('getLocationBtn');
    const deleteLocationBtn = document.getElementById('deleteLocationBtn');
    const googleMapUrlInput = document.getElementById('google_map_url');

    function updateStatus(message, isHtml = false) {
        if (!geoStatus) return;
        if (isHtml) {
            geoStatus.innerHTML = message;
        } else {
            geoStatus.textContent = message;
        }
    }

    function updateLocation(lat, lon) {
        if (!latitudeInput || !longitudeInput) return;
        latitudeInput.value = lat;
        longitudeInput.value = lon;
        const mapLink = `https://www.google.com/maps?q=${lat},${lon}`;
        updateStatus(`تم تحديد موقعك بنجاح. <a href="${mapLink}" target="_blank" class="text-blue-500 hover:underline">عرض على الخريطة</a>`, true);
    }

    function deleteLocation() {
        if (!latitudeInput || !longitudeInput || !googleMapUrlInput) return;
        latitudeInput.value = '';
        longitudeInput.value = '';
        googleMapUrlInput.value = '';
        updateStatus('تم حذف الموقع المحفوظ. يمكنك تحديده مجدداً.');
    }

    function showExistingLocation() {
        if (!latitudeInput || !longitudeInput) return;
        const lat = latitudeInput.value;
        const lon = longitudeInput.value;
        if (lat && lon) {
            updateLocation(lat, lon);
        } else {
            updateStatus("لم يتم تحديد موقع بعد. يمكنك تحديده تلقائياً أو عبر رابط خرائط جوجل.");
        }
    }

    function fetchGeolocation() {
        if (navigator.geolocation) {
            updateStatus('جاري تحديد موقعك...');
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    updateLocation(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    let errorMessage = 'فشل تحديد الموقع. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED: errorMessage += "لقد رفضت الإذن بتحديد الموقع."; break;
                        case error.POSITION_UNAVAILABLE: errorMessage += "معلومات الموقع غير متاحة."; break;
                        case error.TIMEOUT: errorMessage += "انتهت مهلة طلب تحديد الموقع."; break;
                        default: errorMessage += "حدث خطأ غير معروف."; break;
                    }
                    updateStatus(errorMessage);
                    // showExistingLocation(); // Re-show old location on failure
                }
            );
        } else {
            updateStatus("المتصفح لا يدعم تحديد الموقع الجغرافي.");
        }
    }
    
    if (document.getElementById('agentProfileForm')) {
        showExistingLocation(); // Show initial state on page load
        if (getLocationBtn) {
            getLocationBtn.addEventListener('click', fetchGeolocation);
        }
        if (deleteLocationBtn) {
            deleteLocationBtn.addEventListener('click', deleteLocation);
        }
    }
});
</script> 