<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- External Libraries CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>

<style>
    /* Custom Styles for Wizard and Libraries */
    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background-color: #e5e7eb; /* gray-200 */
        color: #6b7280; /* gray-500 */
        font-weight: bold;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .step-indicator.active {
        background-color: #e0e7ff; /* indigo-100 */
        color: #4f46e5; /* indigo-600 */
        border-color: #4f46e5; /* indigo-600 */
    }
    .step-indicator.completed {
        background-color: #16a34a; /* green-600 */
        color: white;
    }
    .step-connector {
        flex-grow: 1;
        height: 2px;
        background-color: #e5e7eb; /* gray-200 */
    }
    .step-connector.completed {
        background-color: #16a34a; /* green-600 */
    }
    /* Select2 Customization */
    .select2-container .select2-selection--multiple {
        min-height: 42px;
        border-color: #D1D5DB; /* gray-300 */
        border-radius: 0.375rem; /* rounded-md */
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4f46e5; /* indigo-600 */
        color: white;
        border: none;
        border-radius: 0.25rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #6366f1; /* indigo-500 */
    }
    .step-card {
        background-color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        transition: all 0.3s ease-in-out;
    }
</style>

<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($data['title']) ?></h1>
        <p class="text-gray-600">اتبع الخطوات التالية لبناء تقريرك المخصص.</p>
    </div>

    <!-- Step Indicator Bar -->
    <div class="flex items-center w-full max-w-2xl mx-auto mb-12">
        <div id="step-indicator-1" class="step-indicator active">1</div>
        <div id="step-connector-1" class="step-connector"></div>
        <div id="step-indicator-2" class="step-indicator">2</div>
        <div id="step-connector-2" class="step-connector"></div>
        <div id="step-indicator-3" class="step-indicator">3</div>
    </div>

    <!-- The Form -->
    <form id="report-form" class="space-y-4">
        <input type="hidden" name="export_type" id="export_type" value="">

        <!-- Step 1: Select Tables -->
        <div id="step-1" class="step-card">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-700">الخطوة 1: اختر الجداول</h2>
                <p class="text-sm text-gray-500 mt-1">اختر جدولاً أو أكثر من القائمة. إذا اخترت أكثر من جدول، ستحتاج إلى تحديد علاقات الربط في الخطوة التالية.</p>
            </div>
            <div class="mb-4">
                <label for="tables" class="block font-medium sr-only">الجداول</label>
                <select id="tables" name="tables[]" class="w-full" multiple>
                    <?php 
                    $selectedTables = $_POST['tables'] ?? [];
                    foreach($data['tables'] as $table): ?>
                        <option value="<?= $table ?>" <?= (in_array($table, $selectedTables)) ? 'selected' : '' ?>><?= ucfirst($table) ?></option>
                    <?php endforeach; ?>
                </select>
                 <p id="table-selection-error" class="text-red-500 text-sm mt-1 hidden">الرجاء اختيار جدول واحد على الأقل.</p>
            </div>
            <div class="flex justify-end">
                <button type="button" id="next-to-step-2" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">التالي <i class="ri-arrow-left-line align-middle"></i></button>
            </div>
        </div>

        <!-- Step 2: Configure Columns, Joins, and Filters -->
        <div id="step-2" class="step-card hidden">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">الخطوة 2: اختر الأعمدة والفلاتر</h2>
            
            <!-- Columns Selection -->
            <div id="columns-container" class="mb-6">
                <label class="block font-medium text-gray-800">أ. اختر الأعمدة لعرضها:</label>
                <div id="columns-checkboxes" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2 p-4 bg-gray-50 rounded-md max-h-64 overflow-y-auto"></div>
                 <p id="column-selection-error" class="text-red-500 text-sm mt-1 hidden">الرجاء اختيار عمود واحد على الأقل.</p>
            </div>

            <!-- Joins -->
            <div id="joins-container" class="hidden mb-6">
                <label class="block font-medium text-gray-800">ب. حدد علاقات الربط (JOINs):</label>
                 <p class="text-sm text-gray-500 mt-1 mb-2">بما أنك اخترت أكثر من جدول، يجب تحديد كيفية ربطها معًا.</p>
                <div id="joins-list" class="space-y-4"></div>
                <button type="button" id="add-join-btn" class="mt-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300"><i class="ri-add-line align-middle"></i> إضافة علاقة ربط</button>
            </div>

            <!-- Filters -->
            <div id="filters-container" class="mb-6">
                <label class="block font-medium text-gray-800">ج. أضف فلاتر (اختياري):</label>
                <div id="filters-list" class="space-y-2 mt-2"></div>
                <button type="button" id="add-filter" class="mt-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300"><i class="ri-filter-3-line align-middle"></i> إضافة فلتر</button>
            </div>

            <div class="flex items-center justify-between border-t pt-4">
                <button type="button" id="back-to-step-1" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600"><i class="ri-arrow-right-line align-middle"></i> رجوع</button>
                <button type="button" id="preview-report" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">عرض التقرير <i class="ri-eye-line align-middle"></i></button>
            </div>
        </div>

        <!-- Step 3: Preview and Export -->
        <div id="step-3" class="step-card hidden">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">الخطوة 3: المعاينة والتصدير</h2>
            
            <!-- SQL Query Display -->
            <div id="query-details-container" class="mb-4 hidden">
                <h3 class="font-semibold text-gray-600 mb-2">ملخص الاستعلام (SQL Query):</h3>
                <div class="p-3 bg-gray-100 text-gray-800 font-mono text-sm rounded-md overflow-x-auto">
                    <code id="query-details"></code>
                </div>
            </div>

             <!-- Report Results -->
            <div id="report-results-container" class="overflow-x-auto hidden">
                <table id="report-table" class="display w-full" style="width:100%"></table>
            </div>
            <div id="no-results-message" class="text-center py-8 text-gray-500 hidden">
                <i class="ri-table-line text-4xl mb-2"></i>
                <p>لا توجد بيانات تطابق الفلاتر المحددة.</p>
            </div>

            <div class="flex items-center justify-between border-t pt-4 mt-6">
                <button type="button" id="back-to-step-2" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600"><i class="ri-arrow-right-line align-middle"></i> رجوع</button>
                <div id="export-buttons" class="hidden">
                    <button type="button" data-export="excel" class="export-btn bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"><i class="ri-file-excel-2-line align-middle"></i> Excel</button>
                    <button type="button" data-export="csv" class="export-btn bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"><i class="ri-file-text-line align-middle"></i> CSV</button>
                    <button type="button" data-export="pdf" class="export-btn bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"><i class="ri-file-pdf-line align-middle"></i> PDF</button>
                    <button type="button" data-export="json" class="export-btn bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600"><i class="ri-code-s-slash-line align-middle"></i> JSON</button>
                </div>
            </div>
        </div>
        </form>
