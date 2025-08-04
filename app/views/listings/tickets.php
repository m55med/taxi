<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Tickets</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Ticket # or Phone)</label>
                <input type="text" id="search_term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., T-12345 or 968...">
            </div>
            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                <input type="text" id="date_range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
            </div>
            <!-- Created By -->
            <div>
                <label for="created_by" class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                <select id="created_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Platform -->
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-600 mb-1">Platform</label>
                <select id="platform_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Platforms</option>
                     <?php foreach ($data['platforms'] as $platform): ?>
                        <option value="<?= $platform['id'] ?>"><?= htmlspecialchars($platform['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Is VIP -->
            <div>
                <label for="is_vip" class="block text-sm font-medium text-gray-600 mb-1">VIP</label>
                <select id="is_vip" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <!-- Category -->
            <div>
                 <label for="category" class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                <select id="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                </select>
            </div>
            <!-- Subcategory -->
            <div>
                <label for="subcategory" class="block text-sm font-medium text-gray-600 mb-1">Subcategory</label>
                <select id="subcategory_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                    <option value="">All Subcategories</option>
                </select>
            </div>
            <!-- Code -->
            <div class="lg:col-start-4">
                <label for="code" class="block text-sm font-medium text-gray-600 mb-1">Code</label>
                <select id="code_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                     <option value="">All Codes</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button id="reset-button" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</button>
            <button id="search-button" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                <svg id="search-spinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            <tbody id="tickets-tbody" class="bg-white divide-y divide-gray-200">
                <!-- Rows will be injected by JavaScript -->
            </tbody>
        </table>
        <div id="loading-row" class="hidden text-center py-10 text-gray-500">Loading...</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // State
    let tickets = [];
    let isLoading = true;
    const allCategories = <?= json_encode($data['ticket_categories']) ?>;

    // Element References
    const searchInput = document.getElementById('search_term');
    const dateRangeInput = document.getElementById('date_range');
    const createdBySelect = document.getElementById('created_by');
    const platformSelect = document.getElementById('platform_id');
    const isVipSelect = document.getElementById('is_vip');
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const codeSelect = document.getElementById('code_id');
    const searchButton = document.getElementById('search-button');
    const resetButton = document.getElementById('reset-button');
    const tbody = document.getElementById('tickets-tbody');
    const loadingRow = document.getElementById('loading-row');
    const searchSpinner = document.getElementById('search-spinner');

    const flatpickrInstance = flatpickr(dateRangeInput, {
            mode: 'range',
        dateFormat: 'Y-m-d'
    });

    const formatDate = (date) => {
        if (!date) return '';
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    };

    const getFilters = () => {
        const selectedDates = flatpickrInstance.selectedDates;
        return {
            search_term: searchInput.value,
            start_date: selectedDates.length > 0 ? formatDate(selectedDates[0]) : '',
            end_date: selectedDates.length > 1 ? formatDate(selectedDates[1]) : '',
            created_by: createdBySelect.value,
            platform_id: platformSelect.value,
            is_vip: isVipSelect.value,
            category_id: categorySelect.value,
            subcategory_id: subcategorySelect.value,
            code_id: codeSelect.value,
        };
    };

    const getClassificationText = (ticket) => {
        if (!ticket.category_name) return '-';
        return [ticket.category_name, ticket.subcategory_name, ticket.code_name].filter(Boolean).join(' > ');
    };
    
    const renderTable = () => {
        tbody.innerHTML = '';
        loadingRow.style.display = 'none';
        searchSpinner.classList.add('hidden');

        if (isLoading) {
            loadingRow.style.display = 'block';
            searchSpinner.classList.remove('hidden');
            return;
        }

        if (tickets.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-gray-500">No tickets found.</td></tr>`;
            return;
        }

        tickets.forEach(ticket => {
            const row = tbody.insertRow();
            row.className = 'hover:bg-gray-50 transition';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                    <a href="<?= URLROOT ?>/tickets/view/${ticket.ticket_id}" target="_blank" class="hover:underline">${ticket.ticket_number}</a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.created_by_username}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.platform_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.phone || ''}</td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <span class="block truncate max-w-xs" title="${getClassificationText(ticket)}">${getClassificationText(ticket)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${new Date(ticket.created_at).toLocaleString()}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    ${ticket.is_vip == 1 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Yes</span>' 
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>'
                    }
                </td>
            `;
        });
    };

    const fetchTickets = async () => {
        isLoading = true;
        renderTable();
        
        const params = new URLSearchParams(getFilters()).toString();
        try {
            const response = await fetch(`<?= URLROOT ?>/listings/get_tickets_api?${params}`);
            const data = await response.json();
            tickets = data.error ? [] : data;
        } catch (error) {
            console.error('Error fetching tickets:', error);
            tickets = [];
        } finally {
            isLoading = false;
            renderTable();
        }
    };

    const onCategoryChange = () => {
        const categoryId = categorySelect.value;
        const category = allCategories.find(c => c.id == categoryId);
        const subcategories = category ? category.subcategories : [];

        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        subcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            subcategorySelect.appendChild(option);
        });
        subcategorySelect.disabled = !categoryId;
        onSubcategoryChange();
    };

    const onSubcategoryChange = () => {
        const categoryId = categorySelect.value;
        const subcategoryId = subcategorySelect.value;
        const category = allCategories.find(c => c.id == categoryId);
        const subcategories = category ? category.subcategories : [];
        const subcategory = subcategories.find(s => s.id == subcategoryId);
        const codes = subcategory ? subcategory.codes : [];

        codeSelect.innerHTML = '<option value="">All Codes</option>';
        codes.forEach(code => {
            const option = document.createElement('option');
            option.value = code.id;
            option.textContent = code.name;
            codeSelect.appendChild(option);
        });
        codeSelect.disabled = !subcategoryId;
    };

    const resetFilters = () => {
        searchInput.value = '';
        flatpickrInstance.clear();
        createdBySelect.value = '';
        platformSelect.value = '';
        isVipSelect.value = '';
        categorySelect.value = '';
        onCategoryChange();
        fetchTickets();
    };

    // Event Listeners
    searchButton.addEventListener('click', fetchTickets);
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            fetchTickets();
        }
    });
    resetButton.addEventListener('click', resetFilters);
    categorySelect.addEventListener('change', onCategoryChange);
    subcategorySelect.addEventListener('change', onSubcategoryChange);

    // Initializations
    allCategories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categorySelect.appendChild(option);
    });

    fetchTickets();
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?> 
