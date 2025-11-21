<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>

<!-- Local CSS for better performance -->
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css">

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Custom scrollbar for better mobile experience */
    .scrollbar-thin {
        scrollbar-width: thin;
    }

    .scrollbar-thin::-webkit-scrollbar {
        height: 6px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Mobile scroll indicator animation */
    @keyframes scrollHint {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(10px); }
    }

    .scroll-hint {
        animation: scrollHint 2s infinite;
    }

    /* Line clamp utility for text truncation */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Enhanced scrollbar for better UX */
    .scrollbar-smooth {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }

    .scrollbar-smooth::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .scrollbar-smooth::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .scrollbar-smooth::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: background 0.2s ease;
    }

    .scrollbar-smooth::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Loading animation improvements */
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }

    .shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
    }

    /* Focus improvements for accessibility */
    .focus-ring:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }

    /* Better button hover effects */
    .btn-hover-lift:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Rotate animation for expand/collapse buttons */
    .rotate-90 {
        transform: rotate(90deg);
        transition: transform 0.2s ease;
    }

    .rotate-180 {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }

    /* Enhanced transitions for table rows */
    .transition-all {
        transition: all 0.2s ease;
    }

    /* Smooth expand/collapse animations */
    [x-show] {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    [x-show="false"] {
        opacity: 0;
        transform: scale(0.95);
    }

    [x-show="true"] {
        opacity: 1;
        transform: scale(1);
    }
</style>

<div class="p-8 bg-gray-100 min-h-screen" x-data="reviewsPage()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">All Reviews</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor and manage quality reviews and feedback.</p>
        </div>

        <!-- Summary Stats -->
        <div class="flex items-center space-x-6 mt-4 md:mt-0">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600" x-text="totalItems || reviews.length">0</div>
                <div class="text-xs text-gray-500 uppercase">Total Reviews</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600" x-text="getAverageRating()">0</div>
                <div class="text-xs text-gray-500 uppercase">Avg Rating</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600" x-text="getUniqueAgentsCount()">0</div>
                <div class="text-xs text-gray-500 uppercase">Agents</div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 relative">
                <input type="text"
                       x-model="searchQuery"
                       @input="filterReviews()"
                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Search by reviewer, agent, ticket number, or team...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <div class="flex items-center space-x-2">
                <button @click="clearSearch()"
                        class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 btn-hover-lift transition focus-ring">
                    <i class="fas fa-times mr-1"></i>Clear
                </button>
                <button class="px-3 py-2 text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition focus-ring"
                        title="Keyboard shortcuts: Ctrl+K to search, Esc to clear, Ctrl+R to reset filters">
                    <i class="fas fa-keyboard mr-1"></i>Shortcuts
                </button>
                <div class="text-sm text-gray-500" x-show="searchQuery">
                    <span x-text="reviews.length"></span> of <span x-text="totalItems"></span> results
                </div>
            </div>
        </div>

        <!-- Quick Search Suggestions -->
        <div class="mt-3 flex flex-wrap gap-2" x-show="searchQuery.length > 0">
            <template x-for="suggestion in (searchSuggestions || [])" :key="suggestion">
                <button @click="applySearchSuggestion(suggestion)"
                        class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition">
                    <span x-text="suggestion"></span>
                </button>
            </template>
        </div>
    </div>
<!-- Filter Section -->
<div 
    class="bg-white p-6 rounded-lg shadow-md mb-8"
>
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 gap-4">
        <div class="flex items-center space-x-3">
            <h2 class="text-xl font-semibold text-gray-700">Filters</h2>
            <!-- Active Filters Indicator -->
            <div class="flex items-center space-x-2" x-show="getActiveFiltersCount() > 0">
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                    <span x-text="getActiveFiltersCount()"></span> active
                </span>
                <button @click="clearAllFilters()" class="text-xs text-gray-500 hover:text-gray-700 underline" title="Clear all filters">
                    Clear all
                </button>
            </div>
        </div>


            <!-- Quick Date Filter Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-600 mr-2 hidden lg:inline">Quick filters:</span>
                <button @click="setPeriod('all')" :class="{'bg-blue-600 text-white': activePeriod === 'all', 'bg-gray-200 text-gray-700': activePeriod !== 'all'}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300 btn-hover-lift focus-ring">All Time</button>
                <button @click="setPeriod('today')" :class="{'bg-blue-600 text-white': activePeriod === 'today', 'bg-gray-200 text-gray-700': activePeriod !== 'today'}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300 btn-hover-lift focus-ring">Today</button>
                <button @click="setPeriod('week')" :class="{'bg-blue-600 text-white': activePeriod === 'week', 'bg-gray-200 text-gray-700': activePeriod !== 'week'}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300 btn-hover-lift focus-ring">Last 7 Days</button>
                <button @click="setPeriod('month')" :class="{'bg-blue-600 text-white': activePeriod === 'month', 'bg-gray-200 text-gray-700': activePeriod !== 'month'}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300 btn-hover-lift focus-ring">This Month</button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" x-ref="daterangepicker" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>

            <!-- Context Type -->
            <div>
                <label for="context_type" class="block text-sm font-medium text-gray-600 mb-1">
                    Context
                    <span x-show="filters.context_type" class="text-blue-600 text-xs ml-1">(filtered)</span>
                </label>
                <select id="context_type" x-model="filters.context_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        :class="filters.context_type ? 'border-blue-300 bg-blue-50' : ''">
                    <option value="">All Contexts</option>
                    <option value="Ticket">Ticket</option>
                    <option value="Call">Call</option>
                </select>
            </div>
            
            <!-- Category -->
            <div>
                 <label for="category" class="block text-sm font-medium text-gray-600 mb-1">
                    Category
                    <span x-show="selectedCategory" class="text-blue-600 text-xs ml-1">(filtered)</span>
                 </label>
                <select id="category" x-model="selectedCategory" @change="onCategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        :class="selectedCategory ? 'border-blue-300 bg-blue-50' : ''">
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category.id">
                        <option :value="category.id" x-text="category.name"></option>
                    </template>
                </select>
            </div>
            
            <!-- Subcategory -->
            <div>
                <label for="subcategory" class="block text-sm font-medium text-gray-600 mb-1">
                    Subcategory
                    <span x-show="selectedSubcategory" class="text-blue-600 text-xs ml-1">(filtered)</span>
                </label>
                <select id="subcategory" x-model="selectedSubcategory" @change="onSubcategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        :class="selectedSubcategory ? 'border-blue-300 bg-blue-50' : ''" :disabled="!selectedCategory">
                    <option value="">All Subcategories</option>
                    <template x-for="subcategory in subcategories" :key="subcategory.id">
                        <option :value="subcategory.id" x-text="subcategory.name"></option>
                    </template>
                </select>
            </div>
            
            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-600 mb-1">
                    Code
                    <span x-show="filters.code_id" class="text-blue-600 text-xs ml-1">(filtered)</span>
                </label>
                <select id="code" x-model="filters.code_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        :class="filters.code_id ? 'border-blue-300 bg-blue-50' : ''" :disabled="!selectedSubcategory">
                     <option value="">All Codes</option>
                    <template x-for="code in codes" :key="code.id">
                        <option :value="code.id" x-text="code.name"></option>
                    </template>
                </select>
            </div>

            <!-- Agent Reviewed Filter -->
            <div>
                <label for="agent_id" class="block text-sm font-medium text-gray-600 mb-1">
                    Agent Reviewed
                    <span x-show="filters.agent_id" class="text-blue-600 text-xs ml-1">(filtered)</span>
                </label>
                <select id="agent_id" x-model="filters.agent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        :class="filters.agent_id ? 'border-blue-300 bg-blue-50' : ''">
                    <option value="">All Agents</option>
                    <?php
                    $selectedAgentId = $_GET['agent_id'] ?? '';
                    foreach ($data['agents'] as $agent):
                        $selected = ($selectedAgentId == $agent['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $agent['id'] ?>" <?= $selected ?>><?= htmlspecialchars($agent['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button @click="resetFilters" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 btn-hover-lift transition focus-ring">Reset</button>
            <button @click="fetchReviews()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 btn-hover-lift transition focus-ring flex items-center">
                <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Search</span>
            </button>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Table Controls -->
        <div class="bg-gray-50 px-4 py-3 border-b">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <!-- View Mode Controls -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700">View:</span>
                    <div class="flex rounded-lg bg-white border p-1">
                        <button @click="setTableViewMode('compact')"
                                :class="tableViewMode === 'compact' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                                class="px-3 py-1 text-sm rounded-md transition-colors">
                            Compact
                        </button>
                        <button @click="setTableViewMode('expanded')"
                                :class="tableViewMode === 'expanded' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                                class="px-3 py-1 text-sm rounded-md transition-colors">
                            Expanded
                        </button>
                        <button @click="setTableViewMode('detailed')"
                                :class="tableViewMode === 'detailed' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                                class="px-3 py-1 text-sm rounded-md transition-colors">
                            Detailed
                        </button>
                    </div>
                </div>

                <!-- Results Info & Controls -->
                <div class="flex items-center space-x-4">
                    <!-- Items per page -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select x-model="itemsPerPage" @change="changeItemsPerPage()" class="text-sm border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <!-- Results counter -->
                    <div class="text-sm text-gray-600" x-show="totalItems > 0">
                        <span x-text="getCurrentPageStart()"></span> -
                        <span x-text="getCurrentPageEnd()"></span> of
                        <span x-text="totalItems"></span> reviews
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Scroll indicator for mobile -->
        <div class="md:hidden px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b text-sm text-blue-700 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
            <div class="relative flex items-center justify-center space-x-2">
                <i class="fas fa-arrows-alt-h text-blue-600 animate-bounce"></i>
                <span class="font-medium">اسحب أفقياً لرؤية المحتوى الكامل أو قم بتوسيع الصفوف</span>
                <i class="fas fa-hand-paper text-blue-600 animate-pulse"></i>
            </div>
        </div>

        <!-- Desktop Scroll Hint -->
        <div class="hidden md:block px-4 py-2 bg-gray-50 border-b text-xs text-gray-600 text-center">
            <i class="fas fa-mouse-pointer mr-1"></i>
            استخدم عجلة الفأرة أو اسحب شريط التمرير لعرض المحتوى الكامل
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4 scrollbar-smooth">
            <template x-if="isLoading">
                <!-- Skeleton Loading for Mobile -->
                <template x-for="n in 5" :key="n">
                    <div class="bg-white rounded-lg shadow-md p-4 animate-pulse">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                            <div class="h-6 bg-gray-200 rounded w-16"></div>
                        </div>
                        <div class="space-y-2">
                            <div class="h-3 bg-gray-200 rounded w-32"></div>
                            <div class="h-3 bg-gray-200 rounded w-28"></div>
                            <div class="h-3 bg-gray-200 rounded w-20"></div>
                        </div>
                    </div>
                </template>
            </template>

            <template x-if="!isLoading && !error && reviews && reviews.length > 0">
                <template x-for="review in reviews" :key="review?.review_id || 'unknown'">
                    <div 
                         class="bg-white rounded-lg shadow-md p-4 border-l-4 hover:shadow-lg transition-shadow duration-200"
                         :class="review ? getRatingBorderColor(review.rating) : 'border-gray-400'">

                        <!-- Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 text-sm truncate max-w-32"
                                         x-text="review?.reviewer_name || 'Unknown'"
                                         :title="review?.reviewer_name || 'Unknown'"></div>
                                    <div class="text-xs text-gray-500" x-text="review?.reviewed_at ? new Date(review.reviewed_at).toLocaleDateString() : 'N/A'"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold" :class="review ? getRatingTextColor(review.rating) : 'text-gray-500'" x-text="review ? (review.rating + '%') : 'N/A'"></div>
                                <div class="text-xs text-gray-500">Rating</div>
                            </div>
                        </div>

                        <!-- Agent Info -->
                        <div class="mb-3" x-show="review && review.agent_name">
                            <div class="text-xs text-gray-500 mb-1">Agent Reviewed</div>
                            <div class="font-medium text-gray-800 text-sm truncate" x-text="review?.agent_name || 'N/A'" :title="review?.agent_name || 'N/A'"></div>
                        </div>

                        <!-- Context -->
                        <div class="mb-3">
                            <div class="text-xs text-gray-500 mb-1">Context</div>
                            <a :href="review ? getContextUrl(review) : '#'" target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline transition-colors">
                                <span x-text="review ? getContextText(review) : 'N/A'"></span>
                            </a>
                        </div>

                        <!-- Classification -->
                        <div class="mb-3" x-show="review && getClassificationText(review) !== '-'">
                            <div class="text-xs text-gray-500 mb-1">Classification</div>
                            <div class="text-sm text-gray-700 truncate" x-text="review ? getClassificationText(review) : 'N/A'" :title="review ? getClassificationText(review) : 'N/A'"></div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4" x-show="review && review.review_notes">
                            <div class="text-xs text-gray-500 mb-1">Notes</div>
                            <div class="text-sm text-gray-600 line-clamp-2" x-text="review?.review_notes || 'No notes'" :title="review?.review_notes || 'No notes'"></div>
                        </div>

                        <!-- Expand/Collapse for Mobile -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <div class="flex items-center space-x-3">
                                <!-- Expand Details Button -->
                                <button @click="review && review.review_id ? toggleRowExpansion(review.review_id) : null"
                                        class="text-blue-600 hover:text-blue-800 text-sm flex items-center transition-colors">
                                    <i class="fas fa-chevron-down mr-1" :class="{'rotate-180': review && review.review_id && isRowExpanded(review.review_id)}" style="transition: transform 0.2s ease;"></i>
                                    <span x-text="review && review.review_id && isRowExpanded(review.review_id) ? 'Hide Details' : 'Show Details'"></span>
                                </button>

                                <!-- Discussion Link -->
                                <a :href="review && review.discussion_id ? `<?= URLROOT ?>/discussions#discussion-${review.discussion_id}` : '#' "
                                   x-show="review && review.open_discussion_count > 0"
                                   class="text-yellow-600 hover:text-yellow-900 text-sm flex items-center">
                                    <i class="fas fa-comments mr-1"></i>
                                    Discussion
                                </a>
                            </div>

                            <!-- Admin Actions -->
                            <template x-if="canManageReviews()">
                                <div class="flex items-center space-x-2">
                                    <button @click="review ? editReview(review) : null"
                                            class="text-indigo-600 hover:text-indigo-900 p-1" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click="review ? confirmDeleteReview(review) : null"
                                            class="text-red-600 hover:text-red-900 p-1" title="Delete">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Expanded Mobile Details -->
                        <div x-show="review && review.review_id && isRowExpanded(review.review_id)" x-transition class="mt-4 pt-4 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
                            <div class="space-y-3">
                                <!-- Additional Details -->
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Full Classification:</span>
                                    <span class="ml-2 text-gray-600" x-text="review ? (getClassificationText(review) || 'N/A') : 'N/A'"></span>
                                </div>

                                <!-- Full Notes -->
                                <div x-show="review && review.review_notes" class="text-sm">
                                    <span class="font-medium text-gray-700 block mb-1">Complete Notes:</span>
                                    <div class="bg-white rounded p-2 text-gray-600 whitespace-pre-line" x-text="review?.review_notes || 'No notes available'"></div>
                                </div>

                                <!-- Additional Actions in Expanded View -->
                                <div class="flex items-center space-x-3 pt-2">
                                    <button @click="review && review.review_id ? toggleRowExpansion(review.review_id) : null"
                                            class="text-blue-600 hover:text-blue-800 text-sm underline">
                                        Hide Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>

            <!-- Mobile Empty/Error State -->
            <template x-if="!isLoading && (error || !reviews || reviews.length === 0)">
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i x-show="error" class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                        <i x-show="!error" class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2" x-text="error ? 'Error Loading Reviews' : 'No reviews found'"></h3>
                    <p class="text-gray-500 text-sm mb-4" x-text="error || 'There are no reviews matching your current filters.'"></p>
                    <div class="flex flex-col space-y-2">
                        <button x-show="error" @click="loadReviews()" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                            Try Again
                        </button>
                        <button x-show="!error" @click="resetFilters()" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                            Reset Filters
                        </button>
                        <button x-show="!error" @click="setPeriod('all')" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 transition">
                            Show All Time
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block" x-show="!isMobileView">
            <!-- Table View Controls -->
            <div class="px-4 py-2 bg-white border-b flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button @click="expandAllRows()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                        Expand All
                    </button>
                    <button @click="collapseAllRows()" class="text-sm text-gray-600 hover:text-gray-800 underline">
                        Collapse All
                    </button>
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center space-x-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="currentPage === 1"
                            class="px-2 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <template x-for="page in getVisiblePages()" :key="page">
                        <button @click="goToPage(page)"
                                :class="page === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'"
                                class="px-3 py-1 text-sm border rounded">
                            <span x-text="page"></span>
                        </button>
                    </template>

                    <button @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-2 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto scrollbar-smooth">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap w-12">
                            <span class="sr-only">Expand</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                            <span class="hidden sm:inline">Reviewer</span>
                            <span class="sm:hidden">Reviewer</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                            <span class="hidden sm:inline">Agent Reviewed</span>
                            <span class="sm:hidden">Agent</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                            <span class="hidden sm:inline">Context</span>
                            <span class="sm:hidden">Context</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                            <span class="hidden md:inline">Classification</span>
                            <span class="md:hidden">Class</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Rating</th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                            <span class="hidden lg:inline">Notes</span>
                            <span class="lg:hidden">Notes</span>
                        </th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                        <th scope="col" class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="isLoading">
                        <!-- Skeleton Loading for Desktop Table -->
                        <template x-for="n in 8" :key="n">
                            <tr class="animate-pulse">
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-28"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-6 bg-gray-200 rounded w-12"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-32"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap">
                                    <div class="h-8 bg-gray-200 rounded w-20"></div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!isLoading && (error || !reviews || reviews.length === 0)">
                        <tr>
                            <td colspan="9" class="text-center py-10 text-gray-500">
                                <svg x-show="error" class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <svg x-show="!error" class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <p class="mt-2 text-lg font-semibold text-gray-700" x-text="error ? 'Error Loading Reviews' : 'No reviews found'"></p>
                                <p class="text-sm text-gray-500 mb-4" x-text="error || 'There are no reviews matching your current filters.'"></p>
                                <div class="flex justify-center space-x-3">
                                    <button x-show="error" @click="loadReviews()" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                                        Try Again
                                    </button>
                                    <button x-show="!error" @click="resetFilters()" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                                        Reset Filters
                                    </button>
                                    <button x-show="!error" @click="setPeriod('all')" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 transition">
                                        Show All Time
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!isLoading && !error && reviews && reviews.length > 0">
                        <template x-for="review in reviews" :key="review?.review_id || 'unknown'">
                            <tr class="hover:bg-gray-50 transition" :class="{'bg-blue-50': review && review.review_id && isRowExpanded(review.review_id)}">
                                <!-- Expand/Collapse Button -->
                                <td class="px-2 py-4 text-center">
                                    <button @click="review && review.review_id ? toggleRowExpansion(review.review_id) : null"
                                            class="text-gray-400 hover:text-gray-600 transition-colors"
                                            :class="{'rotate-90': review && review.review_id && isRowExpanded(review.review_id)}">
                                        <i class="fas fa-chevron-right text-sm"></i>
                                    </button>
                                </td>

                                <!-- Reviewer Name -->
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="group relative">
                                        <div class="font-medium text-gray-900 truncate max-w-24 sm:max-w-32 md:max-w-40"
                                             x-text="review?.reviewer_name || 'Unknown'"
                                             :title="review?.reviewer_name || 'Unknown'"></div>
                                        <!-- Enhanced tooltip for long names -->
                                        <div class="absolute z-20 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-2 px-3 -top-10 left-0 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap max-w-xs truncate">
                                            <span x-text="review?.reviewer_name || 'Unknown'"></span>
                                            <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Agent Name -->
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="group relative">
                                        <div class="font-medium text-gray-800 truncate max-w-24 sm:max-w-32 md:max-w-40"
                                             x-text="review?.agent_name || 'N/A'"
                                             :title="review?.agent_name || 'N/A'"></div>
                                        <!-- Enhanced tooltip for long names -->
                                        <div class="absolute z-20 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-2 px-3 -top-10 left-0 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap max-w-xs truncate">
                                            <span x-text="review?.agent_name || 'N/A'"></span>
                                            <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Context -->
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <a :href="review ? getContextUrl(review) : '#'" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors">
                                        <span x-text="review ? getContextText(review) : 'N/A'"></span>
                                    </a>
                                </td>

                                <!-- Classification -->
                                <td class="px-2 md:px-6 py-4 text-sm text-gray-600">
                                    <div class="group relative">
                                        <span class="block truncate max-w-32 md:max-w-48"
                                              x-text="review ? getClassificationText(review) : 'N/A'"
                                              :title="review ? getClassificationText(review) : 'N/A'"></span>
                                        <!-- Enhanced tooltip for long classifications -->
                                        <div class="absolute z-20 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-2 px-3 -top-12 left-0 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap max-w-sm">
                                            <span x-text="review ? getClassificationText(review) : 'N/A'"></span>
                                            <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Rating -->
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm">
                                     <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="review ? getRatingColor(review.rating) : 'bg-gray-100 text-gray-600'" x-text="review ? (review.rating + ' / 100') : 'N/A'"></span>
                                </td>

                                <!-- Notes -->
                                <td class="px-2 md:px-6 py-4 text-sm text-gray-600">
                                    <div class="group relative">
                                        <span class="block truncate max-w-24 sm:max-w-32 md:max-w-40"
                                              x-text="review?.review_notes || '-'"
                                              :title="review?.review_notes || '-'"></span>
                                        <!-- Enhanced tooltip for long notes -->
                                        <div class="absolute z-20 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-2 px-3 -top-12 left-0 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 max-w-sm">
                                            <div class="max-h-32 overflow-y-auto">
                                                <span x-text="review?.review_notes || 'No notes available'"></span>
                                            </div>
                                            <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="review?.reviewed_at ? new Date(review.reviewed_at).toLocaleDateString() : 'N/A'"></td>
                                <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- Discussion Link (existing) -->
                                        <a :href="review && review.discussion_id ? `<?= URLROOT ?>/discussions#discussion-${review.discussion_id}` : '#'" x-show="review && review.open_discussion_count > 0" class="text-yellow-600 hover:text-yellow-900 flex items-center">
                                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM9 9H5v2h4V9zm6-2H5V5h10v2z"></path></svg>
                                            <span class="hidden lg:inline">Open Discussion</span>
                                        </a>

                                        <!-- Admin Actions -->
                                        <template x-if="canManageReviews()">
                                            <div class="flex items-center space-x-1">
                                                <!-- Edit Review -->
                                                <button @click="review ? editReview(review) : null" class="text-indigo-600 hover:text-indigo-900" title="Edit Review">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>

                                                <!-- Delete Review -->
                                                <button @click="review ? confirmDeleteReview(review) : null" class="text-red-600 hover:text-red-900" title="Delete Review">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- If no actions available, show dash -->
                                        <template x-if="!canManageReviews() && (review ? review.open_discussion_count == 0 : true)">
                                            <span class="text-gray-400">-</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>

                            <!-- Expanded Row Details -->
                            <tr x-show="review && review.review_id && isRowExpanded(review.review_id)" x-transition class="bg-blue-25">
                                <td colspan="9" class="px-4 py-6">
                                    <div class="bg-white rounded-lg border p-6 shadow-sm">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            <!-- Left Column - Basic Info -->
                                            <div class="space-y-4">
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Review Details</h4>
                                                    <div class="space-y-2 text-sm">
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-600">Reviewer:</span>
                                                            <span class="font-medium" x-text="review?.reviewer_name || 'Unknown'"></span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-600">Agent:</span>
                                                            <span class="font-medium" x-text="review?.agent_name || 'N/A'"></span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-600">Rating:</span>
                                                            <span :class="review ? getRatingTextColor(review.rating) : 'text-gray-500'" class="font-bold" x-text="review ? (review.rating + '/100') : 'N/A'"></span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-600">Date:</span>
                                                            <span class="font-medium" x-text="review?.reviewed_at ? new Date(review.reviewed_at).toLocaleDateString() : 'N/A'"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Context & Classification -->
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Context & Classification</h4>
                                                    <div class="space-y-2 text-sm">
                                                        <div>
                                                            <span class="text-gray-600">Context:</span>
                                                            <a :href="review ? getContextUrl(review) : '#'" target="_blank"
                                                               class="text-blue-600 hover:text-blue-800 font-medium hover:underline ml-2">
                                                                <span x-text="review ? getContextText(review) : 'N/A'"></span>
                                                            </a>
                                                        </div>
                                                        <div x-show="review && getClassificationText(review) !== '-'">
                                                            <span class="text-gray-600">Classification:</span>
                                                            <span class="font-medium ml-2" x-text="review ? getClassificationText(review) : 'N/A'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right Column - Notes & Actions -->
                                            <div class="space-y-4">
                                                <!-- Review Notes -->
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Review Notes</h4>
                                                    <div class="bg-gray-50 rounded-lg p-3">
                                                        <p x-show="review && review.review_notes" class="text-sm text-gray-700 whitespace-pre-line"
                                                           x-text="review?.review_notes || 'No notes available'"></p>
                                                        <p x-show="!review || !review.review_notes" class="text-sm text-gray-500 italic">
                                                            No notes provided for this review.
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Actions</h4>
                                                    <div class="flex items-center space-x-3">
                                                        <!-- Discussion Link -->
                                                        <a :href="review && review.discussion_id ? `<?= URLROOT ?>/discussions#discussion-${review.discussion_id}` : '#'"
                                                           x-show="review && review.open_discussion_count > 0"
                                                           class="inline-flex items-center px-3 py-2 text-sm bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition">
                                                            <i class="fas fa-comments mr-2"></i>
                                                            View Discussion
                                                        </a>

                                                        <!-- Admin Actions -->
                                                        <template x-if="canManageReviews()">
                                                            <div class="flex items-center space-x-2">
                                                                <button @click="review ? editReview(review) : null"
                                                                        class="inline-flex items-center px-3 py-2 text-sm bg-indigo-100 text-indigo-800 rounded-lg hover:bg-indigo-200 transition">
                                                                    <i class="fas fa-edit mr-2"></i>
                                                                    Edit
                                                                </button>
                                                                <button @click="review ? confirmDeleteReview(review) : null"
                                                                        class="inline-flex items-center px-3 py-2 text-sm bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition">
                                                                    <i class="fas fa-trash mr-2"></i>
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </template>

                                                        <!-- No actions message -->
                                                        <div x-show="!canManageReviews() && (review ? review.open_discussion_count == 0 : true)"
                                                             class="text-sm text-gray-500 italic">
                                                            No actions available
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Edit Review Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Review</h3>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Review Details -->
                    <div class="bg-gray-50 p-3 rounded-lg text-sm">
                        <p><strong>Reviewer:</strong> <span x-text="editingReview.reviewer_name"></span></p>
                        <p><strong>Context:</strong> <span x-text="getContextText(editingReview)"></span></p>
                        <p><strong>Date:</strong> <span x-text="editingReview.reviewed_at ? new Date(editingReview.reviewed_at).toLocaleDateString() : ''"></span></p>
                    </div>
                    
                    <!-- Rating Input -->
                    <div>
                        <label for="edit_rating" class="block text-sm font-medium text-gray-700 mb-2">Rating (0-100)</label>
                        <input type="number" id="edit_rating" x-model="editForm.rating" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter rating">
                        <div x-show="editForm.errors.rating" x-text="editForm.errors.rating" class="text-red-500 text-sm mt-1"></div>
                    </div>
                    
                    <!-- Notes Input -->
                    <div>
                        <label for="edit_notes" class="block text-sm font-medium text-gray-700 mb-2">Review Notes</label>
                        <textarea id="edit_notes" x-model="editForm.review_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter review notes"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                    <button @click="submitEditReview()" :disabled="editForm.loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                        <svg x-show="editForm.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="editForm.loading ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-red-600">Delete Review</h3>
                    <button @click="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Warning Icon -->
                    <div class="flex items-center justify-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this review? This action cannot be undone.</p>
                        
                        <!-- Review Details -->
                        <div class="bg-red-50 p-3 rounded-lg text-sm text-left">
                            <p><strong>Reviewer:</strong> <span x-text="deletingReview.reviewer_name"></span></p>
                            <p><strong>Context:</strong> <span x-text="getContextText(deletingReview)"></span></p>
                            <p><strong>Rating:</strong> <span x-text="deletingReview.rating + '/100'"></span></p>
                            <p><strong>Date:</strong> <span x-text="deletingReview.reviewed_at ? new Date(deletingReview.reviewed_at).toLocaleDateString() : ''"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                    <button @click="submitDeleteReview()" :disabled="deleteForm.loading" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                        <svg x-show="deleteForm.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="deleteForm.loading ? 'Deleting...' : 'Delete Review'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>


<script type="text/javascript">
// Define categories data safely
window.ticketCategories = [];
try {
    const rawCategoriesData = <?= json_encode($data['ticket_categories'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.ticketCategories = rawCategoriesData || [];
} catch (e) {
    window.ticketCategories = [];
}

// Define user session data safely
window.currentUser = {};
window.userRole = 'guest';
window.userId = 0;
try {
    window.currentUser = <?= json_encode($data['current_user'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.userRole = <?= json_encode($data['user_role'] ?? 'guest', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.userId = <?= json_encode($data['user_id'] ?? 0, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
} catch (e) {
    window.currentUser = {};
    window.userRole = 'guest';
    window.userId = 0;
}

function reviewsPage() {
  return {
    // Simple state variables - ensure reviews is always an array
    reviews: [],
    isLoading: true,
    error: null,
    searchQuery: '',
    searchSuggestions: [],
    activePeriod: 'all', // Track which period filter is active

    // Filters
    filters: {
        start_date: '',
        end_date: '',
        context_type: '',
        category_id: '',
        subcategory_id: '',
        code_id: '',
        agent_id: '',
    },

    // Pagination
    currentPage: 1,
    itemsPerPage: 25,
    totalPages: 1,
    totalItems: 0,

    // UI state
    tableViewMode: 'compact',
    expandedRows: new Set(),
    selectedReviewIndex: 0,
    isMobileView: false,

    // Categories for filters
    categories: window.ticketCategories || [],
    subcategories: [],
    codes: [],
    selectedCategory: '',
    selectedSubcategory: '',

    // Date picker
    flatpickrInstance: null,

    // Modal states
    showEditModal: false,
    showDeleteModal: false,
    editingReview: {},
    deletingReview: {},
    editForm: {
        rating: '',
        review_notes: '',
        loading: false,
        errors: {}
    },
    deleteForm: {
        loading: false
    },

    // User session data
    currentUser: window.currentUser || {},
    userRole: window.userRole || 'guest',
    userId: window.userId || 0,

    init() {
        // Ensure reviews is always an array
        if (!Array.isArray(this.reviews)) {
            this.reviews = [];
        }
        
        // Read URL parameters and set initial filter values
        const urlParams = new URLSearchParams(window.location.search);
        this.filters.agent_id = urlParams.get('agent_id') || '';

        // Set selected values for dropdowns
        this.selectedCategory = urlParams.get('category_id') || '';
        this.selectedSubcategory = urlParams.get('subcategory_id') || '';

        // Initialize date picker with a delay to ensure DOM is ready
        this.$nextTick(() => {
            // Add a small delay to ensure the DOM is fully rendered
            setTimeout(() => {
                try {
                    // Check if flatpickr is available
                    if (typeof flatpickr === 'undefined') {
                        console.warn('Flatpickr not found! Loading flatpickr from CDN...');
                        // Dynamically load flatpickr if not available
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
                        document.head.appendChild(link);
                        
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js';
                        document.head.appendChild(script);
                        
                        script.onload = () => this.initializeDatepicker();
                    } else {
                        this.initializeDatepicker();
                    }
                } catch (e) {
                    console.error('Error initializing flatpickr:', e);
                }
            }, 100);
        });
        
    },
    
    // Separate method for initializing the datepicker
    initializeDatepicker() {
        const datePickerElement = this.$refs.daterangepicker;
        if (!datePickerElement) {
            console.warn('Date picker element not found in DOM');
            return;
        }
        
        try {
            this.flatpickrInstance = flatpickr(datePickerElement, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                onChange: (selectedDates) => {
                    if (selectedDates.length === 2) {
                        this.filters.start_date = selectedDates[0] ? this.formatDate(selectedDates[0]) : '';
                        this.filters.end_date = selectedDates[1] ? this.formatDate(selectedDates[1]) : '';
                    }
                }
            });
            console.log('Flatpickr initialized successfully');
        } catch (e) {
            console.error('Error in flatpickr initialization:', e);
        }

        // Simple watchers for category changes
        this.$watch('selectedCategory', (newVal) => {
            this.filters.category_id = newVal;
            this.onCategoryChange();
        });

        this.$watch('selectedSubcategory', (newVal) => {
            this.filters.subcategory_id = newVal;
            this.onSubcategoryChange();
        });

        // Detect mobile view
        this.checkMobileView();
        window.addEventListener('resize', () => this.checkMobileView());

        // Load initial data immediately
        console.log('Initializing reviews page, loading data...');
        this.loadReviews();
    },
    
    // Simple load reviews function
    loadReviews(resetPage = false) {
        this.isLoading = true;

        // Only reset to first page if explicitly requested (for new searches/filters)
        if (resetPage) {
            this.currentPage = 1;
        }

        const params = new URLSearchParams({
            q: this.searchQuery,
            page: this.currentPage,
            per_page: this.itemsPerPage,
            ...this.filters
        });

        fetch(`<?= URLROOT ?>/quality/search_reviews_api?${params}`)
            .then(res => res.json())
            .then(data => {
                this.isLoading = false;
                this.error = null;
                if (data.success) {
                    this.reviews = Array.isArray(data.data) ? data.data : [];
                    this.totalItems = data.pagination?.total || 0;
                    this.totalPages = data.pagination?.total_pages || 1;
                    console.log('Reviews loaded successfully:', this.reviews.length, 'items');
                } else {
                    console.error('API Error:', data.error);
                    this.reviews = [];
                    this.error = data.error || 'Failed to load reviews';
                }
            })
            .catch(err => {
                this.isLoading = false;
                console.error('Network Error:', err);
                this.reviews = [];
                this.error = 'Network error occurred';
            });
    },

    // Simple search function
    searchReviews() {
        this.loadReviews(true); // Reset to first page when searching
    },

    setPeriod(period) {
        // Update active period state
        this.activePeriod = period;

        if (period === 'all') {
            this.filters.start_date = '';
            this.filters.end_date = '';
            try {
                if (this.flatpickrInstance && this.flatpickrInstance.clear) {
                    this.flatpickrInstance.clear();
                }
            } catch (e) {
                console.error('Error clearing flatpickr');
            }
        } else {
            let startDate = new Date();
            let endDate = new Date();

            if (period === 'today') {
                // Keep current dates
            } else if (period === 'week') {
                startDate.setDate(startDate.getDate() - 7);
            } else if (period === 'month') {
                startDate.setMonth(startDate.getMonth() - 1);
            }

            this.filters.start_date = this.formatDate(startDate);
            this.filters.end_date = this.formatDate(endDate);

            try {
                if (this.flatpickrInstance && this.flatpickrInstance.setDate) {
                    this.flatpickrInstance.setDate([this.filters.start_date, this.filters.end_date]);
                }
            } catch (e) {
                console.error('Error setting flatpickr date');
            }
        }

        this.loadReviews();
    },

    // Legacy function - kept for backward compatibility
    fetchReviews() {
        this.loadReviews();
    },

    // Simple search functions
    filterReviews() {
        // Simple debounce
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.searchReviews();
            this.loadSearchSuggestions();
        }, 300);
    },

    clearSearch() {
        this.searchQuery = '';
        clearTimeout(this.searchTimeout);
        this.loadReviews(true); // Reset to first page when clearing search
    },

    // Load search suggestions from API
    loadSearchSuggestions() {
        if (!this.searchQuery || this.searchQuery.length < 2) {
            this.searchSuggestions = [];
            return;
        }

        fetch(`<?= URLROOT ?>/quality/search_suggestions_api?q=${encodeURIComponent(this.searchQuery)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.searchSuggestions = data.suggestions || [];
                } else {
                    this.searchSuggestions = [];
                }
            })
            .catch(err => {
                console.error('Error loading suggestions:', err);
                this.searchSuggestions = [];
            });
    },

    applySearchSuggestion(suggestion) {
        this.searchQuery = suggestion.replace('Ticket #', '');
        this.filterReviews();
    },

    getSearchSuggestions() {
        return this.searchSuggestions || [];
    },

    // Simple summary functions
    getAverageRating() {
        if (!this.reviews || this.reviews.length === 0) return '0.0';
        const sum = (this.reviews || []).reduce((acc, review) => acc + (parseFloat(review?.rating) || 0), 0);
        return (sum / this.reviews.length).toFixed(1);
    },

    getUniqueAgentsCount() {
        if (!this.reviews || this.reviews.length === 0) return 0;
        const uniqueAgents = new Set();
        (this.reviews || []).forEach(review => {
            if (review && review.agent_name) {
                uniqueAgents.add(review.agent_name);
            }
        });
        return uniqueAgents.size;
    },
    
    // Simple utility functions
    resetFilters() {
        this.filters = {
            start_date: '',
            end_date: '',
            context_type: '',
            category_id: '',
            subcategory_id: '',
            code_id: '',
            agent_id: ''
        };
        this.selectedCategory = '';
        this.selectedSubcategory = '';
        this.searchQuery = '';

        try {
            if (this.flatpickrInstance && this.flatpickrInstance.clear) {
                this.flatpickrInstance.clear();
            }
        } catch (e) {
            console.error('Error clearing flatpickr');
        }

        this.loadReviews(true); // Reset to first page when resetting filters
    },

    formatDate(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    },

    onCategoryChange() {
        this.selectedSubcategory = '';
        this.filters.subcategory_id = '';
        this.filters.code_id = '';

        const category = this.categories.find(c => c.id == this.selectedCategory);
        this.subcategories = category ? (category.subcategories || []) : [];
        this.codes = [];
    },

    onSubcategoryChange() {
        this.filters.code_id = '';
        const subcategory = this.subcategories.find(s => s.id == this.selectedSubcategory);
        this.codes = subcategory ? (subcategory.codes || []) : [];
    },

    getContextUrl(review) {
        const root = '<?= URLROOT ?>';
        if (review.context_type === 'Ticket' && review.ticket_id) {
            return `${root}/tickets/view/${review.ticket_id}`;
        }
        if (review.context_type === 'Call' && review.driver_id) {
            return `${root}/drivers/details/${review.driver_id}`;
        }
        return '#';
    },

    getContextText(review) {
        if (review.context_type === 'Ticket') {
            return `Ticket #${review.ticket_number || review.ticket_id}`;
        }
        if (review.context_type === 'Call') {
            return `Call to ${review.driver_name || 'Driver #' + review.driver_id}`;
        }
        return 'N/A';
    },

    getClassificationText(review) {
        if (!review.category_name) return '-';
        let parts = [review.category_name];
        if (review.subcategory_name) parts.push(review.subcategory_name);
        if (review.code_name) parts.push(review.code_name);
        return parts.join(' > ');
    },

    getRatingColor(rating) {
        if (rating >= 90) return 'bg-green-100 text-green-800';
        if (rating >= 70) return 'bg-yellow-100 text-yellow-800';
        if (rating >= 50) return 'bg-orange-100 text-orange-800';
        return 'bg-red-100 text-red-800';
    },

    getRatingBorderColor(rating) {
        if (rating >= 90) return 'border-green-400';
        if (rating >= 70) return 'border-yellow-400';
        if (rating >= 50) return 'border-orange-400';
        return 'border-red-400';
    },

    getRatingTextColor(rating) {
        if (rating >= 90) return 'text-green-600';
        if (rating >= 70) return 'text-yellow-600';
        if (rating >= 50) return 'text-orange-600';
        return 'text-red-600';
    },

    checkMobileView() {
        this.isMobileView = window.innerWidth < 768;
    },

    getActiveFiltersCount() {
        let count = 0;
        if (this.filters.start_date || this.filters.end_date) count++;
        if (this.filters.context_type) count++;
        if (this.selectedCategory) count++;
        if (this.selectedSubcategory) count++;
        if (this.filters.code_id) count++;
        if (this.filters.agent_id) count++;
        return count;
    },

    clearAllFilters() {
        this.resetFilters();
    },

    // Simple row expansion functions
    toggleRowExpansion(reviewId) {
        if (this.expandedRows.has(reviewId)) {
            this.expandedRows.delete(reviewId);
        } else {
            this.expandedRows.add(reviewId);
        }
    },

    isRowExpanded(reviewId) {
        return this.expandedRows.has(reviewId);
    },

    expandAllRows() {
        (this.reviews || []).forEach(review => {
            if (review && review.review_id) {
                this.expandedRows.add(review.review_id);
            }
        });
    },

    collapseAllRows() {
        this.expandedRows.clear();
    },

    // Simple pagination functions
    getCurrentPageStart() {
        if (this.totalItems === 0) return 0;
        return (this.currentPage - 1) * this.itemsPerPage + 1;
    },

    getCurrentPageEnd() {
        const end = this.currentPage * this.itemsPerPage;
        return Math.min(end, this.totalItems);
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.loadReviews();
        }
    },

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadReviews();
        }
    },

    goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
            this.loadReviews();
        }
    },

    // Simple table functions
    setTableViewMode(mode) {
        this.tableViewMode = mode;
        this.expandedRows.clear();
    },

    changeItemsPerPage() {
        this.itemsPerPage = parseInt(this.itemsPerPage);
        this.loadReviews(true); // Reset to first page when changing items per page
    },

    getVisiblePages() {
        const pages = [];
        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }

        return pages;
    },

    // Check if user can manage reviews (admin, quality_manager, developer roles)
    canManageReviews() {
        const adminRoles = ['admin', 'Quality', 'developer'];
        return adminRoles.includes(this.userRole);
    },

    // Open edit modal
    editReview(review) {
        if (!this.canManageReviews()) {
            this.showToast('You do not have permission to edit reviews.', 'error');
            return;
        }
        
        this.editingReview = { ...review };
        this.editForm.rating = review.rating;
        this.editForm.review_notes = review.review_notes || '';
        this.editForm.errors = {};
        this.showEditModal = true;
    },

    // Close edit modal
    closeEditModal() {
        this.showEditModal = false;
        this.editingReview = {};
        this.editForm = {
            rating: '',
            review_notes: '',
            loading: false,
            errors: {}
        };
    },

    // Submit edit review
    submitEditReview() {
        // Reset errors
        this.editForm.errors = {};
        
        // Validate
        const ratingNum = Number(this.editForm.rating);
        if (!Number.isFinite(ratingNum) || ratingNum < 0 || ratingNum > 100) {
            this.editForm.errors.rating = 'Rating must be between 0 and 100';
            return;
        }
        
        this.editForm.loading = true;
        
        const formData = new FormData();
        formData.append('review_id', this.editingReview.review_id);
        formData.append('rating', ratingNum);
        formData.append('review_notes', this.editForm.review_notes);
        
        fetch(`<?= URLROOT ?>/quality/update_review`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text(); // Get as text first
        })
        .then(text => {
            this.editForm.loading = false;
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid response. Expected JSON but got: ' + text.substring(0, 100));
            }
            
            if (data.success) {
                this.showToast('Review updated successfully!', 'success');
                this.closeEditModal();
                this.fetchReviews(); // Refresh the list
            } else {
                this.showToast('Error updating review: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            this.editForm.loading = false;
            console.error('Update error:', error);
            this.showToast('Network error while updating review. Please check your connection and try again.', 'error');
        });
    },

    // Open delete modal
    confirmDeleteReview(review) {
        if (!this.canManageReviews()) {
            this.showToast('You do not have permission to delete reviews.', 'error');
            return;
        }
        
        this.deletingReview = { ...review };
        this.showDeleteModal = true;
    },

    // Close delete modal
    closeDeleteModal() {
        this.showDeleteModal = false;
        this.deletingReview = {};
        this.deleteForm.loading = false;
    },

    // Submit delete review
    submitDeleteReview() {
        this.deleteForm.loading = true;
        
        const formData = new FormData();
        formData.append('review_id', this.deletingReview.review_id);
        
        fetch(`<?= URLROOT ?>/quality/delete_review`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text(); // Get as text first
        })
        .then(text => {
            this.deleteForm.loading = false;
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid response. Expected JSON but got: ' + text.substring(0, 100));
            }
            
            if (data.success) {
                this.showToast('Review deleted successfully!', 'success');
                this.closeDeleteModal();
                this.fetchReviews(); // Refresh the list
            } else {
                this.showToast('Error deleting review: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            this.deleteForm.loading = false;
            console.error('Delete error:', error);
            this.showToast('Network error while deleting review. Please check your connection and try again.', 'error');
        });
    },

    // Show toast notification
    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 max-w-sm px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        // Set colors based on type
        switch(type) {
            case 'success':
                toast.className += ' bg-green-500 text-white';
                break;
            case 'error':
                toast.className += ' bg-red-500 text-white';
                break;
            case 'warning':
                toast.className += ' bg-yellow-500 text-white';
                break;
            default:
                toast.className += ' bg-blue-500 text-white';
        }
        
        toast.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 5000);
    }
  };
}

// Ensure Alpine.js is loaded and function is available
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking Alpine.js...');
    
    // Check if Alpine.js is loaded
    if (typeof Alpine === 'undefined') {
        console.warn('Alpine.js not found! Loading Alpine.js from CDN...');
        // Load Alpine.js dynamically if it's not available
        const alpineScript = document.createElement('script');
        alpineScript.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
        alpineScript.defer = true;
        document.head.appendChild(alpineScript);
        
        // Initialize after loading
        alpineScript.onload = function() {
            console.log('Alpine.js loaded successfully');
            if (typeof reviewsPage === 'function') {
                window.Alpine.data('reviewsPage', reviewsPage);
            }
        };
    } else {
        console.log('Alpine.js loaded successfully');
    }
});

// Ensure function is globally available
window.reviewsPage = reviewsPage;
// Make sure Alpine.js can access the function
document.addEventListener('alpine:init', () => {
    window.Alpine.data('reviewsPage', reviewsPage);
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>