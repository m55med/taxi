<?php
defined('BASE_PATH') or define('BASE_PATH', '');
// Initialize variables if not set
$driver = $data['driver'] ?? null;
$countries = $data['countries'] ?? [];
$car_types = $data['car_types'] ?? [];
?>

<?php if (isset($driver)): ?>
<!-- Collapsible Driver Info Form -->
<div class="bg-white rounded-lg shadow">
    <!-- Header for Toggling -->
    <div id="driverInfoToggle" class="p-6 cursor-pointer flex justify-between items-center border-b border-transparent hover:bg-gray-50 rounded-t-lg">
        <h3 class="text-lg font-semibold text-gray-800">Edit Driver Information</h3>
        <i id="driverInfoIcon" class="fas fa-chevron-down transform transition-transform duration-300"></i>
    </div>

    <!-- Collapsible Content -->
    <div id="driverInfoContent" class="hidden p-6 pt-0">
        <form id="driverInfoForm" method="POST" class="space-y-4">
            <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
            
            <div>
                <label for="driverName" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="driverName" name="name" value="<?= htmlspecialchars($driver['name'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="driverEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="driverEmail" name="email" value="<?= htmlspecialchars($driver['email'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="driverGender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                <select id="driverGender" name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Gender</option>
                    <option value="male" <?= ($driver['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= ($driver['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div>
                <label for="driverCountry" class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                <select id="driverCountry" name="country_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Nationality</option>
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
                <label for="driverAppStatus" class="block text-sm font-medium text-gray-700 mb-1">App Status</label>
                <select id="driverAppStatus" name="app_status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active" <?= ($driver['app_status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($driver['app_status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="banned" <?= ($driver['app_status'] ?? '') == 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>

            <div>
                <label for="driverCarType" class="block text-sm font-medium text-gray-700 mb-1">Car Type</label>
                <select id="driverCarType" name="car_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Car Type</option>
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
                <label for="driverNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="driverNotes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($driver['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 font-semibold flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Update Driver Info
            </button>
        </form>
    </div>
</div>
<?php endif; ?> 