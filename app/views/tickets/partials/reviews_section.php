<?php
// Create a unique identifier for this review section instance
// to avoid DOM ID conflicts when this partial is rendered multiple times on the same page.
$review_unique_id_suffix = 'new';
if (isset($add_review_url)) {
    // Attempt to create a suffix from the URL, e.g., 'ticket_detail_123'
    $path_parts = explode('/', rtrim($add_review_url, '/'));
    $id_part = end($path_parts);
    $type_part = prev($path_parts);
    if (is_numeric($id_part) && is_string($type_part)) {
        $review_unique_id_suffix = htmlspecialchars($type_part) . '_' . htmlspecialchars($id_part);
    }
} else {
    // Fallback for a generic new review
    $review_unique_id_suffix = 'review_' . rand();
}
?>
<!-- Reviews Section -->
<div x-data="{ openReviewForm: false }" class="mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-700">
            <i class="fas fa-star-half-alt text-gray-400 mr-3"></i>Reviews
        </h3>
        <?php if (isset($can_add_review) && $can_add_review) : ?>
            <button @click="openReviewForm = !openReviewForm" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm font-medium" :aria-expanded="openReviewForm" aria-controls="review-form-<?= $review_unique_id_suffix ?>">
                <i class="fas fa-plus mr-1"></i> Add Review
            </button>
        <?php endif; ?>
    </div>

    <!-- Review Form -->
    <div x-show="openReviewForm" x-transition class="bg-gray-50 p-4 rounded-lg border mb-4" id="review-form-<?= $review_unique_id_suffix ?>" 
         x-data="reviewFormPartial({ 
             categories: <?= htmlspecialchars(json_encode($ticket_categories ?? [])) ?>, 
             ticketDetails: <?= htmlspecialchars(json_encode($ticket_details ?? [])) ?> 
         })">
        <form action="<?= isset($add_review_url) ? $add_review_url : '' ?>" method="POST">
            <!-- RATING -->
            <div class="mb-4">
                <label for="rating_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <div class="flex items-center space-x-3">
                    <input type="range" id="rating_<?= $review_unique_id_suffix ?>" name="rating" min="0" max="100" x-model.number="rating" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <input type="number" x-model.number="rating" class="w-20 p-1 border border-gray-300 rounded-md text-center" min="0" max="100">
                </div>
            </div>

            <!-- CLASSIFICATION - HIDDEN -->
            <div class="mb-4" style="display: none;">
                <div class="flex items-center mb-2">
                    <i class="fas fa-sitemap text-blue-500 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700">Classification</span>
                    <span class="text-xs text-blue-600 ml-2 bg-blue-50 px-2 py-1 rounded">(Auto-filled from ticket details)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="category_id_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category_id_<?= $review_unique_id_suffix ?>" x-model="categoryId" @change="fetchSubcategories" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Category</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label for="subcategory_id_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Subcategory</label>
                    <select id="subcategory_id_<?= $review_unique_id_suffix ?>" x-model="subcategoryId" @change="fetchCodes" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" :disabled="!categoryId || subcategoriesLoading">
                        <template x-if="subcategoriesLoading"><option>Loading...</option></template>
                        <template x-if="!subcategoriesLoading"><option value="">Select Subcategory</option></template>
                        <template x-for="subcategory in subcategories" :key="subcategory.id">
                            <option :value="subcategory.id" x-text="subcategory.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label for="code_id_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                    <select id="code_id_<?= $review_unique_id_suffix ?>" x-model="codeId" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" :disabled="!subcategoryId || codesLoading">
                        <template x-if="codesLoading"><option>Loading...</option></template>
                        <template x-if="!codesLoading"><option value="">Select Code</option></template>
                        <template x-for="code in codes" :key="code.id">
                            <option :value="code.id" x-text="code.name"></option>
                        </template>
                    </select>
                </div>
            </div>
            </div>

            <!-- Hidden inputs to ensure values are submitted -->
            <input type="hidden" name="ticket_category_id" :value="categoryId">
            <input type="hidden" name="ticket_subcategory_id" :value="subcategoryId">
            <input type="hidden" name="ticket_code_id" :value="codeId">

            <!-- Classification Display (Read-only) -->
            <div class="mb-4" x-show="categoryId">
                <div class="flex items-center mb-2">
                    <i class="fas fa-sitemap text-green-500 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700">Classification</span>
                    <span class="text-xs text-green-600 ml-2 bg-green-50 px-2 py-1 rounded">(Auto-filled from ticket details)</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                    <div class="flex flex-wrap items-center gap-x-2 text-sm text-gray-600">
                        <span class="font-semibold" x-text="ticketDetails.category_name || 'Loading...'"></span>
                        <span class="text-gray-400 mx-1" x-show="ticketDetails.subcategory_name">&gt;</span>
                        <span x-text="ticketDetails.subcategory_name" x-show="ticketDetails.subcategory_name"></span>
                        <span class="text-gray-400 mx-1" x-show="ticketDetails.code_name">&gt;</span>
                        <span class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs font-medium" x-text="ticketDetails.code_name" x-show="ticketDetails.code_name"></span>
                    </div>
                </div>
            </div>

            <!-- REVIEW REASONS (when rating < 100) -->
            <div class="mb-4" x-show="rating < 100" x-transition>
                <label for="review_reasons_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                    Review Reasons (Select applicable issues)
                </label>
                <select id="review_reasons_<?= $review_unique_id_suffix ?>" x-model="selectedReason" @change="addReasonToNotes" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
                    <option value="">-- Select reason to add to notes --</option>
                    <option value="Ticket investigation" :disabled="addedReasons.includes('Ticket investigation')">Ticket investigation</option>
                    <option value="Deduction and Refund" :disabled="addedReasons.includes('Deduction and Refund')">Deduction and Refund</option>
                    <option value="Message content" :disabled="addedReasons.includes('Message content')">Message content</option>
                    <option value="Cancellation fees" :disabled="addedReasons.includes('Cancellation fees')">Cancellation fees</option>
                    <option value="Following Updates" :disabled="addedReasons.includes('Following Updates')">Following Updates</option>
                    <option value="Company's profile" :disabled="addedReasons.includes('Company&apos;s profile')">Company's profile</option>
                    <option value="Handling Skills" :disabled="addedReasons.includes('Handling Skills')">Handling Skills</option>
                </select>
                <!-- Display added reasons -->
                <div x-show="addedReasons.length > 0" class="mt-2 flex flex-wrap gap-2">
                    <template x-for="reason in addedReasons" :key="reason">
                        <span class="inline-flex items-center px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">
                            <span x-text="reason"></span>
                            <button type="button" @click="removeReason(reason)" class="ml-1 text-orange-600 hover:text-orange-800">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </span>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Selected reasons will be automatically added to the notes below
                </p>
            </div>

            <!-- NOTES -->
            <div class="mb-4">
                <label for="review_notes_<?= $review_unique_id_suffix ?>" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="review_notes" id="review_notes_<?= $review_unique_id_suffix ?>" x-model="reviewNotes" rows="4" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Add notes..."></textarea>
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end border-t pt-4">
                <button type="button" @click="openReviewForm = false" class="text-gray-600 mr-4">Cancel</button>
                <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600">Submit Review</button>
            </div>
        </form>
    </div>

    <!-- Existing Reviews -->
    <?php if (!empty($reviews)) : ?>
        <ul class="space-y-4">
            <?php foreach ($reviews as $review) : ?>
                <?php
                    $bgColor = 'bg-yellow-50 border-yellow-400'; // Default for mid-range
                    if ($review['rating'] >= 80) {
                        $bgColor = 'bg-green-50 border-green-400';
                    } elseif ($review['rating'] < 50) {
                        $bgColor = 'bg-red-50 border-red-400';
                    }
                ?>
                <li class="p-4 rounded-lg border-l-4 <?= $bgColor ?>">
                    <div class="flex justify-between items-center">
                        <p class="font-bold">
                            Reviewed by: <?= htmlspecialchars($review['reviewer_name']) ?>
                            - <span class="font-bold text-lg"><?= htmlspecialchars($review['rating']) ?>/100</span>
                        </p>
                        <span class="text-xs text-gray-500"><?= date('Y-m-d H:i', strtotime($review['reviewed_at'])) ?></span>
                    </div>
                    <?php if (!empty($review['review_notes'])) : ?>
                        <p class="mt-2 text-gray-700 bg-white p-2 rounded border border-gray-200"><?= nl2br(htmlspecialchars($review['review_notes'])) ?></p>
                    <?php endif; ?>

                    <!-- Classification Display -->
                    <?php if (!empty($review['category_name'])): ?>
                    <div class="mt-3 flex items-center text-sm text-gray-600 bg-white p-2 rounded border border-gray-200">
                        <i class="fas fa-sitemap text-gray-400 mr-3"></i>
                        <div class="flex flex-wrap items-center gap-x-2">
                            <span class="font-semibold"><?= htmlspecialchars($review['category_name']) ?></span>
                            <?php if (!empty($review['subcategory_name'])): ?>
                                <span class="text-gray-400 mx-1">&gt;</span>
                                <span><?= htmlspecialchars($review['subcategory_name']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($review['code_name'])): ?>
                                <span class="text-gray-400 mx-1">&gt;</span>
                                <span class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs font-medium"><?= htmlspecialchars($review['code_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Discussion Section for this Review -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                         <?php
                            // Ensure all required variables are passed to the partial
                            $discussion_partial_data = [
                                'discussions' => $review['discussions'] ?? [],
                                'add_url' => BASE_URL . "/discussions/add/review/" . $review['id'],
                                'can_add_discussion' => in_array($currentUser['role'], ['Quality', 'Team_leader', 'admin', 'developer']),
                                'currentUser' => $currentUser // Pass the user context down
                            ];
                            render_partial('tickets/partials/discussions_section.php', $discussion_partial_data);
                        ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
            No reviews for this item yet.
        </div>
    <?php endif; ?>
