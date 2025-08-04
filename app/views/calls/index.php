<?php include_once __DIR__ . '/../includes/header.php'; ?>

<link href="<?= URLROOT ?>/css/calls/styles.css?v=1.1" rel="stylesheet">
<style>
    /* Custom styles for this page */
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>
<script>
    const URLROOT = "<?= URLROOT ?>";

    // --- Shared Utility Functions ---
    
    /**
     * Displays a toast notification.
     * @param {string} message The message to display.
     * @param {string} type 'success', 'error', or 'info'.
     */
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        toast.className = `p-4 rounded-lg shadow-lg text-white ${bgColor} flex items-center`;
        toast.innerHTML = `<i class="fas ${icon} mr-3"></i> <p>${message}</p>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    /**
     * Copies text to the clipboard.
     * @param {string} text The text to copy.
     * @param {string} entityName The name of the item being copied (e.g., 'Phone number').
     */
    function copyToClipboard(text, entityName = 'Text') {
        if (!navigator.clipboard) {
            showToast('Clipboard API not available.', 'error');
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            showToast(`${entityName} copied to clipboard!`);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showToast('Failed to copy to clipboard.', 'error');
        });
    }
</script>

<div class="container mx-auto px-4 py-8">
    <!-- Notification Placeholder -->
    <div id="toast-container" class="fixed top-5 right-5 z-[100] space-y-2"></div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Call Center</h1>
        <form action="<?= URLROOT ?>/calls" method="GET" class="flex-grow md:max-w-xs" onsubmit="return false;">
    <div class="relative">
        <input type="search" name="phone" id="phone-search" placeholder="Search by phone number..."
            class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            autocomplete="off"
            value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>

        <!-- Autocomplete Dropdown -->
        <div id="search-suggestions" class="absolute z-50 bg-white border border-gray-300 mt-1 w-full rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
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
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <?php include __DIR__ . '/sections/driver-profile.php'; ?>
                <?php include __DIR__ . '/sections/driver-info-form.php'; ?>
            </div>
            <div class="col-span-12 lg:col-span-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-2" aria-label="Tabs">
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-indigo-500 text-indigo-600" data-tab="call-form">
                                <i class="fas fa-phone-alt mr-2"></i> Call Details
                            </button>
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="call-history">
                                <i class="fas fa-history mr-2"></i> Call History
                                <?php if (!empty($data['call_history'])): ?>
                                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"><?= count($data['call_history']) ?></span>
                                <?php endif; ?>
                            </button>
                            <button class="tab-button whitespace-nowrap flex-1 py-4 px-1 text-center border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="documents">
                                <i class="fas fa-file-alt mr-2"></i> Documents
                                <?php if (!empty($data['required_documents'])): ?>
                                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"><?= count($data['required_documents']) ?></span>
                                <?php endif; ?>
                            </button>
                        </nav>
                    </div>
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

<!-- Page-specific logic -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tab switching logic
    const tabs = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const inactiveClasses = ['border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'];
    const activeClasses = ['border-indigo-500', 'text-indigo-600'];

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.remove(...activeClasses);
                t.classList.add(...inactiveClasses);
            });
            tab.classList.add(...activeClasses);
            tab.classList.remove(...inactiveClasses);

            tabContents.forEach(c => {
                c.classList.remove('active');
            });
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

    // Reliable beacon to release hold on driver
    window.addEventListener('beforeunload', function () {
        if (navigator.sendBeacon) {
            navigator.sendBeacon('<?= URLROOT ?>/calls/releaseHold', new Blob());
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('phone-search');
    const suggestionsBox = document.getElementById('search-suggestions');
    let debounceTimer;

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        clearTimeout(debounceTimer);
        suggestionsBox.innerHTML = '';
        suggestionsBox.classList.add('hidden');

        if (query.length < 3) return;

        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${URLROOT}/drivers/search?q=${encodeURIComponent(query)}`);
                if (!response.ok) return;
                const results = await response.json();

                if (!results.length) return;

                results.forEach(driver => {
                    const item = document.createElement('div');
                    item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                    item.textContent = `${driver.name} - ${driver.phone}`;
                    item.addEventListener('click', () => {
                        // Redirect to /calls?phone=xxxx
                        window.location.href = `${URLROOT}/calls?phone=${encodeURIComponent(driver.phone)}`;
                    });
                    suggestionsBox.appendChild(item);
                });

                suggestionsBox.classList.remove('hidden');
            } catch (err) {
                console.error('Error fetching suggestions:', err);
            }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('hidden');
        }
    });
});
</script>


<?php include_once __DIR__ . '/../includes/footer.php'; ?>
