<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($data['title']) ?></h1>
        
        <form method="POST" class="bg-white p-6 rounded-lg shadow space-y-4">
            <div>
                <label for="table" class="block font-medium">1. اختر الجدول الرئيسي:</label>
                <select id="table" name="table" class="w-full mt-1 p-2 border rounded-md">
                    <option value="">-- اختر --</option>
                    <?php foreach($data['tables'] as $table): ?>
                        <option value="<?= $table ?>"><?= ucfirst($table) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="columns-container" class="hidden">
                <label class="block font-medium">2. اختر الأعمدة لعرضها:</label>
                <div id="columns-checkboxes" class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-1"></div>
            </div>

            <div id="filters-container" class="hidden">
                <label class="block font-medium">3. أضف فلاتر (اختياري):</label>
                <div id="filters-list" class="space-y-2 mt-1"></div>
                <button type="button" id="add-filter" class="mt-2 bg-gray-200 text-gray-700 px-3 py-1 rounded-md">إضافة فلتر</button>
            </div>

            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md">إنشاء التقرير</button>
        </form>

        <?php if (!empty($data['reportData'])): ?>
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">نتائج التقرير</h2>
                <div class="mb-4 p-2 bg-gray-100 text-gray-600 font-mono text-sm rounded-md">
                    <strong>Query:</strong> <?= htmlspecialchars($data['queryDetails']) ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php foreach (array_keys($data['reportData'][0]) as $header): ?>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?= htmlspecialchars($header) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($data['reportData'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td class="px-6 py-4"><?= htmlspecialchars($cell) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif(!empty($data['queryDetails'])): ?>
             <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <p><?= htmlspecialchars($data['queryDetails']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('table').addEventListener('change', function() {
            const tableName = this.value;
            const columnsContainer = document.getElementById('columns-container');
            const filtersContainer = document.getElementById('filters-container');
            const columnsCheckboxes = document.getElementById('columns-checkboxes');
            
            columnsCheckboxes.innerHTML = '';
            document.getElementById('filters-list').innerHTML = '';

            if (tableName) {
                fetch(`<?= BASE_PATH ?>/reports/custom/getColumns?table=${tableName}`)
                    .then(response => response.json())
                    .then(columns => {
                        columns.forEach(column => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center';
                            div.innerHTML = `<input type="checkbox" id="col-${column}" name="columns[]" value="${column}" class="mr-2"><label for="col-${column}">${column}</label>`;
                            columnsCheckboxes.appendChild(div);
                        });
                        columnsContainer.classList.remove('hidden');
                        filtersContainer.classList.remove('hidden');
                    });
            } else {
                columnsContainer.classList.add('hidden');
                filtersContainer.classList.add('hidden');
            }
        });

        let filterCount = 0;
        document.getElementById('add-filter').addEventListener('click', function() {
            const list = document.getElementById('filters-list');
            const newFilter = document.createElement('div');
            newFilter.className = 'flex items-center gap-2';

            const columnSelect = document.createElement('select');
            columnSelect.name = `filters[${filterCount}][column]`;
            columnSelect.className = 'p-2 border rounded-md';
            // Populate with columns
            const currentColumns = Array.from(document.querySelectorAll('#columns-checkboxes input')).map(cb => cb.value);
            currentColumns.forEach(col => {
                const option = document.createElement('option');
                option.value = col;
                option.textContent = col;
                columnSelect.appendChild(option);
            });

            newFilter.appendChild(columnSelect);
            newFilter.innerHTML += `<input type="text" name="filters[${filterCount}][value]" placeholder="القيمة" class="p-2 border rounded-md">`;
            
            list.appendChild(newFilter);
            filterCount++;
        });
    </script>
</body>
</html> 