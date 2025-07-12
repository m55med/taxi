<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Ticket Discussions Report</h1>
            <p class="text-lg text-gray-600">A log of all discussions initiated on tickets.</p>
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
                    <label for="search" class="block text-sm font-medium text-gray-700">Search Reason/Notes</label>
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="opened_by" class="block text-sm font-medium text-gray-700">Opened By</label>
                    <select name="opened_by" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Users</option>
                        <?php foreach($data['filter_options']['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['opened_by'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                     <select name="status" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="open" <?= ($data['filters']['status'] ?? '') == 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= ($data['filters']['status'] ?? '') == 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Discussions Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ticket #</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Opened By</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date Opened</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['discussions'])): ?>
                        <tr><td colspan="5" class="text-center py-10">No discussions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['discussions'] as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/tickets/view/<?= $item['ticket_id_val'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($item['ticket_number']) ?></a></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $item['user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($item['opened_by_user']) ?></a></td>
                                <td class="px-6 py-4 text-sm text-gray-800 font-medium"><?= htmlspecialchars($item['reason']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= ($item['status'] == 'open') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= htmlspecialchars(ucfirst($item['status'])) ?>
                                    </span>
                                </td>
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