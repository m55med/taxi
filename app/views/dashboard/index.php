<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
// Make data easier to access and set defaults
$d = $data['dashboardData'];
$role = $d['user_role'] ?? 'agent';
$isPrivileged = in_array($role, ['admin', 'developer', 'quality_manager', 'Team_leader']);
?>

<div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen font-sans">
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>!</p>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
        
        <!-- Tickets Stats -->
        <div class="bg-white p-5 rounded-xl shadow-sm flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-500">Tickets</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $d['ticket_stats']['total_details'] ?? 0 ?></p>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="font-semibold text-purple-600">VIP:</span> <?= $d['ticket_stats']['vip_details'] ?? 0 ?> | 
                    <span class="font-semibold text-gray-600">Normal:</span> <?= $d['ticket_stats']['normal_details'] ?? 0 ?>
                </div>
            </div>
            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                <i class="fas fa-ticket-alt fa-lg"></i>
            </div>
        </div>

        <!-- Call Stats -->
        <div class="bg-white p-5 rounded-xl shadow-sm flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-500">Calls</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= ($d['call_stats']['incoming'] ?? 0) + ($d['call_stats']['outgoing'] ?? 0) ?></p>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="font-semibold text-green-600">Outgoing:</span> <?= $d['call_stats']['outgoing'] ?? 0 ?> | 
                    <span class="font-semibold text-blue-600">Incoming:</span> <?= $d['call_stats']['incoming'] ?? 0 ?>
                </div>
            </div>
            <div class="bg-green-100 text-green-600 p-3 rounded-full">
                <i class="fas fa-phone-alt fa-lg"></i>
            </div>
        </div>

        <!-- Driver Stats -->
        <div class="bg-white p-5 rounded-xl shadow-sm flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-500">Drivers</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $d['driver_stats']['total'] ?? 0 ?></p>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="font-semibold text-green-600">Active:</span> <?= $d['driver_stats']['active'] ?? 0 ?> | 
                    <span class="font-semibold text-red-600">Missing Docs:</span> <?= $d['driver_stats']['missing_documents'] ?? 0 ?>
                </div>
            </div>
            <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                <i class="fas fa-car fa-lg"></i>
            </div>
        </div>

        <!-- User Stats -->
        <?php if ($isPrivileged && isset($d['user_stats'])): ?>
        <div class="bg-white p-5 rounded-xl shadow-sm flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-500">Users</h3>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $d['user_stats']['total'] ?? 0 ?></p>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="font-semibold text-green-600">Online:</span> <?= $d['user_stats']['online'] ?? 0 ?> | 
                    <span class="font-semibold text-red-600">Banned:</span> <?= $d['user_stats']['banned'] ?? 0 ?>
                </div>
            </div>
            <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
                <i class="fas fa-users fa-lg"></i>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left/Main Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Chart -->
            <?php if ($isPrivileged && isset($d['daily_trends'])): ?>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Activity Trend (Last 15 Days)</h3>
                <div class="h-80">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <!-- Leaderboards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top Ticket Creators -->
                <div class="bg-white p-5 rounded-xl shadow-sm">
                    <h3 class="font-semibold text-gray-700 mb-3">Top Ticket Creators</h3>
                    <ul class="space-y-3">
                        <?php foreach (array_slice($d['leaderboards']['tickets'] ?? [], 0, 5) as $i => $entry): ?>
                        <li class="flex items-center">
                            <span class="text-sm font-bold text-gray-400 w-6"><?= $i + 1 ?></span>
                            <span class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($entry['name']) ?></span>
                            <span class="ml-auto text-sm font-bold text-indigo-600"><?= $entry['count'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- Top Outgoing Callers -->
                <div class="bg-white p-5 rounded-xl shadow-sm">
                    <h3 class="font-semibold text-gray-700 mb-3">Top Outgoing Callers</h3>
                    <ul class="space-y-3">
                         <?php foreach (array_slice($d['leaderboards']['outgoing_calls'] ?? [], 0, 5) as $i => $entry): ?>
                        <li class="flex items-center">
                            <span class="text-sm font-bold text-gray-400 w-6"><?= $i + 1 ?></span>
                            <span class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($entry['name']) ?></span>
                            <span class="ml-auto text-sm font-bold text-green-600"><?= $entry['count'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                 <!-- Top Incoming Call Handlers -->
                <div class="bg-white p-5 rounded-xl shadow-sm">
                    <h3 class="font-semibold text-gray-700 mb-3">Top Incoming Handlers</h3>
                    <ul class="space-y-3">
                        <?php foreach (array_slice($d['leaderboards']['incoming_calls'] ?? [], 0, 5) as $i => $entry): ?>
                        <li class="flex items-center">
                            <span class="text-sm font-bold text-gray-400 w-6"><?= $i + 1 ?></span>
                            <span class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($entry['name']) ?></span>
                            <span class="ml-auto text-sm font-bold text-blue-600"><?= $entry['count'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white p-5 rounded-xl shadow-sm">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?= URLROOT ?>/create_ticket" class="quick-action-btn bg-blue-500">New Ticket</a>
                    <a href="<?= URLROOT ?>/calls" class="quick-action-btn bg-green-500">New Call</a>
                    <?php if ($isPrivileged): ?>
                    <a href="<?= URLROOT ?>/quality/reviews" class="quick-action-btn bg-yellow-500">All Reviews</a>
                    <?php endif; ?>
                    <a href="<?= URLROOT ?>/discussions" class="quick-action-btn bg-indigo-500">Discussions</a>
                     <?php if (in_array($role, ['admin', 'developer'])): ?>
                        <a href="<?= URLROOT ?>/upload" class="quick-action-btn bg-gray-600">Upload Drivers</a>
                        <a href="<?= URLROOT ?>/admin/users" class="quick-action-btn bg-gray-600">User Settings</a>
                        <a href="<?= URLROOT ?>/admin/permissions" class="quick-action-btn bg-gray-600">Permissions</a>
                        <a href="<?= URLROOT ?>/logs" class="quick-action-btn bg-gray-600">System Log</a>
                    <?php endif; ?>
                    <a href="<?= URLROOT ?>/profile" class="quick-action-btn bg-purple-500">Profile</a>
                    <a href="<?= URLROOT ?>/logout" class="quick-action-btn bg-red-500">Logout</a>
                </div>
            </div>

            <!-- Call Ratio Chart -->
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Call Ratio</h3>
                <div class="h-48 w-48 mx-auto">
                    <canvas id="callRatioChart"></canvas>
                </div>
            </div>
            
             <!-- Marketer Stats -->
            <?php if (isset($d['marketer_stats'])): ?>
            <div class="bg-white p-5 rounded-xl shadow-sm">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Marketing</h3>
                <div class="text-sm space-y-2">
                    <p><strong>Visits:</strong> <span class="font-bold text-gray-700"><?= $d['marketer_stats']['visits'] ?></span></p>
                    <p><strong>Registrations:</strong> <span class="font-bold text-green-600"><?= $d['marketer_stats']['registrations'] ?></span></p>
                    <?php if(!empty($d['marketer_stats']['top_countries'])): ?>
                    <div>
                        <strong>Top Countries:</strong>
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <?php foreach($d['marketer_stats']['top_countries'] as $country => $count): ?>
                                <li><?= htmlspecialchars($country) ?>: <span class="font-semibold"><?= $count ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.quick-action-btn {
    @apply text-white p-3 rounded-lg text-center text-sm font-semibold transition-transform transform hover:scale-105;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const-white p-4 rounded-lg shadow-md a
    // Doughnut Chart for Call Ratio
    const callRatioCtx = document.getElementById('callRatioChart')?.getContext('2d');
    if (callRatioCtx) {
        new Chart(callRatioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Outgoing', 'Incoming'],
                datasets: [{
                    data: [<?= $d['call_ratio']['outgoing'] ?? 0 ?>, <?= $d['call_ratio']['incoming'] ?? 0 ?>],
                    backgroundColor: ['#10B981', '#3B82F6'], // green-500, blue-500
                    hoverBackgroundColor: ['#059669', '#2563EB'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (c) => `${c.label}: ${c.raw}%` }
                    }
                }
            }
        });
    }

    // Bar Chart for Activity Trend
    <?php if ($isPrivileged && isset($d['daily_trends'])): ?>
    const activityCtx = document.getElementById('activityChart')?.getContext('2d');
    if (activityCtx) {
        const trendData = <?= json_encode($d['daily_trends']) ?>;
        const labels = trendData.map(d => new Date(d.action_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const ticketData = trendData.map(d => d.tickets);
        const callData = trendData.map(d => d.calls);

        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tickets',
                        data: ticketData,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)', // blue-500
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Calls',
                        data: callData,
                        backgroundColor: 'rgba(16, 185, 129, 0.5)', // green-500
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#e5e7eb' } }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>