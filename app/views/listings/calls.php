<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Calls</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">
                    Search (Contact Name or Phone)
                </label>
                <input type="text" id="search_term"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., John Doe or 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Select date range">
            </div>
            <!-- User Involved -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-600 mb-1">User Involved</label>
                <select id="user_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <!-- Call Type -->
            <div>
                <label for="call_type" class="block text-sm font-medium text-gray-600 mb-1">Call Type</label>
                <select id="call_type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All</option>
                    <option value="incoming">Incoming</option>
                    <option value="outgoing">Outgoing</option>
                </select>
            </div>
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <select id="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            <div id="outgoing-filters-container" class="hidden lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                    <select id="category_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div>
                    <label for="subcategory_id" class="block text-sm font-medium text-gray-600 mb-1">Subcategory</label>
                    <select id="subcategory_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" disabled>
                            <option value="">All Subcategories</option>
                        </select>
                    </div>
                    <div>
                    <label for="code_id" class="block text-sm font-medium text-gray-600 mb-1">Code</label>
                    <select id="code_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" disabled>
                            <option value="">All Codes</option>
                        </select>
                    </div>
                </div>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button id="reset-button"
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Reset</button>
            <button id="search-button"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
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
            <tbody id="calls-tbody" class="bg-white divide-y divide-gray-200">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
        <div id="loading-row" class="hidden text-center py-10">
            <i class="fas fa-spinner fa-spin fa-2x text-blue-500"></i>
        </div>

        <!-- Pagination -->
        <div id="pagination-container"
            class="hidden px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
            <p id="pagination-info" class="text-sm text-gray-700"></p>
            <div class="flex items-center space-x-2">
                <button id="prev-button"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button id="next-button"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // STATE
        let calls = [];
        let isLoading = true;
        let pagination = { page: 1, limit: 25, total: 0, total_pages: 1 };
        
        // Use fallback [] if ticket_categories is not set
        const allCategories = <?= json_encode($data['ticket_categories'] ?? []) ?>;
        console.log('Initial ticket_categories:', allCategories);

        // DOM ELEMENTS
        const tbody = document.getElementById('calls-tbody');
        const loadingRow = document.getElementById('loading-row');
        const searchButton = document.getElementById('search-button');
        const resetButton = document.getElementById('reset-button');
        const callTypeSelect = document.getElementById('call_type');
        const outgoingFiltersContainer = document.getElementById('outgoing-filters-container');
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const codeSelect = document.getElementById('code_id');

        // PAGINATION ELEMENTS
        const paginationContainer = document.getElementById('pagination-container');
        const paginationInfo = document.getElementById('pagination-info');
        const prevButton = document.getElementById('prev-button');
        const nextButton = document.getElementById('next-button');

        // Initialize date picker
        const flatpickrInstance = flatpickr("#date_range", {
            mode: 'range', dateFormat: 'Y-m-d'
        });

        // Helpers
        function formatDate(date) {
            let d = new Date(date), m = '' + (d.getMonth() + 1), day = '' + d.getDate(), y = d.getFullYear();
            if (m.length < 2) m = '0' + m; if (day.length < 2) day = '0' + day;
            return [y, m, day].join('-');
        }
        function formatStatus(status) {
            return status ? status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '';
        }
        function formatDuration(sec) {
            if (sec === null || sec < 0) return '-';
            const h = Math.floor(sec / 3600), m = Math.floor((sec % 3600) / 60), s = Math.floor(sec % 60);
            return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
        }
        function getStatusColor(status) {
        const colors = {
                answered: 'bg-green-100 text-green-800',
                no_answer: 'bg-yellow-100 text-yellow-800',
                missed: 'bg-gray-100 text-gray-800',
                busy: 'bg-red-100 text-red-800',
                not_available: 'bg-purple-100 text-purple-800',
                wrong_number: 'bg-red-200 text-red-900',
            rescheduled: 'bg-blue-100 text-blue-800'
        };
        return colors[status] || 'bg-gray-200 text-gray-800';
    }
        function getClassificationText(call) {
            if (!call.category_name) return '-';
            return [call.category_name, call.subcategory_name, call.code_name].filter(Boolean).join(' > ');
        }

        // Render
        function renderTable() {
            console.log('Rendering table. isLoading:', isLoading, 'calls length:', calls.length);
            tbody.innerHTML = '';
            loadingRow.style.display = isLoading ? 'table-row' : 'none';

            if (!isLoading && calls.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-500">No calls found.</td></tr>';
            } else {
                calls.forEach(call => {
                    const row = tbody.insertRow();
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                    <td class="px-6 py-4">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full ${call.call_type === 'Incoming' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                        }">${call.call_type}</span>
                    </td>
                    <td class="px-6 py-4">
                      ${(call.call_type === 'Outgoing' && call.contact_id)
                            ? `<a href="<?= URLROOT ?>/drivers/details/${call.contact_id}" target="_blank"
                                class="text-sm font-semibold text-blue-600 hover:underline">${call.contact_name}</a>`
                            : ''
                        }
                      ${(call.call_type === 'Incoming' && call.ticket_id)
                            ? `<a href="<?= URLROOT ?>/tickets/view/${call.ticket_id}" target="_blank"
                                class="text-sm font-semibold text-blue-600 hover:underline">${call.contact_name}</a>`
                            : ''
                        }
                      ${!((call.call_type === 'Outgoing' && call.contact_id) ||
                            (call.call_type === 'Incoming' && call.ticket_id))
                            ? `<span class="text-sm font-semibold text-gray-800">${call.contact_name}</span>`
                            : ''
                        }
                      <p class="text-xs text-gray-500">${call.contact_phone}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">${call.user_name}</td>
                    <td class="px-6 py-4">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(call.status)}">
                        ${formatStatus(call.status)}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                      ${getClassificationText(call) !== '-' ? `<div>${getClassificationText(call)}</div>` : ''}
                      ${call.call_type === 'Outgoing' && call.next_call_at
                            ? `<div class="text-xs text-gray-500">
                               Next Call: ${new Date(call.next_call_at).toLocaleString()}
                             </div>`
                            : ''
                        }
                      ${call.call_type === 'Incoming' && call.duration_seconds !== null
                            ? `<div>Duration: ${formatDuration(call.duration_seconds)}</div>`
                            : ''
                        }
                      ${call.call_type === 'Incoming' && call.ticket_number
                            ? `<a href="<?= URLROOT ?>/tickets/view/${call.ticket_id}" target="_blank"
                                class="text-xs text-blue-600 hover:underline">
                               Ticket: ${call.ticket_number}
                             </a>`
                            : ''
                        }
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                      ${new Date(call.call_time).toLocaleString()}
                    </td>
                `;
                });
            }
            renderPagination();
        }

        function renderPagination() {
            if (!isLoading && pagination.total_pages > 1) {
                paginationContainer.style.display = 'flex';
                paginationInfo.textContent =
                    `Showing ${(pagination.page - 1) * pagination.limit + 1}
               to ${Math.min(pagination.page * pagination.limit, pagination.total)}
               of ${pagination.total} results`;
                prevButton.disabled = pagination.page <= 1;
                nextButton.disabled = pagination.page >= pagination.total_pages;
            } else {
                paginationContainer.style.display = 'none';
            }
        }

        // Fetch
        function fetchCalls(page = 1) {
            isLoading = true;
            pagination.page = page;
            renderTable();

            const filters = new URLSearchParams(getFilters());
            filters.append('page', pagination.page);
            filters.append('limit', pagination.limit);

            console.log(`Fetching calls with params: ${filters.toString()}`);

            fetch(`<?= URLROOT ?>/listings/get_calls_api?${filters.toString()}`)
                .then(res => {
                    console.log('API response status:', res.status);
                    if (!res.ok) {
                       throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('API response data:', data);
                    if (data && data.data) {
                        calls = data.data;
                        pagination.total = parseInt(data.total, 10) || 0;
                        pagination.total_pages = parseInt(data.total_pages, 10) || 0;
                    } else {
                        calls = [];
                        pagination.total = 0;
                        pagination.total_pages = 0;
                        console.error('API Error: Invalid data structure received.', data);
                    }
                })
                .catch(err => {
                    console.error('Fetch Error:', err);
                    calls = [];
                })
                .finally(() => {
                    isLoading = false;
                    renderTable();
                });
        }

        // Pagination handlers
        function changePage(newPage) {
            if (newPage > 0 && newPage <= pagination.total_pages) {
                fetchCalls(newPage);
            }
        }

        // Filters
        function getFilters() {
            const dates = flatpickrInstance.selectedDates;
            return {
                search_term: document.getElementById('search_term').value,
                start_date: dates[0] ? formatDate(dates[0]) : '',
                end_date: dates[1] ? formatDate(dates[1]) : '',
                user_id: document.getElementById('user_id').value,
                call_type: callTypeSelect.value,
                status: document.getElementById('status').value,
                category_id: categorySelect.value,
                subcategory_id: subcategorySelect.value,
                code_id: codeSelect.value
            };
        }

        function resetFilters() {
            document.getElementById('search_term').value = '';
            flatpickrInstance.clear();
            document.getElementById('user_id').value = '';
            callTypeSelect.value = 'all';
            document.getElementById('status').value = '';
            categorySelect.value = '';
            onCallTypeChange();
            fetchCalls();
        }

        function onCallTypeChange() {
            const isOut = callTypeSelect.value === 'outgoing';
            outgoingFiltersContainer.style.display = isOut ? 'grid' : 'none';
            if (!isOut) {
                categorySelect.value = '';
                subcategorySelect.value = '';
                codeSelect.value = '';
            }
            onCategoryChange();
        }

        function onCategoryChange() {
            const catId = categorySelect.value;
            const cat = allCategories.find(c => c.id == catId);
            const subs = cat ? cat.subcategories : [];
            subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
            subs.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id; opt.textContent = s.name;
                subcategorySelect.appendChild(opt);
            });
            subcategorySelect.disabled = !catId;
            onSubcategoryChange();
        }

        function onSubcategoryChange() {
            const catId = categorySelect.value;
            const subId = subcategorySelect.value;
            const cat = allCategories.find(c => c.id == catId);
            const subs = cat ? cat.subcategories : [];
            const sub = subs.find(s => s.id == subId);
            const codes = sub ? sub.codes : [];
            codeSelect.innerHTML = '<option value="">All Codes</option>';
            codes.forEach(cd => {
                const opt = document.createElement('option');
                opt.value = cd.id; opt.textContent = cd.name;
                codeSelect.appendChild(opt);
            });
            codeSelect.disabled = !subId;
        }

        // Event Listeners
        searchButton.addEventListener('click', () => fetchCalls());
        resetButton.addEventListener('click', resetFilters);
        callTypeSelect.addEventListener('change', onCallTypeChange);
        categorySelect.addEventListener('change', onCategoryChange);
        subcategorySelect.addEventListener('change', onSubcategoryChange);
        prevButton.addEventListener('click', () => changePage(pagination.page - 1));
        nextButton.addEventListener('click', () => changePage(pagination.page + 1));

        // Initial population & load
        allCategories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id; opt.textContent = cat.name;
            categorySelect.appendChild(opt);
        });
        fetchCalls();
    });
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 