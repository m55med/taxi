<?php
// File: /app/views/reports/ReferralVisits/index.php
include_once APPROOT . '/views/includes/header.php';
?>

<body class="bg-gray-100">
    <?php include_once APPROOT . '/views/includes/nav.php'; ?>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($data['title']) ?></h1>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-gray-600">Total Visits</h3>
                <p class="text-3xl font-bold mt-2 text-blue-600"><?= $data['summary']['total_visits'] ?? 0 ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-gray-600">Successful Registrations</h3>
                <p class="text-3xl font-bold mt-2 text-green-600"><?= $data['summary']['successful_registrations'] ?? 0 ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-gray-600">Unique Affiliates</h3>
                <p class="text-3xl font-bold mt-2 text-purple-600"><?= $data['summary']['unique_affiliates'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Filters</h2>
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label for="affiliate_id" class="block text-sm font-medium text-gray-700">Affiliate</label>
                    <select name="affiliate_id" id="affiliate_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">All Affiliates</option>
                        <?php foreach ($data['affiliates'] as $affiliate): ?>
                            <option value="<?= $affiliate['id'] ?>" <?= ($data['filters']['affiliate_id'] ?? '') == $affiliate['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($affiliate['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="registration_status" class="block text-sm font-medium text-gray-700">Registration Status</label>
                    <select name="registration_status" id="registration_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">All</option>
                        <option value="visit_only" <?= ($data['filters']['registration_status'] ?? '') == 'visit_only' ? 'selected' : '' ?>>Visit Only</option>
                        <option value="form_opened" <?= ($data['filters']['registration_status'] ?? '') == 'form_opened' ? 'selected' : '' ?>>Form Opened</option>
                        <option value="attempted" <?= ($data['filters']['registration_status'] ?? '') == 'attempted' ? 'selected' : '' ?>>Attempted</option>
                        <option value="successful" <?= ($data['filters']['registration_status'] ?? '') == 'successful' ? 'selected' : '' ?>>Successful</option>
                        <option value="duplicate_phone" <?= ($data['filters']['registration_status'] ?? '') == 'duplicate_phone' ? 'selected' : '' ?>>Duplicate Phone</option>
                        <option value="failed_other" <?= ($data['filters']['registration_status'] ?? '') == 'failed_other' ? 'selected' : '' ?>>Failed (Other)</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>

                <div class="flex items-end space-x-2 col-span-full justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-1"></i> Apply Filter
                    </button>
                    <a href="<?= BASE_PATH ?>/reports/referral-visits" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        <i class="fas fa-eraser mr-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Export Buttons -->
        <div class="mb-6 flex justify-end space-x-2">
             <?php
                $exportQuery = http_build_query(array_filter($data['filters']));
            ?>
            <a href="<?= BASE_PATH ?>/reports/referral-visits/export-excel?<?= $exportQuery ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                <i class="fas fa-file-excel mr-1"></i> Export to Excel
            </a>
            <a href="<?= BASE_PATH ?>/reports/referral-visits/export-json?<?= $exportQuery ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                <i class="fas fa-file-code mr-1"></i> Export to JSON
            </a>
        </div>

        <!-- Results Table -->
        <div class="bg-white p-4 rounded-lg shadow-md overflow-x-auto">
            <table class="table-auto w-full text-sm text-left">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Visit Time</th>
                        <th class="px-4 py-2">Affiliate Name</th>
                        <th class="px-4 py-2">Registration Status</th>
                        <th class="px-4 py-2">Registered Driver</th>
                        <th class="px-4 py-2">IP Address</th>
                        <th class="px-4 py-2">User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['visits'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No data available.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['visits'] as $visit): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= $visit['id'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['visit_recorded_at']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['affiliate_user_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['registration_status']) ?></td>
                                <td class="px-4 py-2">
                                    <?php if (!empty($visit['registered_driver_id']) && !empty($visit['registered_driver_name'])): ?>
                                        <a href="<?= BASE_PATH ?>/drivers/details/<?= $visit['registered_driver_id'] ?>" class="text-blue-600 hover:underline">
                                            <?= htmlspecialchars($visit['registered_driver_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2"><?= htmlspecialchars($visit['ip_address']) ?></td>
                                <td class="px-4 py-2" title="<?= htmlspecialchars($visit['user_agent']) ?>">
                                    <?= htmlspecialchars(substr($visit['user_agent'], 0, 50)) . '...' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php
            // Helper function to build query string for pagination links
            function getPaginationQuery($filters, $page) {
                $queryParams = array_merge($filters, ['page' => $page]);
                return http_build_query(array_filter($queryParams));
            }
            ?>
            <nav class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?= count($data['visits']) ?></span> of <span class="font-medium"><?= $data['total'] ?></span> results
                </div>
                <?php if ($data['totalPages'] > 1): ?>
                <ul class="inline-flex items-center -space-x-px">
                    <!-- Previous Button -->
                    <li>
                        <a href="?<?= getPaginationQuery($data['filters'], $data['page'] - 1) ?>" class="<?= $data['page'] <= 1 ? 'pointer-events-none text-gray-400' : 'text-blue-600' ?> px-3 py-2 ml-0 leading-tight bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100">Previous</a>
                    </li>

                    <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <li>
                        <a href="?<?= getPaginationQuery($data['filters'], $i) ?>" class="<?= $data['page'] == $i ? 'bg-blue-50 text-blue-600' : 'bg-white' ?> px-3 py-2 leading-tight border border-gray-300 hover:bg-gray-100"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li>
                        <a href="?<?= getPaginationQuery($data['filters'], $data['page'] + 1) ?>" class="<?= $data['page'] >= $data['totalPages'] ? 'pointer-events-none text-gray-400' : 'text-blue-600' ?> px-3 py-2 leading-tight bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100">Next</a>
                    </li>
                </ul>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</body>
</html> 