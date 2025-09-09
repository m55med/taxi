<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Flash Messages -->
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Search Results</h1>
        <a href="/" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-home mr-2"></i>
            Home
        </a>
    </div>

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form method="GET" action="/search/results" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="q" value="<?= htmlspecialchars($data['query'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Search by ticket number or phone..." autocomplete="off">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
        </form>
    </div>

    <!-- Results -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (!empty($data['results'])): ?>
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-lg font-semibold text-gray-800">
                    Found <?= $data['total'] ?> result<?= $data['total'] > 1 ? 's' : '' ?>
                    <?php if (!empty($data['query'])): ?>
                        for "<?= htmlspecialchars($data['query']) ?>"
                    <?php endif; ?>
                </h2>
            </div>

            <div class="divide-y divide-gray-200">
                <?php foreach ($data['results'] as $result): ?>
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $result['type'] === 'ticket' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                        <i class="fas <?= $result['type'] === 'ticket' ? 'fa-ticket-alt' : 'fa-phone' ?> mr-1"></i>
                                        <?= ucfirst($result['type']) ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Created: <?= date('M d, Y H:i', strtotime($result['created_at'])) ?>
                                    </span>
                                    <?php if ($result['is_vip']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-crown mr-1"></i>
                                            VIP
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    <?= htmlspecialchars($result['display_text']) ?>
                                </h3>

                                <div class="text-sm text-gray-600">
                                    <span class="font-medium">Ticket ID:</span> #<?= $result['ticket_id'] ?>
                                    <?php if (!empty($result['phone'])): ?>
                                        | <span class="font-medium">Phone:</span> <?= htmlspecialchars($result['phone']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ml-4">
                                <a href="<?= $result['url'] ?>"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition">
                                    <i class="fas fa-eye mr-2"></i>
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (!empty($data['query'])): ?>
                        We couldn't find any tickets or phone numbers matching "<?= htmlspecialchars($data['query']) ?>"
                    <?php else: ?>
                        Try searching for a ticket number or phone number
                    <?php endif; ?>
                </p>
                <div class="flex justify-center space-x-3">
                    <a href="/search" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>
                        New Search
                    </a>
                    <a href="/" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                        <i class="fas fa-home mr-2"></i>
                        Go Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
