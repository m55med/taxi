<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز الاتصال</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_PATH ?>/app/views/call/css/styles.css" rel="stylesheet">
    <style>
        /* Additional styles for active/inactive tabs */
        .tab-button.active {
            border-bottom-color: #4f46e5; /* indigo-600 */
            color: #4f46e5;
            font-weight: 700;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
    <script> const BASE_PATH = '<?= BASE_PATH ?>'; </script>
</head>

<body class="bg-gray-100 font-sans">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Notification Placeholder -->
        <div id="notification-toast" class="fixed top-5 right-5 z-[100]"></div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">مركز الاتصال</h1>
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

        <!-- Conditional Content: Rate Limit / No Driver / Main Content -->
        <?php if (isset($rate_limit_exceeded) && $rate_limit_exceeded === true): ?>
            <div id="rate-limit-alert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md text-center">
                <p class="font-bold text-lg mb-2">لقد تجاوزت الحد الأقصى للمكالمات</p>
                <p>يرجى الانتظار لمدة <span id="countdown" class="font-bold text-xl"><?= $wait_time ?? 0 ?></span> ثانية.</p>
            </div>
        <?php elseif (empty($driver)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-info-circle text-4xl mb-3"></i>
                <p class="font-bold text-lg">لا يوجد سائقين في قائمة الانتظار حالياً</p>
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
                            <nav class="-mb-px flex space-x-4 space-x-reverse px-6" aria-label="Tabs">
                                <button class="tab-button active" data-tab="call-form">
                                    <i class="fas fa-phone-alt ml-2"></i> تفاصيل المكالمة
                                </button>
                                <button class="tab-button" data-tab="call-history">
                                    <i class="fas fa-history ml-2"></i> سجل المكالمات
                                </button>
                                <button class="tab-button" data-tab="documents">
                                    <i class="fas fa-file-alt ml-2"></i> المستندات
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
    <script src="<?= BASE_PATH ?>/app/views/call/js/shared.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/call/js/driver-profile.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/call/js/driver-info.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/call/js/documents.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/call/js/transfer.js"></script>
    <script src="<?= BASE_PATH ?>/app/views/call/js/call-form.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching logic
            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs and content
                    tabs.forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    // Activate clicked tab and its content
                    tab.classList.add('active');
                    document.getElementById(tab.dataset.tab + '-content').classList.add('active');
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
                navigator.sendBeacon('<?= BASE_PATH ?>/call/releaseHold');
            }
        });
    </script>
</body>

</html>