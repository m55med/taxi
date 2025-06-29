<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-6" x-data="reportFilters()">
    <!-- Page Header -->
    <div class="flex-grow">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($data['page_main_title']) ?></h1>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="<?= BASE_PATH ?>/reports/employee-activity-score" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <!-- Team Filter -->
            <div>
                <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                <select id="team_id" name="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Teams</option>
                    <?php foreach ($data['teams'] as $team): ?>
                        <option value="<?= $team->id ?>" <?= ($data['filters']['team_id'] ?? '') == $team->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($team->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Role Filter -->
            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role_id" name="role_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Roles</option>
                    <?php foreach ($data['roles'] as $role): ?>
                        <option value="<?= $role->id ?>" <?= ($data['filters']['role_id'] ?? '') == $role->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($role->name)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Buttons -->
            <div class="md:col-span-4 flex items-end justify-start space-x-2">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <button @click.prevent="clearFilters()" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Employee Score Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Team</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Points</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tickets</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Calls</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['scores'])): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No data available for the selected filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['scores'] as $index => $user): ?>
                        <?php
                            $logParams = http_build_query([
                                'user_id' => $user['id'],
                                'date_from' => $data['filters']['date_from'],
                                'date_to' => $data['filters']['date_to']
                            ]);
                        ?>
                        <tr class="hover:bg-gray-50">
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
                                <a href="<?= BASE_PATH ?>/logs?<?= $logParams ?>" class="text-indigo-600 hover:text-indigo-900" title="View activity for <?= htmlspecialchars($user['username']) ?>">
                                    <?= htmlspecialchars($user['username']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['team_name'] ?? 'N/A') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                <i class="fas fa-star text-yellow-500 mr-1"></i>
                                <?= number_format($user['points_details']['final_total_points'] ?? 0, 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-ticket-alt text-purple-500 mr-1"></i>
                                <?= number_format(($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0)) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-phone-alt text-green-500 mr-1"></i>
                                <?= number_format(($user['call_stats']['total_incoming_calls'] ?? 0) + ($user['call_stats']['total_outgoing_calls'] ?? 0)) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?= BASE_PATH ?>/logs?<?= $logParams ?>" class="text-indigo-600 hover:text-indigo-900" title="View detailed activity log">
                                    View Activity
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
function reportFilters() {
    return {
        clearFilters() {
            window.location.href = window.location.pathname;
        }
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 