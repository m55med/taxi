<?php require APPROOT . '/views/includes/header.php'; ?>

<style>
    .stat-card {
        transition: transform 0.3s, box-shadow 0.3s;
        animation: fadeIn 0.5s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .pagination a, .pagination span {
        transition: background-color 0.3s;
    }
</style>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800">Coupons Report</h1>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Coupons Card -->
        <div class="stat-card bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <h2 class="text-gray-500 text-lg font-semibold">Total Coupons</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($data['stats']['total_coupons'] ?? 0) ?></p>
        </div>
        <!-- Used Coupons Card -->
        <div class="stat-card bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <h2 class="text-gray-500 text-lg font-semibold">Used Coupons</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($data['stats']['used_coupons'] ?? 0) ?></p>
        </div>
        <!-- Unused Coupons Card -->
        <div class="stat-card bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
            <h2 class="text-gray-500 text-lg font-semibold">Unused Coupons</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($data['stats']['unused_coupons'] ?? 0) ?></p>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">All Coupons</h2>

        <!-- Filter Form -->
        <form action="<?= BASE_PATH ?>/reports/coupons" method="get" class="mb-6 flex flex-wrap items-center gap-4">
            <div class="flex-grow">
                <label for="code" class="sr-only">Search by Code</label>
                <input type="text" name="code" id="code" placeholder="Search by Code..." value="<?= htmlspecialchars($data['filters']['code'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="sr-only">Filter by Status</label>
                <select name="status" id="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="used" <?= ($data['filters']['status'] ?? '') == 'used' ? 'selected' : '' ?>>Used</option>
                    <option value="unused" <?= ($data['filters']['status'] ?? '') == 'unused' ? 'selected' : '' ?>>Unused</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg transition">Filter</button>
            <a href="<?= BASE_PATH ?>/reports/coupons" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg transition">Reset</a>
        </form>

        <!-- Coupons Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Code</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Value</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Country</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Created At</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Used At</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Used By</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Ticket #</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($data['coupons'])): ?>
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No coupons found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['coupons'] as $coupon): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($coupon['code']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars(number_format($coupon['value'], 2)) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($coupon['country_name'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4">
                                    <?php if ($coupon['is_used']): ?>
                                        <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Used</span>
                                    <?php else: ?>
                                        <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs">Unused</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($coupon['created_at']))) ?></td>
                                <td class="py-3 px-4"><?= $coupon['used_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($coupon['used_at']))) : 'N/A' ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($coupon['used_by_name'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4"><?= $coupon['ticket_id'] ? '<a href="'.BASE_PATH.'/tickets/view/'.$coupon['ticket_id'].'" class="text-blue-500 hover:underline">'.htmlspecialchars($coupon['ticket_number']).'</a>' : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6 flex justify-between items-center pagination">
            <?php if ($data['total_pages'] > 1): ?>
                <div class="flex">
                    <?php if ($data['current_page'] > 1): ?>
                        <a href="?page=<?= $data['current_page'] - 1 ?>&<?= http_build_query($data['filters']) ?>" class="px-4 py-2 mx-1 bg-white border rounded-md hover:bg-gray-100">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                        <a href="?page=<?= $i ?>&<?= http_build_query($data['filters']) ?>" class="px-4 py-2 mx-1 <?= $i == $data['current_page'] ? 'bg-blue-500 text-white' : 'bg-white' ?> border rounded-md hover:bg-gray-100"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($data['current_page'] < $data['total_pages']): ?>
                        <a href="?page=<?= $data['current_page'] + 1 ?>&<?= http_build_query($data['filters']) ?>" class="px-4 py-2 mx-1 bg-white border rounded-md hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="text-sm text-gray-600">
                Page <?= $data['current_page'] ?> of <?= $data['total_pages'] ?>
            </div>
        </div>

    </div>
</div>

<?php require APPROOT . '/views/includes/footer.php'; ?> 