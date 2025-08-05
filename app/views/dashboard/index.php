<?php require_once APPROOT . '/views/includes/header.php'; ?>

<?php
// Make data easier to access and set defaults
$d = $data['dashboardData'];
$role = $d['user_role'] ?? 'agent';
$isPrivileged = in_array($role, ['admin', 'developer', 'quality_manager', 'Team_leader']);

// Helper function to get greeting based on time
function getGreeting() {
    $hour = date('H');
    if ($hour < 12) return "Good Morning";
    if ($hour < 18) return "Good Afternoon";
    return "Good Evening";
}
?>

<div class="p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Welcome Header -->
        <div class="border-0 shadow-lg bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold">
                            <?= getGreeting() ?>, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?>!
                            </h1>
                        </div>
                        <p class="text-sm opacity-90">
                            Welcome to your Customer Service Dashboard
                        </p>
                    </div>
                    <div class="flex flex-col items-start md:items-end gap-2 text-right">
                        <div class="flex items-center gap-2 text-sm opacity-90">
                            <i class="fas fa-clock"></i>
                            <div class="flex items-center gap-2 text-sm opacity-90">
    <i class="fas fa-clock"></i>
    <span id="local-time">--:--:--</span> <!-- سيتم ملؤه من JavaScript -->
</div>
                        </div>
                        <div class="flex items-center gap-2 text-sm opacity-90">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('l, F j, Y') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Tickets Stat Card -->
            <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500">Tickets</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $d['ticket_stats']['total_details'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">VIP: <?= $d['ticket_stats']['vip_details'] ?? 0 ?> | Normal: <?= $d['ticket_stats']['normal_details'] ?? 0 ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-cyan-400 to-blue-500">
                        <i class="fas fa-ticket-alt fa-lg"></i>
                    </div>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-gradient-to-r from-cyan-400 to-blue-500"></div>
            </div>

            <!-- Calls Stat Card -->
            <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500">Calls</p>
                        <p class="text-3xl font-bold text-gray-800"><?= ($d['call_stats']['incoming'] ?? 0) + ($d['call_stats']['outgoing'] ?? 0) ?></p>
                        <p class="text-xs text-gray-500">Outgoing: <?= $d['call_stats']['outgoing'] ?? 0 ?> | Incoming: <?= $d['call_stats']['incoming'] ?? 0 ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-green-400 to-teal-500">
                        <i class="fas fa-phone-alt fa-lg"></i>
                    </div>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-gradient-to-r from-green-400 to-teal-500"></div>
            </div>

            <!-- Drivers Stat Card -->
            <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500">Drivers</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $d['driver_stats']['total'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Active: <?= $d['driver_stats']['active'] ?? 0 ?> | Missing Docs: <?= $d['driver_stats']['missing_documents'] ?? 0 ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-yellow-400 to-amber-500">
                        <i class="fas fa-car fa-lg"></i>
                    </div>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-gradient-to-r from-yellow-400 to-amber-500"></div>
            </div>

            <!-- Users Stat Card -->
            <?php if ($isPrivileged && isset($d['user_stats'])): ?>
            <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500">Users</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $d['user_stats']['total'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Online: <?= $d['user_stats']['online'] ?? 0 ?> | Banned: <?= $d['user_stats']['banned'] ?? 0 ?></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-purple-500 to-indigo-500">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Activity Chart -->
                <?php if ($isPrivileged && isset($d['daily_trends'])): ?>
                <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Activity Trend</h3>
                        <p class="text-sm text-gray-500">Last 15 days performance overview</p>
                    </div>
                    <div class="h-80">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Leaderboards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Top Ticket Creators -->
                    <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 rounded-lg bg-blue-100 text-blue-600"><i class="fas fa-trophy"></i></div>
                            <h3 class="font-semibold text-gray-800">Top Ticket Creators</h3>
                        </div>
                        <div class="space-y-3">
                            <?php foreach (array_slice($d['leaderboards']['tickets'] ?? [], 0, 5) as $i => $entry): ?>
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border"><?= $i + 1 ?></span>
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <p class="text-sm font-medium text-gray-800 truncate flex-1"><?= htmlspecialchars($entry['name']) ?></p>
                                <span class="text-sm font-bold text-blue-600"><?= $entry['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Top Outgoing Callers -->
                    <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 rounded-lg bg-green-100 text-green-600"><i class="fas fa-phone-volume"></i></div>
                            <h3 class="font-semibold text-gray-800">Top Outgoing Callers</h3>
                        </div>
                        <div class="space-y-3">
                            <?php foreach (array_slice($d['leaderboards']['outgoing_calls'] ?? [], 0, 5) as $i => $entry): ?>
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border"><?= $i + 1 ?></span>
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <p class="text-sm font-medium text-gray-800 truncate flex-1"><?= htmlspecialchars($entry['name']) ?></p>
                                <span class="text-sm font-bold text-green-600"><?= $entry['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Top Incoming Handlers -->
                    <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600"><i class="fas fa-headset"></i></div>
                            <h3 class="font-semibold text-gray-800">Top Incoming Handlers</h3>
                        </div>
                        <div class="space-y-3">
                           <?php foreach (array_slice($d['leaderboards']['incoming_calls'] ?? [], 0, 5) as $i => $entry): ?>
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border"><?= $i + 1 ?></span>
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <p class="text-sm font-medium text-gray-800 truncate flex-1"><?= htmlspecialchars($entry['name']) ?></p>
                                <span class="text-sm font-bold text-indigo-600"><?= $entry['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Quick Actions -->
                <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Quick Actions</h3>
                        <p class="text-sm text-gray-500">Frequently used functions</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="<?= URLROOT ?>/create_ticket" class="h-auto p-4 flex flex-col items-start gap-2 group bg-blue-500 text-white rounded-lg">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-plus"></i><span class="font-medium">New Ticket</span></div>
                            <span class="text-xs opacity-80">Create a new ticket</span>
                        </a>
                        <a href="<?= URLROOT ?>/calls" class="h-auto p-4 flex flex-col items-start gap-2 group bg-green-500 text-white rounded-lg">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-phone"></i><span class="font-medium">New Call</span></div>
                            <span class="text-xs opacity-80">Log a new call</span>
                        </a>
                        <a href="<?= URLROOT ?>/discussions" class="h-auto p-4 flex flex-col items-start gap-2 group bg-gray-200 text-gray-800 rounded-lg">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-comments"></i><span class="font-medium">Discussions</span></div>
                             <span class="text-xs text-gray-500">View discussions</span>
                        </a>
                         <a href="<?= URLROOT ?>/profile" class="h-auto p-4 flex flex-col items-start gap-2 group bg-gray-200 text-gray-800 rounded-lg">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-user"></i><span class="font-medium">Profile</span></div>
                            <span class="text-xs text-gray-500">View your profile</span>
                        </a>
                        <a href="<?= URLROOT ?>/logout" class="h-auto p-4 flex flex-col items-start gap-2 group bg-red-500 text-white rounded-lg col-span-2">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-sign-out-alt"></i><span class="font-medium">Logout</span></div>
                            <span class="text-xs opacity-80">Sign out of your account</span>
                        </a>
                    </div>
                </div>
                
                <!-- Call Ratio Chart -->
                <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Call Distribution</h3>
                        <p class="text-sm text-gray-500">Outgoing vs Incoming calls ratio</p>
                    </div>
                    <div class="h-48 relative">
                         <canvas id="callRatioChart"></canvas>
                    </div>
                     <div class="flex justify-center gap-6 mt-4">
                        <div class="flex items-center gap-2">
                          <div class="w-3 h-3 rounded-full bg-green-500"></div>
                          <span class="text-sm text-gray-500">Outgoing (<?= $d['call_ratio']['outgoing'] ?? 0 ?>%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                          <span class="text-sm text-gray-500">Incoming (<?= $d['call_ratio']['incoming'] ?? 0 ?>%)</span>
                        </div>
                      </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function updateLocalTimeAndDate() {
    const now = new Date();

    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');

    const timeString = `${hours}:${minutes}:${seconds}`;

    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString(undefined, options);

    const timeEl = document.getElementById('local-time');
    const dateEl = document.getElementById('local-date');

    if (timeEl) timeEl.textContent = timeString;
    if (dateEl) dateEl.textContent = dateString;
}

setInterval(updateLocalTimeAndDate, 1000); // يحدث كل ثانية
updateLocalTimeAndDate(); // تشغيل أول مرة مباشرة
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Doughnut Chart for Call Ratio
    const callRatioCtx = document.getElementById('callRatioChart')?.getContext('2d');
    if (callRatioCtx) {
        new Chart(callRatioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Outgoing', 'Incoming'],
                datasets: [{
                    data: [<?= $d['call_ratio']['outgoing'] ?? 0 ?>, <?= $d['call_ratio']['incoming'] ?? 0 ?>],
                    backgroundColor: ['#22C55E', '#3B82F6'], // green-500, blue-500
                    hoverBackgroundColor: ['#16A34A', '#2563EB'],
                    borderColor: '#FFFFFF',
                    borderWidth: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: false
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
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tickets',
                        data: ticketData,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointRadius: 4,
                    },
                    {
                        label: 'Calls',
                        data: callData,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointRadius: 4,
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
                    legend: { position: 'top', align: 'end' },
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        backgroundColor: '#fff',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8
                    }
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