</div>

<!-- External Libraries JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // --- State Management ---
    let currentStep = 1;
    let availableColumns = {};
    let joinCount = 0;
    let filterCount = 0;
    let reportDataTable = null;
    const initialSelectedColumns = <?= json_encode($_POST['columns'] ?? []) ?>;

    // --- DOM Elements ---
    const steps = {
        1: $('#step-1'),
        2: $('#step-2'),
        3: $('#step-3')
    };
    const indicators = {
        1: $('#step-indicator-1'),
        2: $('#step-indicator-2'),
        3: $('#step-indicator-3')
    };
    const connectors = {
        1: $('#step-connector-1'),
        2: $('#step-connector-2')
    };
    
    // --- Initialization ---
    $('#tables').select2({
        placeholder: 'ابحث واختر الجداول',
        dir: 'rtl'
    });
    
    // --- Functions ---
    const goToStep = (step) => {
        $(`.step-card`).addClass('hidden');
        steps[step].removeClass('hidden');

        $('.step-indicator').removeClass('active completed');
        $('.step-connector').removeClass('completed');

        for (let i = 1; i <= 3; i++) {
            if (i < step) {
                indicators[i].addClass('completed');
                if(connectors[i]) connectors[i].addClass('completed');
            } else if (i === step) {
                indicators[i].addClass('active');
            }
        }
        currentStep = step;
    };

    const fetchColumnsForTables = (tableNames) => {
        if (tableNames.length === 0) {
            $('#columns-container').addClass('hidden');
            $('#filters-container').addClass('hidden');
            $('#joins-container').addClass('hidden');
            return;
        }

        const query = tableNames.map(t => `tables[]=${encodeURIComponent(t)}`).join('&');
        $.ajax({
            url: `<?= URLROOT ?>/reports/custom/getColumns?${query}`,
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                availableColumns = data;
                populateColumnsCheckboxes();
                populateFilters();
                $('#columns-container, #filters-container').removeClass('hidden');
                
                if (tableNames.length > 1) {
                    $('#joins-container').removeClass('hidden');
                } else {
                    $('#joins-container').addClass('hidden');
                    $('#joins-list').html('');
                    joinCount = 0;
                }
            },
            error: () => {
                 Swal.fire('خطأ', 'فشل في جلب الأعمدة. يرجى المحاولة مرة أخرى.', 'error');
            }
        });
    };
    
    const populateColumnsCheckboxes = () => {
        const container = $('#columns-checkboxes').html('');
        for (const table in availableColumns) {
            const tableHeader = $(`<h4 class="col-span-full font-semibold mt-2 text-gray-700">الجدول: ${table}</h4>`);
            container.append(tableHeader);
            availableColumns[table].forEach(column => {
                const qualifiedName = `${table}.${column}`;
                const isChecked = initialSelectedColumns.includes(qualifiedName) ? 'checked' : '';
                const checkboxDiv = $(`
                    <div class="flex items-center">
                        <input type="checkbox" id="col-${qualifiedName}" name="columns[]" value="${qualifiedName}" ${isChecked} class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="col-${qualifiedName}" class="ml-2 block text-sm text-gray-900">${column}</label>
                    </div>
                `);
                container.append(checkboxDiv);
            });
        }
    };
    
    const populateFilters = () => {
        $('#filters-list').html('');
        filterCount = 0;
    };
    
    const addJoin = () => {
        const selectedTables = $('#tables').val();
        if (selectedTables.length < 2) return;

        const joinId = joinCount++;
        const newJoin = $(`
            <div id="join-${joinId}" class="p-4 border rounded-md bg-gray-50 space-y-3">
                <div class="flex items-center justify-between">
                     <h4 class="font-semibold text-gray-600">علاقة ربط #${joinId + 1}</h4>
                     <button type="button" class="remove-join text-red-500 hover:text-red-700 font-bold"><i class="ri-close-line"></i></button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-center">
                    <select name="joins[${joinId}][left_table]" class="left-table-select p-2 border rounded-md" required></select>
                    <select name="joins[${joinId}][left_column]" class="left-column-select p-2 border rounded-md" required></select>
                    <select name="joins[${joinId}][type]" class="p-2 border rounded-md">
                        <option value="INNER">INNER JOIN</option>
                        <option value="LEFT">LEFT JOIN</option>
                        <option value="RIGHT">RIGHT JOIN</option>
                    </select>
                    <select name="joins[${joinId}][right_table]" class="right-table-select p-2 border rounded-md" required></select>
                    <select name="joins[${joinId}][right_column]" class="right-column-select p-2 border rounded-md" required></select>
                </div>
            </div>
        `);
        
        $('#joins-list').append(newJoin);

        const leftTableSelect = newJoin.find('.left-table-select');
        const rightTableSelect = newJoin.find('.right-table-select');
        
        selectedTables.forEach(table => {
            leftTableSelect.append(new Option(table, table));
            rightTableSelect.append(new Option(table, table));
        });

        const updateColumns = (tableSelect, columnSelect) => {
            const selectedTable = tableSelect.val();
            columnSelect.html('');
            if (selectedTable && availableColumns[selectedTable]) {
                availableColumns[selectedTable].forEach(col => columnSelect.append(new Option(col, col)));
            }
        };

        leftTableSelect.on('change', () => updateColumns(leftTableSelect, newJoin.find('.left-column-select'))).trigger('change');
        rightTableSelect.on('change', () => updateColumns(rightTableSelect, newJoin.find('.right-column-select'))).trigger('change');
    };

    const addFilter = () => {
        const filterId = filterCount++;
        const newFilter = $(`
            <div class="flex items-center gap-2 p-2 border rounded-md">
                <select name="filters[${filterId}][column]" class="p-2 border rounded-md w-1/3"></select>
                <select name="filters[${filterId}][operator]" class="p-2 border rounded-md w-1/4">
                    <option value="=">يساوي</option> <option value="!=">لا يساوي</option> <option value=">">أكبر من</option>
                    <option value="<">أصغر من</option> <option value=">=">أكبر أو يساوي</option> <option value="<=">أصغر أو يساوي</option>
                    <option value="LIKE">يحتوي على</option> <option value="NOT LIKE">لا يحتوي على</option>
                </select>
                <input type="text" name="filters[${filterId}][value]" placeholder="القيمة" class="p-2 border rounded-md w-1/3">
                <button type="button" class="remove-filter text-red-500 hover:text-red-700 font-bold"><i class="ri-close-line"></i></button>
            </div>
        `);
        
        const columnSelect = newFilter.find('select[name*="[column]"]');
        for (const table in availableColumns) {
             availableColumns[table].forEach(col => {
                columnSelect.append(`<option value="${table}.${col}">${table}.${col}</option>`);
            });
        }
        $('#filters-list').append(newFilter);
    };

    // --- Event Listeners ---
    $('#next-to-step-2').on('click', () => {
        const selectedTables = $('#tables').val();
        if (selectedTables.length === 0) {
            $('#table-selection-error').removeClass('hidden');
            return;
        }
        $('#table-selection-error').addClass('hidden');
        fetchColumnsForTables(selectedTables);
        goToStep(2);
    });

    $('#back-to-step-1').on('click', () => goToStep(1));
    $('#back-to-step-2').on('click', () => goToStep(2));

    $('#add-join-btn').on('click', addJoin);
    $('#add-filter').on('click', addFilter);
    
    $('#joins-list').on('click', '.remove-join', function() { $(this).closest('[id^="join-"]').remove(); });
    $('#filters-list').on('click', '.remove-filter', function() { $(this).parent().remove(); });

    $('#preview-report').on('click', function() {
        const selectedColumns = $('input[name="columns[]"]:checked').length;
        if (selectedColumns === 0) {
            $('#column-selection-error').removeClass('hidden');
            return;
        }
        $('#column-selection-error').addClass('hidden');

        const formData = $('#report-form').serialize();
        
        Swal.fire({
            title: 'جاري إنشاء التقرير...',
            text: 'الرجاء الانتظار.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: `<?= URLROOT ?>/reports/custom`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: (response) => {
                Swal.close();
                goToStep(3);

                if(reportDataTable) {
                    reportDataTable.destroy();
                }
                $('#report-table').empty();

                if (response.error) {
                     Swal.fire('خطأ', response.error, 'error');
                     return;
                }

                if (response.queryDetails) {
                    $('#query-details-container').removeClass('hidden');
                    $('#query-details').text(response.queryDetails);
                }

                if (response.reportData && response.reportData.length > 0) {
                    $('#report-results-container').removeClass('hidden');
                    $('#no-results-message').addClass('hidden');
                    $('#export-buttons').removeClass('hidden');
                    
                    reportDataTable = $('#report-table').DataTable({
                        data: response.reportData,
                        columns: Object.keys(response.reportData[0]).map(key => ({ title: key, data: key })),
                        responsive: true,
                        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' }
                    });
            } else {
                    $('#report-results-container').addClass('hidden');
                    $('#no-results-message').removeClass('hidden');
                    $('#export-buttons').addClass('hidden');
                }
            },
            error: (xhr, status, error) => {
                Swal.fire('خطأ فادح', 'حدث خطأ غير متوقع أثناء إنشاء التقرير. ' + error, 'error');
            }
        });
    });
    
    $('.export-btn').on('click', function() {
        $('#export_type').val($(this).data('export'));
        // We use a traditional form submission for file download
        const form = document.getElementById('report-form');
        const originalAction = form.action;
        const originalTarget = form.target;
        
        form.action = `<?= URLROOT ?>/reports/custom`;
        form.method = 'POST';
        form.target = '_blank'; // Open in a new tab to not interrupt the user's flow
        form.submit();
        
        // Reset form attributes
        form.action = originalAction;
        form.target = originalTarget;
        $('#export_type').val('');
    });

    // Initial load check
    if ($('#tables').val().length > 0) {
        fetchColumnsForTables($('#tables').val());
    }
        });
    </script>

</body>
</html> 
