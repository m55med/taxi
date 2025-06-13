<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'سجل الأنشطة') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .table-fixed { table-layout: fixed; }
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/app/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?></h1>
    </div>

    <!-- Filters -->
    <?php include_once __DIR__ . '/partials/filters.php'; ?>

    <!-- Activity Table -->
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
        <?php if (!empty($activities)): ?>
            <?php include_once __DIR__ . '/partials/activity_table.php'; ?>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-search fa-3x text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">لا توجد أنشطة تطابق معايير البحث الحالية.</p>
                <p class="text-gray-400 mt-2">جرّب تعديل الفلاتر أو توسيع النطاق الزمني.</p>
            </div>
        <?php endif; ?>
        
        <!-- Pagination Controls -->
        <?php include_once __DIR__ . '/partials/pagination_controls.php'; ?>
    </div>
</div>

<script>
    // Simple script to clear filters
    document.getElementById('clear-filters-btn').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = '<?= BASE_PATH ?>/logs';
    });
</script>

</body>
</html> 