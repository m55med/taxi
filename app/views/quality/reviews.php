<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>

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
</style>

<div class="p-8 bg-gray-100 min-h-screen" x-data="reviewsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Reviews</h1>
    
    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Filters</h2>
            <!-- Quick Date Filter Buttons -->
            <div class="flex items-center space-x-2">
                <button @click="setPeriod('all')" :class="{'bg-blue-600 text-white': activePeriod === 'all', 'bg-gray-200 text-gray-700': activePeriod !== 'all'}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300">All Time</button>
                <button @click="setPeriod('today')" :class="{'bg-blue-600 text-white': activePeriod === 'today', 'bg-gray-200 text-gray-700': activePeriod !== 'today'}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300">Today</button>
                <button @click="setPeriod('week')" :class="{'bg-blue-600 text-white': activePeriod === 'week', 'bg-gray-200 text-gray-700': activePeriod !== 'week'}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300">Last 7 Days</button>
                <button @click="setPeriod('month')" :class="{'bg-blue-600 text-white': activePeriod === 'month', 'bg-gray-200 text-gray-700': activePeriod !== 'month'}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-300">This Month</button>
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
                <label for="context_type" class="block text-sm font-medium text-gray-600 mb-1">Context</label>
                <select id="context_type" x-model="filters.context_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="Ticket">Ticket</option>
                    <option value="Call">Call</option>
                </select>
            </div>
            
            <!-- Category -->
            <div>
                 <label for="category" class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                <select id="category" x-model="selectedCategory" @change="onCategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category.id">
                        <option :value="category.id" x-text="category.name"></option>
                    </template>
                </select>
            </div>
            
            <!-- Subcategory -->
            <div>
                <label for="subcategory" class="block text-sm font-medium text-gray-600 mb-1">Subcategory</label>
                <select id="subcategory" x-model="selectedSubcategory" @change="onSubcategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" :disabled="!selectedCategory">
                    <option value="">All Subcategories</option>
                    <template x-for="subcategory in subcategories" :key="subcategory.id">
                        <option :value="subcategory.id" x-text="subcategory.name"></option>
                    </template>
                </select>
            </div>
            
            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-600 mb-1">Code</label>
                <select id="code" x-model="filters.code_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" :disabled="!selectedSubcategory">
                     <option value="">All Codes</option>
                    <template x-for="code in codes" :key="code.id">
                        <option :value="code.id" x-text="code.name"></option>
                    </template>
                </select>
            </div>

            <!-- Agent Reviewed Filter -->
            <div>
                <label for="agent_id" class="block text-sm font-medium text-gray-600 mb-1">Agent Reviewed</label>
                <select id="agent_id" x-model="filters.agent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            <button @click="resetFilters" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</button>
            <button @click="fetchReviews()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
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
        <!-- Scroll indicator for mobile -->
        <div class="md:hidden px-4 py-2 bg-blue-50 border-b text-xs text-blue-700 text-center scroll-hint">
            <i class="fas fa-arrows-alt-h mr-1"></i>
            Swipe to scroll horizontally
        </div>

        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
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
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">
                                <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <p class="mt-2">Loading data...</p>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!isLoading && reviews.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                <p class="mt-2 text-lg font-semibold text-gray-700">No reviews found</p>
                                <p class="text-sm text-gray-500 mb-4">There are no reviews matching your current filters.</p>
                                <div class="flex justify-center space-x-3">
                                    <button @click="resetFilters()" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                                        Reset Filters
                                    </button>
                                    <button @click="setPeriod('all')" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 transition">
                                        Show All Time
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-for="review in reviews" :key="review.review_id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="review.reviewer_name"></td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800" x-text="review.agent_name || 'N/A'"></td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <a :href="getContextUrl(review)" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                    <span x-text="getContextText(review)"></span>
                                </a>
                            </td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                               <span x-text="getClassificationText(review)" class="block truncate max-w-xs" :title="getClassificationText(review)"></span>
                            </td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getRatingColor(review.rating)" x-text="review.rating + ' / 100'"></span>
                            </td>
                            <td class="px-2 md:px-6 py-4 text-sm text-gray-600">
                                <span class="block truncate max-w-xs" :title="review.review_notes" x-text="review.review_notes || '-'"></span>
                            </td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="new Date(review.reviewed_at).toLocaleDateString()"></td>
                            <td class="px-2 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Discussion Link (existing) -->
                                    <a :href="`<?= URLROOT ?>/discussions#discussion-${review.discussion_id}`" x-show="review.open_discussion_count > 0" class="text-yellow-600 hover:text-yellow-900 flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM9 9H5v2h4V9zm6-2H5V5h10v2z"></path></svg>
                                        <span class="hidden lg:inline">Open Discussion</span>
                                    </a>
                                    
                                    <!-- Admin Actions -->
                                    <template x-if="canManageReviews()">
                                        <div class="flex items-center space-x-1">
                                            <!-- Edit Review -->
                                            <button @click="editReview(review)" class="text-indigo-600 hover:text-indigo-900" title="Edit Review">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Delete Review -->
                                            <button @click="confirmDeleteReview(review)" class="text-red-600 hover:text-red-900" title="Delete Review">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    
                                    <!-- If no actions available, show dash -->
                                    <template x-if="!canManageReviews() && review.open_discussion_count == 0">
                                        <span class="text-gray-400">-</span>
                                    </template>
                                </div>
                            </td>
                        </tr>
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


