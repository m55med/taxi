<?php require APPROOT . '/views/includes/header.php'; ?>

<!-- Debug preview -->

<style>
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        animation: fadeIn 0.8s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800"><?= htmlspecialchars($data['title'] ?? 'Analytics Dashboard') ?></h1>
            <p class="text-lg text-gray-600">An overview of system performance and key metrics.</p>
        </div>
        <div class="flex items-center mt-4 md:mt-0">
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition mr-2">
                <i class="fas fa-file-excel mr-2"></i>Export to Excel
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'json'])) ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                <i class="fas fa-file-code mr-2"></i>Export to JSON
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex-grow">
                    <label for="period" class="block text-sm font-medium text-gray-700">Quick Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="custom" <?= ($data['filters']['period'] ?? 'custom') == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        <option value="today" <?= ($data['filters']['period'] ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="7days" <?= ($data['filters']['period'] ?? '') == '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="30days" <?= ($data['filters']['period'] ?? '') == '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="all" <?= ($data['filters']['period'] ?? '') == 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Analytics Sections -->
    <div class="space-y-8">
        <!-- Driver Acquisition Analytics -->
        <?php if (!empty($data['driver_conversion'])): ?>
        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Driver Acquisition Analytics</h2>
            <div class="chart-container">
                <canvas id="driverConversionChart"></canvas>
            </div>
        </section>
        <?php endif; ?>

        <!-- Call Center Analytics -->
        <?php if (!empty($data['call_center_stats'])): ?>
        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Call Center Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="chart-container">
                    <canvas id="callOutcomesChart"></canvas>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-3">Staff Performance</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-left py-2 px-3 font-semibold text-sm">Employee</th>
                                    <th class="text-left py-2 px-3 font-semibold text-sm">Total Calls</th>
                                    <th class="text-left py-2 px-3 font-semibold text-sm">Answered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['call_center_stats']['performance'] as $perf): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-3">
                                        <a href="<?= URLROOT ?>/reports/myactivity?user_id=<?= $perf['user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($perf['user_name']) ?></a>
                                    </td>
                                    <td class="py-2 px-3"><?= htmlspecialchars($perf['total_calls']) ?></td>
                                    <td class="py-2 px-3"><?= htmlspecialchars($perf['answered_calls']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Ticketing System Analytics -->
        <?php if (!empty($data['ticketing_stats'])): ?>
        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Ticketing System Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="chart-container">
                    <canvas id="ticketsByPlatformChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="ticketsByStatusChart"></canvas>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const randomColor = () => `rgba(${Math.floor(Math.random()*255)},${Math.floor(Math.random()*255)},${Math.floor(Math.random()*255)},0.7)`;

    document.getElementById('period').addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('filter-form').submit();
        }
    });

    <?php if (!empty($data['driver_conversion'])): ?>
    const driverCtx = document.getElementById('driverConversionChart').getContext('2d');
    const driverData = <?= json_encode($data['driver_conversion']) ?>;
    new Chart(driverCtx, {
        type: 'bar',
        data: {
            labels: driverData.map(d => d.data_source),
            datasets: [{
                label: 'Conversion Rate (%)',
                data: driverData.map(d => parseFloat(d.conversion_rate)),
                backgroundColor: driverData.map(() => randomColor())
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { title: { display: true, text: 'Driver Conversion Rate by Source' } },
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
    <?php endif; ?>

    <?php if (!empty($data['call_center_stats']['outcomes'])): ?>
    const callCtx = document.getElementById('callOutcomesChart').getContext('2d');
    const callData = <?= json_encode($data['call_center_stats']['outcomes']) ?>;
    new Chart(callCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(callData),
            datasets: [{
                data: Object.values(callData),
                backgroundColor: Object.keys(callData).map(() => randomColor())
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>

    <?php if (!empty($data['ticketing_stats']['by_platform'])): ?>
    const platformCtx = document.getElementById('ticketsByPlatformChart').getContext('2d');
    const platformData = <?= json_encode($data['ticketing_stats']['by_platform']) ?>;
    new Chart(platformCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(platformData),
            datasets: [{
                data: Object.values(platformData),
                backgroundColor: Object.keys(platformData).map(() => randomColor())
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>

    <?php if (!empty($data['ticketing_stats']['by_status'])): ?>
    const statusCtx = document.getElementById('ticketsByStatusChart').getContext('2d');
    const statusData = <?= json_encode($data['ticketing_stats']['by_status']) ?>;
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
