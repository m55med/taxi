<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-8 bg-gray-100 min-h-screen" x-data="driversPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Driver Management</h1>
    <p class="text-gray-600 mb-8">A comprehensive view to filter, search, and manage all drivers.</p>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <template x-for="stat in stats" :key="stat.key">
            <div @click="setFilterAndFetch('main_system_status', stat.key)"
                 class="p-4 rounded-lg shadow-md cursor-pointer transition-transform transform hover:scale-105"
                 :class="filters.main_system_status === stat.key ? stat.active_bg : 'bg-white'">
                <h3 class="text-lg font-semibold" :class="filters.main_system_status === stat.key ? 'text-white' : 'text-gray-500'" x-text="stat.label"></h3>
                <p class="text-3xl font-bold" :class="filters.main_system_status === stat.key ? 'text-white' : 'text-gray-800'" x-text="stat.count"></p>
            </div>
        </template>
    </div>

    <!-- Filter and Bulk Action Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="flex flex-wrap justify-between items-center gap-6">
            <!-- Search -->
            <div class="flex-grow">
                <label for="search_term" class="sr-only">Search</label>
                <input type="text" id="search_term" x-model.debounce.500ms="filters.search_term" @keydown.enter="fetchDrivers()" class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by name, phone, email...">
            </div>
            <!-- Bulk Actions -->
            <div class="flex items-center space-x-4" x-show="selectedDrivers.length > 0">
                <span class="text-sm font-semibold text-gray-600" x-text="`${selectedDrivers.length} selected`"></span>
                <select x-model="bulkAction" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Choose Bulk Action...</option>
                    <optgroup label="Change Main Status">
                        <option value="main_system_status-pending">Pending</option>
                        <option value="main_system_status-completed">Completed</option>
                        <option value="main_system_status-blocked">Blocked</option>
                    </optgroup>
                    <optgroup label="Change App Status">
                        <option value="app_status-active">Active</option>
                        <option value="app_status-inactive">Inactive</option>
                    </optgroup>
                    <optgroup label="Change Car Type">
                        <?php foreach($data['car_types'] as $type): ?>
                            <option value="car_type_id-<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                <button @click="applyBulkAction" :disabled="!bulkAction" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400">Apply</button>
            </div>
        </div>
        <!-- Advanced Filters -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
            <select x-model="filters.main_system_status" @change="fetchDrivers()" class="form-select">
                <option value="">All Main Statuses</option>
                <option value="pending">Pending</option>
                <option value="waiting_chat">Waiting Chat</option>
                <option value="no_answer">No Answer</option>
                <option value="rescheduled">Rescheduled</option>
                <option value="completed">Completed</option>
                <option value="blocked">Blocked</option>
                <option value="reconsider">Reconsider</option>
                <option value="needs_documents">Needs Documents</option>
            </select>
            <select x-model="filters.app_status" @change="fetchDrivers()" class="form-select">
                <option value="">All App Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="banned">Banned</option>
            </select>
            <select x-model="filters.car_type_id" @change="fetchDrivers()" class="form-select">
                <option value="">All Car Types</option>
                <?php foreach($data['car_types'] as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select x-model="filters.has_many_trips" @change="fetchDrivers()" class="form-select">
                <option value="">All Trip Histories</option>
                <option value="1">More than 10 trips</option>
                <option value="0">10 trips or less</option>
            </select>
            <select x-model="filters.has_missing_documents" @change="fetchDrivers()" class="form-select">
                <option value="">All Document Statuses</option>
                <option value="1">Has Missing Docs</option>
                <option value="0">Docs Complete</option>
            </select>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-4"><input type="checkbox" @change="toggleSelectAll($event.target.checked)"></th>
                    <th class="th">Driver</th>
                    <th class="th">Main Status</th>
                    <th class="th">App Status</th>
                    <th class="th">Details</th>
                    <th class="th">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading"><tr><td colspan="6" class="text-center py-10"><i class="fas fa-spinner fa-spin fa-2x text-blue-500"></i></td></tr></template>
                <template x-if="!isLoading && drivers.length === 0"><tr><td colspan="6" class="text-center py-10 text-gray-500">No drivers found for the selected filters.</td></tr></template>
                <template x-for="driver in drivers" :key="driver.id">
                    <tr class="hover:bg-gray-50">
                        <td class="p-4"><input type="checkbox" :value="driver.id" x-model="selectedDrivers"></td>
                        <td class="td">
                            <a :href="`<?= URLROOT ?>/drivers/details/${driver.id}`" class="font-semibold text-blue-600 hover:underline" x-text="driver.name"></a>
                            <p class="text-sm text-gray-500" x-text="driver.phone"></p>
                        </td>
                        <td class="td"><span class="status-badge" :class="getStatusColor(driver.main_system_status)" x-text="formatStatus(driver.main_system_status)"></span></td>
                        <td class="td"><span class="status-badge" :class="getStatusColor(driver.app_status)" x-text="formatStatus(driver.app_status)"></span></td>
                        <td class="td">
                            <p x-text="`Calls: ${driver.call_count}`"></p>
                            <p :class="driver.missing_documents_count > 0 ? 'text-red-600' : 'text-green-600'" x-text="`Missing Docs: ${driver.missing_documents_count}`"></p>
                            <p x-text="`Car: ${driver.car_type_name || 'N/A'}`"></p>
                            <p x-text="driver.has_many_trips == 1 ? '> 10 Trips' : '<= 10 Trips'"></p>
                        </td>
                        <td class="td">
                            <a :href="`<?= URLROOT ?>/drivers/details/${driver.id}`" class="text-blue-500 hover:text-blue-700">View</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <!-- Pagination -->
        <div x-show="!isLoading && pagination.total_pages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
             <p class="text-sm text-gray-700">
                Showing <span x-text="pagination.total > 0 ? (pagination.page - 1) * pagination.limit + 1 : 0"></span>
                to <span x-text="Math.min(pagination.page * pagination.limit, pagination.total)"></span>
                of <span x-text="pagination.total"></span> results
            </p>
            <div class="flex items-center space-x-2">
                <button @click="changePage(pagination.page - 1)" :disabled="pagination.page <= 1" class="pagination-btn">Prev</button>
                <button @click="changePage(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="pagination-btn">Next</button>
            </div>
        </div>
    </div>
</div>

<style>
.form-select { @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white; }
.th { @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
.td { @apply px-6 py-4 whitespace-nowrap text-sm text-gray-700; }
.status-badge { @apply px-2.5 py-0.5 text-xs font-semibold rounded-full; }
.pagination-btn { @apply px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed; }
</style>

<script>
function driversPage() {
    return {
        drivers: [],
        statsData: <?= json_encode($data['stats']) ?>,
        isLoading: true,
        pagination: { page: 1, limit: 15, total: 0, total_pages: 1 },
        filters: { search_term: '', main_system_status: '', app_status: '', car_type_id: '', has_many_trips: '', has_missing_documents: '' },
        selectedDrivers: [],
        bulkAction: '',
        
        get stats() {
            const statusLabels = {
                total: 'Total Drivers', pending: 'Pending', waiting_chat: 'Waiting Chat',
                no_answer: 'No Answer', rescheduled: 'Rescheduled', completed: 'Completed',
                blocked: 'Blocked', reconsider: 'Reconsider', needs_documents: 'Needs Docs'
            };
            const statOrder = ['total', 'pending', 'completed', 'needs_documents', 'rescheduled', 'blocked'];
            return statOrder.map(key => ({
                key,
                label: statusLabels[key] || this.formatStatus(key),
                count: this.statsData[key] || 0,
                active_bg: 'bg-blue-600 shadow-lg'
            }));
        },

        init() {
            this.fetchDrivers();
            this.$watch('filters.search_term', () => this.fetchDrivers());
        },

        fetchDrivers(page = 1) {
            this.isLoading = true;
            this.pagination.page = page;
            const params = new URLSearchParams({...this.filters, page, limit: this.pagination.limit});
            
            fetch(`<?= URLROOT ?>/listings/get_drivers_api?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if(data && !data.error) {
                        this.drivers = data.data;
                        this.pagination.total = data.total;
                        this.pagination.total_pages = data.total_pages;
                    } else { this.drivers = []; console.error('API Error:', data.error); }
                })
                .catch(err => { console.error('Fetch Error:', err); this.drivers = []; })
                .finally(() => this.isLoading = false);
        },

        applyBulkAction() {
            if (!this.bulkAction || this.selectedDrivers.length === 0) {
                alert('Please select an action and at least one driver.');
                return;
            }
            if (!confirm(`Are you sure you want to apply this action to ${this.selectedDrivers.length} drivers?`)) return;

            const [field, value] = this.bulkAction.split('-');
            
            fetch(`<?= URLROOT ?>/listings/bulk_update_drivers`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ driver_ids: this.selectedDrivers, field, value })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status) {
                    alert(data.message);
                    this.fetchDrivers();
                    this.selectedDrivers = [];
                    this.bulkAction = '';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('An unexpected error occurred.'));
        },

        setFilterAndFetch(key, value) {
            if (this.filters[key] === value) {
                this.filters[key] = ''; // Toggle off if clicked again
            } else {
                this.filters[key] = value === 'total' ? '' : value;
            }
            this.fetchDrivers();
        },

        changePage(page) { if (page > 0 && page <= this.pagination.total_pages) this.fetchDrivers(page); },
        toggleSelectAll(checked) { this.selectedDrivers = checked ? this.drivers.map(d => d.id) : []; },
        formatStatus(status) { return status ? status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'; },
        getStatusColor(status) {
            const colors = {
                pending: 'bg-yellow-100 text-yellow-800', completed: 'bg-green-100 text-green-800',
                active: 'bg-green-100 text-green-800', inactive: 'bg-gray-200 text-gray-800',
                blocked: 'bg-red-100 text-red-800', banned: 'bg-red-200 text-red-900',
                needs_documents: 'bg-purple-100 text-purple-800', reconsider: 'bg-indigo-100 text-indigo-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
    };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 