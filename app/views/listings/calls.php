<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen" x-data="allCallsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Calls</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Contact Name or Phone)</label>
                <input type="text" id="search_term" x-model="filters.search_term" @keydown.enter="fetchCalls()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., John Doe or 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" x-ref="daterangepicker" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>
            <!-- User Involved -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-600 mb-1">User Involved</label>
                <select id="user_id" x-model="filters.user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <!-- Call Type -->
            <div>
                <label for="call_type" class="block text-sm font-medium text-gray-600 mb-1">Call Type</label>
                <select id="call_type" x-model="filters.call_type" @change="onCallTypeChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All</option>
                    <option value="incoming">Incoming</option>
                    <option value="outgoing">Outgoing</option>
                </select>
            </div>
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <select id="status" x-model="filters.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <optgroup label="Incoming">
                        <option value="answered">Answered</option>
                        <option value="missed">Missed</option>
                    </optgroup>
                    <optgroup label="Outgoing">
                        <option value="no_answer">No Answer</option>
                        <option value="answered">Answered</option>
                        <option value="busy">Busy</option>
                        <option value="not_available">Not Available</option>
                        <option value="wrong_number">Wrong Number</option>
                        <option value="rescheduled">Rescheduled</option>
                    </optgroup>
                </select>
            </div>
            <!-- Outgoing Call Filters -->
            <template x-if="filters.call_type === 'outgoing'">
                <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                        <select id="category" x-model="selectedCategory" @change="onCategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <template x-for="category in categories" :key="category.id"><option :value="category.id" x-text="category.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label for="subcategory" class="block text-sm font-medium text-gray-600 mb-1">Subcategory</label>
                        <select id="subcategory" x-model="selectedSubcategory" @change="onSubcategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-lg" :disabled="!selectedCategory">
                            <option value="">All Subcategories</option>
                            <template x-for="subcategory in subcategories" :key="subcategory.id"><option :value="subcategory.id" x-text="subcategory.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-600 mb-1">Code</label>
                        <select id="code" x-model="filters.code_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" :disabled="!selectedSubcategory">
                            <option value="">All Codes</option>
                            <template x-for="code in codes" :key="code.id"><option :value="code.id" x-text="code.name"></option></template>
                        </select>
                    </div>
                </div>
            </template>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button @click="resetFilters" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Reset</button>
            <button @click="fetchCalls()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading"><tr><td colspan="6" class="text-center py-10"><i class="fas fa-spinner fa-spin fa-2x text-blue-500"></i></td></tr></template>
                <template x-if="!isLoading && calls.length === 0"><tr><td colspan="6" class="text-center py-10 text-gray-500">No calls found.</td></tr></template>
                <template x-for="call in calls" :key="`${call.call_type}-${call.id}`">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full" :class="call.call_type === 'Incoming' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" x-text="call.call_type"></span></td>
                        <td class="px-6 py-4">
                             <template x-if="call.call_type === 'Outgoing' && call.contact_id">
                                 <a :href="`<?= URLROOT ?>/drivers/details/${call.contact_id}`" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline" x-text="call.contact_name"></a>
                             </template>
                             <template x-if="call.call_type === 'Incoming' && call.ticket_id">
                                <a :href="`<?= URLROOT ?>/tickets/view/${call.ticket_id}`" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline" x-text="call.contact_name"></a>
                             </template>
                             <template x-if="!((call.call_type === 'Outgoing' && call.contact_id) || (call.call_type === 'Incoming' && call.ticket_id))">
                                 <span class="text-sm font-semibold text-gray-800" x-text="call.contact_name"></span>
                             </template>
                             <p class="text-xs text-gray-500" x-text="call.contact_phone"></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="call.user_name"></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(call.status)" x-text="formatStatus(call.status)"></span></td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <div x-text="getClassificationText(call)" x-show="getClassificationText(call) !== '-'"></div>

                            <template x-if="call.call_type === 'Outgoing'">
                                <div class="text-xs text-gray-500" x-show="call.next_call_at" x-text="`Next Call: ${new Date(call.next_call_at).toLocaleString()}`"></div>
                            </template>
                            <template x-if="call.call_type === 'Incoming'">
                                <div x-show="call.duration_seconds !== null" x-text="`Duration: ${formatDuration(call.duration_seconds)}`"></div>
                                <template x-if="call.ticket_number">
                                    <a :href="`<?= URLROOT ?>/tickets/view/${call.ticket_id}`" target="_blank" class="text-xs text-blue-600 hover:underline" x-text="`Ticket: ${call.ticket_number}`"></a>
                                </template>
                            </template>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="new Date(call.call_time).toLocaleString()"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination -->
        <div x-show="!isLoading && pagination.total_pages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-700">
                Showing <span x-text="(pagination.page - 1) * pagination.limit + 1"></span>
                to <span x-text="Math.min(pagination.page * pagination.limit, pagination.total)"></span>
                of <span x-text="pagination.total"></span> results
            </p>
            <div class="flex items-center space-x-2">
                <button @click="changePage(pagination.page - 1)" :disabled="pagination.page <= 1" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <button @click="changePage(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
