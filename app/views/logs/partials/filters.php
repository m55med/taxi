<form action="<?= BASE_PATH ?>/logs" method="GET" class="bg-white p-4 rounded-lg shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 items-end">
        
        <!-- Search -->
        <div class="lg:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700">بحث شامل</label>
            <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ابحث في التفاصيل، اسم المستخدم...">
        </div>

        <!-- Activity Type -->
        <div>
            <label for="activity_type" class="block text-sm font-medium text-gray-700">نوع النشاط</label>
            <select name="activity_type" id="activity_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="all" <?= ($filters['activity_type'] ?? 'all') == 'all' ? 'selected' : '' ?>>الكل</option>
                <option value="ticket" <?= ($filters['activity_type'] ?? '') == 'ticket' ? 'selected' : '' ?>>تذاكر</option>
                <option value="call" <?= ($filters['activity_type'] ?? '') == 'call' ? 'selected' : '' ?>>مكالمات</option>
                <option value="assignment" <?= ($filters['activity_type'] ?? '') == 'assignment' ? 'selected' : '' ?>>تحويلات</option>
            </select>
        </div>

        <!-- User -->
        <?php if (in_array($userRole, ['admin', 'developer', 'quality_manager', 'Team_leader'])): ?>
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">الموظف</label>
            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="all">كل الموظفين</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user->id ?>" <?= ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' ?>><?= htmlspecialchars($user->username) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Team -->
        <?php if (in_array($userRole, ['admin', 'developer', 'quality_manager', 'Team_leader'])): ?>
        <div>
            <label for="team_id" class="block text-sm font-medium text-gray-700">الفريق</label>
            <select name="team_id" id="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" <?= ($userRole === 'Team_leader') ? 'disabled' : '' ?>>
                <option value="all">كل الفرق</option>
                <?php foreach ($teams as $team): ?>
                     <option value="<?= $team->id ?>" <?= ($filters['team_id'] ?? '') == $team->id ? 'selected' : '' ?>><?= htmlspecialchars($team->name) ?></option>
                <?php endforeach; ?>
            </select>
             <?php if ($userRole === 'Team_leader'): ?>
                <input type="hidden" name="team_id" value="<?= htmlspecialchars($filters['team_id']) ?>">
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Date From -->
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700">من تاريخ</label>
            <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <!-- Date To -->
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700">إلى تاريخ</label>
            <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        
        <!-- Buttons -->
        <div class="flex justify-start space-x-2 space-x-reverse">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-filter ml-2"></i>
                <span>تطبيق</span>
            </button>
            <a href="#" id="clear-filters-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-eraser ml-2"></i>
                <span>مسح</span>
            </a>
        </div>
    </div>
</form> 