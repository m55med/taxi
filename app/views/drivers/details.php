<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'تفاصيل السائق') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .timeline-item:before {
            content: '';
            position: absolute;
            right: 1.25rem;
            top: 1.25rem;
            bottom: -1.25rem;
            width: 2px;
            background-color: #e5e7eb;
            transform: translateX(50%);
        }
        .timeline-item:last-child:before {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/app/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?>: <?= htmlspecialchars($driver['name']) ?></h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-right ml-2"></i>
            عودة
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Driver Details & Assignment) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md h-fit">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-id-card-alt text-gray-400 mr-3"></i>
                    بيانات السائق الأساسية
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center"><i class="fas fa-user text-gray-400 ml-3 w-5 text-center"></i><strong>الاسم:</strong> <span class="mr-2"><?= htmlspecialchars($driver['name']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-phone text-gray-400 ml-3 w-5 text-center"></i><strong>الهاتف:</strong> <span class="mr-2" dir="ltr"><?= htmlspecialchars($driver['phone']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-envelope text-gray-400 ml-3 w-5 text-center"></i><strong>البريد الإلكتروني:</strong> <span class="mr-2"><?= htmlspecialchars($driver['email'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-venus-mars text-gray-400 ml-3 w-5 text-center"></i><strong>الجنس:</strong> <span class="mr-2"><?= htmlspecialchars($driver['gender'] === 'male' ? 'ذكر' : ($driver['gender'] === 'female' ? 'أنثى' : 'N/A')) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-globe-africa text-gray-400 ml-3 w-5 text-center"></i><strong>الدولة:</strong> <span class="mr-2"><?= htmlspecialchars($driver['country_name'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-car text-gray-400 ml-3 w-5 text-center"></i><strong>نوع السيارة:</strong> <span class="mr-2"><?= htmlspecialchars($driver['car_type_name'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-star text-gray-400 ml-3 w-5 text-center"></i><strong>التقييم:</strong> <span class="mr-2"><?= htmlspecialchars($driver['rating']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-mobile-alt text-gray-400 ml-3 w-5 text-center"></i><strong>حالة التطبيق:</strong> <span class="mr-2"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $driver['app_status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= htmlspecialchars($driver['app_status']) ?></span></span></div>
                    <div class="flex items-center"><i class="fas fa-cogs text-gray-400 ml-3 w-5 text-center"></i><strong>حالة النظام:</strong> <span class="mr-2"><?= htmlspecialchars($driver['main_system_status']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-database text-gray-400 ml-3 w-5 text-center"></i><strong>مصدر البيانات:</strong> <span class="mr-2"><?= htmlspecialchars($driver['data_source']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-user-plus text-gray-400 ml-3 w-5 text-center"></i><strong>أضيف بواسطة:</strong> <span class="mr-2"><?= htmlspecialchars($driver['added_by_username'] ?? 'System') ?></span></div>
                    <div class="flex items-center"><i class="far fa-clock text-gray-400 ml-3 w-5 text-center"></i><strong>تاريخ الإنشاء:</strong> <span class="mr-2" dir="ltr"><?= date('Y-m-d H:i', strtotime($driver['created_at'])) ?></span></div>
                </div>
            </div>
            
            <!-- Assignment Form Partial -->
            <?php include_once __DIR__ . '/partials/assignment_form.php'; ?>
        </div>
        
        <!-- Sidebar (History) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Call History -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-history text-gray-400 mr-3"></i>
                    سجل المكالمات
                </h2>
                <?php if (!empty($callHistory)): ?>
                    <div class="relative">
                        <?php foreach ($callHistory as $call): ?>
                            <div class="timeline-item mb-6 pr-10 relative">
                                <div class="absolute right-0 top-0">
                                    <span class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-phone-alt"></i>
                                    </span>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="font-semibold text-gray-800">
                                            مكالمة بواسطة: <?= htmlspecialchars($call['staff_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500" dir="ltr">
                                            <?= date('Y-m-d H:i', strtotime($call['created_at'])) ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1"><strong>الحالة:</strong> <?= htmlspecialchars($call['call_status']) ?></p>
                                    <p class="text-sm text-gray-600"><strong>ملاحظات:</strong> <?= htmlspecialchars($call['notes'] ?: 'لا يوجد') ?></p>
                                     <?php if ($call['next_call_at']): ?>
                                        <p class="text-xs text-red-600 mt-2"><strong>متابعة في:</strong> <?= date('Y-m-d H:i', strtotime($call['next_call_at'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">لا يوجد سجل مكالمات لهذا السائق.</p>
                <?php endif; ?>
            </div>

            <!-- Assignment History -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-exchange-alt text-gray-400 mr-3"></i>
                    سجل التحويلات
                </h2>
                <?php if (!empty($assignmentHistory)): ?>
                    <div class="relative">
                        <?php foreach ($assignmentHistory as $assignment): ?>
                            <div class="timeline-item mb-6 pr-10 relative">
                                <div class="absolute right-0 top-0">
                                    <span class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600">
                                        <i class="fas fa-exchange-alt"></i>
                                    </span>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="font-semibold text-gray-800">
                                            تحويل من: <?= htmlspecialchars($assignment['from_username']) ?> إلى: <?= htmlspecialchars($assignment['to_username']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500" dir="ltr">
                                            <?= date('Y-m-d H:i', strtotime($assignment['created_at'])) ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600"><strong>ملاحظات:</strong> <?= htmlspecialchars($assignment['note'] ?: 'لا يوجد') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">لا توجد تحويلات لهذا السائق.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html> 