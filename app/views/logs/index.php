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
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/views/includes/nav.php'; ?>

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
                <p class="text-gray-500 text-lg">No activities found matching your criteria.</p>
                <p class="text-gray-400 mt-2">Try adjusting the filters or expanding the date range.</p>
            </div>
        <?php endif; ?>
        
        <!-- Pagination Controls -->
        <?php if (!empty($activities)) include_once __DIR__ . '/partials/pagination_controls.php'; ?>
    </div>
</div>

<script>
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