<?php require APPROOT . '/views/includes/header.php'; ?>
<div class="p-4 lg:p-6" x-data="dateFilters('<?= $data['filters']['from_date'] ?>', '<?= $data['filters']['to_date'] ?>')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-pause-circle mr-3 text-indigo-500"></i>
            <span>Breaks Report</span>
        </h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center">
            <div class="bg-blue-100 text-blue-500 p-4 rounded-full mr-4">
                <i class="fas fa-clock fa-2x"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Break Duration</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_duration_formatted ?? '00:00:00') ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center">
            <div class="bg-green-100 text-green-500 p-4 rounded-full mr-4">
                <i class="fas fa-coffee fa-2x"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Breaks Taken</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_breaks ?? '0') ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center">
            <div class="bg-purple-100 text-purple-500 p-4 rounded-full mr-4">
                <i class="fas fa-users fa-2x"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Employees on Break</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_users ?? '0') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
    <form action="<?= URLROOT ?>/reports/breaks" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <!-- Search -->
    <div class="md:col-span-1">
        <label for="search_employee" class="block text-sm font-medium text-gray-700 mb-1">Search Employee</label>
        <div x-data='searchableSelect(<?= json_encode($data["users"] ?? []) ?>)'
             x-init="init()"
             data-initial-value="<?= htmlspecialchars($data['filters']['user_id'] ?? '') ?>"
             data-initial-label="<?= htmlspecialchars($data['selected_user_name'] ?? '') ?>"
             data-model-name="user_id"
             data-placeholder="Select an employee..."
             class="relative">

            <input type="hidden" :name="modelName" :value="selected ? selected.id : ''">

            <button @click="toggle" type="button"
                    class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <span class="block truncate" x-text="selectedLabel"></span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <i class="fas fa-chevron-down h-5 w-5 text-gray-400"></i>
                </span>
            </button>

            <div x-show="open" @click.away="open = false" x-transition x-cloak
                 class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10">
                <div class="p-2">
                    <input type="text" x-model="searchTerm" x-ref="search"
                           class="w-full px-2 py-1 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Search employees..." autocomplete="off">
                </div>
                <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto focus:outline-none sm:text-sm">
                    <template x-for="option in filteredOptions" :key="option.id">
                        <li @click="selectOption(option)"
                            class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                            :class="{ 'bg-indigo-600 text-white': selected && selected.id == option.id }">
                            <span class="block truncate" x-text="option.name"></span>
                            <template x-if="selected && selected.id == option.id">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white">
                                    <i class="fas fa-check h-5 w-5"></i>
                                </span>
                            </template>
                        </li>
                    </template>
                    <template x-if="filteredOptions.length === 0">
                        <li class="text-gray-500 cursor-default select-none relative py-2 pl-3 pr-9">
                            No employees found.
                        </li>
                    </template>
                </ul>
            </div>
        </div>

    </div>
    <!-- Date Range -->
    <div class="md:col-span-2 grid grid-cols-2 gap-4">
        <div>
            <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <input type="date" name="from_date" id="from_date" class="w-full border-gray-300 rounded-lg shadow-sm" x-model="fromDate">
        </div>
        <div>
            <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <input type="date" name="to_date" id="to_date" class="w-full border-gray-300 rounded-lg shadow-sm" x-model="toDate">
        </div>
    </div>
    <!-- Action Buttons -->
    <div class="md:col-span-1 flex items-end gap-2">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors w-full">Filter</button>
        <a href="<?= URLROOT ?>/reports/breaks" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">Reset</a>
    </div>
</form>

        <!-- Quick Filters -->
        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-sm font-medium text-gray-600">Quick Select:</span>
            <a href="?period=today" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'today', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'today' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Today</a>
            <a href="?period=last7" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last7', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last7' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 7 Days</a>
            <a href="?period=last30" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last30', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last30' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 30 Days</a>
            <a href="?period=this_month" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'this_month', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'this_month' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">This Month</a>
            <a href="?period=all" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'all', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'all' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">All Time</a>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-md p-6">
        <table class="min-w-full bg-white">
            <thead class="border-b-2 border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Employee</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Total Breaks</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Total Duration</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($data['summary'])): ?>
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No break data found for the selected criteria.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['summary'] as $row): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <a href="<?= URLROOT ?>/reports/breaks/user/<?= $row->user_id ?>" class="text-indigo-600 font-medium hover:underline">
                                    <?= htmlspecialchars($row->user_name) ?>
                                </a>
                            </td>
                            <td class="py-4 px-4"><?= $row->total_breaks ?></td>
                            <td class="py-4 px-4 font-mono"><?= htmlspecialchars($row->total_duration_formatted) ?></td>
                            <td class="py-4 px-4">
                                <a href="<?= URLROOT ?>/reports/breaks/user/<?= $row->user_id ?>" class="bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-xs font-semibold hover:bg-indigo-200 transition-colors">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function dateFilters(fromDate, toDate) {
        return {
            fromDate: fromDate || '',
            toDate: toDate || '',
        }
    }
</script>

<script src="<?= URLROOT ?>/js/components/searchable-select.js?v=<?= time() ?>"></script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
