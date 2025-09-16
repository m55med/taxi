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
                            <span id="local-time">--:--:--</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm opacity-90">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('l, F j, Y') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white p-4 border-0 shadow-lg rounded-xl">
            <form action="<?= URLROOT ?>/dashboard" method="GET" class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1 w-full">
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex-1 w-full">
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="pt-5 flex gap-2">
                    <!-- زر الفلترة -->
                    <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>

                    <!-- زر الريست -->
                    <a href="<?= URLROOT ?>/dashboard" 
                    class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border text-sm font-medium rounded-md shadow-sm text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>



        <!-- Stats Grid -->
        <div class="text-sm text-gray-500 mb-4">
            Showing data from <span class="font-semibold"><?= htmlspecialchars(date('M j, Y', strtotime($d['date_from']))) ?></span> to <span class="font-semibold"><?= htmlspecialchars(date('M j, Y', strtotime($d['date_to']))) ?></span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Tickets Stat Card -->
            <a href="<?= URLROOT ?>/listings/tickets" class="block">
                <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white rounded-xl p-6 cursor-pointer hover:scale-105 hover:bg-gray-50">
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
                    <div class="absolute top-2 right-2 opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-external-link-alt text-gray-400 text-sm"></i>
                    </div>
                </div>
            </a>

            <!-- Calls Stat Card -->
            <a href="<?= URLROOT ?>/listings/calls" class="block">
                <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white rounded-xl p-6 cursor-pointer hover:scale-105 hover:bg-gray-50">
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
                    <div class="absolute top-2 right-2 opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-external-link-alt text-gray-400 text-sm"></i>
                    </div>
                </div>
            </a>

            <!-- Drivers Stat Card -->
            <a href="<?= URLROOT ?>/listings/drivers" class="block">
                <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white rounded-xl p-6 cursor-pointer hover:scale-105 hover:bg-gray-50">
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
                    <div class="absolute top-2 right-2 opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-external-link-alt text-gray-400 text-sm"></i>
                    </div>
                </div>
            </a>

            <!-- Reviews Stat Card -->
            <?php if (isset($d['review_discussion_stats']['reviews'])): ?>
            <a href="<?= URLROOT ?>/quality/reviews" class="block">
                <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white rounded-xl p-6 cursor-pointer hover:scale-105 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-gray-500">Quality</p>
                            <p class="text-3xl font-bold text-gray-800"><?= $d['review_discussion_stats']['reviews'] ?? 0 ?></p>
                            <p class="text-xs text-gray-500">Quality reviews completed</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-pink-400 to-rose-500">
                            <i class="fas fa-star fa-lg"></i>
                        </div>
                    </div>
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-gradient-to-r from-pink-400 to-rose-500"></div>
                    <div class="absolute top-2 right-2 opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-external-link-alt text-gray-400 text-sm"></i>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <!-- Users Stat Card -->
            <?php if (($d['user_role'] ?? '') === 'admin' && isset($d['user_stats'])): ?>
            <a href="<?= URLROOT ?>/admin/users" class="block">
                <div class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 bg-white rounded-xl p-6 cursor-pointer hover:scale-105 hover:bg-gray-50">
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
                    <div class="absolute top-2 right-2 opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <i class="fas fa-external-link-alt text-gray-400 text-sm"></i>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Activity Chart -->
                <?php if ($isPrivileged && isset($d['daily_trends']) && !empty($d['daily_trends'])): ?>
                <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Activity Trend</h3>
                        <p class="text-sm text-gray-500">Last 15 days performance overview</p>
                    </div>
                    <div class="h-80">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                <?php elseif ($isPrivileged): ?>
                <div class="bg-white p-6 border-0 shadow-lg rounded-xl">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Activity Trend</h3>
                        <p class="text-sm text-gray-500">Last 15 days performance overview</p>
                    </div>
                    <div class="h-80 flex items-center justify-center">
                        <p class="text-gray-500">No activity data available for the selected period</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Leaderboards -->
                <div class="grid grid-cols-1 gap-4 md:gap-6">
                    <!-- Top Ticket Creators -->
                    <div class="bg-white p-4 md:p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-blue-100 text-blue-600"><i class="fas fa-trophy text-sm md:text-base"></i></div>
                            <h3 class="font-semibold text-gray-800 text-sm md:text-base">Top Ticket Creators</h3>
                        </div>
                        <div class="space-y-2 md:space-y-3">
                            <?php foreach (array_slice($d['leaderboards']['tickets'] ?? [], 0, 5) as $i => $entry): ?>
                            <a href="https://cs.taxif.com/logs?agent_id=<?= $entry['user_id'] ?? $entry['agent_id'] ?? '' ?>" target="_blank"
                               class="flex items-center gap-2 md:gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors cursor-pointer">
                                <span class="flex-shrink-0 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-xs font-bold border border-blue-200 text-blue-600 bg-blue-50"><?= $i + 1 ?></span>
                                <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs md:text-sm font-medium text-gray-800"><?= htmlspecialchars($entry['name']) ?></p>
                            </div>
                                <span class="text-xs md:text-sm font-bold text-blue-600 flex-shrink-0"><?= $entry['count'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Outgoing Callers -->
                    <div class="bg-white p-4 md:p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-green-100 text-green-600"><i class="fas fa-phone-volume text-sm md:text-base"></i></div>
                            <h3 class="font-semibold text-gray-800 text-sm md:text-base">Top Outgoing Callers</h3>
                        </div>
                        <div class="space-y-2 md:space-y-3">
                            <?php foreach (array_slice($d['leaderboards']['outgoing_calls'] ?? [], 0, 5) as $i => $entry): ?>
                            <a href="https://cs.taxif.com/logs?agent_id=<?= $entry['user_id'] ?? $entry['agent_id'] ?? '' ?>" target="_blank"
                               class="flex items-center gap-2 md:gap-3 p-2 rounded-lg hover:bg-green-50 transition-colors cursor-pointer">
                                <span class="flex-shrink-0 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-xs font-bold border border-green-200 text-green-600 bg-green-50"><?= $i + 1 ?></span>
                                <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs md:text-sm font-medium text-gray-800"><?= htmlspecialchars($entry['name']) ?></p>
                            </div>
                                <span class="text-xs md:text-sm font-bold text-green-600 flex-shrink-0"><?= $entry['count'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Incoming Handlers -->
                    <div class="bg-white p-4 md:p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-indigo-100 text-indigo-600"><i class="fas fa-headset text-sm md:text-base"></i></div>
                            <h3 class="font-semibold text-gray-800 text-sm md:text-base">Top Incoming Handlers</h3>
                        </div>
                        <div class="space-y-2 md:space-y-3">
                            <?php foreach (array_slice($d['leaderboards']['incoming_calls'] ?? [], 0, 5) as $i => $entry): ?>
                            <a href="https://cs.taxif.com/logs?agent_id=<?= $entry['user_id'] ?? $entry['agent_id'] ?? '' ?>" target="_blank"
                               class="flex items-center gap-2 md:gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors cursor-pointer">
                                <span class="flex-shrink-0 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-xs font-bold border border-indigo-200 text-indigo-600 bg-indigo-50"><?= $i + 1 ?></span>
                                <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                    <?= mb_substr($entry['name'], 0, 2) ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs md:text-sm font-medium text-gray-800"><?= htmlspecialchars($entry['name']) ?></p>
                            </div>
                                <span class="text-xs md:text-sm font-bold text-indigo-600 flex-shrink-0"><?= $entry['count'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Reviews -->
                    <div class="bg-white p-4 md:p-6 border-0 shadow-lg rounded-xl">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-pink-100 text-pink-600"><i class="fas fa-star text-sm md:text-base"></i></div>
                            <h3 class="font-semibold text-gray-800 text-sm md:text-base">Top Reviews</h3>
                        </div>
                        <div class="space-y-2 md:space-y-3">
                            <?php
                            // أفضل الموظفين من حيث متوسط التقييمات المستلمة
                            $topReviewedEmployees = $d['leaderboards']['reviews'] ?? [];

                            // Take top 5 employees
                            if (!empty($topReviewedEmployees) && is_array($topReviewedEmployees)) {
                                $topReviewedEmployees = array_slice($topReviewedEmployees, 0, 5);
                            } else {
                                $topReviewedEmployees = [];
                            }
                            ?>

                            <?php if (!empty($topReviewedEmployees)): ?>
                                <?php foreach ($topReviewedEmployees as $i => $employee): ?>
                                <a href="<?= URLROOT ?>/quality/reviews?agent_id=<?= $employee['user_id'] ?>" target="_blank"
                                   class="flex items-center gap-2 md:gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors cursor-pointer">
                                    <span class="flex-shrink-0 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-xs font-bold border border-pink-200 text-pink-600 bg-pink-50"><?= $i + 1 ?></span>
                                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 text-xs">
                                        <?= mb_substr($employee['name'], 0, 2) ?>
                                </div>
                                    <div class="flex-1">
                                        <p class="text-xs md:text-sm font-medium text-gray-800"><?= htmlspecialchars($employee['name']) ?></p>
                                        <p class="text-xs text-gray-500">Avg: <?= $employee['average_rating'] ?>/100</p>
                            </div>
                                    <span class="text-xs md:text-sm font-bold text-pink-600 flex-shrink-0"><?= $employee['total_reviews'] ?></span>
                                </a>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-500">
                                    <p class="text-xs md:text-sm">No reviews data available</p>
                    </div>
                    <?php endif; ?>
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
                        <a href="https://cs.taxif.com/quality/reviews" target="_blank" class="h-auto p-4 flex flex-col items-start gap-2 group bg-pink-500 text-white rounded-lg">
                            <div class="flex items-center gap-2 w-full"><i class="fas fa-star"></i><span class="font-medium">Quality</span></div>
                            <span class="text-xs opacity-80">Quality reviews</span>
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

        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');

        // تحديد AM أو PM
        const ampm = hours >= 12 ? 'PM' : 'AM';

        // تحويل من 24 ساعة إلى 12 ساعة
        hours = hours % 12;
        hours = hours ? hours : 12; // لو 0 نخليه 12

        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;

        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString(undefined, options);

        const timeEl = document.getElementById('local-time');
        const dateEl = document.getElementById('local-date');

        if (timeEl) timeEl.textContent = timeString;
        if (dateEl) dateEl.textContent = dateString;
    }

    setInterval(updateLocalTimeAndDate, 1000);
    updateLocalTimeAndDate();

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
    <?php if ($isPrivileged && isset($d['daily_trends']) && !empty($d['daily_trends'])): ?>
    const activityCtx = document.getElementById('activityChart')?.getContext('2d');
    if (activityCtx) {
        const trendData = <?= json_encode($d['daily_trends']) ?>;
        const labels = trendData.map(d => new Date(d.action_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const ticketData = trendData.map(d => d.tickets);
        const callData = trendData.map(d => d.calls);
        const reviewData = trendData.map(d => d.reviews);

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
                    },
                    {
                        label: 'Reviews',
                        data: reviewData,
                        backgroundColor: 'rgba(236, 72, 153, 0.1)', // pink-500
                        borderColor: 'rgba(236, 72, 153, 1)', // pink-500
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(236, 72, 153, 1)',
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

// Debug info for all users (privileged and non-privileged)
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
