<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="knowledgeBase()">

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

    <!-- Folders Section - Google Docs Style -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <i class="fas fa-folder-open text-2xl text-indigo-600"></i>
                <h2 class="text-2xl font-bold text-gray-800">Folders</h2>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full" x-text="`Total: ${folders.length} folders`"></span>
                <?php if ($data['can_create']) : ?>
                    <a href="<?= URLROOT ?>/knowledge_base/folders/create"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>New Folder</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Folder Grid - Google Docs Style -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7 gap-4">

            <!-- All Articles Folder -->
            <div class="group">
                <button @click="selectFolder(null)"
                        :class="selectedFolder === null ? 'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-200' : 'hover:shadow-lg border-gray-200'"
                        class="w-full bg-white border-2 rounded-xl p-4 text-center transition-all duration-300 hover:border-indigo-300">

                    <div class="flex flex-col items-center space-y-3">
                        <!-- Large Icon -->
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl flex items-center justify-center group-hover:from-indigo-200 group-hover:to-indigo-300 transition-all duration-300">
                            <i class="fas fa-layer-group text-2xl text-indigo-600"></i>
                        </div>

                        <!-- Folder Name -->
                        <div class="text-center">
                            <h3 class="text-base font-medium text-gray-800 mb-0.5">All Articles</h3>
                            <p class="text-xs text-gray-500" x-text="totalArticles + ' articles'"></p>
                        </div>

                        <!-- Selection Indicator -->
                        <div :class="selectedFolder === null ? 'opacity-100' : 'opacity-0'"
                             class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center transition-opacity duration-200">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Dynamic Folders -->
            <template x-for="folder in folders" :key="folder.id">
                <div class="group relative">
                    <button @click="selectFolder(folder.id)"
                            :class="selectedFolder === folder.id ? 'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-200' : 'hover:shadow-lg border-gray-200'"
                            class="w-full bg-white border-2 rounded-xl p-4 text-center transition-all duration-300 hover:border-indigo-300">

                        <div class="flex flex-col items-center space-y-3">
                            <!-- Large Icon with Color -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-105"
                                 :style="`background: linear-gradient(135deg, ${folder.color}20, ${folder.color}30); border: 2px solid ${folder.color}40;`">
                                <i :class="folder.icon" class="text-xl" :style="`color: ${folder.color}`"></i>
                            </div>

                            <!-- Folder Name -->
                            <div class="text-center">
                                <h3 class="text-base font-medium text-gray-800 mb-0.5" x-text="folder.name"></h3>
                                <p class="text-xs text-gray-500" x-text="(folder.article_count || 0) + ' articles'"></p>
                            </div>

                            <!-- Selection Indicator -->
                            <div :class="selectedFolder === folder.id ? 'opacity-100' : 'opacity-0'"
                                 class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center transition-opacity duration-200">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                    </button>

                    <!-- Folder Actions Menu (for admins) -->
                    <?php if ($data['can_create']) : ?>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="relative">
                                <button @click="toggleFolderMenu($el, folder.id)"
                                        class="w-8 h-8 bg-white border border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-ellipsis-v text-gray-600 text-sm"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="openFolderMenu === folder.id"
                                     @click.away="closeFolderMenu()"
                                     x-cloak
                                     class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1">
                                    <a :href="`<?= URLROOT ?>/knowledge_base/folders/edit/${folder.id}`"
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                        <i class="fas fa-edit mr-3 text-blue-500"></i>
                                        Edit Folder
                                    </a>
                                    <a :href="`<?= URLROOT ?>/knowledge_base/folders/delete/${folder.id}`"
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                       onclick="return confirm('Are you sure you want to delete this folder and move all its articles to General?')">
                                        <i class="fas fa-trash mr-3 text-red-500"></i>
                                        Delete Folder
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </template>

            <!-- Add New Folder Card (for admins) -->
            <?php if ($data['can_create']) : ?>
                <div class="group">
                    <a href="<?= URLROOT ?>/knowledge_base/folders/create"
                       class="w-full bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-4 text-center transition-all duration-300 hover:border-indigo-400 hover:bg-indigo-50">

                        <div class="flex flex-col items-center space-y-3">
                            <!-- Large Plus Icon -->
                            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors duration-300">
                                <i class="fas fa-plus text-xl text-gray-400 group-hover:text-indigo-600 transition-colors duration-300"></i>
                            </div>

                            <!-- Add Text -->
                            <div class="text-center">
                                <h3 class="text-base font-medium text-gray-600 group-hover:text-indigo-600 mb-0.5">New Folder</h3>
                                <p class="text-xs text-gray-500">Create folder</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Current Selection Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg" x-show="selectedFolder !== null">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-folder-open text-indigo-600"></i>
                    <span class="text-sm text-gray-600">Viewing folder:</span>
                    <template x-for="folder in folders" :key="folder.id">
                        <span x-show="selectedFolder === folder.id"
                              class="font-medium text-gray-800"
                              x-text="folder.name"></span>
                    </template>
                </div>
                <button @click="selectFolder(null)"
                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    <i class="fas fa-times mr-1"></i>Clear filter
                </button>
            </div>
        </div>
    </div>

    <!-- Search Bar -->

    <div class="mb-8">

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

                            <!-- Folder Badge -->
                            <template x-if="article.folder_name">
                                <div class="flex items-center space-x-2 mb-3">
                                    <div class="w-4 h-4 rounded-full flex items-center justify-center"
                                         :style="`background-color: ${article.folder_color || '#6B7280'}; color: white`">
                                        <i :class="article.folder_icon || 'fas fa-folder'" class="text-xs"></i>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :style="`background-color: ${article.folder_color || '#6B7280'}20; color: ${article.folder_color || '#6B7280'}`"
                                          x-text="article.folder_name"></span>
                                </div>
                            </template>

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

        folders: <?= json_encode($data['folders'] ?? []) ?>,

        selectedFolder: new URLSearchParams(window.location.search).get('folder') || null,

        totalArticles: <?= count($data['articles']) ?>,

        showCreateFolderModal: false,

        openFolderMenu: null,

        isLoading: false,

        init() {

            this.$watch('searchQuery', (newValue, oldValue) => {

                // To avoid an immediate re-fetch on page load, we only search if the query has changed.

                 if (newValue !== oldValue) {

                    this.search();

                }

            });

            this.$watch('selectedFolder', (newValue, oldValue) => {

                if (newValue !== oldValue) {

                    this.filterByFolder();

                }

            });

        },

        selectFolder(folderId) {

            this.selectedFolder = folderId;

            this.updateURL();

        },

        toggleFolderMenu(button, folderId) {

            event.stopPropagation();

            if (this.openFolderMenu === folderId) {

                this.openFolderMenu = null;

            } else {

                this.openFolderMenu = folderId;

            }

        },

        closeFolderMenu() {

            this.openFolderMenu = null;

        },

        filterByFolder() {

            if (this.selectedFolder === null) {

                // Show all articles

                this.articles = <?= json_encode($data['articles']) ?>;

            } else {

                // Filter articles by folder

                this.isLoading = true;

                const url = `<?= URLROOT ?>/knowledge_base/folder/${this.selectedFolder}`;

                fetch(url)

                    .then(res => res.json())

                    .then(data => {

                        this.articles = data;

                    })

                    .catch(err => console.error('Folder filter failed:', err))

                    .finally(() => this.isLoading = false);

            }

        },

        updateURL() {

            const url = new URL(window.location);

            if (this.selectedFolder) {

                url.searchParams.set('folder', this.selectedFolder);

            } else {

                url.searchParams.delete('folder');

            }

            if (this.searchQuery) {

                url.searchParams.set('q', this.searchQuery);

            } else {

                url.searchParams.delete('q');

            }

            window.history.pushState({}, '', url);

        },

        search: debounce(function() {

            if (this.searchQuery.trim() === '') {

                // If the search query is empty, show articles based on selected folder

                this.filterByFolder();

                return;

            }

            this.isLoading = true;

            let url = `<?= URLROOT ?>/knowledge_base/search?q=${encodeURIComponent(this.searchQuery)}`;

            if (this.selectedFolder) {

                url += `&folder=${this.selectedFolder}`;

            }

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