<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة معلومات الرحلات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f7fafc; }
        .card { @apply bg-white rounded-xl shadow-lg overflow-hidden; }
        .card-header { @apply p-4 bg-gray-50 border-b border-gray-200; }
        .card-header h3 { @apply text-lg font-bold text-gray-800; }
        .kpi-card { @apply bg-white rounded-xl shadow-md p-5 flex items-center; }
        .kpi-icon { @apply w-12 h-12 flex items-center justify-center rounded-full mr-4; }
        .kpi-info h4 { @apply text-gray-500 font-semibold; }
        .kpi-info p { @apply text-2xl font-bold text-gray-800; }
        .table-responsive { @apply w-full text-sm text-right; }
        .table-responsive th { @apply px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center; }
        .table-responsive td { @apply px-5 py-4 border-b border-gray-200 bg-white; }
    </style>
</head>

<body class="text-gray-700">
    <?php include __DIR__ . '/../includes/nav.php'; ?>
    <?php 
        function format_seconds($seconds) {
            if ($seconds === null || $seconds == 0) return 'N/A';
            $minutes = floor($seconds / 60);
            return $minutes . ' دقيقة';
        }

        $stats = $data['dashboard']['general_stats'];
        $cost = $data['dashboard']['cost_kpis'];
        $time = $data['dashboard']['time_kpis'];
        $suspicious_drivers = $data['dashboard']['driver_kpis'];
        $suspicious_passengers = $data['dashboard']['passenger_kpis'];
    ?>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header & Filters -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">لوحة معلومات تحليل الرحلات</h1>
            <p class="text-gray-600">نظرة شاملة على أداء أسطولك والعمليات.</p>
        </div>
        
        <div class="card mb-8">
            <div class="card-header">
                <h3><i class="fas fa-filter mr-2 text-gray-400"></i> خيارات الفلترة</h3>
            </div>
            <form action="" method="GET" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="driver_query" class="block text-sm font-medium text-gray-700 mb-1">بحث عن سائق</label>
                        <input type="text" name="driver_query" id="driver_query" value="<?= htmlspecialchars($data['filters']['driver_query'] ?? '') ?>" placeholder="اسم السائق أو ID" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="passenger_query" class="block text-sm font-medium text-gray-700 mb-1">بحث عن راكب</label>
                        <input type="text" name="passenger_query" id="passenger_query" value="<?= htmlspecialchars($data['filters']['passenger_query'] ?? '') ?>" placeholder="اسم، هاتف، أو ID" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <a href="<?= BASE_PATH ?>/reports/trips" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300 ml-3 transition">مسح الفلاتر</a>
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-md hover:bg-indigo-700 transition"><i class="fas fa-search mr-1"></i> بحث</button>
                </div>
            </form>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="kpi-card">
                <div class="kpi-icon bg-blue-100 text-blue-600"><i class="fas fa-wallet"></i></div>
                <div class="kpi-info"><h4>إجمالي الإيرادات</h4><p><?= number_format($cost['total_revenue'] ?? 0, 2) ?> <span class="text-sm font-normal">OMR</span></p></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon bg-green-100 text-green-600"><i class="fas fa-route"></i></div>
                <div class="kpi-info"><h4>الرحلات المكتملة</h4><p><?= number_format($stats['completed_trips'] ?? 0) ?></p></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon bg-red-100 text-red-600"><i class="fas fa-ban"></i></div>
                <div class="kpi-info"><h4>الرحلات الملغاة</h4><p><?= number_format($stats['cancelled_trips'] ?? 0) ?></p></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon bg-yellow-100 text-yellow-600"><i class="fas fa-star"></i></div>
                <div class="kpi-info"><h4>متوسط التكلفة</h4><p><?= number_format($cost['avg_cost'] ?? 0, 2) ?> <span class="text-sm font-normal">OMR</span></p></div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: KPIs -->
            <div class="lg:col-span-1 space-y-8">
                <div class="card">
                    <div class="card-header"><h3>إحصائيات عامة</h3></div>
                    <ul class="p-4 space-y-3 text-sm">
                        <li class="flex justify-between items-center"><span><i class="fas fa-route text-gray-400 mr-2"></i>إجمالي كل الرحلات</span><span class="font-bold text-lg"><?= number_format($stats['total_trips'] ?? 0) ?></span></li>
                        <li class="flex justify-between items-center"><span><i class="fas fa-clock text-gray-400 mr-2"></i>متوسط مدة الرحلة</span><span class="font-bold text-lg"><?= format_seconds($stats['avg_duration_seconds']) ?></span></li>
                        <li class="flex justify-between items-center"><span><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>متوسط مسافة الرحلة</span><span class="font-bold text-lg"><?= number_format($stats['avg_distance_km'] ?? 0, 1) ?> كم</span></li>
                    </ul>
                </div>
                 <div class="card">
                    <div class="card-header"><h3>مؤشرات الدفع والتكاليف</h3></div>
                    <ul class="p-4 space-y-3 text-sm">
                        <li class="flex justify-between items-center"><span><i class="fas fa-money-bill-wave text-green-500 mr-2"></i>رحلات الدفع النقدي</span><span class="font-bold text-lg"><?= number_format($cost['cash_trips'] ?? 0) ?></span></li>
                        <li class="flex justify-between items-center"><span><i class="far fa-credit-card text-indigo-500 mr-2"></i>رحلات الدفع بالبطاقة</span><span class="font-bold text-lg"><?= number_format($cost['card_trips'] ?? 0) ?></span></li>
                        <li class="flex justify-between items-center"><span><i class="fas fa-tags text-gray-400 mr-2"></i>إجمالي الخصومات</span><span class="font-bold text-lg"><?= number_format($cost['total_discounts'] ?? 0, 2) ?> OMR</span></li>
                    </ul>
                </div>
                 <div class="card">
                    <div class="card-header"><h3>مؤشرات زمنية</h3></div>
                     <ul class="p-4 space-y-3 text-sm">
                        <li class="flex justify-between items-center"><span><i class="fas fa-car-side text-gray-400 mr-2"></i>متوسط زمن وصول السائق</span><span class="font-bold text-lg"><?= format_seconds($time['avg_arrival_seconds']) ?></span></li>
                        <li class="flex justify-between items-center"><span><i class="fas fa-user-clock text-gray-400 mr-2"></i>متوسط انتظار الراكب للسائق</span><span class="font-bold text-lg"><?= format_seconds($time['avg_loading_seconds']) ?></span></li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Tables -->
            <div class="lg:col-span-2 space-y-8">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h3><i class="fas fa-user-secret mr-2 text-red-500"></i>كشف السائقين المشتبه بهم (أعلى نسبة إلغاء)</h3>
                        <span class="text-xs font-semibold bg-red-100 text-red-700 px-2 py-1 rounded-full">أعلى 10</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table-responsive">
                            <thead><tr><th>السائق</th><th>إجمالي الرحلات</th><th>الإلغاءات</th><th>نسبة الإلغاء</th><th>الإيرادات</th></tr></thead>
                            <tbody>
                                <?php if(empty($suspicious_drivers)): ?>
                                    <tr><td colspan="5" class="text-center py-6">لا يوجد سائقين يتجاوزون عتبة الشك.</td></tr>
                                <?php else: foreach ($suspicious_drivers as $driver): ?>
                                    <tr>
                                        <td>
                                            <p class="font-semibold"><?= htmlspecialchars($driver['driver_name'] ?? 'N/A') ?></p>
                                            <p class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($driver['driver_id'] ?? '') ?></p>
                                        </td>
                                        <td class="text-center"><?= number_format($driver['total_trips'] ?? 0) ?></td>
                                        <td class="text-center font-bold text-red-600"><?= number_format($driver['cancelled_by_driver'] ?? 0) ?></td>
                                        <td class="text-center font-bold text-red-600"><?= htmlspecialchars($driver['cancellation_rate'] ?? 0) ?>%</td>
                                        <td class="text-center font-semibold"><?= number_format($driver['total_revenue'] ?? 0, 2) ?> <span class="text-xs">OMR</span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                 <div class="card">
                     <div class="card-header flex justify-between items-center">
                        <h3><i class="fas fa-user-ninja mr-2 text-orange-500"></i>كشف الركاب المشتبه بهم (أعلى نسبة إلغاء)</h3>
                        <span class="text-xs font-semibold bg-orange-100 text-orange-700 px-2 py-1 rounded-full">أعلى 10</span>
                    </div>
                    <div class="overflow-x-auto">
                         <table class="table-responsive">
                            <thead><tr><th>الراكب</th><th>إجمالي الطلبات</th><th>الإلغاءات</th><th>نسبة الإلغاء</th><th>إجمالي المدفوعات</th></tr></thead>
                            <tbody>
                                <?php if(empty($suspicious_passengers)): ?>
                                    <tr><td colspan="5" class="text-center py-6">لا يوجد ركاب يتجاوزون عتبة الشك.</td></tr>
                                <?php else: foreach ($suspicious_passengers as $passenger): ?>
                                    <tr>
                                        <td>
                                            <p class="font-semibold"><?= htmlspecialchars($passenger['passenger_name'] ?? 'N/A') ?></p>
                                            <p class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($passenger['passenger_id'] ?? '') ?></p>
                                        </td>
                                        <td class="text-center"><?= number_format($passenger['total_requests'] ?? 0) ?></td>
                                        <td class="text-center font-bold text-orange-600"><?= number_format($passenger['cancelled_by_passenger'] ?? 0) ?></td>
                                        <td class="text-center font-bold text-orange-600"><?= htmlspecialchars($passenger['cancellation_rate'] ?? 0) ?>%</td>
                                        <td class="text-center font-semibold"><?= number_format($passenger['total_paid'] ?? 0, 2) ?> <span class="text-xs">OMR</span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
