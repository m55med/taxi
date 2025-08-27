<?php require_once APPROOT . '/views/includes/header.php'; ?>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php flash('kb_message'); ?>
    
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Knowledge Base</h1>
                <p class="mt-1 text-gray-600">Find articles, tutorials, and answers to common questions.</p>
            </div>
            <?php if ($data['can_create']) : ?>
                <a href="<?= URLROOT ?>/knowledge_base/create" class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create New Article
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Bar -->
    <div x-data="knowledgeBase()" class="mb-8">
        <form action="<?= URLROOT ?>/knowledge_base" method="GET" @submit.prevent="search">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search fa-lg"></i>
                </span>
                <input type="text" name="q" placeholder="Search by title or content..." 
                       x-model.debounce.350ms="searchQuery" 
                       class="w-full px-12 py-3 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-lg">
            </div>
        </form>
    
        <!-- Articles Grid -->
        <div class="mt-8">
            <div x-show="isLoading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin fa-3x text-gray-400"></i>
                <p class="mt-2 text-gray-600">Searching...</p>
            </div>

            <div x-show="!isLoading && articles.length === 0">
                <div class="text-center bg-white rounded-lg shadow-md p-12">
                    <i class="fas fa-search-minus fa-4x text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-700">No results found for "<span x-text="searchQuery"></span>"</h2>
                    <p class="text-gray-500 mt-2">Try searching for a different term or check for typos.</p>
                </div>
            </div>

            <div x-show="!isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="article in articles" :key="article.id">
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col">
                        <div class="p-6 flex-grow">
                            <template x-if="article.ticket_code_name">
                                <span class="px-3 py-1 text-xs font-bold bg-indigo-100 text-indigo-800 rounded-full mb-3 inline-block" x-text="article.ticket_code_name"></span>
                            </template>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                <a :href="`<?= URLROOT ?>/knowledge_base/show/${article.id}`" class="hover:text-indigo-600 hover:underline" x-text="article.title"></a>
                            </h3>
                            <p class="text-gray-500 text-sm">
                                Last updated on <span x-text="new Date(article.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })"></span>
                                by <span x-text="article.author_name || 'N/A'"></span>
                            </p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-b-xl flex justify-end items-center space-x-3">
                            <a :href="`<?= URLROOT ?>/knowledge_base/show/${article.id}`" class="text-gray-500 hover:text-indigo-600" title="View">
                                <i class="fas fa-eye fa-fw"></i>
                            </a>
                            <?php if ($data['can_edit']) : ?>
                                <a :href="`<?= URLROOT ?>/knowledge_base/edit/${article.id}`" class="text-gray-500 hover:text-yellow-500" title="Edit">
                                    <i class="fas fa-edit fa-fw"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($data['can_delete']) : ?>
                                <form action="<?= URLROOT ?>/knowledge_base/destroy" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                    <input type="hidden" name="id" :value="article.id">
                                    <button type="submit" class="text-gray-500 hover:text-red-600" title="Delete"><i class="fas fa-trash-alt fa-fw"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function knowledgeBase() {
    return {
        searchQuery: new URLSearchParams(window.location.search).get('q') || '',
        articles: <?= json_encode($data['articles']) ?>,
        isLoading: false,
        init() {
            this.$watch('searchQuery', (newValue, oldValue) => {
                // To avoid an immediate re-fetch on page load, we only search if the query has changed.
                 if (newValue !== oldValue) {
                    this.search();
                }
            });
        },
        search: debounce(function() {
            if (this.searchQuery.trim() === '') {
                // If the search query is empty, you might want to show all articles or do nothing.
                // Here, we'll fetch all articles as an example.
                this.fetchAllArticles();
                return;
            }

            this.isLoading = true;
            const url = `<?= URLROOT ?>/knowledge_base/search?q=${encodeURIComponent(this.searchQuery)}`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    this.articles = data;
                })
                .catch(err => console.error('Search failed:', err))
                .finally(() => this.isLoading = false);
        }, 350),

        fetchAllArticles: function() {
            this.isLoading = true;
            const url = `<?= URLROOT ?>/knowledge_base/search?q=`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    this.articles = data;
                })
                .catch(err => console.error('Fetch all failed:', err))
                .finally(() => this.isLoading = false);
        }
    }
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 