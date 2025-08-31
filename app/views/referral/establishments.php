<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div id="flash-message" 
         class="fixed top-5 right-5 z-50 w-full max-w-xs p-4 rounded-lg shadow-lg text-white 
                transform translate-x-full opacity-0 transition-all duration-500 ease-in-out
                <?= ($_SESSION['flash_message_type'] ?? 'success') === 'success' ? 'bg-green-600' : 'bg-red-600' ?>">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <?php if (($_SESSION['flash_message_type'] ?? 'success') === 'success'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium"><?= htmlspecialchars($_SESSION['flash_message']) ?></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button id="flash-close-btn" class="inline-flex text-white opacity-70 hover:opacity-100 transition">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 h-1 bg-white/50" id="flash-progress"></div>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_message_type']); ?>
<?php endif; ?>
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Establishments Management</h1>
        <p class="text-gray-600">
            <?php if ($data['user_role'] === 'marketer'): ?>
                Manage your registered establishments
            <?php else: ?>
                Manage all registered establishments
            <?php endif; ?>
        </p>
    </div>
    <?php if ($data['user_role'] === 'marketer' && isset($data['current_marketer'])): ?>
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold text-blue-800 mb-2">
            🎯 Your Referral Link
        </h2>
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" 
                   id="main-marketer-link"
                   value="https://taxif.om/establishments?id=<?= $data['current_marketer']->id ?>" 
                   class="flex-1 text-sm px-3 py-2 border border-blue-300 rounded bg-white text-blue-800 font-mono" 
                   readonly>
            <button onclick="copyMainMarketerLink()" 
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <i class="fas fa-copy mr-1"></i> Copy
            </button>
            <button onclick="shareMarketerLink(<?= $data['current_marketer']->id ?>, '<?= htmlspecialchars(addslashes($data['current_marketer']->name)) ?>')" 
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition">
                <i class="fas fa-share-alt mr-1"></i> Share
            </button>
        </div>
    </div>

