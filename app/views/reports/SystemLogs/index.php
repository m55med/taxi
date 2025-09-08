<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">System Event Logs</h1>
            <p class="text-lg text-gray-600">A detailed record of system-level discussions and events.</p>
        </div>
        <div class="flex items-center mt-4 md:mt-0">
             <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition mr-2">
                <i class="fas fa-file-excel mr-2"></i>Export to Excel
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'json'])) ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                <i class="fas fa-file-code mr-2"></i>Export to JSON
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">
                <!-- Text Search -->
                <div class="lg:col-span-2 xl:col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search message or context..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <!-- Level Filter -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                    <select name="level" id="level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Levels</option>
                        <?php foreach($data['levels'] as $level): ?>
                            <option value="<?= htmlspecialchars($level) ?>" <?= ($data['filters']['level'] ?? '') == $level ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($level)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- User Filter -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Users</option>
                         <?php foreach($data['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <!-- Date Filters -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                 <!-- Quick Period -->
                <div class="lg:col-span-2 xl:col-span-1">
                    <label for="period" class="block text-sm font-medium text-gray-700">Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="custom" <?= ($data['filters']['period'] ?? 'custom') == 'custom' ? 'selected' : '' ?>>Custom</option>
                        <option value="today" <?= ($data['filters']['period'] ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="7days" <?= ($data['filters']['period'] ?? '') == '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="30days" <?= ($data['filters']['period'] ?? '') == '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                        <option value="all" <?= ($data['filters']['period'] ?? '') == 'all' ? 'selected' : '' ?>>All Time</option>
                    </select>
                </div>

                <div class="lg:col-span-2 xl:col-span-1">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Timestamp</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Level</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Message</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Context</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($data['logs'])): ?>
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">No system logs found matching the criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['logs'] as $log): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-4 whitespace-nowrap"><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= ($log['level'] == 'open') ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= ucfirst(htmlspecialchars($log['level'])) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="<?= URLROOT ?>/reports/myactivity?user_id=<?= $log['user_id'] ?>" class="text-blue-500 hover:underline">
                                        <?= htmlspecialchars($log['username'] ?? 'N/A') ?>
                                    </a>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($log['message']) ?></td>
                                <td class="py-3 px-4"><pre class="whitespace-pre-wrap font-sans"><?= htmlspecialchars($log['context']) ?></pre></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            <?php require APPROOT . '/views/includes/pagination_controls.php'; ?>
        </div>
    </div>
</div>

<script>
// Logic to submit form when quick period is changed
document.getElementById('period').addEventListener('change', function() {
    if (this.value !== 'custom') {
        document.getElementById('filter-form').submit();
    }
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?> 