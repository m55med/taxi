<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Driver Management</h1>
    <p class="text-gray-600 mb-8">A comprehensive view to filter, search, and manage all drivers.</p>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <?php
        $stats = $data['stats'];
        $statOrder = ['total', 'pending', 'completed', 'needs_documents', 'rescheduled', 'blocked'];
        $statusLabels = [
            'total' => 'Total Drivers', 'pending' => 'Pending', 'completed' => 'Completed',
            'needs_documents' => 'Needs Docs', 'rescheduled' => 'Rescheduled', 'blocked' => 'Blocked'
        ];
        ?>
        <?php foreach ($statOrder as $key): ?>
            <?php $isActive = (isset($data['filters']['main_system_status']) && $data['filters']['main_system_status'] == $key); ?>
            <a href="?main_system_status=<?= $key == 'total' ? '' : $key ?>" class="stat-card p-4 rounded-lg shadow-md transition-transform transform hover:scale-105 <?= $isActive ? 'bg-blue-600 text-white shadow-lg' : 'bg-white' ?>">
                <h3 class="text-lg font-semibold <?= $isActive ? 'text-white' : 'text-gray-500' ?>"><?= $statusLabels[$key] ?></h3>
                <p class="text-3xl font-bold <?= $isActive ? 'text-white' : 'text-gray-800' ?>"><?= $stats[$key] ?? 0 ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="filter-form" method="GET" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Name, Phone, Email)</label>
                    <input type="text" name="search_term" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>">
                </div>
                <div>
                     <label for="main_system_status" class="block text-sm font-medium text-gray-600 mb-1">Main Status</label>
                    <select name="main_system_status" class="form-select">
                        <option value="">All Main Statuses</option>
                        <option value="pending" <?= (isset($data['filters']['main_system_status']) && $data['filters']['main_system_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= (isset($data['filters']['main_system_status']) && $data['filters']['main_system_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                        <!-- Add other statuses as needed -->
                    </select>
                </div>
                 <div>
                     <label for="car_type_id" class="block text-sm font-medium text-gray-600 mb-1">Car Type</label>
                    <select name="car_type_id" class="form-select">
                        <option value="">All Car Types</option>
                        <?php foreach($data['car_types'] as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= (isset($data['filters']['car_type_id']) && $data['filters']['car_type_id'] == $type['id']) ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
             <div class="mt-6 flex justify-end space-x-4">
                <a href="<?= URLROOT ?>/listings/drivers" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Search</button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="th">Driver</th>
                    <th class="th">Main Status</th>
                    <th class="th">App Status</th>
                    <th class="th">Details</th>
                    <th class="th">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['drivers'])): ?>
                    <tr><td colspan="5" class="text-center py-10 text-gray-500">No drivers found.</td></tr>
                <?php else: ?>
                    <?php foreach($data['drivers'] as $driver): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="td">
                                <a href="<?= URLROOT ?>/drivers/details/<?= $driver['id'] ?>" class="font-semibold text-blue-600 hover:underline"><?= htmlspecialchars($driver['name']) ?></a>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($driver['phone']) ?></p>
                            </td>
                            <td class="td"><span class="status-badge"><?= htmlspecialchars($driver['main_system_status']) ?></span></td>
                            <td class="td"><span class="status-badge"><?= htmlspecialchars($driver['app_status']) ?></span></td>
                            <td class="td">
                                <p>Calls: <?= $driver['call_count'] ?></p>
                                <p class="<?= $driver['missing_documents_count'] > 0 ? 'text-red-600' : 'text-green-600' ?>">Missing Docs: <?= $driver['missing_documents_count'] ?></p>
                                <p>Car: <?= htmlspecialchars($driver['car_type_name'] ?? 'N/A') ?></p>
                            </td>
                            <td class="td">
                                <a href="<?= URLROOT ?>/drivers/details/<?= $driver['id'] ?>" class="text-blue-500 hover:text-blue-700">View</a>
                            </td>
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
                <a href="<?= $isFirstPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isFirstPage ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>">Prev</a>
                
                 <?php
                    $queryParams = http_build_query(array_merge($data['filters'], ['page' => $data['pagination']['current_page'] + 1]));
                    $isLastPage = $data['pagination']['current_page'] >= $data['pagination']['total_pages'];
                 ?>
                <a href="<?= $isLastPage ? '#' : '?' . $queryParams ?>" class="pagination-btn <?= $isLastPage ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</div>

<style>
.form-select { @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white; }
.th { @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
.td { @apply px-6 py-4 whitespace-nowrap text-sm text-gray-700; }
.status-badge { @apply px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800; }
.pagination-btn { @apply px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50; }
</style>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
