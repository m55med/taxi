<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div x-data="{}" class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Users Report</h1>
        <p class="text-sm text-gray-500 mt-1">Analyze user performance, calls, tickets, and points statistics.</p>
    </div>
    
    <!-- Flash Messages -->
    <?php require_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <?php 
            $stats = [
                ['label' => 'Total Points', 'value' => number_format($summary_stats['total_points'] ?? 0, 2), 'icon' => 'fa-star', 'color' => 'text-indigo-500'],
                ['label' => 'Total Calls', 'value' => number_format(($summary_stats['total_incoming_calls'] ?? 0) + ($summary_stats['total_outgoing_calls'] ?? 0)), 'icon' => 'fa-phone-alt', 'color' => 'text-blue-500'],
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
                    <a href="<?= BASE_PATH ?>/reports/users/export?<?= http_build_query($filters) ?>" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 text-sm font-medium flex items-center transition">
                        <i class="fas fa-file-excel mr-2"></i>Export
                    </a>
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
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-8 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Activity Summary</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center text-gray-500">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-11 w-11 relative">
                                            <img class="h-11 w-11 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random&color=fff&font-size=0.5" alt="">
                                            <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full <?= $user['is_online'] ? 'bg-green-400' : 'bg-gray-400' ?> ring-2 ring-white"></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                                <span><?= htmlspecialchars($user['username']) ?></span>
                                                <?php
                                                    $statusClasses = ['active' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'banned' => 'bg-red-100 text-red-800'];
                                                ?>
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$user['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                                    <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-8 py-4 whitespace-nowrap">
                                    <div class="grid grid-cols-3 gap-6">
                                        <!-- Calls -->
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-blue-100 text-blue-600 rounded-lg p-3 flex items-center justify-center"><i class="fas fa-phone-alt fa-lg"></i></div>
                                            <div>
                                                <div class="font-bold text-gray-800"><?= number_format(($user['call_stats']['total_incoming_calls'] ?? 0) + ($user['call_stats']['total_outgoing_calls'] ?? 0)) ?></div>
                                                <div class="text-xs text-gray-500">
                                                    <span title="Incoming"><?= number_format($user['call_stats']['total_incoming_calls'] ?? 0) ?> In</span> / 
                                                    <span title="Outgoing"><?= number_format($user['call_stats']['total_outgoing_calls'] ?? 0) ?> Out</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Tickets -->
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-green-100 text-green-600 rounded-lg p-3 flex items-center justify-center"><i class="fas fa-ticket-alt fa-lg"></i></div>
                                            <div>
                                                <div class="font-bold text-gray-800"><?= number_format(($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0)) ?></div>
                                                <div class="text-xs text-gray-500">
                                                    <span title="Normal"><?= number_format($user['normal_tickets'] ?? 0) ?> N</span> / 
                                                    <span title="VIP"><?= number_format($user['vip_tickets'] ?? 0) ?> V</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Assignments -->
                                        <div class="flex items-center space-x-3">
                                             <div class="bg-purple-100 text-purple-600 rounded-lg p-3 flex items-center justify-center"><i class="fas fa-exchange-alt fa-lg"></i></div>
                                            <div>
                                                <div class="font-bold text-gray-800"><?= number_format($user['assignments_count'] ?? 0) ?></div>
                                                <div class="text-xs text-gray-500">Assignments</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <span class="text-xl font-bold text-indigo-600"><?= number_format($user['points_details']['final_total_points'] ?? 0, 2) ?></span>
                                        <?php if (!empty($user['points_details']['bonus_reasons'])): ?>
                                            <div x-data="{ tooltip: false }" class="relative z-10 ml-2">
                                                <i @mouseenter="tooltip = true" @mouseleave="tooltip = false" class="fas fa-gift text-blue-500 cursor-pointer"></i>
                                                <div x-show="tooltip" class="absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-2 p-3 w-max max-w-sm bg-gray-800 text-white text-xs rounded-lg shadow-xl" x-transition style="display:none;">
                                                    <h4 class="font-bold border-b border-gray-600 pb-1 mb-2">Bonus Details:</h4>
                                                    <ul class="list-disc list-inside space-y-1">
                                                        <?php foreach($user['points_details']['bonus_reasons'] as $reason): ?>
                                                            <li><?= htmlspecialchars($reason) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <div class="mt-2 border-t border-gray-600 pt-2 font-mono">
                                                        <div class="flex justify-between"><span>Base:</span> <span><?= number_format($user['points_details']['total_base_points'] ?? 0, 2) ?></span></div>
                                                        <div class="flex justify-between"><span>Bonus:</span> <span class="text-green-400">+<?= number_format($user['points_details']['total_bonus_amount'] ?? 0, 2) ?></span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                 <?php if (!empty($users)): ?>
                <tfoot class="bg-gray-100">
                    <tr class="font-bold text-gray-800">
                        <td class="px-6 py-4 text-sm">Grand Total</td>
                        <td class="px-8 py-4">
                            <div class="grid grid-cols-3 gap-6 text-sm">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-200 text-blue-800 rounded-lg p-2 flex items-center justify-center"><i class="fas fa-phone-alt"></i></div>
                                    <div><?= number_format(($summary_stats['total_incoming_calls'] ?? 0) + ($summary_stats['total_outgoing_calls'] ?? 0)) ?> Calls</div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-green-200 text-green-800 rounded-lg p-2 flex items-center justify-center"><i class="fas fa-ticket-alt"></i></div>
                                    <div><?= number_format(($summary_stats['normal_tickets'] ?? 0) + ($summary_stats['vip_tickets'] ?? 0)) ?> Tickets</div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-purple-200 text-purple-800 rounded-lg p-2 flex items-center justify-center"><i class="fas fa-exchange-alt"></i></div>
                                    <div><?= number_format($summary_stats['assignments_count'] ?? 0) ?> Assignments</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-xl text-indigo-700">
                            <?= number_format($summary_stats['total_points'] ?? 0, 2) ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 