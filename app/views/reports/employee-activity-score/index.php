<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Employee Activity Score</h1>
            <p class="text-lg text-gray-600">A ranked list of employees based on performance points.</p>
        </div>
        <div class="flex items-center mt-4 md:mt-0">
             <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition mr-2">
                <i class="fas fa-file-excel mr-2"></i>Excel
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'json'])) ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                <i class="fas fa-file-code mr-2"></i>JSON
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">
                <div class="xl:col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700">Employee Name</label>
                    <input type="text" name="search" id="search" placeholder="Search by name..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                    <select name="team_id" id="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Teams</option>
                        <?php foreach($data['teams'] as $team): ?>
                            <option value="<?= $team->id ?>" <?= ($data['filters']['team_id'] ?? '') == $team->id ? 'selected' : '' ?>><?= htmlspecialchars($team->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role_id" id="role_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Roles</option>
                        <?php foreach ($data['roles'] as $role): ?>
                            <option value="<?= $role->id ?>" <?= ($data['filters']['role_id'] ?? '') == $role->id ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $role->name))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700">Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="custom" <?= ($data['filters']['period'] ?? 'custom') == 'custom' ? 'selected' : '' ?>>Custom</option>
                        <option value="today" <?= ($data['filters']['period'] ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="7days" <?= ($data['filters']['period'] ?? '') == '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="30days" <?= ($data['filters']['period'] ?? '') == '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="all" <?= ($data['filters']['period'] ?? '') == 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Filter</button>
                </div>
                <input type="date" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="hidden">
                <input type="date" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="hidden">
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Score Table -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Team</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($data['scores'])): ?>
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">No employees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['scores'] as $index => $user): 
                                $rank = (($data['current_page'] - 1) * 25) + $index + 1;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-center">
                                         <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                            <?php if ($rank === 1) echo 'bg-yellow-400 text-white'; 
                                                elseif ($rank === 2) echo 'bg-gray-400 text-white';
                                                elseif ($rank === 3) echo 'bg-yellow-600 text-white';
                                                else echo 'bg-gray-200 text-gray-600'; ?>">
                                            <?= $rank ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= htmlspecialchars($user['team_name'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-4 text-center font-bold text-indigo-600"><?= number_format($user['points_details']['final_total_points'] ?? 0, 2) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Activity</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
             <!-- Pagination -->
            <div class="p-4">
                <?php require APPROOT . '/views/includes/pagination_controls.php'; ?>
            </div>
        </div>
        <!-- Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div style="height: 450px;">
                <canvas id="topEmployeesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logic to submit form when quick period is changed
    document.getElementById('period').addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('filter-form').submit();
        }
    });

    // Top Employees Chart
    const topEmployeesCtx = document.getElementById('topEmployeesChart').getContext('2d');
    const scoresData = <?= json_encode(array_slice($data['scores'], 0, 10)) ?>;
    
    new Chart(topEmployeesCtx, {
        type: 'bar',
        data: {
            labels: scoresData.map(d => d.username),
            datasets: [{
                label: 'Activity Score',
                data: scoresData.map(d => parseFloat(d.points_details.final_total_points || 0).toFixed(2)),
                backgroundColor: 'rgba(29, 78, 216, 0.7)',
                borderColor: 'rgba(29, 78, 216, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { 
                title: { display: true, text: 'Top 10 Employees by Activity Score', font: {size: 16} },
                legend: { display: false }
            },
            scales: { x: { beginAtZero: true } }
        }
    });
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?> 