<?php
defined('BASE_PATH') or define('BASE_PATH', '');

// Initialize default values for driver if not set
$driver = $driver ?? [
    'id' => '',
    'name' => '',
    'phone' => '',
    'data_source' => '',
    'app_status' => 'active'
];

// Helper function to translate data source keys into readable Arabic text
if (!function_exists('getDataSourceText')) {
    function getDataSourceText($source) {
        $map = [
            'form' => 'نموذج تسجيل',
            'referral' => 'توصية',
            'telegram' => 'تلغرام',
            'staff' => 'عن طريق موظف',
            'admin' => 'إضافة إدارية',
        ];
        return $map[$source] ?? htmlspecialchars($source);
    }
}
// Helper function to translate app status keys
if (!function_exists('getAppStatusInfo')) {
    function getAppStatusInfo($status) {
        $map = [
            'active' => ['text' => 'نشط', 'class' => 'bg-green-100 text-green-800'],
            'inactive' => ['text' => 'غير نشط', 'class' => 'bg-yellow-100 text-yellow-800'],
            'banned' => ['text' => 'محظور', 'class' => 'bg-red-100 text-red-800'],
        ];
        return $map[$status] ?? ['text' => htmlspecialchars($status), 'class' => 'bg-gray-100 text-gray-800'];
    }
}
?>

<!-- Driver Profile Card -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center space-x-4 space-x-reverse mb-4">
        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
            <i class="fas fa-user text-3xl text-gray-400"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($driver['name']) ?></h3>
            <p class="text-sm text-gray-500">ID: <?= $driver['id'] ?></p>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">رقم الهاتف:</span>
            <div class="flex items-center">
                <span id="driverPhone" class="font-mono text-gray-800 tracking-wider"><?= htmlspecialchars($driver['phone']) ?></span>
                <button onclick="copyToClipboard('<?= htmlspecialchars($driver['phone']) ?>')" class="mr-2 text-gray-400 hover:text-indigo-600 transition-colors" title="نسخ الرقم">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">مصدر البيانات:</span>
            <span id="driverDataSource" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                <?= getDataSourceText($driver['data_source']) ?>
            </span>
        </div>

        <div class="flex justify-between items-center text-sm">
            <span class="font-semibold text-gray-600">حالة التطبيق:</span>
            <?php $statusInfo = getAppStatusInfo($driver['app_status']); ?>
            <span id="driverAppStatus" class="px-2 py-1 text-xs font-medium rounded-full <?= $statusInfo['class'] ?>">
                <?= $statusInfo['text'] ?>
            </span>
        </div>
    </div>
</div> 