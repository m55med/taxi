<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'المناقشات') ?></title>
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
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?></h1>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="space-y-4">
            <?php if (!empty($discussions)): ?>
                <?php foreach ($discussions as $discussion): ?>
                    <a href="<?= BASE_PATH . '/tickets/details/' . $discussion['ticket_id'] ?>" class="block p-4 rounded-lg border hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <p class="font-semibold text-lg text-indigo-600">
                                    تذكرة #<?= htmlspecialchars($discussion['ticket_number']) ?>
                                    <span class="text-gray-700 font-normal">- <?= htmlspecialchars($discussion['reason']) ?></span>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    فُتحت بواسطة: <span class="font-medium"><?= htmlspecialchars($discussion['opener_username']) ?></span>
                                    • بتاريخ: <span dir="ltr"><?= date('Y-m-d', strtotime($discussion['created_at'])) ?></span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-4 space-x-reverse">
                                 <span class="text-sm px-3 py-1 rounded-full font-medium <?= $discussion['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $discussion['status'] === 'open' ? 'مفتوحة' : 'مغلقة' ?>
                                </span>
                                <div class="text-center">
                                    <p class="font-bold text-xl text-gray-700"><?= htmlspecialchars($discussion['replies_count']) ?></p>
                                    <p class="text-xs text-gray-500">ردود</p>
                                </div>
                                <i class="fas fa-chevron-left text-gray-400"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-comments fa-3x text-gray-400 mb-4"></i>
                    <p class="text-gray-500 text-lg">لا توجد مناقشات مرتبطة بك حاليًا.</p>
                </div>
        <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html> 