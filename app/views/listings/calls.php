<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Call Logs</h1>
    <p class="text-gray-600 mb-8">Browse and filter through all recorded incoming and outgoing calls.</p>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card bg-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500"><?= \App\Core\Auth::hasRole('agent') ? 'My Total Calls' : 'Total Calls' ?></p>
                <p class="text-3xl font-bold text-gray-800"><?= $data['stats']['total'] ?? 0 ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-phone-alt text-blue-500 text-2xl"></i>
            </div>
        </div>
        <div class="stat-card bg-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500"><?= \App\Core\Auth::hasRole('agent') ? 'My Incoming Calls' : 'Incoming Calls' ?></p>
                <p class="text-3xl font-bold text-gray-800"><?= $data['stats']['incoming'] ?? 0 ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-arrow-down text-green-500 text-2xl"></i>
            </div>
        </div>
        <div class="stat-card bg-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500"><?= \App\Core\Auth::hasRole('agent') ? 'My Outgoing Calls' : 'Outgoing Calls' ?></p>
                <p class="text-3xl font-bold text-gray-800"><?= $data['stats']['outgoing'] ?? 0 ?></p>
            </div>
            <div class="bg-orange-100 rounded-full p-3">
                <i class="fas fa-arrow-up text-orange-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-lg mb-8">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800">Filter & Search</h3>
            <p class="mt-1 text-sm text-gray-500">Use the filters below to refine your search.</p>
        </div>
        <form id="filter-form" method="GET" action="" class="p-6 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label for="search_term" class="block text-sm font-medium text-gray-700 mb-2">Search Contact</label>
                    <input type="text" id="search_term" name="search_term" class="filter-input" value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>" placeholder="Name or Phone...">
                </div>
                <!-- User -->
                <?php if (!\App\Core\Auth::hasRole('agent')): ?>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select id="user_id" name="user_id" class="filter-input">
                        <option value="">All Users</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?= $user->id ?>" <?= (isset($data['filters']['user_id']) && $data['filters']['user_id'] == $user->id) ? 'selected' : '' ?>><?= htmlspecialchars($user->username) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <!-- Call Type -->
                <div>
                    <label for="call_type" class="block text-sm font-medium text-gray-700 mb-2">Call Type</label>
                    <select id="call_type" name="call_type" class="filter-input">
                        <option value="all" <?= (!isset($data['filters']['call_type']) || $data['filters']['call_type'] == 'all') ? 'selected' : '' ?>>All Types</option>
                        <option value="incoming" <?= (isset($data['filters']['call_type']) && $data['filters']['call_type'] == 'incoming') ? 'selected' : '' ?>>Incoming</option>
                        <option value="outgoing" <?= (isset($data['filters']['call_type']) && $data['filters']['call_type'] == 'outgoing') ? 'selected' : '' ?>>Outgoing</option>
                    </select>
                </div>
            </div>
            <!-- Date Filters -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                 <div class="flex flex-wrap items-center gap-2 mb-4">
                    <button type="button" class="quick-date-btn" data-range="all">All Time</button>
                    <button type="button" class="quick-date-btn" data-range="today">Today</button>
                    <button type="button" class="quick-date-btn" data-range="week">This Week</button>
                    <button type="button" class="quick-date-btn" data-range="month">This Month</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                     <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-600 mb-2">From</label>
                        <input type="text" id="start_date" name="start_date" class="filter-input flatpickr-input" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-600 mb-2">To</label>
                        <input type="text" id="end_date" name="end_date" class="filter-input flatpickr-input" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>" placeholder="YYYY-MM-DD">
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="<?= URLROOT ?>/listings/calls" class="secondary-btn">
                    <i class="fas fa-undo mr-2"></i> Reset
                </a>
                <div class="flex items-center gap-4">
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                    <div class="relative">
                        <button type="button" id="export-menu-button" class="secondary-btn">
                            <i class="fas fa-download mr-2"></i> Export
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="export-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-10">
                             <div class="py-1">
                                <button type="submit" name="export" value="excel" class="export-btn"><i class="fas fa-file-excel mr-3 text-green-500"></i>Excel</button>
                                <button type="submit" name="export" value="csv" class="export-btn"><i class="fas fa-file-csv mr-3 text-blue-500"></i>CSV</button>
                                <button type="submit" name="export" value="pdf" class="export-btn"><i class="fas fa-file-pdf mr-3 text-red-500"></i>PDF</button>
                                <button type="submit" name="export" value="json" class="export-btn"><i class="fas fa-file-code mr-3 text-yellow-500"></i>JSON</button>
                                <button type="submit" name="export" value="txt" class="export-btn"><i class="fas fa-file-alt mr-3 text-gray-500"></i>Text</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="th">Type</th>
                        <th class="th">Contact</th>
                        <?php if (!\App\Core\Auth::hasRole('agent')): ?>
                        <th class="th">User</th>
                        <?php endif; ?>
                        <th class="th">Status</th>
                        <th class="th">Duration</th>
                        <th class="th">Date/Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['calls'])): ?>
                        <tr><td colspan="6" class="text-center py-10 text-gray-500">
                            <i class="fas fa-phone-slash text-4xl mb-3 text-gray-400"></i>
                            <p class="font-semibold">No calls found.</p>
                            <p class="text-sm">Try adjusting your filters.</p>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach($data['calls'] as $call): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="td">
                                    <?php if ($call['call_type'] === 'Incoming'): ?>
                                        <span class="status-badge bg-blue-100 text-blue-800"><i class="fas fa-arrow-down mr-2"></i><?= htmlspecialchars($call['call_type']) ?></span>
                                    <?php else: ?>
                                        <span class="status-badge bg-orange-100 text-orange-800"><i class="fas fa-arrow-up mr-2"></i><?= htmlspecialchars($call['call_type']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="td">
                                    <?php if($call['call_type'] === 'Outgoing' && !empty($call['contact_id'])): ?>
                                        <a href="<?= URLROOT ?>/drivers/details/<?= $call['contact_id'] ?>" class="link font-semibold"><?= htmlspecialchars($call['contact_name']) ?></a>
                                    <?php else: ?>
                                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($call['contact_name']) ?></span>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($call['contact_phone']) ?></p>
                                </td>
                                <?php if (!\App\Core\Auth::hasRole('agent')): ?>
                                <td class="td"><?= htmlspecialchars($call['user_name']) ?></td>
                                <?php endif; ?>
                                <td class="td">
                                    <span class="status-badge bg-gray-100 text-gray-800"><?= htmlspecialchars($call['status']) ?></span>
                                </td>
                                <td class="td">
                                    <?= !is_null($call['duration_seconds']) ? gmdate("H:i:s", $call['duration_seconds']) : '-' ?>
                                </td>
                                <td class="td text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($call['call_time'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($data['pagination']['total'] > 0): ?>
        <div class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
             <p class="text-sm text-gray-700">
                Showing <?= (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1 ?>
                to <?= min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total']) ?>
                of <?= $data['pagination']['total'] ?> results
            </p>
            <div class="flex items-center space-x-2">
                 <?php
                    $queryParams = http_build_query(array_merge($data['filters'], ['page' => $data['pagination']['current_page'] - 1]));
                    $isFirstPage = $data['pagination']['current_page'] <= 1;
                 ?>
                <a href="<?= $isFirstPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isFirstPage ? 'disabled' : '' ?>">Previous</a>
                
                 <?php
                    $queryParams = http_build_query(array_merge($data['filters'], ['page' => $data['pagination']['current_page'] + 1]));
                    $isLastPage = $data['pagination']['current_page'] >= $data['pagination']['total_pages'];
                 ?>
                <a href="<?= $isLastPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isLastPage ? 'disabled' : '' ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-input { @apply block w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
.primary-btn { @apply inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all; }
.secondary-btn { @apply inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all; }
.quick-date-btn { @apply px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 text-gray-700 transition-all duration-200 hover:bg-gray-300 focus:outline-none; }
.quick-date-btn.active { @apply bg-blue-600 text-white; }
.export-btn { @apply flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100; }
.th { @apply px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider; }
.td { @apply px-6 py-4 whitespace-nowrap text-sm text-gray-800; }
.link { @apply text-blue-600 hover:text-blue-800 hover:underline transition-colors; }
.status-badge { @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold; }
.pagination-btn { @apply px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50; }
.pagination-btn.disabled { @apply opacity-50 cursor-not-allowed; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Flatpickr for date range
    flatpickr(".flatpickr-input", {
        dateFormat: 'Y-m-d',
        allowInput: true,
    });
    
    // Quick Date Buttons
    const dateButtons = document.querySelectorAll('.quick-date-btn');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    dateButtons.forEach(button => {
        button.addEventListener('click', function() {
            dateButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const range = this.dataset.range;
            const today = new Date();
            let fromDate, toDate;

            switch(range) {
                case 'today':
                    fromDate = toDate = today.toISOString().slice(0, 10);
                    break;
                case 'week':
                    const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 1));
                    fromDate = firstDayOfWeek.toISOString().slice(0, 10);
                    toDate = new Date().toISOString().slice(0, 10);
                    break;
                case 'month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
                    toDate = new Date().toISOString().slice(0, 10);
                    break;
                case 'all':
                    fromDate = '';
                    toDate = '';
                    break;
            }
            startDateInput.value = fromDate;
            endDateInput.value = toDate;
        });
    });

    // Set initial active state for date buttons
    const currentFrom = startDateInput.value;
    const currentTo = endDateInput.value;
    if (currentFrom === '' && currentTo === '') {
        document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
    } else if (currentFrom === new Date().toISOString().slice(0, 10) && currentTo === new Date().toISOString().slice(0, 10)) {
         document.querySelector('.quick-date-btn[data-range="today"]')?.classList.add('active');
    }

    // Export Menu Toggle
    const exportButton = document.getElementById('export-menu-button');
    const exportMenu = document.getElementById('export-menu');
    if (exportButton) {
        exportButton.addEventListener('click', (event) => {
            event.stopPropagation();
            exportMenu.classList.toggle('hidden');
        });
    }
    document.addEventListener('click', (event) => {
        if (exportMenu && !exportMenu.classList.contains('hidden') && !exportButton.contains(event.target)) {
            exportMenu.classList.add('hidden');
        }
    });
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
