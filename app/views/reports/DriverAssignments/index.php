<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Driver Assignments Report</h1>
            <p class="text-lg text-gray-600">A log of all driver assignments between users.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">
                <div class="xl:col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700">Search Note</label>
                    <input type="text" name="search" id="search" placeholder="Search in note..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700">Driver</label>
                    <select name="driver_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Drivers</option>
                        <?php foreach($data['filter_options']['drivers'] as $driver): ?>
                            <option value="<?= $driver['id'] ?>" <?= ($data['filters']['driver_id'] ?? '') == $driver['id'] ? 'selected' : '' ?>><?= htmlspecialchars($driver['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="from_user_id" class="block text-sm font-medium text-gray-700">Assigned From</label>
                    <select name="from_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Users</option>
                        <?php foreach($data['filter_options']['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['from_user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="to_user_id" class="block text-sm font-medium text-gray-700">Assigned To</label>
                    <select name="to_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Users</option>
                        <?php foreach($data['filter_options']['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['to_user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Assignments Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Assigned From</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Note</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Seen</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['assignments'])): ?>
                        <tr><td colspan="6" class="text-center py-10 text-gray-500">No driver assignments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['assignments'] as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/drivers/details/<?= $item['driver_id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($item['driver_name']) ?></a></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $item['from_user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($item['from_user_name']) ?></a></td>
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/reports/myactivity?user_id=<?= $item['to_user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($item['to_user_name']) ?></a></td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-sm truncate" title="<?= htmlspecialchars($item['note']) ?>"><?= htmlspecialchars($item['note']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($item['is_seen']): ?>
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></td>
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