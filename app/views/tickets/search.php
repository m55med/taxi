<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md border">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 text-center">
            <i class="fas fa-search mr-3"></i>
            Search for a Ticket
        </h1>
        
        <p class="text-gray-600 text-center mb-6">
            Enter a ticket number or a customer's phone number to find a ticket.
        </p>

        <form action="<?= URLROOT ?>/tickets/search" method="POST" id="search-form" class="relative">
            <div class="relative">
                <input type="text" id="search_term" name="search_term" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-lg" 
                       placeholder="e.g., T-12345 or 968..."
                       autocomplete="off">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            <div id="suggestions-container" class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg hidden"></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchTerm = document.getElementById('search_term');
    const suggestionsContainer = document.getElementById('suggestions-container');

    searchTerm.addEventListener('keyup', function () {
        const term = this.value;

        if (term.length < 2) {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.classList.add('hidden');
            return;
        }

        fetch(`<?= URLROOT ?>/tickets/ajaxSearch?term=${term}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let suggestions = data.map(item =>
                        `<a href="<?= URLROOT ?>/tickets/view/${item.id}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">${item.label}</a>`
                    ).join('');
                    suggestionsContainer.innerHTML = suggestions;
                    suggestionsContainer.classList.remove('hidden');
                } else {
                    suggestionsContainer.innerHTML = '<div class="px-4 py-2 text-gray-500">No suggestions found</div>';
                    suggestionsContainer.classList.remove('hidden');
                }
            });
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function (e) {
        if (e.target !== searchTerm) {
            suggestionsContainer.classList.add('hidden');
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