<script>
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
    reviews: [],
    isLoading: true,
    filters: {
        start_date: '',
        end_date: '',
        context_type: '',
        category_id: '',
        subcategory_id: '',
        code_id: '',
        agent_id: '',
    },
    activePeriod: '',
    // For cascading dropdowns
    categories: window.ticketCategories || [],
    subcategories: [],
    codes: [],
    selectedCategory: '',
    selectedSubcategory: '',
    flatpickrInstance: null,
    // User session data
    currentUser: window.currentUser || {},
    userRole: window.userRole || 'guest',
    userId: window.userId || 0,
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
    // Debug info
    lastApiUrl: null,
    lastApiResponse: null,

    init() {
        // Read URL parameters and set initial filter values
        const urlParams = new URLSearchParams(window.location.search);
        this.filters.agent_id = urlParams.get('agent_id') || '';
        this.filters.start_date = urlParams.get('start_date') || '';
        this.filters.end_date = urlParams.get('end_date') || '';
        this.filters.context_type = urlParams.get('context_type') || '';
        this.filters.category_id = urlParams.get('category_id') || '';
        this.filters.subcategory_id = urlParams.get('subcategory_id') || '';
        this.filters.code_id = urlParams.get('code_id') || '';

        // Set selected values for dropdowns
        this.selectedCategory = this.filters.category_id;
        this.selectedSubcategory = this.filters.subcategory_id;

        // Load subcategories and codes if category/subcategory are provided
        if (this.filters.category_id) {
            this.onCategoryChange();
        }
        if (this.filters.subcategory_id) {
            // Small delay to ensure subcategories are loaded
            setTimeout(() => {
                this.onSubcategoryChange();
            }, 50);
        }

        // Use $nextTick to ensure the DOM element is ready for flatpickr
        this.$nextTick(() => {
        try {
            if (typeof flatpickr !== 'undefined') {
                this.flatpickrInstance = flatpickr(this.$refs.daterangepicker, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    onChange: (selectedDates) => {
                        if (selectedDates.length === 2) {
                            this.filters.start_date = selectedDates[0] ? this.formatDate(selectedDates[0]) : '';
                            this.filters.end_date = selectedDates[1] ? this.formatDate(selectedDates[1]) : '';
                        }
                        this.activePeriod = ''; // Clear active period button style
                    }
                });

                // Set flatpickr dates if they exist in URL
                if (this.filters.start_date && this.filters.end_date) {
                    this.flatpickrInstance.setDate([this.filters.start_date, this.filters.end_date]);
                }
                } else {
                    console.warn('Flatpickr library not found.');
            }
        } catch (e) {
                console.error('Error initializing flatpickr:', e);
        }
        });

        this.$watch('selectedCategory', () => {
            this.filters.category_id = this.selectedCategory;
        });

        this.$watch('selectedSubcategory', () => {
             this.filters.subcategory_id = this.selectedSubcategory;
        });

        // Load initial data immediately if agent_id is provided, otherwise set default period
        setTimeout(() => {
            if (this.filters.agent_id) {
                // If agent_id is provided, fetch reviews immediately
                this.fetchReviews();
            } else {
                // Otherwise set default to "All Time"
                this.setPeriod('all');
            }
        }, 100);
    },
    
    setPeriod(period) {
        this.activePeriod = period;
        
        if (period === 'all') {
            // Clear date filters
            this.filters.start_date = '';
            this.filters.end_date = '';
            try {
                if (this.flatpickrInstance && this.flatpickrInstance.clear) {
                    this.flatpickrInstance.clear();
                }
            } catch (e) {
                // Error clearing flatpickr in setPeriod
            }
        } else {
            let startDate = new Date();
            let endDate = new Date();

            if (period === 'today') {
                // No change needed for start/end date
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
                // Error setting flatpickr date
            }
        }
        
        this.fetchReviews();
    },

    fetchReviews() {
        this.isLoading = true;
        const params = new URLSearchParams(this.filters).toString();
        const apiUrl = `<?= URLROOT ?>/quality/get_reviews_api?${params}`;
        
        // Store for debugging
        this.lastApiUrl = apiUrl;
        this.lastApiResponse = 'Loading...';
        
        // Debug: Test session info endpoint (only on first load)
        if (!window.sessionDebugDone) {
            window.sessionDebugDone = true;
            fetch(`<?= URLROOT ?>/quality/get_reviews_api?debug_session=true`)
                .then(res => res.json())
                .then(sessionData => {
                    // Session data
                })
                .catch(e => { /* Could not fetch session debug */ });
        }
        
        fetch(apiUrl)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data && data.error) {
                    this.lastApiResponse = `Error: ${data.error}`;
                    let errorMessage = 'API Error: ' + data.error;
                    if (data.debug && data.debug.session_role) {
                        errorMessage += '\nYour role: ' + data.debug.session_role;
                        errorMessage += '\nUser ID: ' + (data.debug.session_user_id || 'Unknown');
                    }
                    alert(errorMessage);
                    this.reviews = [];
                } else if (Array.isArray(data)) {
                    // Direct array response (legacy format)
                    this.reviews = data;
                    this.lastApiResponse = `Success: ${data.length} reviews (direct array)`;
                } else if (data && Array.isArray(data.reviews)) {
                    // Structured response
                    this.reviews = data.reviews;
                    this.lastApiResponse = `${data.message || 'Success'}: ${data.reviews.length} reviews`;
                } else {
                    this.lastApiResponse = 'No valid data in response';
                    this.reviews = [];
                }
                this.isLoading = false;
            })
            .catch(err => {
                this.isLoading = false;
                this.reviews = [];
                this.lastApiResponse = `Network Error: ${err.message}`;
                
                // Only show alert if it's a real network error, not just empty results
                if (err.message.includes('HTTP error') || err.message.includes('Failed to fetch')) {
                    alert('An error occurred while fetching data: ' + err.message);
                }
            });
    },
    
    resetFilters() {
        this.filters = { start_date: '', end_date: '', context_type: '', category_id: '', subcategory_id: '', code_id: '', agent_id: '' };
        this.selectedCategory = '';
        this.selectedSubcategory = '';
        this.subcategories = [];
        this.codes = [];
        this.activePeriod = '';
        
        try {
            if (this.flatpickrInstance && this.flatpickrInstance.clear) {
                this.flatpickrInstance.clear();
            }
        } catch (e) {
            // Error clearing flatpickr
        }
        
        this.fetchReviews();
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
        this.codes = [];
        
        const category = this.categories.find(c => c.id == this.selectedCategory);
        this.subcategories = category ? (category.subcategories || []) : [];
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
        if (!this.editForm.rating || this.editForm.rating < 0 || this.editForm.rating > 100) {
            this.editForm.errors.rating = 'Rating must be between 0 and 100';
            return;
        }
        
        this.editForm.loading = true;
        
        const formData = new FormData();
        formData.append('review_id', this.editingReview.review_id);
        formData.append('rating', this.editForm.rating);
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
    // console.log('DOM loaded, checking Alpine.js...');
    // console.log('reviewsPage function available:', typeof reviewsPage === 'function');
    // console.log('Categories data available:', Array.isArray(window.ticketCategories));
    // console.log('Alpine available:', typeof Alpine !== 'undefined');
    
    // Don't manually start Alpine as it auto-starts with defer
    if (typeof Alpine !== 'undefined') {
        // console.log('Alpine.js loaded successfully');
    } else {
        // console.warn('Alpine.js not found');
    }
});

// Ensure function is globally available
window.reviewsPage = reviewsPage;
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>