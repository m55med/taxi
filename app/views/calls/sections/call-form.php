<?php
defined('BASE_PATH') or exit('No direct script access allowed');
$driver_id = $data['driver']['id'] ?? null;
?>

<?php if (isset($data['driver'])): ?>
<!-- Call Form -->
<div>
    <h3 class="text-xl font-semibold mb-6 text-gray-800">Log New Call</h3>
    <form id="callForm" class="space-y-6">
        <input type="hidden" name="driver_id" value="<?= $driver_id ?>">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">Call Outcome <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-3 gap-4">
                <button type="button" data-status="answered" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-alt text-2xl text-gray-400 group-hover:text-green-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Answered</span>
                </button>
                <button type="button" data-status="no_answer" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-slash text-2xl text-gray-400 group-hover:text-red-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">No Answer</span>
                </button>
                <button type="button" data-status="busy" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-volume text-2xl text-gray-400 group-hover:text-yellow-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Busy</span>
                </button>
                <button type="button" data-status="not_available" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-user-clock text-2xl text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Unavailable</span>
                </button>
                <button type="button" data-status="wrong_number" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-exclamation-triangle text-2xl text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Wrong Number</span>
                </button>
                <button type="button" data-status="rescheduled" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-calendar-alt text-2xl text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Reschedule</span>
                </button>
            </div>
            <input type="hidden" name="call_status" id="selectedCallStatus">
        </div>

        <div>
            <label for="callNotes" class="block text-sm font-medium text-gray-700 mb-2">
                Call Notes
            </label>
            <textarea id="callNotes" name="notes" rows="4" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                placeholder="Enter call outcome and your notes here... (optional)"></textarea>
        </div>

        <div class="hidden" id="nextCallSection">
            <label for="nextCallTime" class="block text-sm font-medium text-gray-700 mb-2">Next Call Time</label>
            <input id="nextCallTime" type="datetime-local" name="next_call_at" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div class="pt-6 border-t border-gray-200 flex items-center gap-4">
            <button type="submit" 
                class="flex-grow bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center font-semibold">
                <i class="fas fa-save mr-2"></i>
                <span>Save Call</span>
            </button>
            
            <a href="<?= BASE_PATH ?>/calls/skip/<?= $driver_id ?>" title="Skip Driver"
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