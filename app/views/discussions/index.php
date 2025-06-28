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
    
<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
        <div class="flex items-center">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($data['page_main_title']); ?></h1>
            <?php if ($data['open_discussions_count'] > 0) : ?>
                <span class="ml-4 bg-red-500 text-white text-sm font-semibold px-3 py-1 rounded-full">
                    <?= $data['open_discussions_count'] ?> Open
                </span>
            <?php endif; ?>
        </div>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center self-start sm:self-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </a>
    </div>

    <!-- Flash Messages -->
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>
    
    <!-- Discussions List -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <?php if (!empty($data['discussions'])) : ?>
            <div class="space-y-6">
                <?php foreach ($data['discussions'] as $discussion) : ?>
                    <div id="discussion-<?= $discussion['id'] ?>" class="p-5 rounded-lg border-2 <?= $discussion['status'] === 'open' ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-gray-50' ?>">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-start">
                            <!-- Discussion Title/Reason -->
                            <div class="flex-grow mb-3 sm:mb-0">
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($discussion['reason']) ?></h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Opened by <span class="font-semibold"><?= htmlspecialchars($discussion['opener_name']) ?></span> on <?= date('M d, Y', strtotime($discussion['created_at'])) ?>
                                </p>
                            </div>
                            <!-- Status and Actions -->
                            <div class="flex items-center space-x-4 flex-shrink-0">
                                <span class="text-sm font-semibold px-3 py-1 rounded-full <?= $discussion['status'] === 'open' ? 'bg-orange-200 text-orange-800' : 'bg-gray-200 text-gray-800' ?>">
                                    <?= ucfirst($discussion['status']) ?>
                                </span>
                                <?php if ($discussion['status'] === 'open' && ($data['currentUser']['role'] === 'admin' || $data['currentUser']['id'] === $discussion['opener_id'])) : ?>
                                    <form action="<?= BASE_PATH ?>/discussions/close/<?= $discussion['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to close this discussion?');">
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold flex items-center">
                                            <i class="fas fa-times-circle mr-1"></i> Close
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Discussion Context -->
                        <div class="mt-4 border-t pt-4">
                            <div class="flex-shrink-0">
                                <p class="text-sm font-semibold text-gray-500">Regarding:</p>
                                <p class="font-bold text-gray-800">
                                    <?php if (!empty($discussion['ticket_number'])) : ?>
                                        <a href="<?= BASE_PATH ?>/tickets/view/<?= $discussion['ticket_id'] ?>" class="text-blue-600 hover:underline">
                                            Ticket #<?= htmlspecialchars($discussion['ticket_number']) ?>
                                        </a>
                                    <?php elseif (!empty($discussion['driver_name'])) : ?>
                                        <a href="<?= BASE_PATH ?>/drivers/details/<?= $discussion['driver_id'] ?>" class="text-blue-600 hover:underline">
                                            Call with <?= htmlspecialchars($discussion['driver_name']) ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-gray-500">N/A</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Discussion Notes -->
                        <div class="prose prose-sm max-w-none mt-4 text-gray-800 bg-white p-4 rounded-lg border">
                            <?= $discussion['notes'] ?>
                        </div>

                        <!-- Replies Section -->
                        <div class="mt-4 ml-4 pl-4 border-l-2 border-gray-200 space-y-4">
                            <?php if (!empty($discussion['replies'])) : ?>
                                <?php foreach ($discussion['replies'] as $reply) : ?>
                                    <div class="bg-gray-100 p-3 rounded-lg">
                                        <p class="text-sm">
                                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($reply['username']) ?></span>:
                                            <span class="text-gray-700"><?= nl2br(htmlspecialchars($reply['message'])) ?></span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1 text-right"><?= date('M d, Y H:i', strtotime($reply['created_at'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Add Reply Form -->
                        <?php if ($discussion['status'] === 'open') : ?>
                            <div class="mt-5 border-t pt-4">
                                <form action="<?= BASE_PATH ?>/discussions/addReply/<?= $discussion['id'] ?>" method="POST">
                                    <textarea name="message" rows="3" class="w-full p-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Write a reply..." required></textarea>
                                    <div class="text-right mt-2">
                                        <button type="submit" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-blue-700">
                                            Submit Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-comments text-4xl mb-3"></i>
                <p>No discussions found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>

</body>
</html> 