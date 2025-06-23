<?php require APPROOT . '/app/views/inc/header.php'; ?>

<body class="bg-gray-100">
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']) ?></h1>
        <a href="<?= BASE_PATH ?>/notifications/history" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
            <i class="fas fa-history mr-2"></i> View History
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 sm:p-8">
        <form action="<?= BASE_PATH ?>/notifications/store" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required>
            </div>

            <div class="mb-6">
                <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Message:</label>
                <p class="text-xs text-gray-500 mb-2">You can use basic HTML tags for formatting, such as &lt;b&gt; for bold, &lt;i&gt; for italic, &lt;br&gt; for line breaks, and &lt;a href="..."&gt; for links.</p>
                <textarea name="message" id="message" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required></textarea>
            </div>

            <div class="flex items-center justify-end space-x-4 space-x-reverse">
                <a href="<?= BASE_PATH ?>/notifications" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                    Send Notification to All Users
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/app/views/inc/footer.php'; ?> 