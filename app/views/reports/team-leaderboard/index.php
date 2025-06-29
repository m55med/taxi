<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-6" x-data="reportFilters()">
    <!-- Page Header -->
    <div class="flex-grow">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($data['page_main_title']) ?></h1>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="<?= BASE_PATH ?>/reports/team-leaderboard" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit" class="w-full md:w-auto inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <button @click.prevent="clearFilters()" class="w-full md:w-auto inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Rank</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-5/12">Team</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Total Points</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Total Tickets</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Total Calls</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['leaderboard'])): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available for the selected period.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['leaderboard'] as $index => $team): ?>
                        <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-gray-100">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
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
                                <a href="<?= BASE_PATH ?>/reports/users?<?= $usersReportParams ?>" class="text-indigo-600 hover:text-indigo-900" title="View users in <?= htmlspecialchars($team['team_name']) ?>">
                                    <?= htmlspecialchars($team['team_name']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                <?= number_format($team['total_points'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-ticket-alt text-purple-500 mr-2"></i>
                                <?= number_format($team['total_tickets']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-phone-alt text-green-500 mr-2"></i>
                                <?= number_format($team['total_calls']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function reportFilters() {
    return {
        clearFilters() {
            window.location.href = window.location.pathname;
        }
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
