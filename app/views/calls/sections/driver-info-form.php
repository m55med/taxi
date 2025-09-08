<?php

// Ensure $driver is an object
$driver = $data['driver'] ?? null;
if (is_array($driver)) {
    $driver = (object) $driver;
}

// Convert array items to objects to avoid stdClass as array error
$countries = $data['countries'] ?? [];
$countries = array_map(fn($c) => (object) $c, $countries);

$car_types = $data['car_types'] ?? [];
$car_types = array_map(fn($c) => (object) $c, $car_types);

?>

<?php if ($driver): ?>
    <!-- Collapsible Driver Info Form -->
    <div class="bg-white rounded-lg shadow">
        <!-- Header for Toggling -->
        <div id="driverInfoToggle"
            class="p-6 cursor-pointer flex justify-between items-center border-b border-transparent hover:bg-gray-50 rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-800">Edit Driver Information</h3>
            <i id="driverInfoIcon" class="fas fa-chevron-down transform transition-transform duration-300"></i>
        </div>

        <!-- Collapsible Content -->
        <div id="driverInfoContent" class="hidden p-6 pt-0">
            <form id="driverInfoForm" method="POST" class="space-y-4">
                <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver->id ?? '') ?>">

                <div>
                    <label for="driverName" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="driverName" name="name" value="<?= htmlspecialchars($driver->name ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="driverEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="driverEmail" name="email" value="<?= htmlspecialchars($driver->email ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="driverGender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select id="driverGender" name="gender"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Gender</option>
                        <option value="male" <?= ($driver->gender ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($driver->gender ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <div>
                    <label for="driverCountry" class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                    <select id="driverCountry" name="country_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Nationality</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country->id) ?>" <?= ($driver->country_id ?? '') == $country->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="driverAppStatus" class="block text-sm font-medium text-gray-700 mb-1">App Status</label>
                    <select id="driverAppStatus" name="app_status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active" <?= ($driver->app_status ?? '') === 'active' ? 'selected' : '' ?>>Active
                        </option>
                        <option value="inactive" <?= ($driver->app_status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive
                        </option>
                        <option value="banned" <?= ($driver->app_status ?? '') === 'banned' ? 'selected' : '' ?>>Banned
                        </option>
                    </select>
                </div>

                <div>
                    <label for="driverCarType" class="block text-sm font-medium text-gray-700 mb-1">Car Type</label>
                    <select id="driverCarType" name="car_type_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Car Type</option>
                        <?php foreach ($car_types as $car_type): ?>
                            <option value="<?= htmlspecialchars($car_type->id) ?>" <?= ($driver->car_type_id ?? '') == $car_type->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($car_type->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="driverNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="driverNotes" name="notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($driver->notes ?? '') ?></textarea>
                </div>

                <!-- Has Many Trips Switch -->
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-gray-700">Driver has &gt; 10 trips?</span>
                    <label for="hasManyTripsToggle" class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="has_many_trips" value="0">
                        <input type="checkbox" name="has_many_trips" value="1" id="hasManyTripsToggle" class="sr-only peer"
                            <?= !empty($driver->has_many_trips) ? 'checked' : '' ?>>
                        <div
                            class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                        </div>
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 font-semibold flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Driver Info
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log("✅ driver-info.js loaded");

    const toggleButton = document.getElementById('driverInfoToggle');
    const contentDiv = document.getElementById('driverInfoContent');
    const icon = document.getElementById('driverInfoIcon');
    const form = document.getElementById('driverInfoForm');

    if (toggleButton && contentDiv && icon) {
        toggleButton.addEventListener('click', () => {
            contentDiv.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    }

    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            submitButton.disabled = true;

            try {
                const response = await fetch(`${URLROOT}/driver/update`, {
                    method: 'POST',
                    body: formData
                });
                const parsedData = await response.json();

                if (parsedData.success && parsedData.driver) {
                    showToast(parsedData.message || 'تم تحديث البيانات بنجاح!', 'success');
                    const driver = parsedData.driver;
                    
                    document.getElementById('driver-profile-name').textContent = driver.name;
                    document.getElementById('driver-profile-email').textContent = driver.email || 'غير متوفر';
                    
                    const statusElement = document.getElementById('driverAppStatus');
                    const statusMap = {
                        active: { text: 'نشط', class: 'bg-green-100 text-green-800' },
                        inactive: { text: 'غير نشط', class: 'bg-yellow-100 text-yellow-800' },
                        banned: { text: 'محظور', class: 'bg-red-100 text-red-800' },
                    };
                    const statusInfo = statusMap[driver.app_status] || { text: driver.app_status, class: 'bg-gray-100 text-gray-800' };
                    statusElement.textContent = statusInfo.text;
                    statusElement.className = `px-2 py-1 text-xs font-medium rounded-full ${statusInfo.class}`;

                    document.getElementById('driver-profile-notes').textContent = driver.notes || 'لا يوجد';
                    
                    const tripsStatusElement = document.getElementById('driverTripsStatus');
                    if (!!parseInt(driver.has_many_trips)) {
                        tripsStatusElement.className = 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
                        tripsStatusElement.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Exceeds 10 Trips';
                    } else {
                        tripsStatusElement.className = 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
                        tripsStatusElement.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Under 10 Trips';
                    }

                    form.querySelector('[name="name"]').value = driver.name;
                    form.querySelector('[name="email"]').value = driver.email;
                    form.querySelector('[name="gender"]').value = driver.gender;
                    form.querySelector('[name="country_id"]').value = driver.country_id;
                    form.querySelector('[name="app_status"]').value = driver.app_status;
                    form.querySelector('[name="car_type_id"]').value = driver.car_type_id;
                    form.querySelector('[name="notes"]').value = driver.notes;
                    form.querySelector('[name="has_many_trips"][type="checkbox"]').checked = !!parseInt(driver.has_many_trips);

                } else {
                    showToast(parsedData.message || 'فشل تحديث البيانات.', 'error');
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                showToast('An unexpected error occurred.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save mr-2"></i> Update Driver Info';
            }
        });
    }
});
</script>
