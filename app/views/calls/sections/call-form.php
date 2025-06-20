<?php
defined('BASE_PATH') or define('BASE_PATH', '');
?>

<?php if (isset($driver)): ?>
<!-- Call Form -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">تسجيل مكالمة جديدة</h3>
    <form id="callForm" class="space-y-4">
        <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">نتيجة المكالمة <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap justify-center gap-6 mb-6">
                <button type="button" data-status="answered"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-phone-alt text-xl transform -rotate-90"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">تم الرد</span>
                </button>
                
                <button type="button" data-status="no_answer"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-phone-slash text-xl transform -rotate-90"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">لم يرد</span>
                </button>
                
                <button type="button" data-status="busy"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-phone-volume text-xl transform -rotate-90"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">مشغول</span>
                </button>
                
                <button type="button" data-status="not_available"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-user-clock text-xl"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">غير متاح</span>
                </button>
                
                <button type="button" data-status="wrong_number"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">رقم خاطئ</span>
                </button>

                <button type="button" data-status="rescheduled"
                    class="call-status-btn group relative flex flex-col items-center">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full border-2 transition-all duration-200 ease-in-out bg-white border-gray-300 text-gray-700">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-600">جدولة</span>
                </button>
            </div>
            <input type="hidden" name="call_status" id="selectedCallStatus">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                ملاحظات المكالمة
            </label>
            <textarea name="notes" rows="3" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="أدخل نتيجة المكالمة وملاحظاتك هنا... (اختياري)"></textarea>
        </div>

        <div class="hidden" id="nextCallSection">
            <label class="block text-sm font-medium text-gray-700 mb-2">موعد المكالمة القادمة</label>
            <input type="datetime-local" name="next_call_at" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div class="pt-4 border-t border-gray-200 flex justify-between items-center gap-2">
            <button type="submit" 
                class="flex-grow bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center font-semibold">
                <i class="fas fa-save ml-2"></i>
                <span>حفظ المكالمة</span>
            </button>
            
            <div class="flex-shrink-0">
                <!-- Skip Button -->
                <a href="<?= BASE_PATH ?>/calls/skip/<?= $driver['id'] ?? '' ?>" title="تخطي"
                   class="skip-btn inline-flex items-center justify-center h-10 w-10 rounded-full text-gray-500 hover:text-white hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-forward"></i>
                </a>
            </div>

            <button type="button" onclick="showTransferModal()" title="تحويل السائق"
                class="flex-shrink-0 bg-gray-200 text-gray-700 w-12 h-12 flex items-center justify-center rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <i class="fas fa-exchange-alt"></i>
            </button>
        </div>
    </form>
</div>
<?php endif; ?> 