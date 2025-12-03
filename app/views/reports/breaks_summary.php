<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="p-4 lg:p-6" x-data="dateFilters('<?= $data['filters']['from_date'] ?? '' ?>', '<?= $data['filters']['to_date'] ?? '' ?>')">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-6">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-pause-circle mr-3 text-indigo-500"></i>
                <span>Breaks Report</span>
            </h1>
            <!-- Current Break Count Label -->
            <div class="bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 text-sm font-semibold px-4 py-2 rounded-full border border-orange-300 shadow-sm">
                <i class="fas fa-user-clock mr-2"></i>
                <span x-data="{ count: <?= $data['current_break_count'] ?? 0 ?> }" x-text="count"></span> Currently on Break
                <span class="inline-block w-2 h-2 bg-orange-500 rounded-full ml-2 animate-pulse"></span>
                <span class="text-xs opacity-75 ml-1">(Live)</span>
            </div>
        </div>
    </div>

    <!-- Auto-filter notification for Team Leaders -->
    <?php if (isset($data['auto_filtered_by_team']) && $data['auto_filtered_by_team']): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                <span class="text-blue-800 font-medium">
                    Showing breaks for your team: <strong><?= htmlspecialchars($data['selected_team_name'] ?? 'Your Team') ?></strong>
                    <a href="<?= URLROOT ?>/reports/breaks" class="text-blue-600 underline ml-2">Show All Teams</a>
                </span>
            </div>
        </div>
    <?php endif; ?>

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
                <p class="text-gray-500 text-sm">Total Employees</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_users ?? '0') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
    <form action="<?= URLROOT ?>/reports/breaks" method="get" class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <!-- Search Employee -->
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
    <!-- Filter by Team -->
    <div class="md:col-span-1">
        <label for="team_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Team</label>
        <select id="team_filter" name="team_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Teams</option>
            <?php foreach ($data['teams'] ?? [] as $team): ?>
                <option value="<?= $team->id ?>" <?= (isset($data['filters']['team_id']) && $data['filters']['team_id'] == $team->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($team->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
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
    <!-- On Break Filter -->
    <div class="md:col-span-1">
        <label class="flex items-center space-x-2 cursor-pointer">
            <input type="checkbox" name="on_break" value="1" <?= (isset($data['on_break_filter']) && $data['on_break_filter']) ? 'checked' : '' ?> class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="text-sm font-medium text-gray-700">Show Only On Break</span>
        </label>
    </div>
    <!-- Action Buttons -->
    <div class="md:col-span-1 flex items-end gap-2" style="grid-column: span 5;">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors w-full">Filter</button>
        <a href="<?= URLROOT ?>/reports/breaks" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">Reset</a>
    </div>
</form>

        <!-- Quick Filters -->
        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-sm font-medium text-gray-600">Quick Select:</span>
            <a href="#" onclick="applyQuickFilter('today')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'today', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'today' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Today</a>
            <a href="#" onclick="applyQuickFilter('last7')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last7', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last7' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 7 Days</a>
            <a href="#" onclick="applyQuickFilter('last30')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last30', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last30' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 30 Days</a>
            <a href="#" onclick="applyQuickFilter('this_month')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'this_month', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'this_month' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">This Month</a>
            <a href="#" onclick="applyQuickFilter('all')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'all', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'all' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">All Time</a>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-md p-6">
        <!-- Last Updated Indicator -->
        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
            <div class="text-sm text-gray-600">
                <i class="fas fa-clock mr-1"></i>
                Last updated: <span id="last-updated" class="font-mono"><?= date('H:i:s') ?></span>
            </div>
            <div class="flex items-center text-xs text-green-600">
                <i class="fas fa-circle mr-1 animate-pulse"></i>
                Auto-refresh every 30s
            </div>
        </div>
        <table class="min-w-full bg-white">
            <thead class="border-b-2 border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortBy('user_name')">Employee <span id="user_name-indicator">↓</span></th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Team</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Current Break</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortBy('total_breaks')">Total Breaks <span id="total_breaks-indicator">↓</span></th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortBy('total_duration_seconds')">Total Duration <span id="total_duration_seconds-indicator">↓</span></th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($data['summary'])): ?>
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No break data found for the selected criteria.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['summary'] as $row): ?>
                        <?php
                        $isOverLimit = isset($row->total_minutes) && $row->total_minutes > 900;
                        $rowClass = $isOverLimit ? 'border-b border-gray-200 hover:bg-red-50 bg-red-25' : 'border-b border-gray-200 hover:bg-gray-50';
                        ?>
                        <tr class="<?= $rowClass ?>" data-user-id="<?= $row->user_id ?>">
                            <td class="py-4 px-4">
                                <a href="<?= URLROOT ?>/reports/breaks/user/<?= $row->user_id ?>" class="text-indigo-600 font-medium hover:underline">
                                    <?= htmlspecialchars($row->user_name) ?>
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <?= htmlspecialchars($row->team_name ?? 'No Team') ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($row->current_break_info): ?>
                                    <?php
                                    $minutes = $row->current_break_info['minutes_elapsed'] ?? 0;
                                    $startTime = $row->current_break_info['start_time'] ?? '';
                                    $isLongBreak = $minutes >= 30;
                                    ?>
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                                            <span class="text-sm font-medium text-orange-700">On Break</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <?php
                                            $dt = new DateTime($startTime, new DateTimeZone('UTC'));
                                            $dt->setTimezone(new DateTimeZone('Africa/Cairo'));
                                            ?>
                                            <div>Started: <?= $dt->format('h:i:s A') ?></div>
                                            <div class="flex items-center space-x-1">
                                                <span>Duration:</span>
                                                <span class="font-mono <?= $isLongBreak ? 'text-red-600 font-bold' : 'text-green-600' ?>">
                                                    <?= floor($minutes / 60) ?>h <?= $minutes % 60 ?>m
                                                </span>
                                                <?php if ($isLongBreak): ?>
                                                    <span class="text-xs text-red-500">(30+ min)</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Not on break</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4"><?= $row->total_breaks ?></td>
                            <td class="py-4 px-4 font-mono <?= $isOverLimit ? 'text-red-600 font-bold' : '' ?>">
                                <?= htmlspecialchars($row->total_duration_formatted) ?>
                                <?php if ($isOverLimit): ?>
                                    <span class="text-xs text-red-500 ml-1">(Over 900 min)</span>
                                <?php endif; ?>
                            </td>
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

    // Function to apply quick filter while preserving other filters
    function applyQuickFilter(period) {
        const url = new URL(window.location);

        // Remove date-related parameters when using quick filters
        url.searchParams.delete('from_date');
        url.searchParams.delete('to_date');

        // Set the new period
        url.searchParams.set('period', period);

        // Keep all other parameters (user_id, team_id, sort_by, etc.)
        window.location.href = url.toString();
    }


    // Sorting functionality
    function sortBy(field) {
        const url = new URL(window.location);
        const currentSortBy = url.searchParams.get('sort_by');
        const currentSortOrder = url.searchParams.get('sort_order');

        if (currentSortBy === field) {
            // Toggle sort order if same field
            url.searchParams.set('sort_order', currentSortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            // New field, default to desc
            url.searchParams.set('sort_by', field);
            url.searchParams.set('sort_order', 'desc');
        }

        window.location.href = url.toString();
    }

    // Update sort indicators dynamically
    document.addEventListener('DOMContentLoaded', function() {
        const sortBy = '<?= $data['sort_by'] ?? 'total_duration_seconds' ?>';
        const sortOrder = '<?= $data['sort_order'] ?? 'desc' ?>';

        // Reset all indicators
        document.getElementById('user_name-indicator').textContent = '↓';
        document.getElementById('total_breaks-indicator').textContent = '↓';
        document.getElementById('total_duration_seconds-indicator').textContent = '↓';

        // Update current sort indicator
        const indicatorElement = document.getElementById(sortBy + '-indicator');
        if (indicatorElement) {
            indicatorElement.textContent = sortOrder === 'asc' ? '↑' : '↓';
        }

        // Auto-refresh break information every 30 seconds
        setInterval(function() {
            updateBreakInformation();
        }, 30000);
    });

    // Function to update break information dynamically
    function updateBreakInformation() {
        fetch('<?= URLROOT ?>/breaks/current')
            .then(response => response.json())
            .then(data => {
                // Update current break count
                const countElement = document.querySelector('[x-data*="count"]');
                if (countElement && countElement.textContent !== data.length.toString()) {
                    countElement.textContent = data.length;
                }

                // Update last updated timestamp
                const now = new Date();
                const lastUpdatedElement = document.getElementById('last-updated');
                if (lastUpdatedElement) {
                    lastUpdatedElement.textContent = now.toLocaleTimeString();
                }

                // Update table rows with current break status
                data.forEach(breakItem => {
                    const userRow = document.querySelector(`[data-user-id="${breakItem.user_id}"]`);
                    if (userRow) {
                        const breakCell = userRow.querySelector('td:nth-child(3)'); // Current Break column
                        if (breakCell) {
                            const minutes = breakItem.minutes_elapsed || 0;
                            const startTime = breakItem.start_time;
                            const isLongBreak = minutes >= 30;

                            breakCell.innerHTML = `
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                                        <span class="text-sm font-medium text-orange-700">On Break</span>
                                    </div>
                                    <div class="text-xs text-gray-600">
<div>Started: ${new Date(startTime).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true,
    timeZone: 'Africa/Cairo'
})}</div>
                                        <div class="flex items-center space-x-1">
                                            <span>Duration:</span>
                                            <span class="font-mono ${isLongBreak ? 'text-red-600 font-bold' : 'text-green-600'}">
                                                ${Math.floor(minutes / 60)}h ${minutes % 60}m
                                            </span>
                                            ${isLongBreak ? '<span class="text-xs text-red-500">(30+ min)</span>' : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error updating break information:', error);
            });
    }
</script>

<script src="<?= URLROOT ?>/js/components/searchable-select.js?v=<?= time() ?>"></script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
