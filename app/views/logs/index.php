

    <?php include_once APPROOT . '/views/includes/header.php'; ?>
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?></h1>
            <button onclick="history.back()"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>

        <!-- Summary -->
        <?php if (isset($activitiesSummary))
            include_once __DIR__ . '/partials/summary.php'; ?>
        <!-- Filters -->
        <?php include_once __DIR__ . '/partials/filters.php'; ?>

        <!-- Activity Table -->
        <form id="bulkActionForm" action="<?= URLROOT ?>/logs/bulk_export" method="POST">
            <input type="hidden" name="export_type" id="export_type_input">
            <div id="bulk-action-hidden-inputs">
                <!-- Hidden inputs for selected activities will be injected here -->
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md mt-6">

                <!-- Table Header Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 pb-4 border-b border-gray-200 gap-4">
                    <h2 class="text-xl font-semibold text-gray-800 flex-shrink-0">Activity List</h2>

                    <!-- Bulk Actions Dropdown -->
                    <div id="bulk-actions-dropdown" class="hidden relative flex-shrink-0 ml-auto sm:ml-0">
                        <button id="bulk-actions-button" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Actions <span id="selected-activities-count" class="ml-1">(0)</span>
                            <i id="bulk-actions-chevron" class="fas fa-chevron-down -ml-1 ml-2 h-5 w-5 transition-transform"></i>
                        </button>
                        <div id="bulk-actions-menu"
                            class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                            <div class="py-1">
                                <button type="button" id="copy-ticket-numbers-btn"
                                    class="js-copy-ticket-numbers w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <i class="fas fa-copy text-purple-500 w-5 text-center mr-2"></i> Copy Ticket
                                    Numbers
                                </button>
                                <button type="button" data-format="excel"
                                    class="js-export-selected w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <i class="fas fa-file-excel text-green-500 w-5 text-center mr-2"></i> Export
                                    Selected (Excel)
                                </button>
                                <button type="button" data-format="json"
                                    class="js-export-selected w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <i class="fas fa-file-code text-blue-500 w-5 text-center mr-2"></i> Export Selected
                                    (JSON)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($activities)): ?>
                    <?php include_once __DIR__ . '/partials/activity_table.php'; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-search fa-3x text-gray-400 mb-4"></i>
                        <p class="text-gray-500 text-lg">No activities found matching your criteria.</p>
                        <p class="text-gray-400 mt-2">Try adjusting the filters or expanding the date range.</p>
                    </div>
                <?php endif; ?>

                <!-- Pagination Controls -->
                <?php if (!empty($activities))
                    include_once __DIR__ . '/partials/pagination_controls.php'; ?>
            </div>
        </form>
    </div>

    <!-- Export Modal -->
    <div id="export-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div id="export-modal-content" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-800">Export All Activities</h3>
                <button id="export-modal-close-btn" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-6">Data will be exported based on the currently applied filters.</p>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Choose export format:</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" data-format="excel"
                        class="export-format-btn w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                        <i class="fas fa-file-excel text-3xl mb-2 text-green-500"></i>
                        <span class="font-semibold text-sm">Excel</span>
                    </button>
                    <button type="button" data-format="json"
                        class="export-format-btn w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                        <i class="fas fa-file-code text-3xl mb-2 text-blue-500"></i>
                        <span class="font-semibold text-sm">JSON</span>
                    </button>
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-4 border-t">
                <button type="button" id="export-modal-cancel-btn"
                    class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-semibold text-sm">Cancel</button>
                <button type="button" id="export-modal-submit-btn"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold text-sm flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export Now
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // STATE
        let selectedActivities = [];
        let exportType = 'excel';

        // ELEMENTS
        const exportModal = document.getElementById('export-modal');
        const exportModalContent = document.getElementById('export-modal-content');
        const openExportModalBtn = document.getElementById('open-export-modal-btn');
        const closeExportModalBtn = document.getElementById('export-modal-close-btn');
        const cancelExportModalBtn = document.getElementById('export-modal-cancel-btn');
        const submitExportModalBtn = document.getElementById('export-modal-submit-btn');
        const exportFormatBtns = document.querySelectorAll('.export-format-btn');
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const activityCheckboxes = document.querySelectorAll('.activity-checkbox');
        const bulkActionForm = document.getElementById('bulkActionForm');
        const hiddenInputsContainer = document.getElementById('bulk-action-hidden-inputs');
        const exportTypeInput = document.getElementById('export_type_input');
        const bulkActionsDropdown = document.getElementById('bulk-actions-dropdown');
        const bulkActionsButton = document.getElementById('bulk-actions-button');
        const bulkActionsMenu = document.getElementById('bulk-actions-menu');
        const selectedCountSpan = document.getElementById('selected-activities-count');
        const exportSelectedBtns = document.querySelectorAll('.js-export-selected');
        const copyTicketNumbersBtn = document.getElementById('copy-ticket-numbers-btn');

        // --- LIVE SEARCH/FILTER LOGIC ---
        const globalSearchInput = document.getElementById('search');
        const activityTableBody = document.querySelector('table tbody');

        if (globalSearchInput && activityTableBody) {
            let debounceTimer;

            const filterTable = () => {
                const searchTerm = globalSearchInput.value.toLowerCase().trim();
                const rows = activityTableBody.querySelectorAll('tr');

                rows.forEach(row => {
                    const detailsCell = row.cells[2];
                    const employeeCell = row.cells[3];
                    const teamCell = row.cells[4];

                    if (detailsCell && employeeCell && teamCell) {
                        const rowText = (
                            detailsCell.textContent + ' ' +
                            employeeCell.textContent + ' ' +
                            teamCell.textContent
                        ).toLowerCase();

                        if (rowText.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            };

            globalSearchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(filterTable, 300); // 300ms debounce
            });
        }

        // MODAL LOGIC
        function openModal() { if (exportModal) exportModal.classList.remove('hidden'); }
        function closeModal() { if (exportModal) exportModal.classList.add('hidden'); }

        if (openExportModalBtn) openExportModalBtn.addEventListener('click', openModal);
        if (closeExportModalBtn) closeExportModalBtn.addEventListener('click', closeModal);
        if (cancelExportModalBtn) cancelExportModalBtn.addEventListener('click', closeModal);
        if (exportModal) {
            exportModal.addEventListener('click', (e) => {
                if (e.target === exportModal) closeModal();
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && exportModal && !exportModal.classList.contains('hidden')) {
                closeModal();
            }
        });
        
        // EXPORT LOGIC
        function updateExportButtonStyles() {
            exportFormatBtns.forEach(btn => {
                const format = btn.dataset.format;
                const icon = btn.querySelector('i');
                if (format === exportType) {
                    btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-lg');
                    btn.classList.remove('bg-white', 'hover:bg-gray-50', 'border-gray-300');
                    icon.classList.add('text-white');
                    icon.classList.remove('text-green-500', 'text-blue-500');
                } else {
                    btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-lg');
                    btn.classList.add('bg-white', 'hover:bg-gray-50', 'border-gray-300');
                    icon.classList.remove('text-white');
                    if (format === 'excel') icon.classList.add('text-green-500');
                    if (format === 'json') icon.classList.add('text-blue-500');
                }
            });
        }
        updateExportButtonStyles();

        exportFormatBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                exportType = btn.dataset.format;
                updateExportButtonStyles();
            });
        });

        if (submitExportModalBtn) {
            submitExportModalBtn.addEventListener('click', () => {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('export');
                url.searchParams.set('export', exportType);
                        window.location.href = url.toString();
                closeModal();
            });
        }

        // BULK/SELECTED ACTIONS LOGIC
        function updateSelectedActivities() {
            selectedActivities = Array.from(activityCheckboxes)
                                    .filter(cb => cb.checked)
                                    .map(cb => cb.value);
            updateBulkUI();
        }

        function updateBulkUI() {
            const count = selectedActivities.length;
            if (count > 0) {
                if (bulkActionsDropdown) bulkActionsDropdown.classList.remove('hidden');
                if (selectedCountSpan) selectedCountSpan.textContent = `(${count})`;
            } else {
                if (bulkActionsDropdown) bulkActionsDropdown.classList.add('hidden');
            }
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count > 0 && count === activityCheckboxes.length;
            }
            
            if (hiddenInputsContainer) {
                hiddenInputsContainer.innerHTML = '';
                selectedActivities.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'activity_ids[]';
                    input.value = id;
                    hiddenInputsContainer.appendChild(input);
                });
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                activityCheckboxes.forEach(checkbox => checkbox.checked = e.target.checked);
                updateSelectedActivities();
            });
        }

        activityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedActivities);
        });

        if (bulkActionsButton) {
            bulkActionsButton.addEventListener('click', (e) => {
                e.stopPropagation();
                if (bulkActionsMenu) bulkActionsMenu.classList.toggle('hidden');
            });
        }
        document.addEventListener('click', (e) => {
            if (bulkActionsDropdown && !bulkActionsDropdown.contains(e.target)) {
                if (bulkActionsMenu) bulkActionsMenu.classList.add('hidden');
            }
        });
        
        exportSelectedBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (selectedActivities.length === 0) {
                    alert('Please select at least one activity to export.');
                    return;
                }
                if (exportTypeInput) exportTypeInput.value = btn.dataset.format;
                if (bulkActionForm) bulkActionForm.submit();
            });
        });

        // COPY TICKET NUMBERS LOGIC
        if (copyTicketNumbersBtn) {
            copyTicketNumbersBtn.addEventListener('click', () => {
                if (selectedActivities.length === 0) {
                    alert('Please select at least one activity to copy ticket numbers.');
                    return;
                }

                // جمع أرقام التذاكر من العناصر المحددة
                const ticketNumbers = [];
                activityCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        // الحصول على الصف الخاص بالـ checkbox
                        const row = checkbox.closest('tr');
                        if (row) {
                            // الحصول على خلية التفاصيل (Details)
                            const detailsCell = row.cells[2]; // الخلية الثالثة (index 2)
                            if (detailsCell) {
                                // الحصول على النص من الرابط الأول في الخلية
                                const link = detailsCell.querySelector('a');
                                if (link) {
                                    // استخراج رقم التذكرة من النص (أول جزء قبل أي فراغ أو شرطة)
                                    const ticketText = link.textContent.trim();
                                    // استخراج رقم التذكرة من النص
                                    const ticketMatch = ticketText.match(/^([A-Za-z0-9\-]+)/);
                                    if (ticketMatch) {
                                        ticketNumbers.push(ticketMatch[1]);
                                    }
                                }
                            }
                        }
                    }
                });

                if (ticketNumbers.length > 0) {
                    // نسخ الأرقام إلى الحافظة
                    const ticketNumbersText = ticketNumbers.join('\n');
                    navigator.clipboard.writeText(ticketNumbersText).then(() => {
                        // إظهار رسالة تأكيد
                        showNotification(`Copied ${ticketNumbers.length} ticket number(s) to clipboard!`, 'success');

                        // إغلاق القائمة
                        if (bulkActionsMenu) bulkActionsMenu.classList.add('hidden');
                    }).catch(err => {
                        console.error('Failed to copy ticket numbers:', err);
                        showNotification('Failed to copy ticket numbers to clipboard.', 'error');
                    });
                } else {
                    alert('No ticket numbers found in selected activities.');
                }
            });
        }

        // DATE FILTERS
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
        const filtersForm = document.getElementById('filters-form');

            function setAndSubmit(start, end) {
            if (dateFrom) dateFrom.value = start;
            if (dateTo) dateTo.value = end;
            if (filtersForm) filtersForm.submit();
        }

        const todayBtn = document.getElementById('today-btn');
        if (todayBtn) {
            todayBtn.addEventListener('click', function () {
                const today = new Date().toISOString().slice(0, 10);
                setAndSubmit(today, today);
            });
        }

        const weekBtn = document.getElementById('week-btn');
        if (weekBtn) {
            weekBtn.addEventListener('click', function () {
                const today = new Date();
                const dayOfWeek = today.getDay();
                const firstDayOfWeek = new Date(today.setDate(today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1))); // Monday as first day
                const lastDayOfWeek = new Date(firstDayOfWeek);
                lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);
                setAndSubmit(firstDayOfWeek.toISOString().slice(0, 10), lastDayOfWeek.toISOString().slice(0, 10));
            });
        }

        const monthBtn = document.getElementById('month-btn');
        if (monthBtn) {
            monthBtn.addEventListener('click', function () {
                const today = new Date();
                const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
                const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10);
                setAndSubmit(firstDayOfMonth, lastDayOfMonth);
            });
        }

        // NOTIFICATION FUNCTION
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            notification.textContent = message;
            document.body.appendChild(notification);

            // إزالة الإشعار بعد 3 ثوانٍ
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        });
    </script>
    <?php include_once APPROOT . '/views/includes/footer.php'; ?>

</body>

</html>