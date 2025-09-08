<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Driver Calls Report</h1>
            <p class="text-lg text-gray-600">A detailed log of all calls made to drivers.</p>
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
                    <label for="search" class="block text-sm font-medium text-gray-700">Search Notes</label>
                    <input type="text" name="search" id="search" placeholder="Search in call notes..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Called By</label>
                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Users</option>
                        <?php foreach($data['filter_options']['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                    <select name="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Teams</option>
                        <?php foreach($data['filter_options']['teams'] as $team): ?>
                            <option value="<?= $team['id'] ?>" <?= ($data['filters']['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Calls Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Called By</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['calls'])): ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">No driver calls found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['calls'] as $call): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="/drivers/details/<?= $call['driver_id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($call['driver_name']) ?></a></td>
                                <td class="px-6 py-4"><a href="/reports/myactivity?user_id=<?= $call['user_id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($call['user_name']) ?></a></td>
                                <td class="px-6 py-4 text-center">
                                     <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= ($call['call_status'] == 'answered') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $call['call_status']))) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-md truncate" title="<?= htmlspecialchars($call['notes']) ?>"><?= htmlspecialchars($call['notes']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"><?= date('Y-m-d H:i', strtotime($call['created_at'])) ?></td>
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