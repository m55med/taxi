<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="reportFilters()">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Team Leaderboard</h1>
        <p class="text-sm text-gray-500 mt-1">Analyze team performance based on points, calls, tickets, and quality.</p>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php 
            $stats = [
                ['label' => 'Total Teams', 'value' => number_format($data['summary_stats']['total_teams'] ?? 0), 'icon' => 'fa-users', 'color' => 'text-indigo-500'],
                ['label' => 'Total Points', 'value' => number_format($data['summary_stats']['total_points'] ?? 0, 2), 'icon' => 'fa-star', 'color' => 'text-yellow-500'],
                ['label' => 'Total Calls', 'value' => number_format($data['summary_stats']['total_calls'] ?? 0), 'icon' => 'fa-phone-alt', 'color' => 'text-blue-500'],
                ['label' => 'Total Tickets', 'value' => number_format($data['summary_stats']['total_tickets'] ?? 0), 'icon' => 'fa-ticket-alt', 'color' => 'text-green-500'],
            ];
        ?>
        <?php foreach ($stats as $stat): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4">
            <div class="bg-gray-100 rounded-full p-3">
                <i class="fas <?= $stat['icon'] ?> fa-lg <?= $stat['color'] ?>"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500"><?= $stat['label'] ?></p>
                <p class="text-2xl font-bold text-gray-800"><?= $stat['value'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>


    <!-- Filters Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filters & Export</h2>
        <form id="filterForm" method="GET" action="">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Date From Filter -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" id="date_from" name="date_from" x-model="dateFrom" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
                <!-- Date To Filter -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" id="date_to" name="date_to" x-model="dateTo" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-gray-500 mr-2">Quick Range:</span>
                    <button type="button" @click="setDateRange('today')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Today</button>
                    <button type="button" @click="setDateRange('7days')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">7 Days</button>
                    <button type="button" @click="setDateRange('this_month')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">This Month</button>
                    <button type="button" @click="setDateRange('all_time')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">All Time</button>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= BASE_PATH ?>/reports/team-leaderboard" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium flex items-center transition">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                    
                    <!-- Export Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 text-sm font-medium flex items-center transition">
                            <i class="fas fa-download mr-2"></i>
                            <span>Export</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20" style="display:none;">
                            <a href="<?= BASE_PATH ?>/reports/team-leaderboard/export?export_type=excel&<?= http_build_query($data['filters']) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-excel mr-2 text-green-500"></i>Export as Excel
                            </a>
                            <a href="<?= BASE_PATH ?>/reports/team-leaderboard/export?export_type=json&<?= http_build_query($data['filters']) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-code mr-2 text-blue-500"></i>Export as JSON
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center transition">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-12">Rank</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Team</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Members</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Points</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Quality</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Calls</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tickets</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['leaderboard'])): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search fa-4x text-gray-300 mb-4"></i>
                                    <h3 class="text-xl font-medium">No Data Found</h3>
                                    <p class="text-sm">No team data available for the selected period.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['leaderboard'] as $index => $team): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                        <?php if ($index === 0) echo 'bg-yellow-400 text-white'; 
                                            elseif ($index === 1) echo 'bg-gray-400 text-white';
                                            elseif ($index === 2) echo 'bg-yellow-600 text-white';
                                            else echo 'bg-gray-200 text-gray-600'; ?>">
                                        <?= $index + 1 ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    <?php
                                        $usersReportParams = http_build_query([
                                            'team_id' => $team['team_id'],
                                            'date_from' => $data['filters']['date_from'],
                                            'date_to' => $data['filters']['date_to']
                                        ]);
                                    ?>
                                    <a href="<?= BASE_PATH ?>/reports/users?<?= $usersReportParams ?>" class="text-indigo-600 hover:text-indigo-900 font-semibold" title="View users in <?= htmlspecialchars($team['team_name']) ?>">
                                        <?= htmlspecialchars($team['team_name']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    <?= number_format($team['member_count']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-600">
                                    <?= number_format($team['total_points'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    <?= number_format($team['avg_quality_score'], 2) ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    <?= number_format($team['total_calls']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    <?= number_format($team['total_tickets']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function reportFilters() {
    return {
        dateFrom: '<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>',
        dateTo: '<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>',
        
        setDateRange(period) {
            const today = new Date();
            let fromDate = new Date();
            let toDate = new Date();

            switch (period) {
                case 'today':
                    break; 
                case '7days':
                    fromDate.setDate(today.getDate() - 6);
                    break;
                case 'this_month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'all_time':
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.submitForm();
                    return;
            }

            this.dateFrom = fromDate.toISOString().split('T')[0];
            this.dateTo = toDate.toISOString().split('T')[0];
            this.submitForm();
        },
        
        submitForm() {
            // Give Alpine time to update the inputs before submitting
            this.$nextTick(() => {
                document.getElementById('filterForm').submit();
            });
        }
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
