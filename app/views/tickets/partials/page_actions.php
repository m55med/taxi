<div class="mb-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <!-- Search Form -->
        <form action="/taxi/tickets/search" method="POST" class="flex-grow w-full sm:w-auto">
            <div class="relative">
                <input type="text" name="ticket_number" placeholder="ابحث برقم التذكرة..." class="shadow-sm appearance-none border rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" required>
                <button type="submit" class="absolute inset-y-0 right-0 px-4 text-gray-500 hover:text-blue-500">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <!-- New Ticket Button -->
        <a href="/taxi/ticket" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md w-full sm:w-auto text-center transition duration-150">
            <i class="fas fa-plus mr-2"></i>
            تذكرة جديدة
        </a>
    </div>
</div> 