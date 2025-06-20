<?php require_once APPROOT . '/app/views/inc/header.php'; ?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md" x-data="{ activeTab: <?= $roles[0]['id'] ?? 0 ?> }">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-4">إدارة صلاحيات الأدوار</h1>

    <?php if (empty($roles)): ?>
        <div class="text-center py-12 text-gray-500">
            <p>لا توجد أدوار لعرضها. يرجى إضافة أدوار أولاً.</p>
        </div>
    <?php else: ?>
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Tabs Navigation -->
            <div class="w-full md:w-1/4">
                <ul class="space-y-2">
                    <?php foreach ($roles as $role): ?>
                        <li>
                            <button @click="activeTab = 'role-<?= $role['id'] ?>'"
                                :class="{ 'bg-indigo-600 text-white': activeTab === 'role-<?= $role['id'] ?>', 'bg-gray-200 text-gray-700 hover:bg-gray-300': activeTab !== 'role-<?= $role['id'] ?>' }"
                                class="w-full text-right px-4 py-3 rounded-md transition-colors duration-300 font-semibold flex items-center justify-between">
                                <span><?= htmlspecialchars($role['name']) ?></span>
                                <?php if (in_array($role['name'], ['admin', 'developer'])): ?>
                                    <i class="fas fa-lock text-yellow-400" title="صلاحيات كاملة وغير قابلة للتعديل"></i>
                                <?php endif; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Tabs Content -->
            <div class="w-full md:w-3/4">
                <form action="<?= BASE_PATH ?>/admin/permissions/save" method="POST">
                    <div id="permissions-content" class="mt-6">
                        <?php foreach ($roles as $role): ?>
                            <div x-show="activeTab === 'role-<?= $role['id'] ?>'" class="p-4 bg-gray-50 rounded-lg shadow-inner">
                                <h3 class="text-xl font-bold mb-4 text-gray-800">صلاحيات: <?= htmlspecialchars($role['name']) ?></h3>

                                <?php
                                $isEditable = !in_array($role['name'], ['admin', 'developer']);
                                if (!$isEditable):
                                ?>
                                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                                        <p class="font-bold">ملاحظة</p>
                                        <p>صلاحيات دور "<?= htmlspecialchars($role['name']) ?>" لا يمكن تعديلها.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <?php foreach ($modules as $module): ?>
                                        <?php
                                            $permissionString = $module['permission'];
                                            $isChecked = false;
                                            if ($isEditable) {
                                                foreach ($permissions[$role['id']] as $userPermission) {
                                                    if (str_starts_with($userPermission, $permissionString) || $userPermission === $permissionString) {
                                                        $isChecked = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $isChecked = true;
                                            }
                                        ?>
                                        <label class="flex items-center space-x-3 space-x-reverse bg-gray-50 p-3 rounded-md hover:bg-gray-100 transition-colors cursor-pointer">
                                            <input type="checkbox"
                                                name="permissions[<?= $role['id'] ?>][]"
                                                value="<?= htmlspecialchars($permissionString) ?>"
                                                class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500"
                                                <?= $isChecked ? 'checked' : '' ?>
                                                <?= !$isEditable ? 'disabled' : '' ?>>
                                            <span class="text-gray-700 font-medium" title="<?= htmlspecialchars($permissionString) ?>"><?= htmlspecialchars($module['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 transition-colors shadow-lg">
                            <i class="fas fa-save ml-2"></i>
                            حفظ الصلاحيات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/app/views/inc/footer.php'; ?> 