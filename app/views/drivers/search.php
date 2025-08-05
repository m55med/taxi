<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Flash Messages -->
    <?php flash('driver_search_error'); ?>

    <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md border">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 text-center">
            <i class="fas fa-user-shield mr-3"></i>
            Search for a Driver
        </h1>
        
        <p class="text-gray-600 text-center mb-6">
            Enter a driver's name or phone number to find their details.
        </p>

        <div x-data="driverSearch()" class="relative">
            <div class="relative">
                <input type="text"
                       id="driver-search"
                       x-model="query"
                       @input.debounce.300ms="search"
                       @focus="isOpen = true"
                       @keydown.escape.prevent="isOpen = false; query = ''"
                       @keydown.arrow-down.prevent="highlightNext()"
                       @keydown.arrow-up.prevent="highlightPrev()"
                       @keydown.enter.prevent="selectHighlighted()"
                       placeholder="e.g., John Doe or 555-1234"
                       class="w-full px-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-lg"
                       autocomplete="off">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
            </div>
            
            <!-- Search Results Dropdown -->
            <div x-show="isOpen && results.length > 0"
                 @click.away="isOpen = false"
                 class="absolute top-full left-0 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-10 max-h-60 overflow-y-auto"
                 style="display: none;">
                <ul>
                    <template x-for="(driver, index) in results" :key="driver.id">
                        <li>
                            <a :href="`<?= URLROOT ?>/drivers/details/${driver.id}`"
                               @mouseenter="highlightedIndex = index"
                               class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white"
                               :class="{ 'bg-blue-500 text-white': highlightedIndex === index }">
                                <span class="font-bold" x-text="driver.name"></span> -
                                <span class="text-gray-500" :class="{ 'text-white': highlightedIndex === index }" x-text="driver.phone"></span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
            <div x-show="isOpen && query.length > 2 && isLoading" class="absolute top-full left-0 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-10 p-4 text-center text-gray-500">
                Searching...
            </div>
            <div x-show="isOpen && query.length > 2 && !isLoading && results.length === 0" class="absolute top-full left-0 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-10 p-4 text-center text-gray-500">
                No results found.
            </div>
        </div>
    </div>
</div>

<script>
function driverSearch() {
    return {
        query: '',
        results: [],
        isOpen: false,
        isLoading: false,
        highlightedIndex: -1,
        search() {
            if (this.query.length < 3) {
                this.results = [];
                this.isOpen = false;
                return;
            }
            this.isLoading = true;
            fetch(`<?= URLROOT ?>/drivers/search?q=${this.query}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                    this.isOpen = true;
                    this.isLoading = false;
                    this.highlightedIndex = -1;
                });
        },
        highlightNext() {
            if (this.highlightedIndex < this.results.length - 1) {
                this.highlightedIndex++;
            }
        },
        highlightPrev() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        selectHighlighted() {
            if (this.highlightedIndex > -1) {
                window.location.href = `<?= URLROOT ?>/drivers/details/${this.results[this.highlightedIndex].id}`;
            }
        }
    };
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
