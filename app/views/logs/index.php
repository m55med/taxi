<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'Activity Log') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100" x-data="logsPage()" x-cloak>
    
<?php include_once APPROOT . '/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?></h1>
    </div>

    <!-- Summary -->
    <?php if (isset($activitiesSummary)) include_once __DIR__ . '/partials/summary.php'; ?>
    <!-- Filters -->
    <?php include_once __DIR__ . '/partials/filters.php'; ?>

    <!-- Activity Table -->
    <form id="bulkActionForm" action="<?= BASE_PATH ?>/logs/bulk_export" method="POST">
        <input type="hidden" name="export_type" id="export_type_input">
        <template x-for="activityId in selectedActivities" :key="activityId">
            <input type="hidden" name="activity_ids[]" :value="activityId">
        </template>

        <div class="bg-white p-6 rounded-lg shadow-md mt-6">
            
            <!-- Table Header Actions -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Activity List</h2>
                
                <!-- Bulk Actions Dropdown -->
                <div x-show="selectedActivities.length > 0" x-cloak class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Actions <span x-text="`(${selectedActivities.length})`" class="ml-1"></span>
                        <i class="fas fa-chevron-down -ml-1 ml-2 h-5 w-5 transition-transform" :class="{'rotate-180': open}"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                        <div class="py-1">
                            <button type="button" @click="submitExport('selected', 'excel')" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-file-excel text-green-500 w-5 text-center mr-2"></i> Export Selected (Excel)
                            </button>
                            <button type="button" @click="submitExport('selected', 'json')" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-file-code text-blue-500 w-5 text-center mr-2"></i> Export Selected (JSON)
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
            <?php if (!empty($activities)) include_once __DIR__ . '/partials/pagination_controls.php'; ?>
        </div>
    </form>
</div>

<!-- Export Modal -->
<div x-show="exportModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="exportModal.open = false">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="exportModal.open = false">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-800">Export All Activities</h3>
            <button @click="exportModal.open = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-sm text-gray-600 mb-6">Data will be exported based on the currently applied filters.</p>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose export format:</label>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="exportType = 'excel'" :class="{ 'bg-indigo-600 text-white border-indigo-600 shadow-lg': exportType === 'excel', 'bg-white hover:bg-gray-50 border-gray-300': exportType !== 'excel' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                    <i class="fas fa-file-excel text-3xl mb-2" :class="exportType === 'excel' ? 'text-white' : 'text-green-500'"></i>
                    <span class="font-semibold text-sm">Excel</span>
                </button>
                <button type="button" @click="exportType = 'json'" :class="{ 'bg-indigo-600 text-white border-indigo-600 shadow-lg': exportType === 'json', 'bg-white hover:bg-gray-50 border-gray-300': exportType !== 'json' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                    <i class="fas fa-file-code text-3xl mb-2" :class="exportType === 'json' ? 'text-white' : 'text-blue-500'"></i>
                    <span class="font-semibold text-sm">JSON</span>
                </button>
            </div>
        </div>
        <div class="flex justify-end space-x-2 pt-4 border-t">
            <button type="button" @click="exportModal.open = false" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-semibold text-sm">Cancel</button>
            <button type="button" @click="submitExport('all')" class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold text-sm flex items-center">
                <i class="fas fa-download mr-2"></i>
                Export Now
            </button>
        </div>
    </div>
</div>

<script>
    function logsPage() {
        return {
            exportModal: { open: false },
            exportType: 'excel',
            selectedActivities: [],
            get allVisibleActivities() { 
                return <?= json_encode(array_map(fn($a) => $a->activity_type . '-' . $a->activity_id, $activities)) ?>;
            },
            
            toggleAll(checked) {
                this.selectedActivities = checked ? this.allVisibleActivities : [];
            },
            
            submitExport(scope, format = 'excel') {
                if (scope === 'all') {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('export');
                    url.searchParams.set('export', this.exportType);
                    window.location.href = url.toString();
                    this.exportModal.open = false;
                } else if (scope === 'selected') {
                    if (this.selectedActivities.length === 0) {
                        alert('Please select at least one activity to export.');
                        return;
                    }
                    const form = document.getElementById('bulkActionForm');
                    document.getElementById('export_type_input').value = format;
                    form.submit();
                }
            }
        }
    }

document.addEventListener('DOMContentLoaded', function () {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const form = document.getElementById('filters-form');

    function setAndSubmit(start, end) {
        dateFrom.value = start;
        dateTo.value = end;
        form.submit();
    }

    document.getElementById('today-btn').addEventListener('click', function() {
        const today = new Date().toISOString().slice(0, 10);
        setAndSubmit(today, today);
    });

    document.getElementById('week-btn').addEventListener('click', function() {
        const today = new Date();
        const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1))); // Monday as first day
        const lastDayOfWeek = new Date(firstDayOfWeek);
        lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);
        
        setAndSubmit(firstDayOfWeek.toISOString().slice(0, 10), lastDayOfWeek.toISOString().slice(0, 10));
    });

    document.getElementById('month-btn').addEventListener('click', function() {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10);
        setAndSubmit(firstDayOfMonth, lastDayOfMonth);
    });
});
</script>
<?php include_once APPROOT . '/views/includes/footer.php'; ?>

</body>
</html> 