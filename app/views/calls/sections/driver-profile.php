<?php

// تأكيد تحويل array إلى object (لو وصل من الـ Controller كـ array)
if (isset($data['driver']) && is_array($data['driver'])) {
    $data['driver'] = (object) $data['driver'];
}

// Default values for the driver, now sourced from the $data array
$driver = $data['driver'] ?? (object) [
    'id' => 'N/A',
    'name' => 'N/A',
    'phone' => 'N/A',
    'email' => 'N/A',
    'data_source' => 'unknown',
    'app_status' => 'inactive',
    'notes' => 'No notes available.',
    'has_many_trips' => 0,
];

// Helper to translate data source keys
if (!function_exists('getDataSourceText')) {
    function getDataSourceText($source)
    {
        $map = [
            'form' => 'Registration Form',
            'referral' => 'Marketer',
            'telegram' => 'Telegram',
            'staff' => 'By Staff',
            'admin' => 'Admin Entry',
        ];
        return $map[$source] ?? htmlspecialchars(ucfirst($source ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Helper to get app status display info
if (!function_exists('getAppStatusInfo')) {
    function getAppStatusInfo($status)
    {
        $map = [
            'active' => ['text' => 'Active', 'class' => 'bg-green-100 text-green-800'],
            'inactive' => ['text' => 'Inactive', 'class' => 'bg-yellow-100 text-yellow-800'],
            'banned' => ['text' => 'Banned', 'class' => 'bg-red-100 text-red-800'],
        ];
        return $map[$status] ?? ['text' => htmlspecialchars(ucfirst($status ?? ''), ENT_QUOTES, 'UTF-8'), 'class' => 'bg-gray-100 text-gray-800'];
    }
}
?>

<!-- Driver Profile Card -->
<div class="bg-white rounded-lg shadow p-6 relative">
    <?php if ($driver && !empty($driver->hold) && isset($driver->hold_by_username) && $driver->hold_by != $_SESSION['user_id']): ?>
        <div
            class="absolute inset-0 bg-yellow-400 bg-opacity-80 flex flex-col items-center justify-center z-10 rounded-lg p-4">
            <i class="fas fa-lock text-4xl text-white mb-3"></i>
            <h4 class="text-xl font-bold text-white text-center">Driver on Hold</h4>
            <p class="text-sm text-white text-center">This driver is currently being handled by:</p>
            <p class="font-bold text-lg text-white mt-1">
                <?= htmlspecialchars($driver->hold_by_username ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <div class="flex items-center space-x-4 mb-4">
        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
            <i class="fas fa-user text-3xl text-gray-400"></i>
        </div>
        <div>
            <h3 id="driver-profile-name" class="text-xl font-bold text-gray-800">
                <?= htmlspecialchars($driver->name ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="text-sm text-gray-500">ID: <?= htmlspecialchars($driver->id ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">Phone Number:</span>
            <div class="flex items-center">
                <span id="driverPhone" class="font-mono text-gray-800 tracking-wider">
                    <?= htmlspecialchars($driver->phone ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <button
                    onclick="copyToClipboard('<?= htmlspecialchars($driver->phone ?? '', ENT_QUOTES, 'UTF-8') ?>', 'Phone number')"
                    class="ml-2 text-gray-400 hover:text-indigo-600 transition-colors" title="Copy Number">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">Email Address:</span>
            <span id="driver-profile-email" class="font-mono text-gray-800 tracking-wider">
                <?= htmlspecialchars($driver->email ?? 'Not Available', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">Data Source:</span>
            <span id="driverDataSource" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                <?= getDataSourceText($driver->data_source ?? '') ?>
            </span>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">App Status:</span>
            <?php $statusInfo = getAppStatusInfo($driver->app_status ?? ''); ?>
            <span id="driverAppStatus" class="px-2 py-1 text-xs font-medium rounded-full <?= $statusInfo['class'] ?>">
                <?= $statusInfo['text'] ?>
            </span>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">Trips Status:</span>
            <?php if (!empty($driver->has_many_trips)): ?>
                <span id="driverTripsStatus" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Exceeds 10 Trips
                </span>
            <?php else: ?>
                <span id="driverTripsStatus" class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                    <i class="fas fa-times-circle mr-1"></i>
                    Under 10 Trips
                </span>
            <?php endif; ?>
        </div>

        <div class="text-sm">
            <span class="font-semibold text-gray-600">Notes:</span>
            <p id="driver-profile-notes" class="mt-1 text-gray-800 bg-gray-50 p-2 rounded-md whitespace-pre-wrap">
                <?= htmlspecialchars($driver->notes ?? 'None', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </div>
</div>
<script>
window.showToast = function (message, type) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `p-4 rounded shadow text-white font-medium transition transform duration-300 ease-in-out 
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

    toast.innerText = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};
</script>

<script>
    console.log("✅ driver-profile.js loaded");
// تعريف الدالة كـ global على الـ window object
window.copyToClipboard = function (text) {
    if (!navigator.clipboard) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showToast('تم نسخ الرقم بنجاح', 'success');
        } catch (err) {
            showToast('فشل نسخ الرقم', 'error');
        }
        document.body.removeChild(textArea);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        showToast('تم نسخ الرقم بنجاح', 'success');
    }).catch(err => {
        showToast('فشل نسخ الرقم', 'error');
    });
};


</script>
