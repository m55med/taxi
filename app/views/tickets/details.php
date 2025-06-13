<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'تفاصيل التذكرة') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/app/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" x-data="ticketDetails()">
    <!-- Flash Messages -->
    <?php include_once APPROOT . '/app/views/includes/flash_messages.php'; ?>

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
            <?php include_once __DIR__ . '/partials/ticket_info.php'; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <?php include_once __DIR__ . '/partials/related_info.php'; ?>
            
            <!-- Reviews Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <?php include_once __DIR__ . '/partials/reviews_section.php'; ?>
            </div>
    
            <!-- Discussions Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <?php include_once __DIR__ . '/partials/discussions_section.php'; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function ticketDetails() {
        return {
            openReviewForm: false,
            openDiscussionForm: false,
            openObjectionForm: {}, // e.g., { 'discussion_1': false, 'discussion_2': true }
        }
    }
</script>

</body>
</html> 