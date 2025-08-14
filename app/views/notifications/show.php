<?php include_once __DIR__ . '/../includes/header.php'; ?>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">

            <!-- Back Button -->
            <div class="mb-6">
                <a href="<?= URLROOT ?>/notifications/history" class="text-blue-600 hover:text-blue-800 transition-colors duration-300 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Notification History
                </a>
            </div>

            <!-- Notification Card -->
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-gray-200">
                    <!-- Header -->
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-2">
                        <?= htmlspecialchars($data['notification']['title']) ?>
                    </h1>
                    <!-- Meta -->
                    <p class="text-sm text-gray-500">
                        <i class="far fa-clock mr-1"></i> Sent on <?= date('F j, Y, g:i a', strtotime($data['notification']['created_at'])) ?>
                    </p>
                </div>

                <!-- Message Body -->
                <div class="p-6 sm:p-8">
                    <div class="prose max-w-none">
                        <?= $data['notification']['message'] /* This is trusted HTML from admin, already purified on creation */ ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

