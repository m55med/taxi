<?php require_once APPROOT . '/views/includes/header.php'; ?>

<style>
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .responsive-table thead {
            display: none;
        }
        .responsive-table tr {
            display: block;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            overflow: hidden;
            background-color: #fff;
        }
        .responsive-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            text-align: right;
            border-bottom: 1px solid #f3f4f6;
        }
        .responsive-table td:last-child {
            border-bottom: 0;
        }
        .responsive-table td::before {
            content: attr(data-label);
            font-weight: 600;
            text-align: left;
            padding-right: 1rem;
            white-space: nowrap;
        }
        .user-card-header {
             padding: 1rem;
             background-color: #f9fafb;
        }
        .user-card-header td {
            padding: 0;
            border: none;
        }
         .user-card-header td::before {
            content: none;
        }
    }
</style>

<div x-data="{}" class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Users Report</h1>
        <p class="text-sm text-gray-500 mt-1">Analyze user performance, calls, tickets, and points statistics.</p>
    </div>
    
    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        <?php 
            $average_quality = ($summary_stats['total_reviews'] ?? 0) > 0 ? ($summary_stats['total_quality_score'] / $summary_stats['total_reviews']) : 0;
            $stats = [
                ['label' => 'Total Points', 'value' => number_format($summary_stats['total_points'] ?? 0, 2), 'icon' => 'fa-star', 'color' => 'text-indigo-500'],
                ['label' => 'Avg Quality', 'value' => number_format($average_quality, 2) . '%', 'icon' => 'fa-gem', 'color' => 'text-teal-500'],
                ['label' => 'Total Calls', 'value' => number_format(($summary_stats['incoming_calls'] ?? 0) + ($summary_stats['outgoing_calls'] ?? 0)), 'icon' => 'fa-phone-alt', 'color' => 'text-blue-500'],
                ['label' => 'Total Tickets', 'value' => number_format(($summary_stats['normal_tickets'] ?? 0) + ($summary_stats['vip_tickets'] ?? 0)), 'icon' => 'fa-ticket-alt', 'color' => 'text-green-500'],
                ['label' => 'VIP Tickets', 'value' => number_format($summary_stats['vip_tickets'] ?? 0), 'icon' => 'fa-crown', 'color' => 'text-yellow-500'],
                ['label' => 'Assignments', 'value' => number_format($summary_stats['assignments_count'] ?? 0), 'icon' => 'fa-exchange-alt', 'color' => 'text-red-500']
            ];
        ?>
        <?php foreach ($stats as $stat): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4">
            <div class="bg-gray-100 rounded-full p-3">
                <i class="fas <?= $stat['icon'] ?> fa-lg <?= $stat['color'] ?>"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500"><?= $stat['label'] ?></p>
                <p class="text-2xl font-bold text-gray-800"><?= $stat['value'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters Card -->
    <div x-data="{
        dateFrom: '<?= htmlspecialchars($filters['date_from'] ?? '') ?>',
        dateTo: '<?= htmlspecialchars($filters['date_to'] ?? '') ?>',
        setDateRange(period) {
            const today = new Date();
            const to = today.toISOString().split('T')[0];
            let from;

            if (period === 'today') {
                from = to;
            } else {
                const fromDate = new Date();
                fromDate.setDate(today.getDate() - (period -1));
                from = fromDate.toISOString().split('T')[0];
            }
            
            this.dateFrom = from;
            this.dateTo = to;
            
            // Give Alpine time to update the inputs before submitting
            this.$nextTick(() => {
                this.$refs.filterForm.submit();
            });
        }
    }" class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filters</h2>
        <form x-ref="filterForm" method="GET" action="">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <!-- Role Filter -->
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" name="role_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= ($filters['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($role['name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- User Filter -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                    <select id="user_id" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Users</option>
                        <?php foreach ($all_users as $user_filter): ?>
                            <option value="<?= $user_filter['id'] ?>" <?= ($filters['user_id'] ?? '') == $user_filter['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user_filter['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Team Filter -->
                <div>
                    <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                    <select id="team_id" name="team_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="banned" <?= ($filters['status'] ?? '') == 'banned' ? 'selected' : '' ?>>Banned</option>
                    </select>
                </div>
                <!-- Date From Filter -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" id="date_from" name="date_from" x-model="dateFrom" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
                <!-- Date To Filter -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" id="date_to" name="date_to" x-model="dateTo" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-gray-500 mr-2">Quick Range:</span>
                    <button type="button" @click="setDateRange('today')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Today</button>
                    <button type="button" @click="setDateRange(7)" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">7 Days</button>
                    <button type="button" @click="setDateRange(30)" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">30 Days</button>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= BASE_PATH ?>/reports/users" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium flex items-center transition">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                    
                    <!-- Export Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 text-sm font-medium flex items-center transition">
                            <i class="fas fa-download mr-2"></i>
                            <span>Export</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20" style="display:none;">
                            <a href="<?= BASE_PATH ?>/reports/users/export?export_type=excel&<?= http_build_query($filters) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-excel mr-2 text-green-500"></i>Export as Excel
                            </a>
                            <a href="<?= BASE_PATH ?>/reports/users/export?export_type=json&<?= http_build_query($filters) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-code mr-2 text-blue-500"></i>Export as JSON
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center transition">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full responsive-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Team</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Calls (In/Out)</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tickets (N/V)</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Points</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Quality</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 md:divide-y-0">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search fa-4x text-gray-300 mb-4"></i>
                                    <h3 class="text-xl font-medium">No Data Found</h3>
                                    <p class="text-sm">No records match your current filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td data-label="User" class="px-6 py-4 whitespace-nowrap user-card-header">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-11 w-11 relative">
                                            <img class="h-11 w-11 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random&color=fff&font-size=0.5" alt="">
                                            <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full <?= $user['is_online'] ? 'bg-green-400' : 'bg-gray-400' ?> ring-2 ring-white"></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="<?= BASE_PATH ?>/logs?user_id=<?= $user['id'] ?>" class="hover:underline text-indigo-600">
                                                    <?= htmlspecialchars($user['username']) ?>
                                                </a>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role" class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    <?= htmlspecialchars($user['role_name'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Team" class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    <?php if (!empty($user['team_id']) && !empty($user['team_name'])): ?>
                                        <a href="<?= BASE_PATH ?>/logs?team_id=<?= $user['team_id'] ?>" class="hover:underline text-indigo-600">
                                            <?= htmlspecialchars($user['team_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status" class="px-6 py-4 whitespace-nowrap text-center">
                                     <?php
                                        $statusClasses = ['active' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'banned' => 'bg-red-100 text-red-800'];
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$user['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                        <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                    </span>
                                </td>
                                <td data-label="Calls (In/Out)" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-800"><?= ($user['incoming_calls'] ?? 0) + ($user['outgoing_calls'] ?? 0) ?></div>
                                    <div class="text-xs text-gray-500">(<?= $user['incoming_calls'] ?? 0 ?>/<?= $user['outgoing_calls'] ?? 0 ?>)</div>
                                </td>
                                <td data-label="Tickets (N/V)" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-800"><?= ($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0) ?></div>
                                    <div class="text-xs text-gray-500">(<?= $user['normal_tickets'] ?? 0 ?>/<?= $user['vip_tickets'] ?? 0 ?>)</div>
                                </td>
                                <td data-label="Points" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-semibold text-indigo-600"><?= number_format($user['total_points'] ?? 0, 2) ?></div>
                                </td>
                                <td data-label="Quality" class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php
                                        $quality = $user['quality_score'] ?? 0;
                                        $reviews_count = $user['total_reviews'] ?? 0;
                                        $quality_bg = 'bg-gray-200';
                                        if ($quality >= 90) $quality_bg = 'bg-green-500';
                                        elseif ($quality >= 75) $quality_bg = 'bg-yellow-500';
                                        elseif ($quality > 0) $quality_bg = 'bg-red-500';
                                    ?>
                                    <?php if ($reviews_count > 0): ?>
                                        <div class="flex flex-col items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="<?= $quality_bg ?> h-2.5 rounded-full" style="width: <?= $quality ?>%"></div>
                                                    </div>
                                            <div class="text-sm font-semibold text-gray-700 mt-1">
                                                <?= number_format($quality, 2) ?>%
                                                <span class="text-xs text-gray-500 font-normal">(<?= $reviews_count ?>)</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">N/A</span>
                                        <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Summary Row -->
                        <tr class="bg-gray-100 font-bold text-gray-800">
                            <td data-label="Total" colspan="4" class="px-6 py-4 text-left text-sm">Grand Total</td>
                            <td data-label="Total Calls" class="px-6 py-4 text-center text-sm">
                                <?= number_format(($summary_stats['incoming_calls'] ?? 0) + ($summary_stats['outgoing_calls'] ?? 0)) ?>
                                (<?= number_format($summary_stats['incoming_calls'] ?? 0) ?>/<?= number_format($summary_stats['outgoing_calls'] ?? 0) ?>)
                            </td>
                            <td data-label="Total Tickets" class="px-6 py-4 text-center text-sm">
                                <?= number_format(($summary_stats['normal_tickets'] ?? 0) + ($summary_stats['vip_tickets'] ?? 0)) ?>
                                (<?= number_format($summary_stats['normal_tickets'] ?? 0) ?>/<?= number_format($summary_stats['vip_tickets'] ?? 0) ?>)
                            </td>
                            <td data-label="Total Points" class="px-6 py-4 text-center text-sm">
                                <?= number_format($summary_stats['total_points'] ?? 0, 2) ?>
                            </td>
                            <td data-label="Avg Quality" class="px-6 py-4 text-center text-sm">
                                <?php if (($summary_stats['total_reviews'] ?? 0) > 0): ?>
                                    <?= number_format(($summary_stats['total_quality_score'] / $summary_stats['total_reviews']), 2) ?>%
                                    <span class="text-xs font-normal text-gray-500">(<?= $summary_stats['total_reviews'] ?>)</span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 