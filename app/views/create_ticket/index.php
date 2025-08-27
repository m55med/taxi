<?php require_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
[x-cloak] { display: none !important; }

/* ULTIMATE FIX for truncated text in mobile - HIGHEST PRIORITY */
@media screen and (max-width: 768px) {
    /* Nuclear option: override ALL truncate classes */
    html body .truncate,
    html body span.truncate,
    html body div.truncate,
    html body button span.truncate,
    html body li span.truncate,
    html body .relative span.truncate,
    html body .searchable-select-button span.truncate {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: initial !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        line-height: 1.3 !important;
        max-height: none !important;
        display: block !important;
        -webkit-line-clamp: unset !important;
    }
}

/* Responsive improvements for searchable select dropdowns */
@media (max-width: 768px) {
    /* Make dropdown text responsive on mobile - MORE SPECIFIC SELECTORS */
    .searchable-select-button .truncate,
    .searchable-select-button .searchable-select-text.truncate,
    .searchable-select-button span.truncate,
    .searchable-select-text.truncate {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        line-height: 1.2 !important;
        max-height: 2.6em !important; /* Show approximately 2 lines */
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }
    
    /* Make dropdown options more readable on mobile */
    .searchable-select-dropdown li {
        white-space: normal !important;
        padding: 8px 12px !important;
        line-height: 1.3 !important;
    }
    
    /* Adjust dropdown button height for mobile */
    .searchable-select-button {
        min-height: 50px !important;
        height: auto !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    
    /* Also apply to regular buttons with same classes */
    button.relative.w-full.bg-white.border {
        min-height: 50px !important;
        height: auto !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    
    /* Make dropdown wider on mobile if needed */
    .searchable-select-dropdown {
        width: 100vw !important;
        max-width: calc(100vw - 32px) !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}

/* Improve text display in all screen sizes */
.searchable-select-text {
    display: block;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 640px) {
    .searchable-select-text {
        white-space: normal !important;
        line-height: 1.2 !important;
        max-height: 2.6em !important;
        overflow: hidden !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        text-overflow: clip !important;
    }
}

/* Override Tailwind's .truncate class completely on mobile */
@media (max-width: 768px) {
    /* Force override truncate class in all contexts - MOST IMPORTANT RULE */
    .truncate,
    span.truncate,
    div.truncate,
    button .truncate,
    li .truncate {
        overflow: visible !important;
        text-overflow: clip !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        hyphens: auto !important;
    }
    
    /* Specific override for spans with truncate class inside buttons */
    button span.truncate,
    .searchable-select-button span.truncate {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        line-height: 1.3 !important;
        max-height: 2.6em !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
}

/* Additional improvements for dropdown options text */
@media (max-width: 768px) {
    /* Improve text display in dropdown options */
    .searchable-select-dropdown li span {
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        line-height: 1.3 !important;
        display: block !important;
    }
    
    /* Remove truncate class effect in mobile */
    .searchable-select-dropdown .truncate,
    .searchable-select-dropdown li .truncate,
    .searchable-select-dropdown li span.truncate {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        line-height: 1.4 !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
    
    /* Adjust dropdown positioning for better mobile experience */
    .searchable-select-dropdown.absolute {
        position: fixed !important;
        top: auto !important;
        bottom: 16px !important;
        left: 16px !important;
        right: 16px !important;
        width: auto !important;
        max-width: none !important;
        transform: none !important;
        z-index: 9999 !important;
        max-height: 60vh !important;
        overflow-y: auto !important;
    }
    
    /* Prevent text overflow in small containers */
    .relative .searchable-select-text {
        min-height: 1.2em;
        word-break: break-word;
    }
}

/* Tooltip for full text on hover (desktop only) */
@media (min-width: 769px) {
    .searchable-select-button:hover .searchable-select-text[title] {
        position: relative;
    }
    
    .searchable-select-button .searchable-select-text[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        line-height: 1.4;
        white-space: normal;
        z-index: 1000;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 4px;
    }
}

/* Unified Classification Select Styles */
.unified-classification {
    /* Ensure proper stacking and spacing */
}

.unified-classification .searchable-select-dropdown {
    /* Enhanced dropdown for mobile */
    border: 1px solid #e5e7eb;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Responsive adjustments for unified classification */
@media (max-width: 768px) {
    .unified-classification .searchable-select-dropdown {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: calc(100vw - 32px) !important;
        max-width: 500px !important;
        max-height: 80vh !important;
        bottom: auto !important;
        right: auto !important;
        z-index: 9999 !important;
    }
    
    .unified-classification .searchable-select-button {
        min-height: 60px !important;
        padding: 12px 40px 12px 12px !important;
    }
    
    .unified-classification .searchable-select-text {
        line-height: 1.4 !important;
        white-space: normal !important;
        overflow: visible !important;
        word-wrap: break-word !important;
    }
}

/* Search highlighting */
mark {
    background-color: #fef3c7 !important;
    color: #92400e !important;
    padding: 1px 2px !important;
    border-radius: 2px !important;
    font-weight: 600 !important;
}

/* Level indicators */
.level-indicator {
    transition: all 0.2s ease;
}

.level-indicator:hover {
    transform: scale(1.1);
}

/* Enhanced dropdown item styling */
.unified-option-item {
    transition: all 0.15s ease;
    border-left: 3px solid transparent;
}

.unified-option-item:hover {
    border-left-color: #6366f1;
    background-color: #f8fafc;
}

.unified-option-item.selected {
    border-left-color: #4f46e5;
    background-color: #eef2ff;
}
</style>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="createTicketForm(
        <?php echo htmlspecialchars(json_encode($data['platforms'])); ?>,
        <?php echo htmlspecialchars(json_encode($data['vip_users'])); ?>
     )"
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
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="ticket_number" x-model="formData.ticket_number" @blur="checkTicketExists" class="block w-full border-gray-300 rounded-md pl-3 pr-10 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                <button @click.prevent="pasteFromClipboard('ticket_number')" type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 group" aria-label="Paste Ticket Number">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 01-2 2H4a2 2 0 01-2-2v-3z"/>
                                    </svg>
                                </button>
                            </div>
                        <div id="ticket-exists-warning" class="hidden mt-1 text-sm text-blue-600">
                            Ticket already exists. <a href="#" id="view-ticket-link" target="_blank" class="font-bold underline">View Details</a>
                        </div>
                        </div>
                        <div>
                            <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform <span class="text-red-500">*</span></label>
                             <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['platforms'])); ?>)" 
                                 x-init="$el.dataset.initialValue = formData.platform_id"
                                 data-model-name="platform_id"
                                 data-placeholder="Select a platform..."
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="searchable-select-button relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="searchable-select-text block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition x-cloak class="searchable-select-dropdown absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
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
                             <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="phone" x-model="formData.phone" class="block w-full border-gray-300 rounded-md pl-3 pr-10 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <button @click.prevent="pasteFromClipboard('phone')" type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 group" aria-label="Paste Phone Number">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 01-2 2H4a2 2 0 01-2-2v-3z"/>
                                    </svg>
                                </button>
                </div>
            </div>
                 <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                            <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode($data['countries'])); ?>)" 
                                 x-init="$el.dataset.initialValue = formData.country_id"
                                 data-model-name="country_id"
                                 data-placeholder="Select a country..."
                                 class="relative mt-1">
                                <button @click="toggle" type="button" class="searchable-select-button relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <span class="searchable-select-text block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition x-cloak class="searchable-select-dropdown absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
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
            
                <!-- Card 2: Unified Classification -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-4">Classification</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="unified_classification" class="block text-sm font-medium text-gray-700 mb-2">
                                Category → Subcategory → Code <span class="text-red-500">*</span>
                            </label>
                            <p class="text-sm text-gray-500 mb-3">Search and select from the complete classification hierarchy</p>
                            
                            <div x-data="unifiedClassificationSelect()" 
                                 class="relative unified-classification">
                                <button @click="toggle" type="button" 
                                        class="searchable-select-button relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-3 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm min-h-[60px]">
                                    <span class="searchable-select-text block" 
                                          x-text="selectedLabel" 
                                          :class="{'text-gray-400': !selectedItem, 'text-gray-900': selectedItem}">
                                    </span>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition x-cloak 
                                     class="searchable-select-dropdown absolute mt-1 w-full rounded-md bg-white shadow-lg z-20 max-h-80">
                                    <!-- Search Input -->
                                    <div class="p-3 border-b">
                                        <input type="text" 
                                               x-model="searchTerm" 
                                               x-ref="search" 
                                               @input="filterOptions"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                               placeholder="Search categories, subcategories, or codes...">
                                    </div>
                                    
                                    <!-- Options List -->
                                    <ul class="max-h-64 rounded-md py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none">
                                        <template x-for="option in filteredOptions" :key="option.uniqueId">
                                            <li @click="selectOption(option)" 
                                                class="unified-option-item cursor-pointer select-none relative hover:bg-indigo-50 transition-colors duration-150"
                                                :class="{ 
                                                    'bg-indigo-100 selected': selectedItem && selectedItem.uniqueId === option.uniqueId,
                                                    'border-l-blue-500': option.level === 'category',
                                                    'border-l-green-500': option.level === 'subcategory',
                                                    'border-l-purple-500': option.level === 'code'
                                                }">
                                                <div class="py-3 px-4 flex items-start space-x-3">
                                                    <!-- Hierarchy Level Indicator -->
                                                    <div class="level-indicator flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold"
                                                         :class="{
                                                             'bg-blue-100 text-blue-700': option.level === 'category',
                                                             'bg-green-100 text-green-700': option.level === 'subcategory', 
                                                             'bg-purple-100 text-purple-700': option.level === 'code'
                                                         }">
                                                        <span x-text="option.level === 'category' ? 'C' : option.level === 'subcategory' ? 'S' : 'Co'"></span>
                                                    </div>
                                                    
                                                    <!-- Text Content -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-medium text-gray-900 mb-1" x-html="option.displayName"></div>
                                                        <div class="text-xs text-gray-500 leading-relaxed" x-text="option.breadcrumb"></div>
                                                    </div>
                                                    
                                                    <!-- Selection Indicator -->
                                                    <template x-if="selectedItem && selectedItem.uniqueId === option.uniqueId">
                                                        <div class="flex-shrink-0">
                                                            <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                </div>
                                            </li>
                                        </template>
                                        
                                        <!-- No Results -->
                                        <template x-if="filteredOptions.length === 0">
                                            <li class="text-gray-500 cursor-default select-none relative py-6 px-4 text-center">
                                                <div class="text-sm">
                                                    <div class="font-medium">No matches found</div>
                                                    <div class="text-xs mt-1">Try adjusting your search terms</div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Selected Classification Display -->
                            <div x-show="selectedItem" x-cloak class="mt-3 p-3 bg-gray-50 rounded-md border">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-700 mb-1">Selected Classification:</div>
                                    <div class="text-gray-600" x-text="selectedItem ? selectedItem.breadcrumb : ''"></div>
                                </div>
                            </div>
                            
                            <!-- Knowledge Base Hint -->
                            <div x-data="{ knowledgeBaseArticle: {} }" 
                                 @kb-article-found.window="knowledgeBaseArticle = $event.detail"
                                 x-show="knowledgeBaseArticle.id" 
                                 x-cloak class="mt-3">
                                <a :href="knowledgeBaseArticle.url" target="_blank" 
                                   class="text-sm text-blue-600 bg-blue-50 p-3 rounded-md hover:bg-blue-100 flex items-center transition-all group">
                                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                                    <div>
                                        <span class="font-semibold">Suggested Solution Available:</span>
                                        <span class="underline ml-1 group-hover:text-blue-700" x-text="knowledgeBaseArticle.title"></span>
                                    </div>
                                </a>
                            </div>
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
                        <div x-data="searchableSelect(availableCoupons.map(c => ({id: c.id, name: `${c.code} (Value: ${c.value})` })))" 
                             data-model-name="couponInput"
                             data-placeholder="Select a coupon..."
                             class="relative"
                             @coupons-updated.window="updateOptions($event.detail.map(c => ({id: c.id, name: `${c.code} (Value: ${c.value})` })))">
                            <button @click="toggle" type="button" class="searchable-select-button relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :disabled="!formData.country_id || availableCoupons.length === 0">
                                <span class="searchable-select-text block truncate" x-text="selectedLabel"></span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition x-cloak class="searchable-select-dropdown absolute mt-1 w-full rounded-md bg-white shadow-lg z-20">
                                 <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search coupons..."></div>
                                <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"><span x-text="option.name"></span></li></template>
                                    <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No coupons found.</li></template>
                                </ul>
                            </div>
                        </div>

                         <button type="button" @click="addSelectedCoupon" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center justify-center" :disabled="!formData.couponInput">
                             <i class="fas fa-plus mr-2"></i> Add Coupon
                         </button>
                     </div>
                     <span x-show="!formData.country_id" class="text-xs text-red-500 mt-2 block">Please select a country to see available coupons.</span>
                     <span x-show="formData.country_id && noCouponsAvailable && !couponsLoading" class="text-xs text-gray-500 mt-2 block">No available coupons for the selected country.</span>
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

    <!-- VIP User Assignment Modal -->
    <div x-show="isVipModalOpen" 
         x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
         @click.self="isVipModalOpen = false">
        
        <div x-show="isVipModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full overflow-visible"
             @click.away="isVipModalOpen = false">
             
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.05 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            VIP Ticket Assignment
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                This is a VIP ticket. Please assign it to a VIP user.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <label for="vip_user_id_modal" class="block text-sm font-medium text-gray-700">Assign VIP User <span class="text-red-500">*</span></label>
                    <div x-data="searchableSelect(<?php echo htmlspecialchars(json_encode(array_map(function($m) { return ['id' => $m['id'], 'name' => $m['username']]; }, $data['vip_users']))); ?>)"
                         x-init="$el.dataset.initialValue = formData.vip_user_id"
                         data-model-name="vip_user_id"
                         data-placeholder="Select a VIP user..."
                         class="relative mt-1">
                        <button @click="toggle" type="button" class="searchable-select-button relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="searchable-select-text block truncate" x-text="selectedLabel"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="searchable-select-dropdown absolute mt-1 w-full rounded-md bg-white shadow-lg z-500">
                            <div class="p-2"><input type="text" x-model="searchTerm" x-ref="search" class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search VIP users..."></div>
                            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <template x-for="option in filteredOptions" :key="option.id"><li @click="selectOption(option)" class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white" :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }"><span class="block truncate" x-text="option.name"></span><template x-if="selected && selected.id == option.id"><span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span></template></li></template>
                                <template x-if="filteredOptions.length === 0"><li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">No VIP users found.</li></template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="isVipModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const URLROOT = '<?= URLROOT ?>';
    
    // Fix truncated text and add tooltips for searchable selects
    document.addEventListener('DOMContentLoaded', function() {
        // Function to remove truncate class on mobile screens
        function fixTruncatedText() {
            if (window.innerWidth <= 768) {
                // Remove truncate class from all elements on mobile
                const truncatedElements = document.querySelectorAll('.truncate');
                truncatedElements.forEach(element => {
                    element.classList.remove('truncate');
                    element.style.whiteSpace = 'normal';
                    element.style.overflow = 'visible';
                    element.style.textOverflow = 'initial';
                    element.style.wordWrap = 'break-word';
                    element.style.lineHeight = '1.3';
                });
            }
        }
        
        // Function to add title attribute for truncated text
        function addTooltipsToTruncatedText() {
            const selectTexts = document.querySelectorAll('.searchable-select-text');
            selectTexts.forEach(element => {
                // Check if text is truncated by comparing scroll width with offset width
                if (element.scrollWidth > element.offsetWidth || element.scrollHeight > element.offsetHeight) {
                    const fullText = element.textContent.trim();
                    if (fullText && fullText.length > 0) {
                        element.setAttribute('title', fullText);
                    }
                }
            });
        }
        
        // Fix truncated text on page load
        fixTruncatedText();
        
        // Add tooltips on page load
        addTooltipsToTruncatedText();
        
        // Also fix after Alpine.js initializes (with delay)
        setTimeout(function() {
            fixTruncatedText();
            addTooltipsToTruncatedText();
        }, 500);
        
        // Fix when Alpine components update
        document.addEventListener('alpine:init', function() {
            setTimeout(function() {
                fixTruncatedText();
                addTooltipsToTruncatedText();
            }, 100);
        });
        
        // Re-add tooltips when content changes (using MutationObserver)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    fixTruncatedText();
                    addTooltipsToTruncatedText();
                }
            });
        });
        
        // Also fix truncated text when window is resized
        window.addEventListener('resize', function() {
            fixTruncatedText();
            addTooltipsToTruncatedText();
        });
        
        // Observe changes in the document
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    });
    
    // Unified Classification Select Function
    function unifiedClassificationSelect() {
        return {
            open: false,
            searchTerm: '',
            selectedItem: null,
            allOptions: [],
            filteredOptions: [],
            
            get selectedLabel() {
                return this.selectedItem ? this.selectedItem.breadcrumb : 'Select category → subcategory → code...';
            },
            
            init() {
                // Build unified options from categories data
                this.buildUnifiedOptions();
                this.filteredOptions = [...this.allOptions];
            },
            
            buildUnifiedOptions() {
                const categories = <?php echo json_encode($data['categories']); ?>;
                console.log('Categories data loaded:', categories); // Debug log
                this.allOptions = [];
                
                if (!categories || !Array.isArray(categories)) {
                    console.error('Categories data is not valid:', categories);
                    return;
                }
                
                categories.forEach(category => {
                    // Add category level
                    this.allOptions.push({
                        uniqueId: `cat_${category.id}`,
                        level: 'category',
                        categoryId: category.id,
                        subcategoryId: null,
                        codeId: null,
                        name: category.name,
                        displayName: this.highlightMatch(category.name, this.searchTerm),
                        breadcrumb: category.name,
                        searchText: category.name.toLowerCase()
                    });
                    
                    // Add subcategories
                    if (category.subcategories && category.subcategories.length > 0) {
                        category.subcategories.forEach(subcategory => {
                            this.allOptions.push({
                                uniqueId: `sub_${subcategory.id}`,
                                level: 'subcategory',
                                categoryId: category.id,
                                subcategoryId: subcategory.id,
                                codeId: null,
                                name: subcategory.name,
                                displayName: this.highlightMatch(subcategory.name, this.searchTerm),
                                breadcrumb: `${category.name} → ${subcategory.name}`,
                                searchText: `${category.name} ${subcategory.name}`.toLowerCase()
                            });
                            
                            // Add codes
                            if (subcategory.codes && subcategory.codes.length > 0) {
                                subcategory.codes.forEach(code => {
                                    this.allOptions.push({
                                        uniqueId: `code_${code.id}`,
                                        level: 'code',
                                        categoryId: category.id,
                                        subcategoryId: subcategory.id,
                                        codeId: code.id,
                                        name: code.name,
                                        displayName: this.highlightMatch(code.name, this.searchTerm),
                                        breadcrumb: `${category.name} → ${subcategory.name} → ${code.name}`,
                                        searchText: `${category.name} ${subcategory.name} ${code.name}`.toLowerCase()
                                    });
                                });
                            }
                        });
                    }
                });
            },
            
            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => {
                        this.$refs.search.focus();
                    });
                }
            },
            
            filterOptions() {
                const term = this.searchTerm.toLowerCase().trim();
                
                if (!term) {
                    this.filteredOptions = [...this.allOptions];
                } else {
                    this.filteredOptions = this.allOptions.filter(option => 
                        option.searchText.includes(term)
                    );
                }
                
                // Update display names with highlighting
                this.filteredOptions.forEach(option => {
                    option.displayName = this.highlightMatch(option.name, this.searchTerm);
                });
            },
            
            highlightMatch(text, searchTerm) {
                if (!searchTerm.trim()) return text;
                
                const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                return text.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
            },
            
            selectOption(option) {
                this.selectedItem = option;
                this.open = false;
                this.searchTerm = '';
                this.filteredOptions = [...this.allOptions];
                
                // Update form data
                const formDataEvent = new CustomEvent('option-selected', {
                    detail: {
                        model: 'category_id',
                        value: option.categoryId
                    }
                });
                window.dispatchEvent(formDataEvent);
                
                const formDataEvent2 = new CustomEvent('option-selected', {
                    detail: {
                        model: 'subcategory_id',
                        value: option.subcategoryId
                    }
                });
                window.dispatchEvent(formDataEvent2);
                
                const formDataEvent3 = new CustomEvent('option-selected', {
                    detail: {
                        model: 'code_id',
                        value: option.codeId
                    }
                });
                window.dispatchEvent(formDataEvent3);
                
                // Check for knowledge base articles
                if (option.codeId) {
                    this.checkKnowledgeBase(option.codeId);
                }
            },
            
            checkKnowledgeBase(codeId) {
                // Check if knowledge base functionality exists
                if (typeof window.createTicketForm === 'function') {
                    fetch(`${URLROOT}/knowledge_base/findByCode/${codeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.id) {
                                window.dispatchEvent(new CustomEvent('kb-article-found', {
                                    detail: {
                                        id: data.id,
                                        title: data.title,
                                        url: `${URLROOT}/knowledge_base/show/${data.id}`
                                    }
                                }));
                            }
                        })
                        .catch(e => {
                            // Silent fail for KB lookup
                        });
                }
            }
        };
    }
    
    // Ensure global compatibility and Knowledge Base integration
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for KB article found events
        window.addEventListener('kb-article-found', function(e) {
            // Update knowledge base article data if createTicketForm exists
            if (window.createTicketFormInstance && window.createTicketFormInstance.knowledgeBaseArticle) {
                window.createTicketFormInstance.knowledgeBaseArticle = e.detail;
            }
        });
        
        // Integrate with existing form if available
        if (window.createTicketForm) {
            // Make unified classification compatible with existing form
            window.unifiedClassificationSelect = unifiedClassificationSelect;
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= URLROOT ?>/js/components/searchable-select.js?v=<?= time() ?>"></script>
<script src="<?= URLROOT ?>/js/create_ticket/main.js?v=<?= time() ?>"></script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 