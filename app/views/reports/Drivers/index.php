<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    </style>
</head>
<body class="bg-gray-100">

<?php include __DIR__ . '/../../includes/nav.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-xl p-6 md:p-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Drivers Report</h1>
                <p class="mt-1 text-sm text-gray-500">View and analyze driver data.</p>
            </div>
            <div>
                <a href="<?= BASE_PATH ?>/reports/drivers/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-file-export mr-2"></i>
                    Export Report
                </a>
            </div>
        </div>

        <!-- Enhanced Statistics Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Driver Statistics</h3>
            
            <!-- Main Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-blue-800">Total Drivers</p>
                    <h4 class="text-2xl font-bold text-blue-900 mt-1"><?= number_format($data['total_drivers'] ?? 0) ?></h4>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-green-800">Active Drivers</p>
                    <h4 class="text-2xl font-bold text-green-900 mt-1"><?= number_format($data['active_drivers'] ?? 0) ?></h4>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-yellow-800">Pending</p>
                    <h4 class="text-2xl font-bold text-yellow-900 mt-1"><?= number_format($data['pending_drivers'] ?? 0) ?></h4>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-red-800">Banned</p>
                    <h4 class="text-2xl font-bold text-red-900 mt-1"><?= number_format($data['banned_drivers'] ?? 0) ?></h4>
                </div>
                 <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-800">On Hold</p>
                    <h4 class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($data['on_hold_drivers'] ?? 0) ?></h4>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Filter Results</h3>
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                <div>
                    <label for="main_system_status" class="block text-sm font-medium text-gray-700">Driver Status</label>
                    <select id="main_system_status" name="main_system_status" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="pending" <?= ($data['filters']['main_system_status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="waiting_chat" <?= ($data['filters']['main_system_status'] ?? '') == 'waiting_chat' ? 'selected' : '' ?>>Waiting Chat</option>
                        <option value="no_answer" <?= ($data['filters']['main_system_status'] ?? '') == 'no_answer' ? 'selected' : '' ?>>No Answer</option>
                        <option value="rescheduled" <?= ($data['filters']['main_system_status'] ?? '') == 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                        <option value="completed" <?= ($data['filters']['main_system_status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="blocked" <?= ($data['filters']['main_system_status'] ?? '') == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        <option value="reconsider" <?= ($data['filters']['main_system_status'] ?? '') == 'reconsider' ? 'selected' : '' ?>>Reconsider</option>
                        <option value="needs_documents" <?= ($data['filters']['main_system_status'] ?? '') == 'needs_documents' ? 'selected' : '' ?>>Needs Documents</option>
                    </select>
                </div>

                <div>
                    <label for="data_source" class="block text-sm font-medium text-gray-700">Data Source</label>
                    <select id="data_source" name="data_source" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="form" <?= ($data['filters']['data_source'] ?? '') == 'form' ? 'selected' : '' ?>>Form</option>
                        <option value="referral" <?= ($data['filters']['data_source'] ?? '') == 'referral' ? 'selected' : '' ?>>Referral</option>
                        <option value="telegram" <?= ($data['filters']['data_source'] ?? '') == 'telegram' ? 'selected' : '' ?>>Telegram</option>
                        <option value="staff" <?= ($data['filters']['data_source'] ?? '') == 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="excel" <?= ($data['filters']['data_source'] ?? '') == 'excel' ? 'selected' : '' ?>>Excel</option>
                    </select>
                </div>

                <div>
                    <label for="has_missing_documents" class="block text-sm font-medium text-gray-700">Documents</label>
                    <select id="has_missing_documents" name="has_missing_documents" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1" <?= ($data['filters']['has_missing_documents'] ?? '') == '1' ? 'selected' : '' ?>>Missing</option>
                        <option value="0" <?= ($data['filters']['has_missing_documents'] ?? '') == '0' ? 'selected' : '' ?>>Complete</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex items-end space-x-3">
                    <button type="submit" class="w-full inline-flex justify-center items-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                     <a href="<?= BASE_PATH ?>/reports/drivers" class="w-full inline-flex justify-center items-center bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="fas fa-undo mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Drivers Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Driver</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Country</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">App Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">System Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Source</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">On Hold</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Added By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registered At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['drivers'])): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-500">
                                <i class="fas fa-user-slash fa-3x mb-3"></i>
                                <p class="text-lg font-medium">No drivers found for the selected criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['drivers'] as $driver): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="<?= BASE_PATH ?>/drivers/details/<?= $driver['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                                <?= htmlspecialchars($driver['name']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700"><?= htmlspecialchars($driver['phone']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($driver['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($driver['country_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= ($driver['app_status'] == 'active') ? 'bg-green-100 text-green-800' : 
                                          (($driver['app_status'] == 'inactive') ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= htmlspecialchars(ucfirst($driver['app_status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $driver['main_system_status']))) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars(ucfirst($driver['data_source'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($driver['hold']): ?>
                                        <i class="fas fa-check-circle text-green-500" title="On hold by <?= htmlspecialchars($driver['hold_by_username'] ?? 'N/A') ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-minus-circle text-gray-400"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($driver['added_by_username'] ?? 'System') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= date('M j, Y, g:i A', strtotime($driver['registered_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-700">
                Showing <span class="font-semibold"><?= ($data['pagination']['offset'] ?? 0) + 1 ?></span>
                to <span class="font-semibold"><?= ($data['pagination']['offset'] ?? 0) + count($data['drivers']) ?></span>
                of <span class="font-semibold"><?= number_format($data['pagination']['total_records'] ?? 0) ?></span> results
            </div>

            <?php if (($data['pagination']['total_pages'] ?? 1) > 1): ?>
                <div class="inline-flex items-center -space-x-px">
                    <?php
                        $queryParams = $_GET;
                        // Previous Page
                        if ($data['pagination']['page'] > 1) {
                            $queryParams['page'] = $data['pagination']['page'] - 1;
                            echo '<a href="?' . http_build_query($queryParams) . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>';
                        }
                        
                        // Page Numbers
                        $startPage = max(1, $data['pagination']['page'] - 2);
                        $endPage = min($data['pagination']['total_pages'], $data['pagination']['page'] + 2);

                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $queryParams['page'] = $i;
                            $isActive = $i == $data['pagination']['page'];
                            echo '<a href="?' . http_build_query($queryParams) . '" class="px-3 py-2 leading-tight ' . ($isActive ? 'text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100') . '">' . $i . '</a>';
                        }

                        // Next Page
                        if ($data['pagination']['page'] < $data['pagination']['total_pages']) {
                            $queryParams['page'] = $data['pagination']['page'] + 1;
                            echo '<a href="?' . http_build_query($queryParams) . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">Next</a>';
                        }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>