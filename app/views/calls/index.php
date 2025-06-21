<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز الاتصال</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_PATH ?>/app/views/calls/css/styles.css" rel="stylesheet">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
    <script> const BASE_PATH = '<?= BASE_PATH ?>'; </script>
</head>

<body class="bg-gray-100 font-sans">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Notification Placeholder -->
        <div id="toast-container" class="fixed top-5 right-5 z-[100] space-y-2"></div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">مركز الاتصال</h1>
            
            <!-- Search Form -->
            <form action="<?= BASE_PATH ?>/calls" method="GET" class="flex-grow md:max-w-xs">
                <div class="relative">
                    <input type="search" name="phone" placeholder="البحث عن طريق رقم الهاتف..."
                           class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </form>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-sm font-semibold text-gray-500 mb-1">مكالمات اليوم</h3>
                    <p class="text-2xl font-bold text-indigo-600"><?= $today_calls_count ?? 0 ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-sm font-semibold text-gray-500 mb-1">قائمة الانتظار</h3>
                    <p class="text-2xl font-bold text-indigo-600"><?= $total_pending_calls ?? 0 ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($debug_info) && !empty($debug_info)) : ?>
            <div class="mb-6 p-4 bg-gray-800 text-white rounded-lg shadow-md font-mono text-sm">
                <h3 class="text-lg font-bold mb-2 border-b border-gray-600 pb-2">معلومات التشخيص (للمطورين)</h3>
                
                <?php if (!empty($debug_info['error'])) : ?>
                    <div class="mb-4">
                        <strong class="text-red-400">خطأ في الاستعلام:</strong>
                        <pre class="bg-red-900 text-white p-2 rounded mt-1"><?= htmlspecialchars($debug_info['error']) ?></pre>
                    </div>
                <?php endif; ?>

                 <div class="mb-4">
                    <strong class="text-yellow-400">ID السائق الذي تم اختياره (قبل القفل):</strong>
                    <span class="text-xl font-bold ml-2"><?= $debug_info['driver_id'] ?? 'None' ?></span>
                </div>

                <div class="mb-4">
                    <strong class="text-cyan-400">الاستعلام الذي تم تنفيذه:</strong>
                    <pre class="bg-gray-900 p-2 rounded mt-1"><?= htmlspecialchars($debug_info['query'] ?? '') ?></pre>
                </div>

                <div>
                    <strong class="text-green-400">المُدخلات:</strong>
                    <pre class="bg-gray-900 p-2 rounded mt-1"><?= htmlspecialchars(print_r($debug_info['params'] ?? [], true)) ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <!-- Conditional Content: Rate Limit / No Driver / Main Content -->
        <?php if (isset($rate_limit_exceeded) && $rate_limit_exceeded === true): ?>
            <div id="rate-limit-alert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md text-center">
                <p class="font-bold text-lg mb-2">لقد تجاوزت الحد الأقصى للمكالمات</p>
                <p>يرجى الانتظار لمدة <span id="countdown" class="font-bold text-xl"><?= $wait_time ?? 0 ?></span> ثانية.</p>
            </div>
        <?php elseif (empty($driver)): ?>
            <div class="text-center py-20 bg-white rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">لا يوجد سائقين في قائمة الانتظار حالياً</h2>
                <p class="text-gray-600">سيتم عرض السائق التالي المتاح هنا تلقائيًا.</p>
            </div>
        <?php else: ?>
            <!-- Main Grid Layout -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Sidebar -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <?php include __DIR__ . '/sections/driver-profile.php'; ?>
                    <?php include __DIR__ . '/sections/driver-info-form.php'; ?>
                </div>

                <!-- Right Content Area with Tabs -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="bg-white rounded-lg shadow">
                        <!-- Tab Buttons -->
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-2 space-x-reverse" aria-label="Tabs">
                                <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-indigo-500 text-indigo-600" data-tab="call-form">
                                    <i class="fas fa-phone-alt ml-2"></i>
                                    <span>تفاصيل المكالمة</span>
                                </button>
                                <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" data-tab="call-history">
                                    <i class="fas fa-history ml-2"></i>
                                    <span>سجل المكالمات</span>
                                </button>
                                <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" data-tab="documents">
                                    <i class="fas fa-file-alt ml-2"></i>
                                    <span>المستندات</span>
                                </button>
                            </nav>
                        </div>
                        
                        <!-- Tab Content -->
                        <div class="p-6">
                            <div id="call-form-content" class="tab-content active">
                                <?php include __DIR__ . '/sections/call-form.php'; ?>
                            </div>
                            <div id="call-history-content" class="tab-content">
                                <?php include __DIR__ . '/sections/call-history.php'; ?>
                            </div>
                            <div id="documents-content" class="tab-content">
                                <?php include __DIR__ . '/sections/documents.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modals -->
    <?php include __DIR__ . '/sections/transfer-modal.php'; ?>

    <!-- Core & Module Scripts -->
    <script src="<?= BASE_PATH ?>/public/js/utils.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/shared.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/driver-profile.js?v=1.1"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/driver-info.js?v=1.3"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/documents.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/transfer.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/calls/js/call-form.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching logic
            const tabs = document.querySelectorAll('.tab-button');
            const inactiveClasses = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'];
            const activeClasses = ['border-indigo-500', 'text-indigo-600'];

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs
                    tabs.forEach(t => {
                        t.classList.remove(...activeClasses);
                        t.classList.add(...inactiveClasses);
                    });

                    // Deactivate all content
                    document.querySelectorAll('.tab-content').forEach(c => {
                        c.classList.remove('active');
                    });

                    // Activate clicked tab
                    tab.classList.remove(...inactiveClasses);
                    tab.classList.add(...activeClasses);

                    // Activate associated content
                    const tabContentId = tab.dataset.tab + '-content';
                    document.getElementById(tabContentId).classList.add('active');
                });
            });

            // Rate limit countdown
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                let timeLeft = parseInt(countdownElement.innerText);
                const interval = setInterval(() => {
                    timeLeft--;
                    countdownElement.innerText = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        window.location.reload();
                    }
                }, 1000);
            }
        });

        // Reliable beacon to release hold on driver
        window.addEventListener('beforeunload', function () {
            if (navigator.sendBeacon) {
                navigator.sendBeacon('<?= BASE_PATH ?>/calls/releaseHold');
            }
        });
    </script>
</body>

</html>