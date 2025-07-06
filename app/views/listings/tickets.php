<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen" x-data="ticketsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Tickets</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Ticket # or Phone)</label>
                <input type="text" id="search_term" x-model="filters.search_term" @keydown.enter="fetchTickets()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., T-12345 or 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" x-ref="daterangepicker" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>
            <!-- Created By -->
            <div>
                <label for="created_by" class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                <select id="created_by" x-model="filters.created_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Platform -->
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-600 mb-1">Platform</label>
                <select id="platform" x-model="filters.platform_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Platforms</option>
                     <?php foreach ($data['platforms'] as $platform): ?>
                        <option value="<?= $platform['id'] ?>"><?= htmlspecialchars($platform['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Is VIP -->
            <div>
                <label for="is_vip" class="block text-sm font-medium text-gray-600 mb-1">VIP</label>
                <select id="is_vip" x-model="filters.is_vip" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
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
            <div class="lg:col-start-4">
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
            <button @click="fetchTickets()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading">
                    <tr><td colspan="7" class="text-center py-10 text-gray-500">Loading...</td></tr>
                </template>
                <template x-if="!isLoading && tickets.length === 0">
                    <tr><td colspan="7" class="text-center py-10 text-gray-500">No tickets found.</td></tr>
                </template>
                <template x-for="ticket in tickets" :key="ticket.ticket_id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            <a :href="`<?= URLROOT ?>/tickets/view/${ticket.ticket_id}`" target="_blank" class="hover:underline" x-text="ticket.ticket_number"></a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="ticket.created_by_username"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="ticket.platform_name"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="ticket.phone"></td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="block truncate max-w-xs" :title="getClassificationText(ticket)" x-text="getClassificationText(ticket)"></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="new Date(ticket.created_at).toLocaleString()"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <span x-show="ticket.is_vip == 1" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Yes</span>
                            <span x-show="ticket.is_vip == 0" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function ticketsPage() {
  return {
    tickets: [],
    isLoading: true,
    filters: {
        search_term: '',
        start_date: '',
        end_date: '',
        created_by: '',
        platform_id: '',
        is_vip: '',
        category_id: '',
        subcategory_id: '',
        code_id: '',
    },
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
        this.fetchTickets();
    },

    fetchTickets() {
        this.isLoading = true;
        const params = new URLSearchParams(this.filters).toString();
        fetch(`<?= URLROOT ?>/listings/get_tickets_api?${params}`)
            .then(res => res.json())
            .then(data => {
                this.tickets = data.error ? [] : data;
                this.isLoading = false;
            }).catch(err => { console.error(err); this.isLoading = false; });
    },
    
    resetFilters() {
        this.filters = { search_term: '', start_date: '', end_date: '', created_by: '', platform_id: '', is_vip: '', category_id: '', subcategory_id: '', code_id: '' };
        this.selectedCategory = ''; this.selectedSubcategory = '';
        this.subcategories = []; this.codes = [];
        this.flatpickrInstance.clear();
        this.fetchTickets();
    },

    formatDate(date) {
        let d = new Date(date), month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    },
    
    onCategoryChange() {
        this.selectedSubcategory = ''; this.filters.subcategory_id = ''; this.filters.code_id = ''; this.codes = [];
        this.filters.category_id = this.selectedCategory;
        const category = this.categories.find(c => c.id == this.selectedCategory);
        this.subcategories = category ? category.subcategories : [];
    },
    
    onSubcategoryChange() {
        this.filters.code_id = '';
        this.filters.subcategory_id = this.selectedSubcategory;
        const subcategory = this.subcategories.find(s => s.id == this.selectedSubcategory);
        this.codes = subcategory ? subcategory.codes : [];
    },
    
    getClassificationText(ticket) {
        if (!ticket.category_name) return '-';
        return [ticket.category_name, ticket.subcategory_name, ticket.code_name].filter(Boolean).join(' > ');
    },
  };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 