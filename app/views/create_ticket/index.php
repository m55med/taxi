<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="createTicketForm(<?php echo htmlspecialchars(json_encode($data['platforms'])); ?>)"
     @option-selected.window="formData[event.detail.model] = event.detail.value">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Create New Ticket</h1>
        <p class="text-sm text-gray-500 mt-1">Fill in the details below to create a new support ticket.</p>
    </div>

    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <form @submit.prevent="submitForm" id="createTicketForm" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Card 1: Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="ticket_number" class="block text-sm font-medium text-gray-700">Ticket Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="ticket_number" x-model="formData.ticket_number" @blur="checkTicketExists" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                <div id="ticket-exists-warning" class="hidden mt-1 text-sm text-blue-600">
                                    Ticket already exists. <a href="#" id="view-ticket-link" target="_blank" class="font-bold underline">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform <span class="text-red-500">*</span></label>
                             <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['platforms'])); ?>)" 
                                 x-init="$el.dataset.initialValue = formData.platform_id"
                                 data-model-name="platform_id"
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                    <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search platforms..."></div>
                                    <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                        <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No platforms found.</li></template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                            <input type="text" id="phone" x-model="formData.phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                            <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['countries'])); ?>)" 
                                 x-init="$el.dataset.initialValue = formData.country_id"
                                 data-model-name="country_id"
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                    <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search countries..."></div>
                                    <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                        <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No countries found.</li></template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Classification -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-4">Classification</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                            <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['categories'])); ?>)" 
                                 x-init="$el.dataset.initialValue = formData.category_id"
                                 data-model-name="category_id"
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                    <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search categories..."></div>
                                    <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                        <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No categories found.</li></template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategory</label>
                            <div x-show="!formData.category_id" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm">Select a category first</div>
                             <div x-show="formData.category_id && subcategories.length === 0" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm">Loading...</div>
                            <template x-if="subcategories.length > 0">
                                <div x-data="searchableSelect(subcategories)" data-model-name="subcategory_id" class="relative mt-1">
                                    <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <span class="block truncate" x-text="selectedLabel"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                        <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search subcategories..."></div>
                                        <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                            <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No subcategories found.</li></template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label for="code_id" class="block text-sm font-medium text-gray-700">Code</label>
                             <div x-show="!formData.subcategory_id" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm">Select a subcategory first</div>
                             <div x-show="formData.subcategory_id && codes.length === 0" class="mt-1 p-2 border rounded-md bg-gray-50 text-gray-500 text-sm">Loading...</div>
                            <template x-if="codes.length > 0">
                                <div x-data="searchableSelect(codes)" data-model-name="code_id" class="relative mt-1">
                                    <button @click="toggle" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <span class="block truncate" x-text="selectedLabel"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                                        <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search codes..."></div>
                                        <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                            <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No codes found.</li></template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-start items-center">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center shadow-lg" :disabled="isSubmitting">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'Creating...' : 'Create Ticket'"></span>
                    </button>
                    <button type="button" @click="resetForm" class="ml-4 px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Reset
                    </button>
                </div>

            </div>

            <!-- Right Column -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Card 3: Coupons -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-4">Coupons</h2>
                     <div class="space-y-4">
                        <select x-model="couponInput" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" :disabled="!formData.country_id || availableCoupons.length === 0">
                            <option value="">Select a coupon...</option>
                            <template x-for="coupon in availableCoupons" :key="coupon.id">
                                <option :value="coupon.id" x-text="`${coupon.code} (Value: ${coupon.value})`"></option>
                            </template>
                        </select>
                        <button type="button" @click="addSelectedCoupon" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center justify-center" :disabled="!couponInput">
                            <i class="fas fa-plus mr-2"></i> Add Coupon
                        </button>
                    </div>
                     <span x-show="!formData.country_id" class="text-xs text-red-500 mt-2 block">Please select a country to see available coupons.</span>
                     <span x-show="formData.country_id && availableCoupons.length === 0 && !couponsLoading" class="text-xs text-gray-500 mt-2 block">No available coupons for the selected country.</span>
                      <span x-show="couponsLoading" class="text-xs text-gray-500 mt-2 block">Loading coupons...</span>

                    <ul id="coupon-list" class="mt-4 space-y-2" x-show="formData.coupons.length > 0">
                        <template x-for="(coupon, index) in formData.coupons" :key="coupon.id">
                             <li class="flex items-center justify-between bg-gray-50 p-3 rounded-md border">
                                <div>
                                    <span class="font-semibold text-gray-800" x-text="coupon.code"></span>
                                    <span class="text-sm text-gray-500 ml-2" x-text="'(Value: ' + coupon.value + ')'"></span>
                                </div>
                                <div class="flex items-center">
                                    <button @click.prevent="copyToClipboard(coupon.code)" type="button" class="text-gray-400 hover:text-gray-600 mr-2" title="Copy code">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button @click.prevent="removeCoupon(index)" type="button" class="text-red-500 hover:text-red-700" title="Remove coupon">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
                
                <!-- Card 4: Notes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-4">Notes</h2>
                    <textarea id="notes" x-model="formData.notes" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Add any relevant notes here..."></textarea>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const URLROOT = '<?= URLROOT ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= URLROOT ?>/js/components/searchable-select.js?v=<?= time() ?>"></script>
<script src="<?= URLROOT ?>/js/create_ticket/main.js?v=<?= time() ?>"></script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 