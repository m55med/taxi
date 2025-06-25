<?php include_once __DIR__ . '/../includes/header.php'; ?>
<link href="<?= BASE_PATH ?>/app/views/calls/css/styles.css?v=1.1" rel="stylesheet">
<style>
    /* Custom styles for this page */
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>
<script> const BASE_PATH = '<?= BASE_PATH ?>'; </script>

<div class="container mx-auto px-4 py-8">
    <!-- Notification Placeholder -->
    <div id="toast-container" class="fixed top-5 right-5 z-[100] space-y-2"></div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Call Center</h1>
        
        <!-- Search Form -->
        <form action="<?= BASE_PATH ?>/calls" method="GET" class="flex-grow md:max-w-xs">
            <div class="relative">
                <input type="search" name="phone" placeholder="Search by phone number..."
                       class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <h3 class="text-sm font-semibold text-gray-500 mb-1">Today's Calls</h3>
                <p class="text-2xl font-bold text-indigo-600"><?= $data['today_calls_count'] ?? 0 ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <h3 class="text-sm font-semibold text-gray-500 mb-1">In Queue</h3>
                <p class="text-2xl font-bold text-indigo-600"><?= $data['total_pending_calls'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Conditional Content: Rate Limit / No Driver / Main Content -->
    <?php if (isset($data['rate_limit_exceeded']) && $data['rate_limit_exceeded'] === true): ?>
        <div id="rate-limit-alert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md text-center">
            <p class="font-bold text-lg mb-2">You have exceeded the call limit</p>
            <p>Please wait for <span id="countdown" class="font-bold text-xl"><?= $data['wait_time'] ?? 0 ?></span> seconds.</p>
        </div>
    <?php elseif (empty($data['driver'])): ?>
        <div class="text-center py-20 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">No Drivers in Queue</h2>
            <p class="text-gray-600">The next available driver will be shown here automatically.</p>
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
                        <nav class="flex space-x-2" aria-label="Tabs">
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-indigo-500 text-indigo-600" data-tab="call-form">
                                <i class="fas fa-phone-alt mr-2"></i>
                                <span>Call Details</span>
                            </button>
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" data-tab="call-history">
                                <i class="fas fa-history mr-2"></i>
                                <span>Call History</span>
                            </button>
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" data-tab="documents">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span>Documents</span>
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
<script src="<?= BASE_PATH ?>/public/js/utils.js?v=1.1"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/shared.js?v=1.1"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/driver-profile.js?v=1.2"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/driver-info.js?v=1.4"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/documents.js?v=1.1"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/transfer.js?v=1.1"></script>
<script src="<?= BASE_PATH ?>/app/views/calls/js/call-form.js?v=1.2"></script>
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
            // Use an empty body as we just need to trigger the server-side script
            navigator.sendBeacon('<?= BASE_PATH ?>/calls/releaseHold', new Blob());
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>