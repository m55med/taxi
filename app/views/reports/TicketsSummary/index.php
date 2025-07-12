<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Tickets Summary Report</h1>
            <p class="text-lg text-gray-600">A high-level overview of ticket distribution.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md"><canvas id="statusChart"></canvas></div>
        <div class="bg-white p-6 rounded-lg shadow-md"><canvas id="vipChart"></canvas></div>
        <div class="bg-white p-6 rounded-lg shadow-md"><canvas id="platformChart"></canvas></div>
        <div class="bg-white p-6 rounded-lg shadow-md"><canvas id="categoryChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartOptions = (title) => ({
        responsive: true, maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'bottom' },
            title: { display: true, text: title, font: {size: 16} }
        }
    });
    const randomColor = () => `rgba(${Math.floor(Math.random()*200)+55}, ${Math.floor(Math.random()*200)+55}, ${Math.floor(Math.random()*200)+55}, 0.8)`;

    // Status Chart
    const statusData = <?= json_encode($data['summary']['by_status'] ?? []) ?>;
    if (Object.keys(statusData).length > 0) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{ data: Object.values(statusData), backgroundColor: ['#10B981', '#EF4444'] }]
            },
            options: chartOptions('Tickets by Status (Open/Closed)')
        });
    }

    // VIP Chart
    const vipData = <?= json_encode($data['summary']['by_vip_status'] ?? []) ?>;
     if (Object.keys(vipData).length > 0) {
        new Chart(document.getElementById('vipChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(vipData),
                datasets: [{ data: Object.values(vipData), backgroundColor: ['#8B5CF6', '#6B7280'] }]
            },
            options: chartOptions('Tickets by Type (VIP/Standard)')
        });
    }

    // Platform Chart
    const platformData = <?= json_encode($data['summary']['by_platform'] ?? []) ?>;
     if (Object.keys(platformData).length > 0) {
        new Chart(document.getElementById('platformChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(platformData),
                datasets: [{ data: Object.values(platformData), backgroundColor: Object.keys(platformData).map(() => randomColor()) }]
            },
            options: chartOptions('Tickets by Platform')
        });
    }

    // Category Chart
    const categoryData = <?= json_encode($data['summary']['by_category'] ?? []) ?>;
     if (Object.keys(categoryData).length > 0) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{ label: 'Total Tickets', data: Object.values(categoryData), backgroundColor: Object.keys(categoryData).map(() => randomColor()) }]
            },
            options: chartOptions('Tickets by Category')
        });
    }
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?> 