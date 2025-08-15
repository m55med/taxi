<?php

require_once APPROOT . '/helpers/session_helper.php';

// Load the main layout
include_once APPROOT . '/views/includes/header.php';
?>

<main class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Driver Search Bar -->
    <div x-data="driverSearch()" class="mb-6 relative">
        <label for="driver-search" class="block text-sm font-medium text-gray-700 mb-1">Search for a driver by phone number:</label>
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
                   placeholder="Enter phone number to search..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
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
                        <a :href="'<?= URLROOT ?>/drivers/details/' + driver.id"
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

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?>: <?= htmlspecialchars($driver['name']) ?></h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </a>
    </div>

    <!-- Flash Messages -->
    <?php flash('driver_assignment_success'); ?>
    <?php flash('driver_assignment_error'); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Driver Details & Assignment) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md h-fit">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-id-card-alt text-gray-400 mr-3"></i>
                    Driver Details
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center"><i class="fas fa-user text-gray-400 mr-3 w-5 text-center"></i><strong>Name:</strong> <span class="ml-2"><?= htmlspecialchars($driver['name']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-phone text-gray-400 mr-3 w-5 text-center"></i><strong>Phone:</strong> <span class="ml-2" dir="ltr"><?= htmlspecialchars($driver['phone']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-envelope text-gray-400 mr-3 w-5 text-center"></i><strong>Email:</strong> <span class="ml-2"><?= htmlspecialchars($driver['email'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-venus-mars text-gray-400 mr-3 w-5 text-center"></i><strong>Gender:</strong> <span class="ml-2"><?= htmlspecialchars(ucfirst($driver['gender'] ?? 'N/A')) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-globe-africa text-gray-400 mr-3 w-5 text-center"></i><strong>Country:</strong> <span class="ml-2"><?= htmlspecialchars($driver['country_name'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-car text-gray-400 mr-3 w-5 text-center"></i><strong>Car Type:</strong> <span class="ml-2"><?= htmlspecialchars($driver['car_type_name'] ?? 'N/A') ?></span></div>
                    <div class="flex items-center"><i class="fas fa-star text-gray-400 mr-3 w-5 text-center"></i><strong>Rating:</strong> <span class="ml-2"><?= htmlspecialchars($driver['rating']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-mobile-alt text-gray-400 mr-3 w-5 text-center"></i><strong>App Status:</strong> <span class="ml-2"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $driver['app_status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= htmlspecialchars($driver['app_status']) ?></span></span></div>
                    <div class="flex items-center"><i class="fas fa-cogs text-gray-400 mr-3 w-5 text-center"></i><strong>System Status:</strong> <span class="ml-2"><?= htmlspecialchars($driver['main_system_status']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-database text-gray-400 mr-3 w-5 text-center"></i><strong>Data Source:</strong> <span class="ml-2"><?= htmlspecialchars($driver['data_source']) ?></span></div>
                    <div class="flex items-center"><i class="fas fa-user-plus text-gray-400 mr-3 w-5 text-center"></i><strong>Added By:</strong> <span class="ml-2"><?= htmlspecialchars($driver['added_by_username'] ?? 'System') ?></span></div>
                    <div class="flex items-center"><i class="far fa-clock text-gray-400 mr-3 w-5 text-center"></i><strong>Created At:</strong> <span class="ml-2" dir="ltr"><?= date('Y-m-d H:i', strtotime($driver['created_at'])) ?></span></div>
                </div>
            </div>
            
            <!-- Assignment Form Partial -->
            <?php include_once __DIR__ . '/partials/assignment_form.php'; ?>

        </div>
        
        <!-- Sidebar (History) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Call History -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-history text-gray-400 mr-3"></i>
                        Call History
                    </div>
                    <div class="relative w-1/2">
                        <input type="text" id="call-history-search" placeholder="Search in history..." class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-1 focus:ring-blue-300">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <?php $activityCount = count($assignmentHistory ?? []); ?>
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">
                        <?= $activityCount ?> Activities
                    </span>
                </h2>
                <?php if (!empty($callHistory)): ?>
                    <div class="relative" id="call-history-container">
                        <?php foreach ($callHistory as $call): ?>
                            <div class="timeline-item mb-6 pl-10 relative">
                                <div class="absolute left-0 top-0">
                                    <span class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-phone-alt"></i>
                                    </span>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="font-semibold text-gray-800">
                                            Call by: <?= htmlspecialchars($call['staff_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500" dir="ltr">
                                            <?= date('Y-m-d H:i', strtotime($call['created_at'])) ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><strong>Status:</strong> <?= htmlspecialchars($call['call_status']) ?></p>
                                    
                                    <?php if (!empty($call['category_name'])): ?>
                                    <div class="mt-2 flex items-center text-sm text-gray-600">
                                        <i class="fas fa-sitemap text-gray-400 mr-3"></i>
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="font-semibold"><?= htmlspecialchars($call['category_name']) ?></span>
                                            <?php if (!empty($call['subcategory_name'])): ?>
                                                <span class="text-gray-400 mx-1">&gt;</span>
                                                <span><?= htmlspecialchars($call['subcategory_name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($call['code_name'])): ?>
                                                <span class="text-gray-400 mx-1">&gt;</span>
                                                <span class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs font-medium"><?= htmlspecialchars($call['code_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <p class="text-sm text-gray-600 mt-2"><strong>Notes:</strong> <?= htmlspecialchars($call['notes'] ?: 'None') ?></p>
                                     <?php if ($call['next_call_at']): ?>
                                        <p class="text-xs text-red-600 mt-2"><strong>Follow-up:</strong> <?= date('Y-m-d H:i', strtotime($call['next_call_at'])) ?></p>
                                    <?php endif; ?>

                                    <!-- Review and Discussion sections for the call -->
                                    <div class="mt-4 pt-4 border-t space-y-4">
                                        <div>
                                            <?php
                                            $reviews = $call['reviews'] ?? [];
                                            $add_review_url = URLROOT . "/review/add/driver_call/" . $call['id'];
                                            $can_add_review = in_array($data['currentUser']['role'], ['quality_manager', 'Team_leader', 'admin', 'developer']);
                                            include APPROOT . '/views/tickets/partials/reviews_section.php';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No call history for this driver.</p>
                <?php endif; ?>
            </div>

            <!-- Assignment History -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-exchange-alt text-gray-400 mr-3"></i>
                    Assignment History
                </h2>
                <?php if (!empty($assignmentHistory)): ?>
                    <div class="relative">
                        <?php foreach ($assignmentHistory as $assignment): ?>
                            <div class="timeline-item mb-6 pl-10 relative">
                                <div class="absolute left-0 top-0">
                                    <span class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600">
                                        <i class="fas fa-exchange-alt"></i>
                                    </span>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="font-semibold text-gray-800">
                                            Assigned from: <?= htmlspecialchars($assignment['from_username']) ?> to: <?= htmlspecialchars($assignment['to_username']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500" dir="ltr">
                                            <?= date('Y-m-d H:i', strtotime($assignment['created_at'])) ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600"><strong>Note:</strong> <?= htmlspecialchars($assignment['note'] ?: 'None') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No assignment history for this driver.</p>
                <?php endif; ?>
            </div>

            <!-- Documents Management Section -->
            <?php if (isset($driver)): ?>
            <div x-data="driverDetails(<?= htmlspecialchars(json_encode($data)) ?>)" class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4 border-b pb-2">Manage Documents</h3>
                <div class="mb-6 bg-gray-50 p-3 rounded-md">
                    <span class="font-semibold">Missing Documents Status:</span>
                    <span x-text="driver.has_missing_documents ? 'Yes' : 'No'" 
                          :class="driver.has_missing_documents ? 'text-red-600 font-bold' : 'text-green-600 font-bold'"></span>
                </div>

                <!-- Add New Document Form -->
                <div class="mb-6">
                    <h4 class="font-semibold text-lg mb-2">Add New Document Requirement</h4>
                    <div class="flex items-center space-x-2">
                        <select x-model="newDocumentId" class="flex-grow p-2 border rounded-md">
                            <option value="">-- Select a document to add --</option>
                            <template x-for="doc in unassignedDocuments" :key="doc.id">
                                <option :value="doc.id" x-text="doc.name"></option>
                            </template>
                        </select>
                        <button @click="addDocument" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md">&plus; Add</button>
                    </div>
                </div>

                <!-- Existing Documents List -->
                <div>
                    <h4 class="font-semibold text-lg mb-2">Current Documents</h4>
                    <div class="space-y-4">
                        <template x-for="doc in documents" :key="doc.id">
                            <div class="p-4 border rounded-lg bg-white shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800" x-text="doc.name"></p>
                                        <p class="text-sm text-gray-500" x-show="doc.updated_by">
                                            Last updated by <span x-text="doc.updated_by"></span> on <span x-text="new Date(doc.updated_at).toLocaleDateString()"></span>
                                        </p>
                                    </div>
                                    <div class="flex-1 flex items-center space-x-2 mt-3 md:mt-0">
                                        <input type="text" x-model="doc.note" placeholder="Add a note..." class="flex-grow p-2 border rounded-md text-sm">
                                        <select x-model="doc.status" @change="updateDocument(doc)" class="p-2 border rounded-md text-sm">
                                            <option value="missing">Missing</option>
                                            <option value="uploaded">Uploaded</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        <button @click="removeDocument(doc.id)" class="text-red-500 hover:text-red-700 p-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="documents.length === 0" class="text-center text-gray-500 py-4">
                            No documents assigned to this driver.
                        </div>
                    </div>
                </div>

                <!-- Deletion Confirmation Modal -->
                <div x-show="isModalOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                     style="display: none;">
                    <div @click.away="closeModal" 
                         x-show="isModalOpen"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto">
                        <div class="flex items-center justify-start">
                             <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 ml-4">Delete Document</h3>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to remove this document requirement? This action cannot be undone.
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="closeModal" type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button @click="confirmRemoveDocument" type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('call-history-search');
        const historyContainer = document.getElementById('call-history-container');
        const timelineItems = historyContainer.querySelectorAll('.timeline-item');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            timelineItems.forEach(item => {
                const textContent = item.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('driverSearch', () => ({
            query: '',
            results: [],
            isOpen: false,
            isLoading: false,
            highlightedIndex: -1,
            search() {
                if (this.query.length < 2) {
                    this.results = [];
                    this.isOpen = false;
                    return;
                }
                this.isLoading = true;
                this.isOpen = true;
                fetch(URLROOT + '/drivers/search?q=' + this.query)
                    .then(response => response.json())
                    .then(data => {
                        this.results = data;
                        this.isLoading = false;
                        this.highlightedIndex = -1;
                    })
                    .catch(() => {
                        this.isLoading = false;
                        this.results = [];
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
                    window.location.href = URLROOT + '/drivers/details/' + this.results[this.highlightedIndex].id;
                }
            }
        }));

        Alpine.data('driverDetails', (data) => ({
            driver: data.driver,
            documents: data.driverDocuments,
            unassignedDocuments: data.unassignedDocuments,
            newDocumentId: '',
            documentToRemove: null,
            isModalOpen: false,

            init() {
                // Initialization logic can go here
            },

            addDocument() {
                if (!this.newDocumentId) {
                    toastr.error('Please select a document to add.');
                    return;
                }
                fetch(URLROOT + '/drivers/addDocument', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            driver_id: this.driver.id,
                            document_type_id: this.newDocumentId
                        })
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            this.documents.push(result.document);
                            this.unassignedDocuments = this.unassignedDocuments.filter(d => d.id != this.newDocumentId);
                            this.newDocumentId = '';
                            toastr.success('Document requirement added successfully.');
                            this.updateMissingDocsStatus();
                        } else {
                            toastr.error(result.message || 'Failed to add document.');
                        }
                    });
            },

            updateDocument(doc) {
                fetch(URLROOT + '/drivers/updateDocument', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            driver_document_id: doc.id,
                            status: doc.status,
                            note: doc.note
                        })
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            toastr.success('Document updated successfully.');
                            const updatedDoc = this.documents.find(d => d.id === doc.id);
                            if (updatedDoc && result.document) {
                                updatedDoc.updated_at = result.document.updated_at;
                                updatedDoc.updated_by = result.document.updated_by;
                            }
                            this.updateMissingDocsStatus();
                        } else {
                            toastr.error(result.message || 'Failed to update document.');
                        }
                    });
            },

            removeDocument(docId) {
                this.documentToRemove = docId;
                this.isModalOpen = true;
            },

            confirmRemoveDocument() {
                if (!this.documentToRemove) return;

                fetch(URLROOT + '/drivers/removeDocument', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            driver_document_id: this.documentToRemove
                        })
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            const removedDoc = this.documents.find(d => d.id === this.documentToRemove);
                            this.documents = this.documents.filter(d => d.id !== this.documentToRemove);
                            if (removedDoc) {
                                this.unassignedDocuments.push({
                                    id: removedDoc.document_type_id,
                                    name: removedDoc.name
                                });
                            }
                            toastr.success('Document requirement removed.');
                            this.updateMissingDocsStatus();
                        } else {
                            toastr.error(result.message || 'Failed to remove document.');
                        }
                        this.closeModal();
                    });
            },

            updateMissingDocsStatus() {
                this.driver.has_missing_documents = this.documents.some(d => d.status === 'missing' || d.status === 'rejected');
            },

            closeModal() {
                this.isModalOpen = false;
                this.documentToRemove = null;
            }
        }));
    });
</script>
<?php include_once APPROOT . '/views/includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    <?php if ($flash = flash('driver_assignment_success')): ?>
        toastr['success']('<?= $flash['message'] ?>');
    <?php endif; ?>
    <?php if ($flash = flash('driver_assignment_error')): ?>
        toastr['error']('<?= $flash['message'] ?>');
    <?php endif; ?>
</script> 