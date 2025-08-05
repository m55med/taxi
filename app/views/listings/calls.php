<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Calls</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <form id="filter-form" method="GET" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-2">
                    <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Contact Name or Phone)</label>
                    <input type="text" id="search_term" name="search_term" class="w-full px-4 py-2 border rounded-lg" value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>" placeholder="e.g., John Doe or 968...">
                </div>
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                    <input type="text" id="date_range" name="date_range" class="w-full px-4 py-2 border rounded-lg" value="<?= htmlspecialchars($data['filters']['date_range'] ?? '') ?>" placeholder="Select date range">
                    <input type="hidden" id="start_date" name="start_date" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>">
                    <input type="hidden" id="end_date" name="end_date" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>">
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-600 mb-1">User Involved</label>
                    <select id="user_id" name="user_id" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">All Users</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= (isset($data['filters']['user_id']) && $data['filters']['user_id'] == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="call_type" class="block text-sm font-medium text-gray-600 mb-1">Call Type</label>
                    <select id="call_type" name="call_type" class="w-full px-4 py-2 border rounded-lg">
                        <option value="all" <?= (isset($data['filters']['call_type']) && $data['filters']['call_type'] == 'all') ? 'selected' : '' ?>>All</option>
                        <option value="incoming" <?= (isset($data['filters']['call_type']) && $data['filters']['call_type'] == 'incoming') ? 'selected' : '' ?>>Incoming</option>
                        <option value="outgoing" <?= (isset($data['filters']['call_type']) && $data['filters']['call_type'] == 'outgoing') ? 'selected' : '' ?>>Outgoing</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?= URLROOT ?>/listings/calls" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Reset</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['calls'])): ?>
                    <tr><td colspan="6" class="text-center py-10 text-gray-500">No calls found.</td></tr>
                <?php else: ?>
                    <?php foreach($data['calls'] as $call): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $call['call_type'] === 'Incoming' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>"><?= htmlspecialchars($call['call_type']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($call['contact_name']) ?></span>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($call['contact_phone']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($call['user_name']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"><?= htmlspecialchars($call['status']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <!-- Details can be added here -->
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= date('Y-m-d H:i', strtotime($call['call_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
             <p class="text-sm text-gray-700">
                Showing <?= $data['pagination']['total'] > 0 ? (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1 : 0 ?>
                to <?= min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total']) ?>
                of <?= $data['pagination']['total'] ?> results
            </p>
            <div class="flex items-center space-x-2">
                 <?php
                    $queryParams = http_build_query(array_merge($data['filters'], ['page' => $data['pagination']['current_page'] - 1]));
                    $isFirstPage = $data['pagination']['current_page'] <= 1;
                 ?>
                <a href="<?= $isFirstPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isFirstPage ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>">Previous</a>
                
                 <?php
                    $queryParams = http_build_query(array_merge($data['filters'], ['page' => $data['pagination']['current_page'] + 1]));
                    $isLastPage = $data['pagination']['current_page'] >= $data['pagination']['total_pages'];
                 ?>
                <a href="<?= $isLastPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isLastPage ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    flatpickr("#date_range", {
        mode: 'range',
        dateFormat: 'Y-m-d',
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                document.getElementById('start_date').value = instance.formatDate(selectedDates[0], "Y-m-d");
                document.getElementById('end_date').value = instance.formatDate(selectedDates[1], "Y-m-d");
            }
        }
    });
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
