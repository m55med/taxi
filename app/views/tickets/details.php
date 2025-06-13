<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'تفاصيل التذكرة') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/app/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?> #<?= htmlspecialchars($ticket['ticket_number']) ?></h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-right ml-2"></i>
            عودة
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">بيانات التذكرة</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="flex items-center"><i class="fas fa-ticket-alt text-gray-400 ml-3"></i><strong>رقم التذكرة:</strong> <span class="mr-2"><?= htmlspecialchars($ticket['ticket_number']) ?><?php if ($ticket['is_vip']): ?><span class="bg-yellow-400 text-white text-xs font-bold mr-2 px-2.5 py-0.5 rounded-full">VIP</span><?php endif; ?></span></div>
                <div class="flex items-center"><i class="fas fa-desktop text-gray-400 ml-3"></i><strong>المنصة:</strong> <span class="mr-2"><?= htmlspecialchars($ticket['platform_name']) ?></span></div>
                <div class="flex items-center"><i class="fas fa-phone text-gray-400 ml-3"></i><strong>رقم الهاتف:</strong> <span class="mr-2" dir="ltr"><?= htmlspecialchars($ticket['phone'] ?? 'N/A') ?></span></div>
                <div class="flex items-center"><i class="fas fa-globe-africa text-gray-400 ml-3"></i><strong>الدولة:</strong> <span class="mr-2"><?= htmlspecialchars($ticket['country_name'] ?? 'N/A') ?></span></div>
                <div class="flex items-center"><i class="fas fa-user text-gray-400 ml-3"></i><strong>الموظف:</strong> <span class="mr-2"><?= htmlspecialchars($ticket['creator_username']) ?></span></div>
                <div class="flex items-center"><i class="fas fa-user-tie text-gray-400 ml-3"></i><strong>قائد الفريق:</strong> <span class="mr-2"><?= htmlspecialchars($ticket['leader_username']) ?></span></div>
                <div class="flex items-center col-span-1 md:col-span-2"><i class="fas fa-sitemap text-gray-400 ml-3"></i><strong>التصنيف:</strong> <span class="mr-2 text-sm"><?= htmlspecialchars($ticket['category_name']) ?> / <?= htmlspecialchars($ticket['subcategory_name']) ?> / <?= htmlspecialchars($ticket['code_name']) ?></span></div>
                <div class="flex items-center col-span-1 md:col-span-2"><i class="far fa-clock text-gray-400 ml-3"></i><strong>تاريخ الإنشاء:</strong> <span class="mr-2"><?= date('Y-m-d H:i', strtotime($ticket['created_at'])) ?></span></div>
                <div class="col-span-1 md:col-span-2">
                    <strong class="flex items-center"><i class="far fa-file-alt text-gray-400 ml-3"></i>الملاحظات:</strong>
                    <p class="text-gray-800 mt-2 bg-gray-50 p-3 rounded-md whitespace-pre-wrap border"><?= htmlspecialchars($ticket['notes'] ?? 'لا يوجد') ?></p>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">الكوبونات المستخدمة</h2>
                <?php if (!empty($ticket['coupons'])): ?>
                    <ul class="space-y-3">
                        <?php foreach ($ticket['coupons'] as $coupon): ?>
                            <li class="flex items-center justify-between p-2 bg-gray-50 rounded-md border">
                                <span class="font-mono text-sm text-green-600 font-bold"><?= htmlspecialchars($coupon['code']) ?></span>
                                <span class="text-sm text-gray-600"> (القيمة: <?= htmlspecialchars($coupon['value']) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">لا توجد كوبونات مستخدمة في هذه التذكرة.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3">تذاكر أخرى للعميل</h2>
                <?php if (!empty($relatedTickets)): ?>
                     <ul class="space-y-3">
                        <?php foreach ($relatedTickets as $relatedTicket): ?>
                             <li class="p-2 bg-gray-50 rounded-md border">
                                <a href="<?= BASE_PATH . '/tickets/details/' . $relatedTicket['id'] ?>" class="font-semibold text-blue-600 hover:underline">
                                    تذكرة #<?= htmlspecialchars($relatedTicket['ticket_number']) ?>
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    بتاريخ: <?= date('Y-m-d', strtotime($relatedTicket['created_at'])) ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                     <p class="text-gray-500">لا توجد تذاكر أخرى مرتبطة بهذا الرقم.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html> 