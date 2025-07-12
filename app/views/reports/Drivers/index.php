<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Drivers Report</h1>
            <p class="text-lg text-gray-600">Comprehensive list of all drivers in the system.</p>
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
                    <label for="search" class="block text-sm font-medium text-gray-700">Driver Name/Phone</label>
                    <input type="text" name="search" id="search" placeholder="Search..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="main_system_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="main_system_status" id="main_system_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Statuses</option>
                        <?php foreach($data['filter_options']['statuses'] as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= ($data['filters']['main_system_status'] ?? '') == $status ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="data_source" class="block text-sm font-medium text-gray-700">Source</label>
                    <select name="data_source" id="data_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Sources</option>
                        <?php foreach($data['filter_options']['sources'] as $source): ?>
                            <option value="<?= htmlspecialchars($source) ?>" <?= ($data['filters']['data_source'] ?? '') == $source ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($source)) ?></option>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
         <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                 <div class="bg-blue-100 rounded-full p-3"><i class="fas fa-users fa-lg text-blue-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Drivers</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['total_drivers'] ?? 0) ?></p>
                </div>
            </div>
             <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                 <div class="bg-green-100 rounded-full p-3"><i class="fas fa-check-circle fa-lg text-green-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active (App)</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['active_drivers'] ?? 0) ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                 <div class="bg-red-100 rounded-full p-3"><i class="fas fa-ban fa-lg text-red-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Banned (App)</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['banned_drivers'] ?? 0) ?></p>
                </div>
            </div>
         </div>
         <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6"><canvas id="sourceChart"></canvas></div>
         <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6"><canvas id="statusChart"></canvas></div>
    </div>


    <!-- Drivers Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Country</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">System Status</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Registered</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['drivers'])): ?>
                        <tr><td colspan="6" class="text-center py-10 text-gray-500">No drivers found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['drivers'] as $driver): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/drivers/details/<?= $driver['id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($driver['name']) ?></a></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($driver['phone']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($driver['country_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $driver['main_system_status']))) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center"><span class="text-sm"><?= htmlspecialchars(ucfirst($driver['data_source'])) ?></span></td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"><?= date('M j, Y', strtotime($driver['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('period').addEventListener('change', function() {
        if (this.value !== 'custom') document.getElementById('filter-form').submit();
    });

    const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom'} } };

    // Source Chart
    const sourceCtx = document.getElementById('sourceChart').getContext('2d');
    const sourceData = <?= json_encode($data['stats']['source_distribution'] ?? []) ?>;
    new Chart(sourceCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(sourceData),
            datasets: [{ data: Object.values(sourceData), backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#6B7280'] }]
        },
        options: { ...chartOptions, plugins: { ...chartOptions.plugins, title: { display: true, text: 'Drivers by Source' } } }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = <?= json_encode($data['stats']['status_distribution'] ?? []) ?>;
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(statusData).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{ data: Object.values(statusData), backgroundColor: ['#8B5CF6', '#EC4899', '#FBBF24', '#22C55E', '#3B82F6', '#6366F1', '#F97316'] }]
        },
        options: { ...chartOptions, plugins: { ...chartOptions.plugins, title: { display: true, text: 'Drivers by System Status' } } }
    });
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>