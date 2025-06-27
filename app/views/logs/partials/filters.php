<form id="filters-form" action="<?= BASE_PATH ?>/logs" method="GET" class="bg-white p-4 rounded-lg shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        
        <!-- Search -->
        <div class="lg:col-span-2 xl:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700">Global Search</label>
            <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search details, username...">
        </div>

        <!-- Activity Type -->
        <div>
            <label for="activity_type" class="block text-sm font-medium text-gray-700">Activity Type</label>
            <select name="activity_type" id="activity_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="all" <?= ($filters['activity_type'] ?? 'all') == 'all' ? 'selected' : '' ?>>All Types</option>
                <option value="ticket" <?= ($filters['activity_type'] ?? '') == 'ticket' ? 'selected' : '' ?>>Tickets</option>
                <option value="outgoing_call" <?= ($filters['activity_type'] ?? '') == 'outgoing_call' ? 'selected' : '' ?>>Outgoing Calls</option>
                <option value="incoming_call" <?= ($filters['activity_type'] ?? '') == 'incoming_call' ? 'selected' : '' ?>>Incoming Calls</option>
                <option value="assignment" <?= ($filters['activity_type'] ?? '') == 'assignment' ? 'selected' : '' ?>>Assignments</option>
            </select>
        </div>

        <!-- User -->
        <?php if (in_array($userRole, ['admin', 'developer', 'quality_manager', 'Team_leader'])): ?>
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">Employee</label>
            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="all">All Employees</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user->id ?>" <?= ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' ?>><?= htmlspecialchars($user->username) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Team -->
        <?php if (in_array($userRole, ['admin', 'developer', 'quality_manager', 'Team_leader'])): ?>
        <div>
            <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
            <select name="team_id" id="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" <?= ($userRole === 'Team_leader') ? 'disabled' : '' ?>>
                <option value="all">All Teams</option>
                <?php foreach ($teams as $team): ?>
                     <option value="<?= $team->id ?>" <?= ($filters['team_id'] ?? '') == $team->id ? 'selected' : '' ?>><?= htmlspecialchars($team->name) ?></option>
                <?php endforeach; ?>
            </select>
             <?php if ($userRole === 'Team_leader'): ?>
                <input type="hidden" name="team_id" value="<?= htmlspecialchars($filters['team_id']) ?>">
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mt-4">
         <!-- Date From -->
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
            <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <!-- Date To -->
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
            <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        
        <!-- Quick Date Buttons -->
        <div class="flex items-end space-x-2">
            <button type="button" id="today-btn" class="px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">1d</button>
            <button type="button" id="week-btn" class="px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">7d</button>
            <button type="button" id="month-btn" class="px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">This Month</button>
        </div>

        <!-- Action Buttons -->
        <div class="xl:col-start-5 flex items-end justify-end space-x-2">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-filter -ml-1 mr-2"></i>
                <span>Filter</span>
            </button>
            <a href="<?= BASE_PATH ?>/logs" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-eraser -ml-1 mr-2"></i>
                <span>Clear</span>
            </a>
        </div>
    </div>
</form> 