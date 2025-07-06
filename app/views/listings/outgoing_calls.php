<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen" x-data="outgoingCallsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Outgoing Calls</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Driver Name or Phone)</label>
                <input type="text" id="search_term" x-model="filters.search_term" @keydown.enter="fetchCalls()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., John Doe or 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" x-ref="daterangepicker" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>
            <!-- Called By -->
            <div>
                <label for="call_by" class="block text-sm font-medium text-gray-600 mb-1">Called By</label>
                <select id="call_by" x-model="filters.call_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Call Status -->
            <div>
                <label for="call_status" class="block text-sm font-medium text-gray-600 mb-1">Call Status</label>
                <select id="call_status" x-model="filters.call_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="no_answer">No Answer</option>
                    <option value="answered">Answered</option>
                    <option value="busy">Busy</option>
                    <option value="not_available">Not Available</option>
                    <option value="wrong_number">Wrong Number</option>
                    <option value="rescheduled">Rescheduled</option>
                </select>
            </div>
            <!-- Category, Subcategory, Code -->
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Caller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Classification</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Call Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Call</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading"><tr><td colspan="6" class="text-center py-10">Loading...</td></tr></template>
                <template x-if="!isLoading && calls.length === 0"><tr><td colspan="6" class="text-center py-10">No calls found.</td></tr></template>
                <template x-for="call in calls" :key="call.call_id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                             <a :href="`<?= URLROOT ?>/drivers/details/${call.driver_id}`" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline" x-text="call.driver_name"></a>
                             <p class="text-xs text-gray-500" x-text="call.driver_phone"></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="call.caller_name"></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(call.call_status)" x-text="formatStatus(call.call_status)"></span></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="getClassificationText(call)"></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="new Date(call.created_at).toLocaleString()"></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="call.next_call_at ? new Date(call.next_call_at).toLocaleString() : '-'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function outgoingCallsPage() {
  return {
    calls: [],
    isLoading: true,
    filters: {
        search_term: '', start_date: '', end_date: '', call_by: '', call_status: '',
        category_id: '', subcategory_id: '', code_id: '',
    },
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

    fetchCalls() {
        this.isLoading = true;
        const params = new URLSearchParams(this.filters).toString();
        fetch(`<?= URLROOT ?>/listings/get_outgoing_calls_api?${params}`)
            .then(res => res.json())
            .then(data => { this.calls = data.error ? [] : data; this.isLoading = false; })
            .catch(err => { console.error(err); this.isLoading = false; });
    },

    resetFilters() {
        this.filters = { search_term: '', start_date: '', end_date: '', call_by: '', call_status: '', category_id: '', subcategory_id: '', code_id: '' };
        this.selectedCategory = ''; this.selectedSubcategory = '';
        this.subcategories = []; this.codes = [];
        this.flatpickrInstance.clear();
        this.fetchCalls();
    },

    formatDate(date) {
        let d = new Date(date), m = '' + (d.getMonth() + 1), day = '' + d.getDate(), y = d.getFullYear();
        if (m.length < 2) m = '0' + m; if (day.length < 2) day = '0' + day;
        return [y, m, day].join('-');
    },
    onCategoryChange() {
        this.selectedSubcategory = ''; this.filters.subcategory_id = ''; this.filters.code_id = '';
        this.filters.category_id = this.selectedCategory;
        const cat = this.categories.find(c => c.id == this.selectedCategory);
        this.subcategories = cat ? cat.subcategories : [];
    },
    onSubcategoryChange() {
        this.filters.code_id = '';
        this.filters.subcategory_id = this.selectedSubcategory;
        const sub = this.subcategories.find(s => s.id == this.selectedSubcategory);
        this.codes = sub ? sub.codes : [];
    },
    getClassificationText(call) {
        if (!call.category_name) return '-';
        return [call.category_name, call.subcategory_name, call.code_name].filter(Boolean).join(' > ');
    },
    formatStatus(status) {
        return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    },
    getStatusColor(status) {
        const colors = {
            answered: 'bg-green-100 text-green-800', no_answer: 'bg-yellow-100 text-yellow-800',
            busy: 'bg-red-100 text-red-800', not_available: 'bg-purple-100 text-purple-800',
            wrong_number: 'bg-red-200 text-red-900', rescheduled: 'bg-blue-100 text-blue-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }
  };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 