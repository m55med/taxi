<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen" x-data="incomingCallsPage()">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Incoming Calls</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div>
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Caller Phone)</label>
                <input type="text" id="search_term" x-model="filters.search_term" @keydown.enter="fetchCalls()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" x-ref="daterangepicker" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>
            <!-- Received By -->
            <div>
                <label for="call_received_by" class="block text-sm font-medium text-gray-600 mb-1">Received By</label>
                <select id="call_received_by" x-model="filters.call_received_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <select id="status" x-model="filters.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="answered">Answered</option>
                    <option value="missed">Missed</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Caller Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Call Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Linked Ticket</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="isLoading"><tr><td colspan="6" class="text-center py-10">Loading...</td></tr></template>
                <template x-if="!isLoading && calls.length === 0"><tr><td colspan="6" class="text-center py-10">No calls found.</td></tr></template>
                <template x-for="call in calls" :key="call.call_id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800" x-text="call.caller_phone_number"></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="call.receiver_name"></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(call.status)" x-text="call.status"></span></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="new Date(call.call_started_at).toLocaleString()"></td>
                        <td class="px-6 py-4 text-sm text-gray-700" x-text="getDuration(call.call_started_at, call.call_ended_at)"></td>
                        <td class="px-6 py-4 text-sm text-blue-600">
                           <template x-if="call.ticket_number">
                                <a :href="`/tickets/view_by_number/${call.ticket_number}`" target="_blank" class="hover:underline" x-text="call.ticket_number"></a>
                           </template>
                           <template x-if="!call.ticket_number">-</template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function incomingCallsPage() {
  return {
    calls: [],
    isLoading: true,
    filters: { search_term: '', start_date: '', end_date: '', call_received_by: '', status: '' },
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
        fetch(`<?= URLROOT ?>/listings/get_incoming_calls_api?${params}`)
            .then(res => res.json())
            .then(data => { this.calls = data.error ? [] : data; this.isLoading = false; })
            .catch(err => { console.error(err); this.isLoading = false; });
    },

    resetFilters() {
        this.filters = { search_term: '', start_date: '', end_date: '', call_received_by: '', status: '' };
        this.flatpickrInstance.clear();
        this.fetchCalls();
    },

    formatDate(date) {
        let d = new Date(date), m = '' + (d.getMonth() + 1), day = '' + d.getDate(), y = d.getFullYear();
        if (m.length < 2) m = '0' + m; if (day.length < 2) day = '0' + day;
        return [y, m, day].join('-');
    },

    getDuration(start, end) {
        if (!start || !end) return '-';
        const duration = new Date(end) - new Date(start);
        if (duration < 0) return '-';
        const seconds = Math.floor((duration / 1000) % 60);
        const minutes = Math.floor((duration / (1000 * 60)) % 60);
        const hours = Math.floor((duration / (1000 * 60 * 60)));
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },

    getStatusColor(status) {
        return status === 'answered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }
  };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 