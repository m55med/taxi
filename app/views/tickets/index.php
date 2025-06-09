<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التذاكر - نظام الدعم الفني</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/app/views/tickets/css/style.css">
</head>
<body class="antialiased">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <header class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-800">نظام إدارة التذاكر</h1>
            <p class="text-lg text-gray-600 mt-2">إنشاء تذكرة جديدة أو البحث عن تذكرة موجودة.</p>
        </header>

        <main class="max-w-4xl mx-auto">
            <form id="ticket-form" class="bg-white rounded-xl shadow-lg p-8">
                
                <?php include 'sections/section_details.php'; ?>
                
                <?php include 'sections/section_classification.php'; ?>

                <?php include 'sections/section_assignment.php'; ?>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-4 space-x-reverse">
                    <button type="button" id="reset-btn" class="btn btn-secondary"><i class="fas fa-redo ml-2"></i>مسح الحقول</button>
                    <button type="submit" id="submit-btn" class="btn btn-primary"><i class="fas fa-plus ml-2"></i>إنشاء تذكرة</button>
                </div>
            </form>
        </main>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-8 right-8 z-[100] w-full max-w-sm space-y-3"></div>

    <script>
        const BASE_PATH = '<?= BASE_PATH ?>';
    </script>
    <script src="<?= BASE_PATH ?>/app/views/tickets/js/main.js"></script>

</body>
</html> 