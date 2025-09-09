<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Flash Messages -->
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4">Search Tickets & Phone Numbers</h1>
        <p class="text-lg text-gray-600">Find tickets by number or search by phone number</p>
    </div>

    <!-- Search Form -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <form method="GET" action="/search/results" id="search-form">
                <div class="mb-6">
                    <label for="search_query" class="block text-sm font-medium text-gray-700 mb-2">
                        Search Query
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search_query" name="q"
                               value="<?= htmlspecialchars($data['query'] ?? '') ?>"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                               placeholder="Enter ticket number or phone number..."
                               autocomplete="off">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        You can search by ticket number (e.g., 12345) or phone number (e.g., +1234567890)
                    </p>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-medium text-lg flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="/" class="bg-gray-200 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-300 transition font-medium text-lg flex items-center justify-center">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </div>
            </form>
        </div>

        <!-- Quick Examples -->
        <div class="mt-8 bg-blue-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">Search Examples</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="bg-white p-3 rounded border">
                    <div class="font-medium text-gray-800">Ticket Number</div>
                    <div class="text-gray-600">Search by ticket ID: <code class="bg-gray-100 px-2 py-1 rounded">12345</code></div>
                </div>
                <div class="bg-white p-3 rounded border">
                    <div class="font-medium text-gray-800">Phone Number</div>
                    <div class="text-gray-600">Search by phone: <code class="bg-gray-100 px-2 py-1 rounded">+1234567890</code></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus search input when page loads
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_query');
    if (searchInput) {
        searchInput.focus();

        // If there's a query parameter, the input already has the value
        if (searchInput.value) {
            searchInput.select();
        }
    }
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
