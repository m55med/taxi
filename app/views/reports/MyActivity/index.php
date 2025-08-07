<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <?php 
        $user = $data['user_info'] ?? null;
        $activity = $data['user_activity'] ?? null;
        $filters = $data['filters'] ?? [];

        // If the user cannot be found, display an error and stop.
        if (!$user) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">User not found. Cannot display activity report.</span>
                  </div>';
            echo '</div>'; // Close container
            require APPROOT . '/views/includes/footer.php';
            exit; // Stop rendering the rest of the page
        }
    ?>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800"><?= htmlspecialchars($user->username ?? 'Unknown User') ?>'s Activity</h1>
            <p class="text-lg text-gray-600">Role: <span class="font-semibold text-indigo-600"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user->role_name ?? 'N/A'))) ?></span></p>
        </div>
    </div>
    
    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-8">
        <form action="" method="get">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user->id ?? '') ?>">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">Apply</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($activity): ?>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-indigo-500">
            <h2 class="text-gray-500 text-lg font-semibold">Total Points</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= number_format($activity['points_details']['final_total_points'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-teal-500">
            <h2 class="text-gray-500 text-lg font-semibold">Quality Score</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= number_format($activity['quality_score'] ?? 0, 2) ?>%</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <h2 class="text-gray-500 text-lg font-semibold">Total Tickets</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= number_format(($activity['normal_tickets'] ?? 0) + ($activity['vip_tickets'] ?? 0)) ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <h2 class="text-gray-500 text-lg font-semibold">Total Calls</h2>
            <p class="text-4xl font-bold text-gray-900 mt-2"><?= number_format(($activity['call_stats']['total_incoming_calls'] ?? 0) + ($activity['call_stats']['total_outgoing_calls'] ?? 0)) ?></p>
        </div>
    </div>
    
    <!-- Points Breakdown -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Points Breakdown</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Tickets -->
            <div class="border p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Tickets</h3>
                <ul class="space-y-1 text-gray-600">
                    <li>Normal Tickets: <span class="font-mono float-right"><?= number_format($activity['normal_tickets'] ?? 0) ?></span></li>
                    <li>VIP Tickets: <span class="font-mono float-right"><?= number_format($activity['vip_tickets'] ?? 0) ?></span></li>
                    <li class="border-t pt-1 mt-1">Ticket Points: <span class="font-mono float-right font-bold text-green-600"><?= number_format($activity['points_details']['ticket_points'] ?? 0, 2) ?></span></li>
                </ul>
            </div>
             <!-- Calls -->
            <div class="border p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Calls</h3>
                <ul class="space-y-1 text-gray-600">
                    <li>Incoming: <span class="font-mono float-right"><?= number_format($activity['call_stats']['total_incoming_calls'] ?? 0) ?></span></li>
                    <li>Outgoing: <span class="font-mono float-right"><?= number_format($activity['call_stats']['total_outgoing_calls'] ?? 0) ?></span></li>
                    <li class="border-t pt-1 mt-1">Call Points: <span class="font-mono float-right font-bold text-green-600"><?= number_format($activity['points_details']['call_points'] ?? 0, 2) ?></span></li>
                </ul>
            </div>
             <!-- Quality & Bonus -->
            <div class="border p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Quality & Bonus</h3>
                <ul class="space-y-1 text-gray-600">
                    <li>Reviews: <span class="font-mono float-right"><?= number_format($activity['total_reviews'] ?? 0) ?></span></li>
                    <li>Avg Rating: <span class="font-mono float-right"><?= number_format($activity['quality_score'] ?? 0, 2) ?>%</span></li>
                    <li class="border-t pt-1 mt-1">Monthly Bonus: <span class="font-mono float-right font-bold text-green-600"><?= number_format($activity['points_details']['bonus_amount'] ?? 0, 2) ?> (<?= number_format($activity['points_details']['bonus_percent'] ?? 0, 1) ?>%)</span></li>
                </ul>
            </div>
        </div>
        <div class="mt-6 border-t pt-4">
             <h3 class="text-xl font-semibold text-gray-800 text-center">Base Points: <?= number_format($activity['points_details']['base_points'] ?? 0, 2) ?> + Bonus: <?= number_format($activity['points_details']['bonus_amount'] ?? 0, 2) ?> = <span class="text-indigo-600">Final Score: <?= number_format($activity['points_details']['final_total_points'] ?? 0, 2) ?></span></h3>
        </div>
    </div>
    <?php else: ?>
        <div class="bg-white p-10 rounded-lg shadow-md text-center">
            <h2 class="text-2xl font-bold text-gray-700">No Activity Data Found</h2>
            <p class="text-gray-500 mt-2">There is no activity recorded for this user in the selected date range.</p>
        </div>
    <?php endif; ?>
</div>

<?php require APPROOT . '/views/includes/footer.php'; ?> 