function allCallsPage() {
  return {
    calls: [], isLoading: true,
    pagination: { page: 1, limit: 25, total: 0, total_pages: 1 },
    filters: { search_term: '', start_date: '', end_date: '', user_id: '', call_type: 'all', status: '', category_id: '', subcategory_id: '', code_id: '' },
    categories: <?= json_encode($data['ticket_categories']) ?>,
    subcategories: [], codes: [],
    selectedCategory: '', selectedSubcategory: '',
    flatpickrInstance: null,

    init() {
        this.flatpickrInstance = flatpickr(this.$refs.daterangepicker, {
            mode: 'range', dateFormat: 'Y-m-d',
            onChange: (selectedDates) => {
                this.filters.start_date = selectedDates.length > 0 ? this.formatDate(selectedDates[0]) : '';
                this.filters.end_date = selectedDates.length > 1 ? this.formatDate(selectedDates[1]) : '';
            }
        });
        this.fetchCalls();
    },
    fetchCalls(page = 1) {
        this.isLoading = true; this.pagination.page = page;
        const filterParams = new URLSearchParams(this.filters);
        filterParams.append('page', this.pagination.page);
        filterParams.append('limit', this.pagination.limit);
        
        fetch(`<?= URLROOT ?>/listings/get_calls_api?${filterParams.toString()}`)
            .then(res => res.json())
            .then(data => {
                if(data && !data.error) {
                    this.calls = data.data; this.pagination.total = data.total; this.pagination.total_pages = data.total_pages;
                } else { this.calls = []; console.error('API Error:', data.error); }
            })
            .catch(err => { console.error('Fetch Error:', err); this.calls = []; })
            .finally(() => this.isLoading = false);
    },
    changePage(page) { if (page > 0 && page <= this.pagination.total_pages) this.fetchCalls(page); },
    resetFilters() {
        this.filters = { search_term: '', start_date: '', end_date: '', user_id: '', call_type: 'all', status: '', category_id: '', subcategory_id: '', code_id: '' };
        this.selectedCategory = ''; this.selectedSubcategory = ''; this.subcategories = []; this.codes = [];
        this.flatpickrInstance.clear();
        this.fetchCalls();
    },
    onCallTypeChange() {
        if (this.filters.call_type !== 'outgoing') {
            this.filters.category_id = ''; this.filters.subcategory_id = ''; this.filters.code_id = '';
            this.selectedCategory = ''; this.selectedSubcategory = '';
        }
    },
    formatDate(date) { let d = new Date(date), m = '' + (d.getMonth() + 1), day = '' + d.getDate(), y = d.getFullYear(); if (m.length < 2) m = '0' + m; if (day.length < 2) day = '0' + day; return [y, m, day].join('-'); },
    onCategoryChange() { this.selectedSubcategory = ''; this.filters.subcategory_id = ''; this.filters.code_id = ''; this.filters.category_id = this.selectedCategory; const cat = this.categories.find(c => c.id == this.selectedCategory); this.subcategories = cat ? cat.subcategories : []; },
    onSubcategoryChange() { this.filters.code_id = ''; this.filters.subcategory_id = this.selectedSubcategory; const sub = this.subcategories.find(s => s.id == this.selectedSubcategory); this.codes = sub ? sub.codes : []; },
    getClassificationText(call) { if (!call.category_name) return '-'; return [call.category_name, call.subcategory_name, call.code_name].filter(Boolean).join(' > '); },
    formatStatus(status) { return status ? status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''; },
    formatDuration(seconds) { if (seconds === null || seconds < 0) return '-'; const h = Math.floor(seconds / 3600); const m = Math.floor((seconds % 3600) / 60); const s = Math.floor(seconds % 60); return [h, m, s].map(v => String(v).padStart(2, '0')).join(':'); },
    getStatusColor(status) {
        const colors = {
            answered: 'bg-green-100 text-green-800', no_answer: 'bg-yellow-100 text-yellow-800',
            missed: 'bg-gray-100 text-gray-800', busy: 'bg-red-100 text-red-800', 
            not_available: 'bg-purple-100 text-purple-800', wrong_number: 'bg-red-200 text-red-900', 
            rescheduled: 'bg-blue-100 text-blue-800'
        };
        return colors[status] || 'bg-gray-200 text-gray-800';
    }
  };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 