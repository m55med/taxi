<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Ticket-Coupon Link Report</h1>
            <p class="text-lg text-gray-600">A log of all coupons applied to tickets.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search Ticket#/Coupon</label>
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Created By User</label>
                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Users</option>
                        <?php foreach($data['filter_options']['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700">Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="custom">Custom</option>
                        <option value="today">Today</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
                </div>
                <input type="date" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="hidden">
                <input type="date" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="hidden">
            </div>
        </form>
    </div>

    <!-- Ticket-Coupons Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ticket #</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Coupon Code</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Applied By</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date Applied</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['ticket_coupons'])): ?>
                        <tr><td colspan="4" class="text-center py-10">No coupon applications found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['ticket_coupons'] as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/tickets/view/<?= $item['ticket_id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($item['ticket_number']) ?></a></td>
                                <td class="px-6 py-4 font-mono text-sm"><?= htmlspecialchars($item['coupon_code']) ?></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $item['user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($item['created_by_user']) ?></a></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>
    </div>
</div>

<?php require APPROOT . '/views/includes/footer.php'; ?> 