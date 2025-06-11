<?php
defined('BASE_PATH') or define('BASE_PATH', '');
// Initialize variables if not set
$countries = $countries ?? [];
?>

<?php if (isset($driver)): ?>
<!-- Collapsible Driver Info Form -->
<div class="bg-white rounded-lg shadow">
    <!-- Header for Toggling -->
    <div id="driverInfoToggle" class="p-6 cursor-pointer flex justify-between items-center border-b border-transparent">
        <h3 class="text-lg font-semibold text-gray-800">تعديل بيانات السائق</h3>
        <i id="driverInfoIcon" class="fas fa-chevron-down transform transition-transform duration-300"></i>
    </div>

    <!-- Collapsible Content -->
    <div id="driverInfoContent" class="hidden p-6 pt-0">
        <form id="driverInfoForm" method="POST" class="space-y-4">
            <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الاسم</label>
                <input type="text" name="name" value="<?= htmlspecialchars($driver['name'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="<?= htmlspecialchars($driver['email'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <option value="">اختر النوع</option>
                    <option value="male" <?= $driver['gender'] == 'male' ? 'selected' : '' ?>>ذكر</option>
                    <option value="female" <?= $driver['gender'] == 'female' ? 'selected' : '' ?>>أنثى</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الجنسية</label>
                <select name="country_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <option value="">اختر الجنسية</option>
                    <?php if (!empty($countries)): ?>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>" 
                                    <?= (isset($driver['country_id']) && $driver['country_id'] == $country['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">حالة التطبيق</label>
                <select name="app_status" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <option value="active" <?= ($driver['app_status'] ?? 'active') == 'active' ? 'selected' : '' ?>>نشط</option>
                    <option value="inactive" <?= ($driver['app_status'] ?? 'active') == 'inactive' ? 'selected' : '' ?>>غير نشط</option>
                    <option value="banned" <?= ($driver['app_status'] ?? 'active') == 'banned' ? 'selected' : '' ?>>محظور</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع السيارة</label>
                <select name="car_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    <option value="">اختر نوع السيارة</option>
                    <?php if (!empty($car_types)): ?>
                        <?php foreach ($car_types as $car_type): ?>
                            <option value="<?= $car_type['id'] ?>" 
                                    <?= (isset($driver['car_type_id']) && $driver['car_type_id'] == $car_type['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($car_type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($driver['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 font-semibold">
                <i class="fas fa-user-edit ml-2"></i>
                تحديث بيانات السائق
            </button>
        </form>
    </div>
</div>
<?php endif; ?> 