<?php require APPROOT . '/views/includes/header.php'; ?>
<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Trips Report & Dashboard</h1>
            <p class="text-lg text-gray-600">KPIs, suspicious activity, and a detailed trip log.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
                <input type="date" name="start_date" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>" class="hidden">
                <input type="date" name="end_date" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>" class="hidden">
                <div>
                    <label for="driver_name" class="block text-sm font-medium text-gray-700">Driver Name</label>
                    <input type="text" name="driver_name" placeholder="Search driver..." value="<?= htmlspecialchars($data['filters']['driver_name'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="passenger_name" class="block text-sm font-medium text-gray-700">Passenger Name</label>
                    <input type="text" name="passenger_name" placeholder="Search passenger..." value="<?= htmlspecialchars($data['filters']['passenger_name'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="order_status" class="block text-sm font-medium text-gray-700">Trip Status</label>
                    <select name="order_status" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <?php foreach($data['filter_options']['statuses'] as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= ($data['filters']['order_status'] ?? '') == $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700">Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="custom">Custom</option>
                        <option value="today">Today</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div><button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Filter</button></div>
            </div>
        </form>
    </div>

    <!-- Tab Navigation -->
    <div x-data="{ tab: 'dashboard' }" class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#" @click.prevent="tab = 'dashboard'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'dashboard' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">KPI Dashboard</a>
                <a href="#" @click.prevent="tab = 'detaillog'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'detaillog' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Detailed Trip Log</a>
            </nav>
        </div>

        <!-- Dashboard Content -->
        <div x-show="tab === 'dashboard'" class="pt-8">
            <!-- KPI cards, charts, suspicious tables -->
             <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="font-semibold text-lg mb-4">General Stats</h3>
                    <!-- ... General Stats Content ... -->
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="font-semibold text-lg mb-4">Cost KPIs</h3>
                    <!-- ... Cost KPIs Content ... -->
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
                    <h3 class="font-semibold text-lg mb-4">Suspicious Drivers</h3>
                    <!-- ... Suspicious Drivers Table ... -->
                </div>
                 <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
                    <h3 class="font-semibold text-lg mb-4">Suspicious Passengers</h3>
                    <!-- ... Suspicious Passengers Table ... -->
                </div>
            </div>
        </div>

        <!-- Detailed Log Content -->
        <div x-show="tab === 'detaillog'" class="pt-8 bg-white shadow-md rounded-lg overflow-hidden">
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Order ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Driver</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Passenger</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Cost (OMR)</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($data['trips_list'])): ?>
                            <tr><td colspan="6" class="text-center py-10">No trips found.</td></tr>
                        <?php else: foreach ($data['trips_list'] as $trip): ?>
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs"><?= htmlspecialchars($trip['order_id']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($trip['driver_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($trip['passenger_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-3 text-center"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800"><?= htmlspecialchars($trip['order_status']) ?></span></td>
                                <td class="px-4 py-3 text-center font-semibold"><?= number_format($trip['final_cost_omr'], 3) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap"><?= date('Y-m-d H:i', strtotime($trip['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>
        </div>
    </div>
</div>
<script src="//unpkg.com/alpinejs" defer></script>
<?php require APPROOT . '/views/includes/footer.php'; ?> 