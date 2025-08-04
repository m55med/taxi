<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Driver Management</h1>
    <p class="text-gray-600 mb-8">A comprehensive view to filter, search, and manage all drivers.</p>

    <!-- Stat Cards -->
    <div id="stats-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <!-- Stats will be rendered here by JS -->
    </div>

    <!-- Filter and Bulk Action Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="flex flex-wrap justify-between items-center gap-6">
            <!-- Search -->
            <div class="flex-grow">
                <label for="search_term" class="sr-only">Search</label>
                <input type="text" id="search_term" class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by name, phone, email...">
            </div>
            <!-- Bulk Actions -->
            <div id="bulk-actions-container" class="hidden items-center space-x-4">
                <span id="selected-drivers-count" class="text-sm font-semibold text-gray-600"></span>
                <select id="bulk-action-select" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <button id="apply-bulk-action-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400">Apply</button>
            </div>
        </div>
        <!-- Advanced Filters -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
            <select id="filter_main_system_status" class="form-select">
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
            <select id="filter_app_status" class="form-select">
                <option value="">All App Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="banned">Banned</option>
            </select>
            <select id="filter_car_type_id" class="form-select">
                <option value="">All Car Types</option>
                <?php foreach($data['car_types'] as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter_has_many_trips" class="form-select">
                <option value="">All Trip Histories</option>
                <option value="1">More than 10 trips</option>
                <option value="0">10 trips or less</option>
            </select>
            <select id="filter_has_missing_documents" class="form-select">
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
                    <th class="p-4"><input type="checkbox" id="select-all-checkbox"></th>
                    <th class="th">Driver</th>
                    <th class="th">Main Status</th>
                    <th class="th">App Status</th>
                    <th class="th">Details</th>
                    <th class="th">Actions</th>
                </tr>
            </thead>
            <tbody id="drivers-tbody" class="bg-white divide-y divide-gray-200">
                <!-- Driver rows will be injected by JS -->
            </tbody>
        </table>
         <div id="loading-row" class="hidden text-center py-10"><i class="fas fa-spinner fa-spin fa-2x text-blue-500"></i></div>

        <!-- Pagination -->
        <div id="pagination-container" class="hidden px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
             <p id="pagination-info" class="text-sm text-gray-700"></p>
            <div class="flex items-center space-x-2">
                <button id="prev-button" class="pagination-btn">Prev</button>
                <button id="next-button" class="pagination-btn">Next</button>
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
document.addEventListener('DOMContentLoaded', () => {
    // STATE
    let drivers = [];
    const statsData = <?= json_encode($data['stats']) ?>;
    let isLoading = true;
    let pagination = { page: 1, limit: 15, total: 0, total_pages: 1 };
    let filters = { search_term: '', main_system_status: '', app_status: '', car_type_id: '', has_many_trips: '', has_missing_documents: '' };
    let selectedDrivers = [];
    
    // DOM ELEMENTS
    const statsContainer = document.getElementById('stats-container');
    const tbody = document.getElementById('drivers-tbody');
    const loadingRow = document.getElementById('loading-row');
    const searchInput = document.getElementById('search_term');
    const filterSelects = document.querySelectorAll('.form-select');
    const paginationContainer = document.getElementById('pagination-container');
    const paginationInfo = document.getElementById('pagination-info');
    const prevButton = document.getElementById('prev-button');
    const nextButton = document.getElementById('next-button');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const bulkActionsContainer = document.getElementById('bulk-actions-container');
    const selectedDriversCount = document.getElementById('selected-drivers-count');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const applyBulkActionBtn = document.getElementById('apply-bulk-action-btn');

    const formatStatus = (status) => status ? status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
    
    const getStatusColor = (status) => {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800', completed: 'bg-green-100 text-green-800',
            active: 'bg-green-100 text-green-800', inactive: 'bg-gray-200 text-gray-800',
            blocked: 'bg-red-100 text-red-800', banned: 'bg-red-200 text-red-900',
            needs_documents: 'bg-purple-100 text-purple-800', reconsider: 'bg-indigo-100 text-indigo-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const renderStats = () => {
        const statusLabels = {
            total: 'Total Drivers', pending: 'Pending', waiting_chat: 'Waiting Chat',
            no_answer: 'No Answer', rescheduled: 'Rescheduled', completed: 'Completed',
            blocked: 'Blocked', reconsider: 'Reconsider', needs_documents: 'Needs Docs'
        };
        const statOrder = ['total', 'pending', 'completed', 'needs_documents', 'rescheduled', 'blocked'];
        
        statsContainer.innerHTML = statOrder.map(key => {
            const isActive = filters.main_system_status === key;
            return `
            <div data-filter-key="main_system_status" data-filter-value="${key}"
                 class="stat-card p-4 rounded-lg shadow-md cursor-pointer transition-transform transform hover:scale-105 ${isActive ? 'bg-blue-600 shadow-lg' : 'bg-white'}">
                <h3 class="text-lg font-semibold ${isActive ? 'text-white' : 'text-gray-500'}">${statusLabels[key] || formatStatus(key)}</h3>
                <p class="text-3xl font-bold ${isActive ? 'text-white' : 'text-gray-800'}">${statsData[key] || 0}</p>
            </div>`;
        }).join('');
    };

    const renderTable = () => {
        tbody.innerHTML = '';
        loadingRow.style.display = isLoading ? 'table-row' : 'none';

        if (!isLoading && drivers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-500">No drivers found for the selected filters.</td></tr>`;
        } else {
            drivers.forEach(driver => {
                const isChecked = selectedDrivers.includes(driver.id.toString());
                const row = tbody.insertRow();
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="p-4"><input type="checkbox" class="driver-checkbox" value="${driver.id}" ${isChecked ? 'checked' : ''}></td>
                    <td class="td">
                        <a href="<?= URLROOT ?>/drivers/details/${driver.id}" class="font-semibold text-blue-600 hover:underline">${driver.name}</a>
                        <p class="text-sm text-gray-500">${driver.phone}</p>
                    </td>
                    <td class="td"><span class="status-badge ${getStatusColor(driver.main_system_status)}">${formatStatus(driver.main_system_status)}</span></td>
                    <td class="td"><span class="status-badge ${getStatusColor(driver.app_status)}">${formatStatus(driver.app_status)}</span></td>
                    <td class="td">
                        <p>Calls: ${driver.call_count}</p>
                        <p class="${driver.missing_documents_count > 0 ? 'text-red-600' : 'text-green-600'}">Missing Docs: ${driver.missing_documents_count}</p>
                        <p>Car: ${driver.car_type_name || 'N/A'}</p>
                        <p>${driver.has_many_trips == 1 ? '> 10 Trips' : '<= 10 Trips'}</p>
                    </td>
                    <td class="td">
                        <a href="<?= URLROOT ?>/drivers/details/${driver.id}" class="text-blue-500 hover:text-blue-700">View</a>
                    </td>
                `;
            });
        }
        updateBulkActionsVisibility();
        renderPagination();
    };

    const renderPagination = () => {
        if (!isLoading && pagination.total_pages > 1) {
            paginationContainer.style.display = 'flex';
            const start = pagination.total > 0 ? (pagination.page - 1) * pagination.limit + 1 : 0;
            paginationInfo.textContent = `Showing ${start} to ${Math.min(pagination.page * pagination.limit, pagination.total)} of ${pagination.total} results`;
            prevButton.disabled = pagination.page <= 1;
            nextButton.disabled = pagination.page >= pagination.total_pages;
        } else {
            paginationContainer.style.display = 'none';
        }
    };

    const fetchDrivers = (page = 1) => {
        isLoading = true;
        pagination.page = page;
        
        // Update filters object from DOM
        filters.search_term = searchInput.value;
        filters.main_system_status = document.getElementById('filter_main_system_status').value;
        filters.app_status = document.getElementById('filter_app_status').value;
        filters.car_type_id = document.getElementById('filter_car_type_id').value;
        filters.has_many_trips = document.getElementById('filter_has_many_trips').value;
        filters.has_missing_documents = document.getElementById('filter_has_missing_documents').value;

        const params = new URLSearchParams({...filters, page, limit: pagination.limit});
        
        renderTable();

        fetch(`<?= URLROOT ?>/listings/get_drivers_api?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data && !data.error) {
                    drivers = data.data;
                    pagination.total = data.total;
                    pagination.total_pages = data.total_pages;
                } else {
                    drivers = [];
                    console.error('API Error:', data.error);
                }
            })
            .catch(err => { console.error('Fetch Error:', err); drivers = []; })
            .finally(() => { isLoading = false; renderTable(); });
    };

    const updateBulkActionsVisibility = () => {
        if (selectedDrivers.length > 0) {
            bulkActionsContainer.style.display = 'flex';
            selectedDriversCount.textContent = `${selectedDrivers.length} selected`;
        } else {
            bulkActionsContainer.style.display = 'none';
        }
    };

    const applyBulkAction = () => {
        const action = bulkActionSelect.value;
        if (!action || selectedDrivers.length === 0) {
            alert('Please select an action and at least one driver.');
            return;
        }
        if (!confirm(`Are you sure you want to apply this action to ${selectedDrivers.length} drivers?`)) return;

        const [field, value] = action.split('-');
        
        fetch(`<?= URLROOT ?>/listings/bulk_update_drivers`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ driver_ids: selectedDrivers, field, value })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status) {
                alert(data.message);
                fetchDrivers();
                selectedDrivers = [];
                bulkActionSelect.value = '';
                updateBulkActionsVisibility();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('An unexpected error occurred.'));
    };

    // EVENT LISTENERS
    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchDrivers(), 500);
    });

    filterSelects.forEach(select => select.addEventListener('change', () => fetchDrivers()));
    
    statsContainer.addEventListener('click', (e) => {
        const card = e.target.closest('.stat-card');
        if (card) {
            const key = card.dataset.filterKey;
            let value = card.dataset.filterValue;
            value = value === 'total' ? '' : value;
            
            if (filters[key] === value) {
                filters[key] = ''; // Toggle off
            } else {
                filters[key] = value;
            }
            document.getElementById('filter_main_system_status').value = filters.main_system_status;
            renderStats();
            fetchDrivers();
        }
    });

    prevButton.addEventListener('click', () => { if(pagination.page > 1) fetchDrivers(pagination.page - 1) });
    nextButton.addEventListener('click', () => { if(pagination.page < pagination.total_pages) fetchDrivers(pagination.page + 1) });
    
    selectAllCheckbox.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        selectedDrivers = [];
        const checkboxes = document.querySelectorAll('.driver-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            if (isChecked) {
                selectedDrivers.push(checkbox.value);
            }
        });
        updateBulkActionsVisibility();
    });

    tbody.addEventListener('change', (e) => {
        if (e.target.classList.contains('driver-checkbox')) {
            const id = e.target.value;
            if (e.target.checked) {
                if (!selectedDrivers.includes(id)) selectedDrivers.push(id);
            } else {
                selectedDrivers = selectedDrivers.filter(driverId => driverId !== id);
            }
            updateBulkActionsVisibility();
        }
    });

    applyBulkActionBtn.addEventListener('click', applyBulkAction);

    // INITIALIZATION
    renderStats();
    fetchDrivers();
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