</div> 
<?php if (!isset($GLOBALS['review_script_loaded'])): ?>
<script>
function reviewFormPartial(initialData) {
    return {
        rating: 50,
        categories: initialData.categories || [],
        ticketDetails: initialData.ticketDetails || {},
        categoryId: initialData.ticketDetails ? initialData.ticketDetails.category_id : null,
        subcategoryId: initialData.ticketDetails ? initialData.ticketDetails.subcategory_id : null,
        codeId: initialData.ticketDetails ? initialData.ticketDetails.code_id : null,
        subcategories: [],
        codes: [],
        subcategoriesLoading: false,
        codesLoading: false,
        selectedReason: '',
        reviewNotes: '',
        addedReasons: [],
        fetchSubcategories() {
            if (!this.categoryId) {
                this.subcategoryId = null; this.subcategories = []; 
                return Promise.resolve();
            }
            this.subcategoriesLoading = true; 
            // Don't reset subcategoryId and codeId when auto-loading
            if (!this.ticketDetails || !this.ticketDetails.subcategory_id) {
                this.subcategoryId = null; 
                this.codeId = null;
            }
            return fetch(`<?= URLROOT ?>/calls/subcategories/${this.categoryId}`)
                .then(res => res.json())
                .then(data => { 
                    this.subcategories = data; 
                    this.subcategoriesLoading = false; 
                });
        },
        fetchCodes() {
            if (!this.subcategoryId) {
                this.codeId = null; this.codes = []; 
                return Promise.resolve();
            }
            this.codesLoading = true; 
            // Don't reset codeId when auto-loading
            if (!this.ticketDetails || !this.ticketDetails.code_id) {
                this.codeId = null;
            }
            return fetch(`<?= URLROOT ?>/calls/codes/${this.subcategoryId}`)
                .then(res => res.json())
                .then(data => { 
                    this.codes = data; 
                    this.codesLoading = false; 
                });
        },
        addReasonToNotes() {
            if (this.selectedReason && !this.addedReasons.includes(this.selectedReason)) {
                // Add reason to tracking array to prevent duplicates
                this.addedReasons.push(this.selectedReason);
                
                // Add reason to notes with proper formatting
                const reasonText = `• ${this.selectedReason}`;
                
                if (this.reviewNotes.trim() === '') {
                    // If notes are empty, start with the reason
                    this.reviewNotes = reasonText;
                } else {
                    // If notes exist, add reason on new line
                    this.reviewNotes += '\n' + reasonText;
                }
                
                // Reset selection
                this.selectedReason = '';
            }
        },
        removeReason(reasonToRemove) {
            // Remove from tracking array
            this.addedReasons = this.addedReasons.filter(reason => reason !== reasonToRemove);
            
            // Remove from notes
            const reasonText = `• ${reasonToRemove}`;
            this.reviewNotes = this.reviewNotes.replace(reasonText, '').replace(/\n\n/g, '\n').trim();
        },
        init() {
             this.$watch('rating', value => {
                if (value > 100) this.rating = 100;
                if (value < 0) this.rating = 0;
                
                // Clear added reasons when rating becomes 100
                if (value >= 100) {
                    // Remove all added reasons from notes
                    this.addedReasons.forEach(reason => {
                        const reasonText = `• ${reason}`;
                        this.reviewNotes = this.reviewNotes.replace(reasonText, '');
                    });
                    this.reviewNotes = this.reviewNotes.replace(/\n\n/g, '\n').trim();
                    
                    this.addedReasons = [];
                    this.selectedReason = '';
                }
            });
            
            // Auto-load subcategories and codes if ticket details are provided
            if (this.ticketDetails && this.categoryId) {
                this.fetchSubcategories().then(() => {
                    // Restore subcategory selection after subcategories are loaded
                    if (this.ticketDetails.subcategory_id) {
                        this.subcategoryId = this.ticketDetails.subcategory_id;
                        this.fetchCodes().then(() => {
                            // Restore code selection after codes are loaded
                            if (this.ticketDetails.code_id) {
                                this.codeId = this.ticketDetails.code_id;
                            }
                        });
                    }
                });
            }
        }
    }
}
</script>
<?php $GLOBALS['review_script_loaded'] = true; endif; ?> 