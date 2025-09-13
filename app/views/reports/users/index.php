<?php require_once APPROOT . '/views/includes/header.php'; ?>



<style>
    /* Optimized Responsive Table Styles - Reduced complexity */

    @media (max-width: 768px) {
        .responsive-table thead {
            display: none;
        }

        .responsive-table tr {
            display: block;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
            background-color: #fff;
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .responsive-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.75rem;
            text-align: right;
            border-bottom: 1px solid #f3f4f6;
        }

        .responsive-table td:last-child {
            border-bottom: 0;
        }

        .responsive-table td::before {
            content: attr(data-label);
            font-weight: 500;
            text-align: left;
            padding-right: 0.5rem;
            white-space: nowrap;
            font-size: 0.875rem;
        }

        .user-card-header {
            padding: 0.75rem;
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

    /* Performance optimizations */
    .responsive-table {
        font-size: 14px; /* Smaller font for better performance */
    }

    .quality-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        background: conic-gradient(from 0deg, currentColor var(--progress), transparent var(--progress));
        mask: radial-gradient(circle at center, transparent 45%, black 55%);
        -webkit-mask: radial-gradient(circle at center, transparent 45%, black 55%);
    }

    .quality-circle::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 60%;
        height: 60%;
        background: white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    /* Reduce animations and transitions */
    .transition-colors {
        transition: color 0.1s ease;
    }

    .hover\:bg-gray-50:hover {
        background-color: #f9fafb;
    }

    /* Simplify tooltips */
    .tooltip {
        position: relative;
    }

    .tooltip .tooltip-content {
        display: none;
        position: absolute;
        z-index: 10;
        background: #374151;
        color: white;
        padding: 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        white-space: nowrap;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 0.25rem;
    }

    .tooltip:hover .tooltip-content {
        display: block;
    }
</style>



<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">



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

            $average_quality = $summary_stats['avg_quality_score'] ?? 0;

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
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filters</h2>

        <form id="filterForm" method="GET" action="">

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
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>

                <!-- Date To Filter -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>

                <!-- Per Page Filter -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700">Results Per Page</label>
                    <select id="per_page" name="per_page" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="10" <?= ($filters['per_page'] ?? 25) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($filters['per_page'] ?? 25) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($filters['per_page'] ?? 25) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($filters['per_page'] ?? 25) == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>

            </div>

            <!-- Action Buttons -->

            <div class="mt-6 flex items-center justify-between">

                <div>

                    <span class="text-sm font-medium text-gray-500 mr-2">Quick Range:</span>
                    <button type="button" onclick="setDateRange('today')" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Today</button>
                    <button type="button" onclick="setDateRange(7)" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">7 Days</button>
                    <button type="button" onclick="setDateRange(30)" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ml-2">30 Days</button>

                </div>

                <div class="flex items-center space-x-4">

                    <a href="/reports/users" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium flex items-center transition">

                        <i class="fas fa-times mr-2"></i>Reset

                    </a>

                    

                    <!-- Export Dropdown -->
                    <div class="relative" id="exportDropdown">
                        <button type="button" onclick="toggleExportDropdown()" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg hover:bg-green-200 text-sm font-medium flex items-center transition">
                            <i class="fas fa-download mr-2"></i>
                            <span>Export</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="exportMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 hidden">
                            <a href="/reports/users/export?export_type=excel&<?= http_build_query($filters) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-excel mr-2 text-green-500"></i>Export as Excel
                            </a>
                            <a href="/reports/users/export?export_type=json&<?= http_build_query($filters) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
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

                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Final Points</th>

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

                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-md">

                                            <?= strtoupper(substr($user['name'] ?? $user['username'], 0, 2)) ?>

                                        </div>

                                        <div class="ml-4">

                                            <div class="text-sm font-semibold text-gray-900">

                                                <a href="/logs?user_id=<?= $user['id'] ?>" class="hover:underline text-indigo-600 transition-colors">

                                                    <?= htmlspecialchars($user['name'] ?? $user['username']) ?>

                                                </a>

                                            </div>

                                            <div class="text-xs text-gray-500 font-medium">ID: <?= $user['id'] ?></div>

                                        </div>

                                    </div>

                                </td>

                                <td data-label="Role" class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">

                                    <?= htmlspecialchars($user['role_name'] ?? 'N/A') ?>

                                </td>

                                <td data-label="Team" class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">

                                    <?php if (!empty($user['team_id']) && !empty($user['team_name'])): ?>

                                        <a href="/logs?team_id=<?= $user['team_id'] ?>" class="hover:underline text-indigo-600">

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

                                    <div class="flex flex-col items-center space-y-1">

                                        <div class="flex items-center space-x-2">

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-phone-alt text-blue-500 text-xs"></i>

                                                <span class="text-sm font-semibold text-gray-800"><?= ($user['incoming_calls'] ?? 0) + ($user['outgoing_calls'] ?? 0) ?></span>

                                            </div>

                                        </div>

                                        <div class="flex items-center space-x-3 text-xs">

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-arrow-down text-green-500"></i>

                                                <span class="text-gray-600"><?= $user['incoming_calls'] ?? 0 ?></span>

                                            </div>

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-arrow-up text-orange-500"></i>

                                                <span class="text-gray-600"><?= $user['outgoing_calls'] ?? 0 ?></span>

                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td data-label="Tickets (N/V)" class="px-6 py-4 whitespace-nowrap text-center">

                                    <div class="flex flex-col items-center space-y-1">

                                        <div class="flex items-center space-x-2">

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-ticket-alt text-indigo-500 text-xs"></i>

                                                <span class="text-sm font-semibold text-gray-800"><?= ($user['normal_tickets'] ?? 0) + ($user['vip_tickets'] ?? 0) ?></span>

                                            </div>

                                        </div>

                                        <div class="flex items-center space-x-3 text-xs">

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-circle text-gray-500"></i>

                                                <span class="text-gray-600"><?= $user['normal_tickets'] ?? 0 ?></span>

                                            </div>

                                            <div class="flex items-center space-x-1">

                                                <i class="fas fa-crown text-yellow-500"></i>

                                                <span class="text-gray-600"><?= $user['vip_tickets'] ?? 0 ?></span>

                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td data-label="Points" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center px-3 py-2 rounded-lg <?= ($user['delegation_applied']) ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 tooltip' : 'bg-gray-50' ?> transition-colors">
                                        <i class="fas fa-star text-indigo-500 mr-2 text-sm"></i>
                                        <span class="text-sm font-bold text-gray-800"><?= number_format($user['total_points'] ?? 0, 2) ?></span>
                                        <?php if ($user['delegation_applied']): ?>
                                            <i class="fas fa-award text-yellow-500 ml-2 text-sm"></i>
                                            <div class="tooltip-content">
                                                <div class="font-semibold text-yellow-300 mb-1">Delegation Bonus</div>
                                                <div class="text-xs space-y-0.5">
                                                    <div>Base: <?= number_format($user['delegation_details']['original_points'], 2) ?></div>
                                                    <div>Bonus: +<?= number_format($user['delegation_details']['percentage'], 2) ?>%</div>
                                                    <div class="text-yellow-200 truncate max-w-xs"><?= htmlspecialchars($user['delegation_details']['reason']) ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td data-label="Quality" class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php
                                        $quality = $user['quality_score'] ?? 0;
                                        $reviews_count = $user['total_reviews'] ?? 0;

                                        $quality_color = 'text-gray-400';
                                        if ($quality >= 90) {
                                            $quality_color = 'text-green-500';
                                        } elseif ($quality >= 75) {
                                            $quality_color = 'text-yellow-500';
                                        } elseif ($quality > 0) {
                                            $quality_color = 'text-red-500';
                                        }
                                    ?>

                                    <?php if ($reviews_count > 0): ?>
                                        <a href="<?= URLROOT ?>/quality/reviews?agent_id=<?= $user['id'] ?>"
                                           class="inline-block p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                            <div class="flex flex-col items-center space-y-1">
                                                <!-- Optimized Progress Circle using CSS -->
                                                <div class="relative w-8 h-8">
                                                    <div class="quality-circle <?= $quality_color ?>" style="--progress: <?= $quality ?>%"></div>
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <span class="text-xs font-bold <?= $quality_color ?>"><?= round($quality) ?>%</span>
                                                    </div>
                                                </div>
                                                <!-- Reviews Count -->
                                                <div class="text-xs text-gray-500">
                                                    <?= $reviews_count ?> review<?= $reviews_count != 1 ? 's' : '' ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                <i class="fas fa-minus text-gray-400 text-xs"></i>
                                            </div>
                                            <span class="text-xs text-gray-400">No reviews</span>
                                        </div>
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
                                    <?= number_format($summary_stats['avg_quality_score'], 2) ?>%
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

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="mt-8 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> to
            <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> of
            <?= $pagination['total'] ?> results
        </div>

        <div class="flex items-center space-x-2">
            <?php if ($pagination['has_prev']): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>"
                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    Previous
                </a>
            <?php endif; ?>

            <?php
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);

            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
                   class="px-3 py-2 text-sm rounded-md transition <?= $i === $pagination['current_page'] ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['has_next']): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>"
                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    // Optimized JavaScript for performance
    function setDateRange(period) {
        const today = new Date();
        const to = today.toISOString().split('T')[0];
        let from;

        if (period === 'today') {
            from = to;
        } else {
            const fromDate = new Date();
            fromDate.setDate(today.getDate() - (period - 1));
            from = fromDate.toISOString().split('T')[0];
        }

        document.getElementById('date_from').value = from;
        document.getElementById('date_to').value = to;

        // Submit form immediately
        document.getElementById('filterForm').submit();
    }

    // Debounce function for better performance
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Toggle export dropdown
    function toggleExportDropdown() {
        const menu = document.getElementById('exportMenu');
        menu.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('exportDropdown');
        const menu = document.getElementById('exportMenu');
        if (!dropdown.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });

    // Optimize scroll performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                // Handle scroll events here if needed
                ticking = false;
            });
            ticking = true;
        }
    });
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>