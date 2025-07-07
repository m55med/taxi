<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div dir="ltr" class="bg-gray-100 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">

        <!-- Header and Date Filters -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Dashboard</h1>
            
            <form id="dateFilterForm" method="POST" action="<?= BASE_PATH ?>/dashboard" class="flex flex-wrap items-center gap-4 bg-white p-4 rounded-lg shadow-sm">
                <div>
                    <label for="start_date" class="text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($data['startDate']) ?>" class="mt-1 block w-full md:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="end_date" class="text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($data['endDate']) ?>" class="mt-1 block w-full md:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Apply Filter</button>
                </div>
                <div class="flex items-end gap-2">
                    <button type="button" class="quick-filter-btn bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm" data-start="<?= $data['quickFilterDates']['today'] ?>" data-end="<?= $data['quickFilterDates']['today'] ?>">Today</button>
                    <button type="button" class="quick-filter-btn bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm" data-start="<?= $data['quickFilterDates']['last7days'] ?>" data-end="<?= $data['quickFilterDates']['today'] ?>">Last 7 Days</button>
                    <button type="button" class="quick-filter-btn bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm" data-start="<?= $data['quickFilterDates']['last30days'] ?>" data-end="<?= $data['quickFilterDates']['today'] ?>">Last 30 Days</button>
                    <a href="<?= BASE_PATH ?>/dashboard" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm">This Month</a>
                </div>
            </form>
        </div>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Users Stats -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Users</p>
                        <p class="text-3xl font-bold"><?php echo $dashboardData['user_stats']['total'] ?? 0; ?></p>
                    </div>
                    <div class="bg-blue-100 text-blue-500 rounded-full w-12 h-12 flex items-center justify-center">
                         <i class="fas fa-users text-2xl"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Active: <?php echo $dashboardData['user_stats']['status_breakdown']['active'] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Pending: <?php echo $dashboardData['user_stats']['status_breakdown']['pending'] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Banned: <?php echo $dashboardData['user_stats']['status_breakdown']['banned'] ?? 0; ?></span>
                </div>
            </div>

            <!-- Drivers Stats -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Total Drivers</p>
                        <p class="text-3xl font-bold"><?php echo $dashboardData['driver_stats']['total'] ?? 0; ?></p>
                    </div>
                    <div class="bg-green-100 text-green-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-id-card text-2xl"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">App Active: <?php echo $dashboardData['driver_stats']['app_status_breakdown']['active'] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Sys Active: <?php echo $dashboardData['driver_stats']['main_system_status_breakdown']['active'] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">On Hold: <?php echo $dashboardData['driver_stats']['on_hold'] ?? 0; ?></span>
                </div>
            </div>

            <!-- Tickets Stats -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Tickets This Period</p>
                        <p class="text-3xl font-bold"><?php echo $dashboardData['ticket_stats']['total'] ?? 0; ?></p>
                    </div>
                    <div class="bg-purple-100 text-purple-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-2xl"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">VIP: <?php echo $dashboardData['ticket_stats']['vip_breakdown'][1] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Normal: <?php echo $dashboardData['ticket_stats']['vip_breakdown'][0] ?? 0; ?></span>
                </div>
            </div>

            <!-- Coupons Stats -->
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500">Coupons Usage</p>
                        <p class="text-3xl font-bold"><?php echo ($dashboardData['coupon_stats']['used'] ?? 0) + ($dashboardData['coupon_stats']['unused'] ?? 0); ?></p>
                    </div>
                    <div class="bg-yellow-100 text-yellow-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-tags text-2xl"></i>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Used: <?php echo $dashboardData['coupon_stats']['used'] ?? 0; ?></span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Unused: <?php echo $dashboardData['coupon_stats']['unused'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Driver Status Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">Driver Status Breakdown</h3>
                <canvas id="driverStatusChart" style="max-height: 300px;"></canvas>
            </div>
            <!-- Ticket Types Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">Ticket Types (Period)</h3>
                <canvas id="ticketTypesChart" style="max-height: 300px;"></canvas>
            </div>
            <!-- User Status Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">User Status Breakdown</h3>
                <canvas id="userStatusChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Rankings Column -->
            <div class="lg:col-span-2 bg-white p-5 rounded-lg shadow-sm">
                <h3 class="font-semibold text-gray-700 mb-4 text-xl">Rankings</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Top Employees -->
                    <div>
                        <h4 class="font-medium text-gray-600 mb-3">Top Employees</h4>
                        <ol class="list-decimal list-inside space-y-2">
                            <?php foreach($dashboardData['rankings']['top_employees'] ?? [] as $name => $score): ?>
                                <li class="truncate"><?= htmlspecialchars($name) ?> <span class="text-sm text-gray-500 font-mono">(<?= $score ?>)</span></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <!-- Top Teams -->
                    <div>
                        <h4 class="font-medium text-gray-600 mb-3">Top Teams</h4>
                        <ol class="list-decimal list-inside space-y-2">
                            <?php foreach($dashboardData['rankings']['top_teams'] ?? [] as $name => $score): ?>
                                <li class="truncate"><?= htmlspecialchars($name) ?> <span class="text-sm text-gray-500 font-mono">(<?= $score ?>)</span></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <!-- Top Marketers -->
                    <div>
                        <h4 class="font-medium text-gray-600 mb-3">Top Marketers</h4>
                        <ol class="list-decimal list-inside space-y-2">
                            <?php foreach($dashboardData['rankings']['top_marketers'] ?? [] as $name => $score): ?>
                                <li class="truncate"><?= htmlspecialchars($name) ?> <span class="text-sm text-gray-500 font-mono">(<?= $score ?> visits)</span></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white p-5 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-gray-700 mb-4 text-xl">Quick Actions</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-4 text-center">
                        <?php $actions = [
                            'New Employee' => ['icon' => 'fa-user-plus', 'link' => '#'],
                            'New Driver' => ['icon' => 'fa-id-card', 'link' => '#'],
                            'New Coupon' => ['icon' => 'fa-tags', 'link' => '#'],
                            'Notification' => ['icon' => 'fa-bell', 'link' => '#'],
                            'Financial Report' => ['icon' => 'fa-file-invoice-dollar', 'link' => '#'],
                            'User Management' => ['icon' => 'fa-users-cog', 'link' => '#'],
                            'Settings' => ['icon' => 'fa-cogs', 'link' => '#'],
                            'View Tickets' => ['icon' => 'fa-ticket-alt', 'link' => '#'],
                            'Marketing' => ['icon' => 'fa-bullhorn', 'link' => '#'],
                            'Logout' => ['icon' => 'fa-sign-out-alt', 'link' => BASE_PATH . '/logout']
                        ]; ?>
                        <?php foreach ($actions as $title => $details) : ?>
                            <a href="<?php echo $details['link']; ?>" class="group flex flex-col items-center justify-center p-2 text-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                <div class="flex items-center justify-center w-16 h-16 bg-gray-100 group-hover:bg-blue-100 rounded-full mb-2 transition-colors duration-200">
                                    <i class="fas <?php echo $details['icon']; ?> text-2xl text-gray-500 group-hover:text-blue-500"></i>
                                </div>
                                <span class="text-sm font-semibold"><?php echo $title; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- System Info -->
                <div class="bg-white p-5 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-gray-700 mb-4 text-xl">System Info</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-600 mb-2">Countries</h4>
                            <div class="flex flex-wrap gap-2">
                               <?php foreach($dashboardData['quick_info']['countries'] ?? [] as $item): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($item) ?></span>
                               <?php endforeach; ?>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-600 mb-2 mt-3">Car Types</h4>
                            <div class="flex flex-wrap gap-2">
                               <?php foreach($dashboardData['quick_info']['car_types'] ?? [] as $item): ?>
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($item) ?></span>
                               <?php endforeach; ?>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-600 mb-2 mt-3">Platforms</h4>
                            <div class="flex flex-wrap gap-2">
                               <?php foreach($dashboardData['quick_info']['platforms'] ?? [] as $item): ?>
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($item) ?></span>
                               <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic for quick filter buttons
    const quickFilterButtons = document.querySelectorAll('.quick-filter-btn');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const filterForm = document.getElementById('dateFilterForm');

    quickFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            startDateInput.value = this.dataset.start;
            endDateInput.value = this.dataset.end;
            filterForm.submit();
        });
    });
});
</script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const CHART_COLORS = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)'
        };

        const chartColors = Object.values(CHART_COLORS);

        // --- Driver Status Chart ---
        const driverStatusCtx = document.getElementById('driverStatusChart')?.getContext('2d');
        if (driverStatusCtx) {
            const driverData = <?php echo json_encode($dashboardData['chart_data']['driver_status'] ?? []); ?>;
            new Chart(driverStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(driverData),
                    datasets: [{
                        label: 'Driver Status',
                        data: Object.values(driverData),
                        backgroundColor: chartColors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }
        
        // --- Ticket Types Chart ---
        const ticketTypesCtx = document.getElementById('ticketTypesChart')?.getContext('2d');
        if (ticketTypesCtx) {
            const ticketData = <?php echo json_encode($dashboardData['chart_data']['ticket_types'] ?? []); ?>;
            new Chart(ticketTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(ticketData),
                    datasets: [{
                        label: 'Ticket Types',
                        data: Object.values(ticketData),
                        backgroundColor: [CHART_COLORS.purple, CHART_COLORS.grey],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }

        // --- User Status Chart ---
        const userStatusCtx = document.getElementById('userStatusChart')?.getContext('2d');
        if (userStatusCtx) {
            const userData = <?php echo json_encode($dashboardData['chart_data']['user_status'] ?? []); ?>;
            new Chart(userStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(userData),
                    datasets: [{
                        label: 'User Status',
                        data: Object.values(userData),
                        backgroundColor: [CHART_COLORS.green, CHART_COLORS.yellow, CHART_COLORS.red, CHART_COLORS.blue],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }

    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>