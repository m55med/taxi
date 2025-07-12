<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Driver Documents Report</h1>
            <p class="text-lg text-gray-600">Compliance status of all required driver documents.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 items-end">
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
                    <label for="document_type_id" class="block text-sm font-medium text-gray-700">Document Type</label>
                    <select name="document_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Types</option>
                        <?php foreach($data['filter_options']['document_types'] as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= ($data['filters']['document_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Statuses</option>
                        <?php foreach($data['filter_options']['statuses'] as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= ($data['filters']['status'] ?? '') == $status ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Filter</button>
                </div>
            </div>
        </form>
    </div>

     <!-- Stats & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <div class="lg:col-span-1 space-y-6">
             <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                 <div class="bg-red-100 rounded-full p-3"><i class="fas fa-exclamation-triangle fa-lg text-red-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Missing</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['missing'] ?? 0) ?></p>
                </div>
            </div>
             <div class="bg-yellow-100 rounded-full p-3"><i class="fas fa-clock fa-lg text-yellow-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Submitted</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['submitted'] ?? 0) ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                 <div class="bg-green-100 rounded-full p-3"><i class="fas fa-check-circle fa-lg text-green-500"></i></div>
                 <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rejected</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($data['stats']['rejected'] ?? 0) ?></p>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6"><canvas id="complianceChart"></canvas></div>
    </div>


    <!-- Documents Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Document Type</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Note</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['documents'])): ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">No documents found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['documents'] as $doc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><a href="<?= BASE_PATH ?>/drivers/details/<?= $doc['driver_id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($doc['driver_name']) ?></a></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($doc['document_type_name']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                        $status_color = 'bg-gray-100 text-gray-800';
                                        if ($doc['status'] == 'submitted') $status_color = 'bg-yellow-100 text-yellow-800';
                                        if ($doc['status'] == 'rejected') $status_color = 'bg-red-100 text-red-800';
                                        if ($doc['status'] == 'missing') $status_color = 'bg-red-100 text-red-800';
                                    ?>
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                        <?= htmlspecialchars(ucfirst($doc['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-sm truncate" title="<?= htmlspecialchars($doc['note']) ?>"><?= htmlspecialchars($doc['note']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    <?= htmlspecialchars($doc['updated_by_user'] ?? 'System') ?> on 
                                    <?= date('Y-m-d H:i', strtotime($doc['updated_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom'} } };
    const complianceCtx = document.getElementById('complianceChart').getContext('2d');
    const complianceData = <?= json_encode($data['stats'] ?? []) ?>;
    new Chart(complianceCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(complianceData),
            datasets: [{ 
                data: Object.values(complianceData), 
                backgroundColor: ['#EF4444', '#F59E0B', '#10B981', '#3B82F6']
            }]
        },
        options: { ...chartOptions, plugins: { ...chartOptions.plugins, title: { display: true, text: 'Document Status Distribution' } } }
    });
});
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?> 