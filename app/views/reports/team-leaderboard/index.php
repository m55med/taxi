<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Team Leaderboard</h1>
            <p class="text-lg text-gray-600">Analyze and compare team performance metrics.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
            </div>
        </form>
    </div>

    <!-- Summary Stats & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <div class="lg:col-span-1 space-y-6">
             <?php 
                $stats = [
                    ['label' => 'Total Teams', 'value' => number_format($data['summary_stats']['total_teams'] ?? 0), 'icon' => 'fa-users', 'color' => 'text-indigo-500'],
                    ['label' => 'Total Points', 'value' => number_format($data['summary_stats']['total_points'] ?? 0, 2), 'icon' => 'fa-star', 'color' => 'text-yellow-500'],
                    ['label' => 'Total Calls', 'value' => number_format($data['summary_stats']['total_calls'] ?? 0), 'icon' => 'fa-phone-alt', 'color' => 'text-blue-500'],
                    ['label' => 'Total Tickets', 'value' => number_format($data['summary_stats']['total_tickets'] ?? 0), 'icon' => 'fa-ticket-alt', 'color' => 'text-green-500'],
                ];
            ?>
            <?php foreach ($stats as $stat): ?>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center space-x-4">
                <div class="bg-gray-100 rounded-full p-3 flex-shrink-0">
                    <i class="fas <?= $stat['icon'] ?> fa-lg <?= $stat['color'] ?>"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500"><?= $stat['label'] ?></p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stat['value'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div style="height: 400px;">
                <canvas id="leaderboardChart"></canvas>
            </div>
        </div>
    </div>


    <!-- Leaderboard Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Team</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Members</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Points</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Quality</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Calls</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tickets</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['leaderboard'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">No team data found for the selected period.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['leaderboard'] as $index => $team): 
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    <a href="/reports/users?team_id=<?= $team['team_id'] ?>&<?= http_build_query(array_diff_key($data['filters'], ['period'=>''])) ?>" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        <?= htmlspecialchars($team['team_name']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600"><?= number_format($team['member_count']) ?></td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-indigo-600"><?= number_format($team['total_points'], 2) ?></td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600"><?= number_format($team['avg_quality_score'], 2) ?>%</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600"><?= number_format($team['total_calls']) ?></td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600"><?= number_format($team['total_tickets']) ?></td>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Quick period selector logic
    document.getElementById('period').addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('filter-form').submit();
        }
    });

    // Leaderboard Chart
    const leaderboardCtx = document.getElementById('leaderboardChart').getContext('2d');
    const leaderboardData = <?= json_encode(array_slice($data['leaderboard'], 0, 10)) ?>; // Top 10
    
    new Chart(leaderboardCtx, {
        type: 'bar',
        data: {
            labels: leaderboardData.map(d => d.team_name),
            datasets: [{
                label: 'Total Points',
                data: leaderboardData.map(d => parseFloat(d.total_points).toFixed(2)),
                backgroundColor: 'rgba(79, 70, 229, 0.7)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { 
                title: { display: true, text: 'Top 10 Teams by Points', font: {size: 16} },
                legend: { display: false }
            },
            scales: { 
                x: { 
                    beginAtZero: true 
                } 
            }
        }
    });
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
