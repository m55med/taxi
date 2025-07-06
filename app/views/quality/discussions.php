<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen" x-data="discussionsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Discussions</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
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
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <select id="status" x-model="filters.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
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
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button @click="resetFilters" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</button>
            <button @click="fetchDiscussions()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
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
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Replies</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading">
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500">
                            <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <p class="mt-2">Loading Discussions...</p>
                        </td>
                    </tr>
                </template>

                <template x-if="!isLoading && discussions.length === 0">
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500">
                             <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            <p class="mt-2 font-semibold">No discussions found.</p>
                        </td>
                    </tr>
                </template>

                <template x-for="discussion in discussions" :key="discussion.discussion_id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="discussion.reason"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="discussion.opener_name"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <a :href="getContextUrl(discussion)" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                <span x-text="getContextText(discussion)"></span>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="discussion.replies_count"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                             <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="discussion.status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="discussion.status"></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="new Date(discussion.created_at).toLocaleDateString()"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a :href="`<?= URLROOT ?>/discussions#discussion-${discussion.discussion_id}`" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function discussionsPage() {
  return {
    discussions: [],
    isLoading: true,
    filters: {
        start_date: '',
        end_date: '',
        context_type: '',
        status: '',
        category_id: '',
        subcategory_id: '',
        code_id: '',
    },
    // For cascading dropdowns
    categories: <?= json_encode($data['ticket_categories']) ?>,
    subcategories: [],
    codes: [],
    selectedCategory: '',
    selectedSubcategory: '',
    flatpickrInstance: null,

    init() {
        this.flatpickrInstance = flatpickr(this.$refs.daterangepicker, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            onChange: (selectedDates) => {
                if (selectedDates.length === 2) {
                    this.filters.start_date = selectedDates[0] ? this.formatDate(selectedDates[0]) : '';
                    this.filters.end_date = selectedDates[1] ? this.formatDate(selectedDates[1]) : '';
                }
            }
        });

        this.$watch('selectedCategory', () => {
            this.filters.category_id = this.selectedCategory;
        });

        this.$watch('selectedSubcategory', () => {
             this.filters.subcategory_id = this.selectedSubcategory;
        });

        this.fetchDiscussions();
    },

    fetchDiscussions() {
      this.isLoading = true;
      const params = new URLSearchParams(this.filters).toString();
      fetch(`<?= URLROOT ?>/quality/get_discussions_api?${params}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            alert('Error: ' + data.error);
            this.discussions = [];
          } else {
            this.discussions = data;
          }
          this.isLoading = false;
        })
        .catch(err => {
          console.error(err);
          alert('An error occurred while fetching data.');
          this.isLoading = false;
        });
    },

    resetFilters() {
        this.filters = { start_date: '', end_date: '', context_type: '', status: '', category_id: '', subcategory_id: '', code_id: '' };
        this.selectedCategory = '';
        this.selectedSubcategory = '';
        this.subcategories = [];
        this.codes = [];
        this.flatpickrInstance.clear();
        this.fetchDiscussions();
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
        this.subcategories = category ? category.subcategories : [];
    },
    
    onSubcategoryChange() {
        this.filters.code_id = '';
        const subcategory = this.subcategories.find(s => s.id == this.selectedSubcategory);
        this.codes = subcategory ? subcategory.codes : [];
    },

    getContextUrl(discussion) {
        const root = '<?= URLROOT ?>';
        if (discussion.reviewable_type.includes('TicketDetail') && discussion.ticket_id) {
            return `${root}/tickets/view/${discussion.ticket_id}`;
        }
        if (discussion.reviewable_type.includes('DriverCall') && discussion.driver_id) {
            return `${root}/drivers/details/${discussion.driver_id}`;
        }
        return '#';
    },

    getContextText(discussion) {
        if (discussion.reviewable_type.includes('TicketDetail')) {
            return `Ticket #${discussion.ticket_number || discussion.ticket_id}`;
        }
        if (discussion.reviewable_type.includes('DriverCall')) {
            return `Call to ${discussion.driver_name || 'Driver #' + discussion.driver_id}`;
        }
        return 'N/A';
    },
  };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 