<?php endif; ?>


    <!-- Filter and Search Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="<?= URLROOT ?>/referral/establishments" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($data['filters']['search']) ?>"
                           placeholder="Search establishments, legal name, owner..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Filter by Marketer (Admin only) -->
                <?php if (in_array($data['user_role'], ['admin', 'developer'])): ?>
                    <div>
                        <label for="filter_marketer" class="block text-sm font-medium text-gray-700 mb-2">Filter by Marketer</label>
                        <select id="filter_marketer" 
                                name="filter_marketer" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Marketers</option>
                            <?php foreach ($data['marketers'] as $marketer): ?>
                                <option value="<?= $marketer->id ?>" <?= $data['filters']['filter_marketer'] == $marketer->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($marketer->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Filter by Contact Info -->
                <div>
                    <label for="filter_contact" class="block text-sm font-medium text-gray-700 mb-2">Contact Filter</label>
                    <select id="filter_contact" 
                            name="filter_contact" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Establishments</option>
                        <option value="with_email" <?= $data['filters']['filter_contact'] === 'with_email' ? 'selected' : '' ?>>With Email</option>
                        <option value="with_phone" <?= $data['filters']['filter_contact'] === 'with_phone' ? 'selected' : '' ?>>With Phone</option>
                        <option value="no_contact" <?= $data['filters']['filter_contact'] === 'no_contact' ? 'selected' : '' ?>>No Contact Info</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <div class="flex gap-2">
                        <select id="sort_by" 
                                name="sort_by" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="created_at" <?= $data['filters']['sort_by'] === 'created_at' ? 'selected' : '' ?>>Created Date</option>
                            <option value="establishment_name" <?= $data['filters']['sort_by'] === 'establishment_name' ? 'selected' : '' ?>>Name</option>
                            <option value="legal_name" <?= $data['filters']['sort_by'] === 'legal_name' ? 'selected' : '' ?>>Legal Name</option>
                            <option value="owner_full_name" <?= $data['filters']['sort_by'] === 'owner_full_name' ? 'selected' : '' ?>>Owner</option>
                        </select>
                        <select name="sort_order" 
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="DESC" <?= $data['filters']['sort_order'] === 'DESC' ? 'selected' : '' ?>>↓</option>
                            <option value="ASC" <?= $data['filters']['sort_order'] === 'ASC' ? 'selected' : '' ?>>↑</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-search mr-2"></i> Search & Filter
                </button>
                <a href="<?= URLROOT ?>/referral/establishments" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Establishments</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $data['summaryStats']->total ?></p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-blue-400 to-blue-600">
                    <i class="fas fa-building fa-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">With Email</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $data['summaryStats']->with_email ?></p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-green-400 to-green-600">
                    <i class="fas fa-envelope fa-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">With Phone</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $data['summaryStats']->with_phone ?></p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-purple-400 to-purple-600">
                    <i class="fas fa-phone fa-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">With Description</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $data['summaryStats']->with_description ?></p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white bg-gradient-to-r from-orange-400 to-orange-600">
                    <i class="fas fa-file-alt fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Export and Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-info-circle"></i>
                Showing <?= $data['pagination']['start_record'] ?> - <?= $data['pagination']['end_record'] ?> of <?= $data['pagination']['total_records'] ?> establishments
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">Export filtered results:</span>
                <?php 
                $exportParams = http_build_query([
                    'search' => $data['filters']['search'],
                    'filter_marketer' => $data['filters']['filter_marketer'],
                    'filter_contact' => $data['filters']['filter_contact']
                ]);
                ?>
                <a href="<?= URLROOT ?>/referral/establishments/export?format=excel&<?= $exportParams ?>" 
                   class="inline-flex items-center px-3 py-2 border border-green-600 text-green-600 rounded-md hover:bg-green-50 text-sm">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
                <a href="<?= URLROOT ?>/referral/establishments/export?format=csv&<?= $exportParams ?>" 
                   class="inline-flex items-center px-3 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm">
                    <i class="fas fa-file-csv mr-2"></i> CSV
                </a>
                <a href="<?= URLROOT ?>/referral/establishments/export?format=json&<?= $exportParams ?>" 
                   class="inline-flex items-center px-3 py-2 border border-purple-600 text-purple-600 rounded-md hover:bg-purple-50 text-sm">
                    <i class="fas fa-file-code mr-2"></i> JSON
                </a>
            </div>
        </div>
    </div>

    <!-- Establishments Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Establishment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marketer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <?php if (in_array($data['user_role'], ['admin', 'developer'])): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($data['establishments'])): ?>
                        <tr>
                            <td colspan="<?= in_array($data['user_role'], ['admin', 'developer']) ? '6' : '5' ?>" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-building text-4xl text-gray-300 mb-2"></i>
                                    <p class="text-lg">No establishments found</p>
                                    <p class="text-sm">Get started by registering your first establishment via the API</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['establishments'] as $establishment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 mr-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                                <i class="fas fa-building text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($establishment->establishment_name ?: 'N/A') ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($establishment->legal_name ?: 'Legal name not provided') ?>
                                            </div>
                                            <?php if ($establishment->street): ?>
                                                <div class="text-xs text-gray-400">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?= htmlspecialchars($establishment->street) ?>
                                                    <?php if ($establishment->house_number): ?>
                                                        <?= htmlspecialchars($establishment->house_number) ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($establishment->owner_full_name ?: 'N/A') ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($establishment->owner_position ?: 'Position not specified') ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if ($establishment->establishment_email): ?>
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-envelope mr-1 text-gray-400"></i>
                                            <a href="mailto:<?= htmlspecialchars($establishment->establishment_email) ?>" class="text-blue-600 hover:text-blue-800">
                                                <?= htmlspecialchars($establishment->establishment_email) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($establishment->establishment_phone): ?>
                                        <div class="text-sm text-gray-900 mt-1">
                                            <i class="fas fa-phone mr-1 text-gray-400"></i>
                                            <a href="tel:<?= htmlspecialchars($establishment->establishment_phone) ?>" class="text-blue-600 hover:text-blue-800">
                                                <?= htmlspecialchars($establishment->establishment_phone) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!$establishment->establishment_email && !$establishment->establishment_phone): ?>
                                        <span class="text-sm text-gray-500">No contact info</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if ($establishment->marketer_name): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-user mr-1"></i>
                                            <?= htmlspecialchars($establishment->marketer_name) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No marketer
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= date('M j, Y', strtotime($establishment->created_at)) ?>
                                    <br>
                                    <span class="text-xs text-gray-500"><?= date('g:i A', strtotime($establishment->created_at)) ?></span>
                                </td>

                                <?php if (in_array($data['user_role'], ['admin', 'developer'])): ?>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="<?= URLROOT ?>/referral/establishments/edit/<?= $establishment->id ?>" 
                                           class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="showDeleteConfirm(<?= $establishment->id ?>, '<?= htmlspecialchars(addslashes($establishment->establishment_name)) ?>')"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($data['pagination']['total_pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow-md">
            <div class="flex-1 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= $data['pagination']['start_record'] ?></span> 
                        to <span class="font-medium"><?= $data['pagination']['end_record'] ?></span> 
                        of <span class="font-medium"><?= $data['pagination']['total_records'] ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous Page -->
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <a href="?page=<?= $data['pagination']['current_page'] - 1 ?>&<?= $currentParams ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start = max(1, $data['pagination']['current_page'] - 2);
                        $end = min($data['pagination']['total_pages'], $data['pagination']['current_page'] + 2);
                        
                        $currentParams = http_build_query([
                            'search' => $data['filters']['search'],
                            'filter_marketer' => $data['filters']['filter_marketer'],
                            'filter_contact' => $data['filters']['filter_contact'],
                            'sort_by' => $data['filters']['sort_by'],
                            'sort_order' => $data['filters']['sort_order']
                        ]);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?page=<?= $i ?>&<?= $currentParams ?>" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                      <?= $i === $data['pagination']['current_page'] 
                                          ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <a href="?page=<?= $data['pagination']['current_page'] + 1 ?>&<?= $currentParams ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0 w-10 h-10 mr-3 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                <p class="text-sm text-gray-600">This action cannot be undone</p>
            </div>
        </div>
        
        <div class="mb-6">
            <p class="text-sm text-gray-700">
                Are you sure you want to delete the establishment <strong id="delete-establishment-name"></strong>?
            </p>
            <p class="text-xs text-gray-500 mt-2">
                This will permanently remove all establishment data from the system.
            </p>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button onclick="hideDeleteConfirm()" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </button>
            <form id="delete-form" method="POST" class="inline">
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Delete Establishment
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Delete confirmation functions
function showDeleteConfirm(establishmentId, establishmentName) {
    const modal = document.getElementById('delete-confirm-modal');
    const nameElement = document.getElementById('delete-establishment-name');
    const form = document.getElementById('delete-form');
    
    nameElement.textContent = establishmentName;
    form.action = '<?= URLROOT ?>/referral/establishments/delete/' + establishmentId;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteConfirm() {
    const modal = document.getElementById('delete-confirm-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('delete-confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteConfirm();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteConfirm();
    }
});

// Flash message handling
document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flash-message');
    if (!flashMessage) return;

    const progressBar = document.getElementById('flash-progress');
    const closeBtn = document.getElementById('flash-close-btn');
    const duration = 4000;
    let timeoutId;

    // Global function for showing toast messages
    window.showToast = (message, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `fixed top-5 right-5 z-50 w-full max-w-xs p-4 rounded-lg shadow-lg text-white transform translate-x-full opacity-0 transition-all duration-500 ease-in-out ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' 
                        ? '<i class="fas fa-check-circle"></i>' 
                        : '<i class="fas fa-exclamation-circle"></i>'}
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="ml-4 text-white opacity-70 hover:opacity-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 500);
        }, 3000);
    };

    const hideFlashMessage = () => {
        flashMessage.classList.add('translate-x-full', 'opacity-0');
        flashMessage.addEventListener('transitionend', () => {
            flashMessage.remove();
        }, { once: true });
    };

    const startFlashTimer = () => {
        progressBar.style.transition = `width ${duration}ms linear`;
        progressBar.style.width = '0%';
        timeoutId = setTimeout(hideFlashMessage, duration);
    };

    setTimeout(() => {
        flashMessage.classList.remove('translate-x-full', 'opacity-0');
        startFlashTimer();
    }, 100);

    closeBtn.addEventListener('click', () => {
        clearTimeout(timeoutId);
        hideFlashMessage();
    });
});

function copyMainMarketerLink() {
    const linkInput = document.getElementById('main-marketer-link');
    linkInput.select();
    document.execCommand('copy');
    window.showToast('Your referral link copied!', 'success');
}

function shareMarketerLink(marketerId, marketerName) {
    const link = `https://taxif.om/establishments?id=${marketerId}`;
    const title = `Registration Link for ${marketerName}`;
    const text = `Register your establishment with ${marketerName} at TaxiF`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: link
        }).then(() => {
            window.showToast('Link shared successfully!', 'success');
        }).catch((error) => {
            window.showToast('Failed to share link. Link copied to clipboard instead.', 'error');
            navigator.clipboard.writeText(link);
        });
    } else {
        navigator.clipboard.writeText(link).then(() => {
            window.showToast('Link copied to clipboard for sharing!', 'success');
        }).catch(() => {
            window.showToast('Failed to copy link.', 'error');
        });
    }
}
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
