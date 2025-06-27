<div class="mb-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <!-- Search Form -->
        <form action="<?= BASE_PATH ?>/tickets/search" method="POST" class="flex-grow w-full sm:w-auto relative">
            <div class="relative">
                <input type="text" name="search_term" id="ticket-search-input" placeholder="Search by Ticket or Phone Number..." class="shadow-sm appearance-none border rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required autocomplete="off">
                <button type="submit" class="absolute inset-y-0 right-0 px-4 text-gray-500 hover:text-blue-500">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div id="search-suggestions" class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10 hidden">
                <!-- Suggestions will be populated here -->
            </div>
        </form>
        
        <!-- New Ticket Button -->
        <a href="<?= BASE_PATH ?>/create_ticket" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md w-full sm:w-auto text-center transition duration-150">
            <i class="fas fa-plus mr-2"></i>
            New Ticket
        </a>
    </div>
</div> 