<?php
defined('BASE_PATH') or exit('No direct script access allowed');
$driver_id = $data['driver']['id'] ?? null;
?>

<?php if (isset($data['driver'])): ?>
<!-- Call Form -->
<div>
    <h3 class="text-xl font-semibold mb-6 text-gray-800">Log New Call</h3>
    <form id="callForm" class="space-y-6" 
          x-data="callFormAlpine()" 
          x-init="init()"
          @submit.prevent="submitForm"
          @option-selected.window="formData[event.detail.model] = event.detail.value">
        <input type="hidden" name="driver_id" value="<?= $driver_id ?>">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">Call Outcome <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-3 gap-4">
                <button type="button" @click="handleStatusClick('answered')" :class="{'selected-status border-green-500': selectedStatus === 'answered'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-alt text-2xl text-gray-400 group-hover:text-green-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Answered</span>
                </button>
                <button type="button" @click="handleStatusClick('no_answer')" :class="{'selected-status border-red-500': selectedStatus === 'no_answer'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-slash text-2xl text-gray-400 group-hover:text-red-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">No Answer</span>
                </button>
                <button type="button" @click="handleStatusClick('busy')" :class="{'selected-status border-yellow-500': selectedStatus === 'busy'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-phone-volume text-2xl text-gray-400 group-hover:text-yellow-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Busy</span>
                </button>
                <button type="button" @click="handleStatusClick('not_available')" :class="{'selected-status border-blue-500': selectedStatus === 'not_available'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-user-clock text-2xl text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Unavailable</span>
                </button>
                <button type="button" @click="handleStatusClick('wrong_number')" :class="{'selected-status border-orange-500': selectedStatus === 'wrong_number'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-exclamation-triangle text-2xl text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Wrong Number</span>
                </button>
                <button type="button" @click="handleStatusClick('rescheduled')" :class="{'selected-status border-purple-500': selectedStatus === 'rescheduled'}" class="call-status-btn group flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-calendar-alt text-2xl text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                    <span class="mt-2 text-sm font-medium text-gray-600">Reschedule</span>
                </button>
            </div>
            <input type="hidden" name="call_status" id="selectedCallStatus" x-model="formData.call_status">
        </div>

        <!-- Ticket Classification -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg">
            <div>
                <label for="ticket_category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['ticket_categories'])); ?>)" data-model-name="ticket_category_id" data-placeholder="Select a category..." class="relative mt-1">
                    <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-[42px]">
                        <span class="block truncate" x-text="selectedLabel"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><i class="fas fa-chevron-down text-gray-400"></i></span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                        <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search..."></div>
                        <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span></li></template>
                            <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No results found.</li></template>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <label for="ticket_subcategory_id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                <div x-show="!formData.ticket_category_id" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm h-[42px] flex items-center">Select a category first</div>
                <div x-show="formData.ticket_category_id && subcategoriesLoading" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm h-[42px] flex items-center">Loading...</div>
                <template x-if="formData.ticket_category_id && !subcategoriesLoading">
                    <div x-data="searchableSelect([])" data-model-name="ticket_subcategory_id" data-placeholder="Select a subcategory..." @options-updated.window="$event.detail.model === 'subcategories' && updateOptions($event.detail.data)" class="relative mt-1">
                        <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-[42px]" :disabled="options.length === 0">
                            <span class="block truncate" x-text="selectedLabel"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><i class="fas fa-chevron-down text-gray-400"></i></span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                            <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search..."></div>
                            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span></li></template>
                                <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No results found.</li></template>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
            <div>
                <label for="ticket_code_id" class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                <div x-show="!formData.ticket_subcategory_id" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm h-[42px] flex items-center">Select a subcategory first</div>
                <div x-show="formData.ticket_subcategory_id && codesLoading" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm h-[42px] flex items-center">Loading...</div>
                <template x-if="formData.ticket_subcategory_id && !codesLoading">
                     <div x-data="searchableSelect([])" data-model-name="ticket_code_id" data-placeholder="Select a code..." @options-updated.window="$event.detail.model === 'codes' && updateOptions($event.detail.data)" class="relative mt-1">
                        <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-[42px]" :disabled="options.length === 0">
                            <span class="block truncate" x-text="selectedLabel"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><i class="fas fa-chevron-down text-gray-400"></i></span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                            <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search..."></div>
                            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span></li></template>
                                <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No results found.</li></template>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <!-- End Ticket Classification -->

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
                :disabled="isSubmitting || !formData.call_status"
                class="flex-grow bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center font-semibold">
                <i class="fas fa-save mr-2"></i>
                <span x-text="isSubmitting ? 'Saving...' : 'Save Call'"></span>
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