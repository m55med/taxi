<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'Driver Details') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 1.25rem;
            top: 1.25rem;
            bottom: -1.25rem;
            width: 2px;
            background-color: #e5e7eb;
            transform: translateX(-50%);
        }
        .timeline-item:last-child:before {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    
<?php include_once APPROOT . '/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <!-- Driver Search Bar -->
    <div x-data="driverSearch()" x-init="init()" class="mb-6 relative">
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

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?= htmlspecialchars($page_main_title); ?>: <?= htmlspecialchars($driver['name']) ?></h1>
        <a href="javascript:history.back()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </a>
    </div>

    <!-- Flash Messages -->
    <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>

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
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
                    <i class="fas fa-history text-gray-400 mr-3"></i>
                    Call History
                </h2>
                <?php if (!empty($callHistory)): ?>
                    <div class="relative">
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
                                    <p class="text-sm text-gray-600 mb-1"><strong>Status:</strong> <?= htmlspecialchars($call['call_status']) ?></p>
                                    <p class="text-sm text-gray-600"><strong>Notes:</strong> <?= htmlspecialchars($call['notes'] ?: 'None') ?></p>
                                     <?php if ($call['next_call_at']): ?>
                                        <p class="text-xs text-red-600 mt-2"><strong>Follow-up:</strong> <?= date('Y-m-d H:i', strtotime($call['next_call_at'])) ?></p>
                                    <?php endif; ?>

                                    <!-- Review and Discussion sections for the call -->
                                    <div class="mt-4 pt-4 border-t space-y-4">
                                        <div>
                                            <?php
                                            $reviews = $call['reviews'] ?? [];
                                            $add_review_url = BASE_PATH . "/review/add/driver_call/" . $call['id'];
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
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
            this.isOpen = true;
            fetch(`<?= URLROOT ?>/drivers/search?q=${encodeURIComponent(this.query)}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                })
                .finally(() => {
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
            if (this.highlightedIndex > -1 && this.results[this.highlightedIndex]) {
                window.location.href = `<?= URLROOT ?>/drivers/details/${this.results[this.highlightedIndex].id}`;
            }
        },
        init() {
            // Can be used for initialization if needed
        }
    }
}
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
</body>
</html> 