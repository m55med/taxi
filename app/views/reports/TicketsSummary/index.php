<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($data['title']) ?></h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- By Status -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">التذاكر حسب الحالة</h2>
                <canvas id="statusChart"></canvas>
            </div>
            <!-- By Priority -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">التذاكر حسب الأولوية</h2>
                <canvas id="priorityChart"></canvas>
            </div>
            <!-- By Category -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">التذاكر حسب الفئة</h2>
                 <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const statusData = <?= json_encode($data['summary']['by_status']) ?>;
        const priorityData = <?= json_encode($data['summary']['by_priority']) ?>;
        const categoryData = <?= json_encode($data['summary']['by_category']) ?>;

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status),
                datasets: [{ data: statusData.map(item => item.count) }]
            }
        });

        new Chart(document.getElementById('priorityChart'), {
            type: 'pie',
            data: {
                labels: priorityData.map(item => item.priority),
                datasets: [{ data: priorityData.map(item => item.count) }]
            }
        });

        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryData.map(item => item.name),
                datasets: [{ label: 'عدد التذاكر', data: categoryData.map(item => item.count) }]
            }
        });
    </script>
</body>
</html> 