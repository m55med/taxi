<?php require APPROOT . '/views/includes/header.php'; ?>
<div class="p-4 lg:p-6" x-data="dateFilters('<?= $data['filters']['from_date'] ?>', '<?= $data['filters']['to_date'] ?>')">
    <div class="mb-6">
        <a href="<?= URLROOT ?>/reports/breaks" class="text-indigo-600 hover:underline text-sm flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Breaks Summary
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2 flex items-center">
            <i class="fas fa-user-clock mr-3 text-indigo-500"></i>
            <span>Break History for <?= htmlspecialchars($data['user']->name) ?></span>
        </h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center">
            <div class="bg-blue-100 text-blue-500 p-4 rounded-full mr-4">
                <i class="fas fa-clock fa-2x"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Break Duration</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_duration_formatted ?? '00:00:00') ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center">
            <div class="bg-green-100 text-green-500 p-4 rounded-full mr-4">
                <i class="fas fa-coffee fa-2x"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Breaks Taken</p>
                <p class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($data['stats']->total_breaks ?? '0') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
        <form action="<?= URLROOT ?>/reports/breaks/user/<?= $data['user']->id ?>" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div class="md:col-span-3 grid grid-cols-2 gap-4">
                <div>
                    <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" name="from_date" id="from_date" class="w-full border-gray-300 rounded-lg shadow-sm" x-model="fromDate">
                </div>
                <div>
                    <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" name="to_date" id="to_date" class="w-full border-gray-300 rounded-lg shadow-sm" x-model="toDate">
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="md:col-span-1 flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors w-full">Filter</button>
                <a href="<?= URLROOT ?>/reports/breaks/user/<?= $data['user']->id ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">Reset</a>
            </div>
        </form>
         <!-- Quick Filters -->
         <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-sm font-medium text-gray-600">Quick Select:</span>
            <a href="#" onclick="applyQuickFilter('today')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'today', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'today' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Today</a>
            <a href="#" onclick="applyQuickFilter('last7')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last7', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last7' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 7 Days</a>
            <a href="#" onclick="applyQuickFilter('last30')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'last30', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'last30' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">Last 30 Days</a>
            <a href="#" onclick="applyQuickFilter('this_month')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'this_month', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'this_month' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">This Month</a>
            <a href="#" onclick="applyQuickFilter('all')" :class="{ 'bg-indigo-600 text-white': '<?= $data['period'] ?>' === 'all', 'bg-gray-100 text-gray-600': '<?= $data['period'] ?>' !== 'all' }" class="px-3 py-1 rounded-full text-sm hover:bg-indigo-500 hover:text-white transition-colors">All Time</a>
        </div>
    </div>

    <!-- Details Table -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-md p-6">
        <table class="min-w-full bg-white">
            <thead class="border-b-2 border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Start Time</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">End Time</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm text-gray-600">Duration</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($data['breaks'])): ?>
                    <tr>
                        <td colspan="3" class="text-center py-10 text-gray-500">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No break data found for the selected criteria.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['breaks'] as $break): ?>
                        <?php
                        // Parse dates using correct format (h:i:s A, Y-m-d)
                        $startDateTime = DateTime::createFromFormat('h:i:s A, Y-m-d', $break->start_time);
                        $endDateTime = $break->end_time ? DateTime::createFromFormat('h:i:s A, Y-m-d', $break->end_time) : null;

                        // Calculate duration in minutes for coloring
                        $durationMinutes = 0;
                        if ($break->duration_seconds) {
                            $durationMinutes = round($break->duration_seconds / 60);
                        } elseif ($endDateTime) {
                            // If break has ended but no duration_seconds, calculate from timestamps
                            $durationMinutes = round(($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 60);
                        }

                        $isLongBreak = $durationMinutes >= 30;
                        $rowClass = $isLongBreak ? 'border-b border-gray-200 hover:bg-red-50 bg-red-25' : 'border-b border-gray-200 hover:bg-gray-50';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="py-4 px-4 font-mono"><?= $startDateTime ? $startDateTime->format('Y-m-d h:i:s A') : 'Invalid Date' ?></td>
                            <td class="py-4 px-4 font-mono">
                                <?= $endDateTime ? $endDateTime->format('Y-m-d h:i:s A') : '<span class="text-yellow-600 font-semibold">In Progress</span>' ?>
                            </td>
                            <td class="py-4 px-4 font-mono <?= $isLongBreak ? 'text-red-600 font-bold' : '' ?>">
                                <?= $break->duration_formatted ?? 'N/A' ?>
                                <?php if ($isLongBreak && $break->duration_formatted): ?>
                                    <span class="text-xs text-red-500 ml-1">(Exceeding break)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function dateFilters(fromDate, toDate) {
        return {
            fromDate: fromDate || '',
            toDate: toDate || '',
        }
    }

    // Function to apply quick filter while preserving other filters
    function applyQuickFilter(period) {
        const url = new URL(window.location);

        // Remove date-related parameters when using quick filters
        url.searchParams.delete('from_date');
        url.searchParams.delete('to_date');

        // Set the new period
        url.searchParams.set('period', period);

        // Keep all other parameters (user_id, team_id, sort_by, etc.)
        window.location.href = url.toString();
    }
</script>

<?php require APPROOT . '/views/includes/footer.php'; ?>
