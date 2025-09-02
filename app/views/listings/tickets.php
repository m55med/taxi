<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Tickets</h1>

    <!-- Stats Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Overview</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600"><?= $data['stats']['total'] ?? 0 ?></div>
                <div class="text-xs text-blue-500 uppercase">إجمالي النتائج</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-600"><?= $data['stats']['vip_count'] ?? 0 ?></div>
                <div class="text-xs text-yellow-500 uppercase">VIP</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-gray-600"><?= $data['stats']['normal_count'] ?? 0 ?></div>
                <div class="text-xs text-gray-500 uppercase">عادي</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600"><?= $data['stats']['unique_creators'] ?? 0 ?></div>
                <div class="text-xs text-green-500 uppercase">المُنشئين</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-purple-600"><?= $data['stats']['platforms_used'] ?? 0 ?></div>
                <div class="text-xs text-purple-500 uppercase">المنصات</div>
            </div>
            <div class="bg-pink-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-pink-600"><?= $data['stats']['reviewed_tickets'] ?? 0 ?></div>
                <div class="text-xs text-pink-500 uppercase">مُراجعة</div>
            </div>
            <div class="bg-indigo-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-indigo-600"><?= $data['stats']['avg_rating'] ?? '0.0' ?></div>
                <div class="text-xs text-indigo-500 uppercase">متوسط التقييم</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-red-600"><?= $data['stats']['review_coverage'] ?? '0' ?>%</div>
                <div class="text-xs text-red-500 uppercase">نسبة التغطية</div>
            </div>
              </div>
  </div>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Advanced Filters</h2>
            <button type="button" id="clear-filters" class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-times mr-2"></i>Clear All
            </button>
        </div>
        <form id="filter-form" method="GET" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-2 relative">
                    <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Smart Search</label>
                    <input type="text" id="search_term" name="search_term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>" placeholder="Search by ticket #, phone, or username..." autocomplete="off">
                    <div class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Search in ticket numbers, phone numbers, or usernames. 
                        <?php if (!empty($data['tickets'])): ?>
                            Try: <?= htmlspecialchars($data['tickets'][0]['ticket_number']) ?>
                        <?php endif; ?>
                    </div>
                    <div id="search-suggestions" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        <!-- Suggestions will be populated by JavaScript -->
                    </div>
                </div>
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                    <div class="relative">
                        <input type="text" id="date_range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="من - إلى" readonly>
                    <input type="hidden" id="start_date" name="start_date" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>">
                    <input type="hidden" id="end_date" name="end_date" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>">
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <button type="button" class="quick-date-btn px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded hover:bg-blue-200" data-days="0">اليوم</button>
                        <button type="button" class="quick-date-btn px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded hover:bg-blue-200" data-days="7">آخر أسبوع</button>
                        <button type="button" class="quick-date-btn px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded hover:bg-blue-200" data-days="30">آخر شهر</button>
                        <button type="button" class="quick-date-btn px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200" onclick="clearDateRange()">كل الفترات</button>
                    </div>
                </div>
                <?php if (!\App\Core\Auth::hasRole('agent')): ?>
                <div class="relative">
                    <label for="created_by_search" class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                    <div class="relative">
                        <input type="text" id="created_by_search" class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer" placeholder="اختر مستخدم..." autocomplete="off" readonly>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                        <input type="hidden" id="created_by" name="created_by" value="<?= htmlspecialchars($data['filters']['created_by'] ?? '') ?>">
                        <div id="user-suggestions" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <div class="p-1">
                                <div class="sticky top-0 bg-white p-2 border-b border-gray-200">
                                    <input type="text" id="user_dropdown_search" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ابحث عن مستخدم...">
                                </div>
                                <div id="user_options" class="py-1">
                                    <div class="user-option px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" data-id="" data-username="">
                                        <span class="text-gray-600 italic">جميع المستخدمين</span>
                                    </div>
                        <?php foreach ($data['users'] as $user): ?>
                                    <div class="user-option px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" data-id="<?= $user->id ?>" data-username="<?= htmlspecialchars($user->username) ?>">
                                        <div class="font-medium"><?= htmlspecialchars($user->username) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($user->name ?? '') ?></div>
                                    </div>
                        <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($data['filters']['created_by'])): ?>
                        <?php 
                        $selectedUser = null;
                        foreach ($data['users'] as $user) {
                            if ($user->id == $data['filters']['created_by']) {
                                $selectedUser = $user;
                                break;
                            }
                        }
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('created_by_search').value = '<?= $selectedUser ? htmlspecialchars($selectedUser->username) : 'جميع المستخدمين' ?>';
                            });
                        </script>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div>
                    <label for="platform_id" class="block text-sm font-medium text-gray-600 mb-1">Platform</label>
                    <select id="platform_id" name="platform_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Platforms</option>
                        <?php foreach ($data['platforms'] as $platform): ?>
                            <option value="<?= $platform['id'] ?>" <?= (isset($data['filters']['platform_id']) && $data['filters']['platform_id'] == $platform['id']) ? 'selected' : '' ?>><?= htmlspecialchars($platform['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="classification_filter" class="block text-sm font-medium text-gray-600 mb-1">Classification</label>
                    <div class="relative">
                        <input type="text" id="classification_search" class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer" placeholder="اختر تصنيف..." autocomplete="off" readonly value="<?= !empty($data['filters']['classification_filter']) ? htmlspecialchars($data['filters']['classification_filter']) : 'جميع التصنيفات' ?>">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                        <input type="hidden" id="classification_filter" name="classification_filter" value="<?= htmlspecialchars($data['filters']['classification_filter'] ?? '') ?>">
                        <div id="classification_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <div class="p-1">
                                <div class="sticky top-0 bg-white p-2 border-b border-gray-200">
                                    <input type="text" id="classification_dropdown_search" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ابحث في التصنيفات...">
                                </div>
                                <div id="classification_options" class="py-1">
                                    <div class="classification-option px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" data-value="">
                                        <span class="text-gray-600 italic">جميع التصنيفات</span>
                                    </div>
                                    
                                    <?php if (!empty($data['ticket_categories'])): ?>
                                        <?php foreach ($data['ticket_categories'] as $category): ?>
                                            <?php if (!empty($category['subcategories'])): ?>
                                                <?php foreach ($category['subcategories'] as $subcategory): ?>
                                                    <?php if (!empty($subcategory['codes'])): ?>
                                                        <?php foreach ($subcategory['codes'] as $code): ?>
                                                            <?php $fullClassification = $category['name'] . ' > ' . $subcategory['name'] . ' > ' . $code['name']; ?>
                                                            <div class="classification-option px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm <?= ($data['filters']['classification_filter'] ?? '') === $fullClassification ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>" data-value="<?= htmlspecialchars($fullClassification) ?>">
                                                                <div class="text-sm">
                                                                    <span class="font-medium text-blue-600"><?= htmlspecialchars($category['name']) ?></span>
                                                                    <span class="text-gray-400"> > </span>
                                                                    <span class="text-purple-600"><?= htmlspecialchars($subcategory['name']) ?></span>
                                                                    <span class="text-gray-400"> > </span>
                                                                    <span class="text-green-600"><?= htmlspecialchars($code['name']) ?></span>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="px-3 py-2 text-sm text-red-500">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            لا توجد تصنيفات في قاعدة البيانات
                                        </div>
                                        <div class="px-3 py-2 text-xs text-gray-500">
                                            يرجى إضافة التصنيفات من لوحة الإدارة
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="is_vip" class="block text-sm font-medium text-gray-600 mb-1">VIP</label>
                    <select id="is_vip" name="is_vip" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="1" <?= (isset($data['filters']['is_vip']) && $data['filters']['is_vip'] == '1') ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= (isset($data['filters']['is_vip']) && $data['filters']['is_vip'] == '0') ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div>
                    <label for="has_reviews" class="block text-sm font-medium text-gray-600 mb-1">Reviews Status</label>
                    <select id="has_reviews" name="has_reviews" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Tickets</option>
                        <option value="1" <?= (isset($data['filters']['has_reviews']) && $data['filters']['has_reviews'] == '1') ? 'selected' : '' ?>>With Reviews</option>
                        <option value="0" <?= (isset($data['filters']['has_reviews']) && $data['filters']['has_reviews'] == '0') ? 'selected' : '' ?>>Without Reviews</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-between items-center">
                <div class="relative inline-block text-left">
                    <div>
                        <button type="button" id="export-button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
                            Export
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div id="export-menu" class="origin-top-right absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="export-button">
                        <div class="py-1" role="none">
                            <a href="#" id="export-excel" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem">
                                <i class="fas fa-file-excel mr-2 text-green-500"></i>Export to Excel
                            </a>
                            <a href="#" id="export-json" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem">
                                <i class="fas fa-file-code mr-2 text-indigo-500"></i>Export to JSON
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="<?= URLROOT ?>/listings/tickets" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                </div>
                <div class="mt-2">
                    <div class="text-xs text-blue-600 flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Set your filters above and click "Search" to apply them
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Responsive table wrapper -->
        <div class="overflow-x-auto">
        <?php
        $is_agent = \App\Core\Auth::hasRole('agent');
        $is_editor = \App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('developer');
        $colspan = 7; // Base columns: Ticket #, Platform, Phone, Classification, Reviews, Created At, VIP
        if (!$is_agent) $colspan++; // Add Creator column
        if ($is_editor) $colspan++; // Add Actions column
        ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                    <?php if (!$is_agent): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creator</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Classification</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviews</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">VIP</th>
                    <?php if ($is_editor): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['tickets'])): ?>
                    <tr>
                        <td colspan="<?= $colspan ?>" class="text-center py-10 text-gray-500">No tickets found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['tickets'] as $ticket): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                <a href="<?= URLROOT ?>/tickets/view/<?= $ticket['ticket_id'] ?>" target="_blank" class="hover:underline"><?= htmlspecialchars($ticket['ticket_number']) ?></a>
                            </td>
                            <?php if (!$is_agent): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex flex-col">
                                    <span class="font-medium"><?= htmlspecialchars($ticket['created_by_username']) ?></span>
                                    <?php if (!empty($ticket['edited_by_username']) && $ticket['edited_by_username'] !== $ticket['created_by_username']): ?>
                                        <span class="text-xs text-gray-400">Edited by: <?= htmlspecialchars($ticket['edited_by_username']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket['team_name'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                            <?= htmlspecialchars($ticket['team_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($ticket['platform_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($ticket['phone'] ?? '') ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="space-y-1 min-w-0">
                                    <?php if (!empty($ticket['category_name']) || !empty($ticket['subcategory_name']) || !empty($ticket['code_name'])): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php if (!empty($ticket['category_name'])): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-sm">
                                                    <i class="fas fa-layer-group mr-1"></i>
                                                    <?= htmlspecialchars($ticket['category_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($ticket['subcategory_name'])): ?>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-sm">
                                                    <i class="fas fa-sitemap mr-1"></i>
                                                    <?= htmlspecialchars($ticket['subcategory_name']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($ticket['code_name'])): ?>
                                            <div class="flex flex-wrap gap-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-sm">
                                                    <i class="fas fa-code mr-1"></i>
                                                    <?= htmlspecialchars($ticket['code_name']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            غير محدد
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <!-- Reviews Column -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php if ($ticket['review_count'] > 0): ?>
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                <?= $ticket['review_count'] ?> review<?= $ticket['review_count'] > 1 ? 's' : '' ?>
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <?= round($ticket['avg_review_rating'], 1) ?>/100
                                            </span>
                                        </div>
                                        <?php if (!empty($ticket['reviews_details'])): ?>
                                            <button type="button" class="text-xs text-blue-600 hover:text-blue-800 cursor-pointer text-left" onclick="showReviewDetails('<?= htmlspecialchars($ticket['reviews_details']) ?>', '<?= htmlspecialchars($ticket['ticket_number']) ?>')">
                                                View details
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                        No reviews
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($ticket['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex flex-col space-y-1">
                                <?= $ticket['is_vip'] == 1 
                                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">VIP</span>' 
                                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Normal</span>' ?>
                                    <?php if (!empty($ticket['vip_marketer_name'])): ?>
                                        <span class="text-xs text-purple-600">by <?= htmlspecialchars($ticket['vip_marketer_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if ($is_editor): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?= URLROOT ?>/tickets/edit/<?= $ticket['ticket_id'] ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        
        <!-- Pagination Controls -->
        <div id="pagination-container" class="bg-white border-t border-gray-200 px-6 py-3">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-700 flex items-center space-x-4">
                    <span>Showing <span id="showing-from"><?= (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1 ?></span> to <span id="showing-to"><?= min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total']) ?></span> of <span id="total-results"><?= $data['pagination']['total'] ?></span> results</span>
                    <select id="page-size" class="px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="25" <?= ($data['pagination']['limit'] == 25) ? 'selected' : '' ?>>25 per page</option>
                        <option value="50" <?= ($data['pagination']['limit'] == 50) ? 'selected' : '' ?>>50 per page</option>
                        <option value="100" <?= ($data['pagination']['limit'] == 100) ? 'selected' : '' ?>>100 per page</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="prev-page" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?= ($data['pagination']['current_page'] <= 1) ? 'disabled' : '' ?>>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div id="page-numbers" class="flex space-x-1">
                        <?php
                        $current = $data['pagination']['current_page'];
                        $total = $data['pagination']['total_pages'];
                        $start = max(1, $current - 2);
                        $end = min($total, $current + 2);
                        
                        if ($start > 1) {
                            echo '<button class="page-btn px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-page="1">1</button>';
                            if ($start > 2) echo '<span class="px-2 text-gray-500">...</span>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++) {
                            $activeClass = ($i == $current) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 hover:bg-gray-50';
                            echo '<button class="page-btn px-3 py-1 text-sm ' . $activeClass . ' border rounded-md" data-page="' . $i . '">' . $i . '</button>';
                        }
                        
                        if ($end < $total) {
                            if ($end < $total - 1) echo '<span class="px-2 text-gray-500">...</span>';
                            echo '<button class="page-btn px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-page="' . $total . '">' . $total . '</button>';
                        }
                        ?>
                    </div>
                    <button id="next-page" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?= ($data['pagination']['current_page'] >= $data['pagination']['total_pages']) ? 'disabled' : '' ?>>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Details Modal -->
    <div id="review-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Review Details</h3>
                        <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 overflow-y-auto max-h-80" id="modal-content">
                    <!-- Review details will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
// URLROOT is already defined in header.php
let searchTimeout = null;
let userSearchTimeout = null;

// Show review details modal
function showReviewDetails(reviewsDetails, ticketNumber) {
    const modal = document.getElementById('review-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    
    modalTitle.textContent = `Reviews for Ticket ${ticketNumber}`;
    
    if (reviewsDetails) {
        const reviews = reviewsDetails.split('|');
        const reviewsHtml = reviews.map(review => {
            const [reviewer, rating] = review.split(':');
            return `
                <div class="border-b border-gray-200 pb-3 mb-3 last:border-b-0">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-900">${reviewer}</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">${rating}/100</span>
                    </div>
                </div>
            `;
        }).join('');
        modalContent.innerHTML = reviewsHtml;
    } else {
        modalContent.innerHTML = '<p class="text-gray-500">No review details available.</p>';
    }
    
    modal.classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('review-modal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize page functionality
    initializeSearchSuggestions();
    initializeUserDropdown();
    initializePagination();
    initializeFilters();
    initializeDateRangePicker();
    initializeQuickDateButtons();
    initializeExportHandlers();

    // Smart search with suggestions
    function initializeSearchSuggestions() {
        const searchInput = document.getElementById('search_term');
        const suggestionsDiv = document.getElementById('search-suggestions');

        if (!searchInput || !suggestionsDiv) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                suggestionsDiv.classList.add('hidden');
                return;
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchSuggestions(query, suggestionsDiv);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.classList.add('hidden');
            }
        });
    }

    function fetchSuggestions(query, container) {
        console.log('Fetching suggestions for:', query);
        
        fetch(`${URLROOT}/listings/search_suggestions_api?q=${encodeURIComponent(query)}&type=ticket`)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(suggestions => {
                console.log('Suggestions received:', suggestions);
                displaySuggestions(suggestions, container, 'search_term');
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                // Show manual suggestions as fallback
                showManualSuggestions(query, container);
            });
    }

    function showManualSuggestions(query, container) {
        // Extract visible ticket numbers and phones from the current table
        const tableRows = document.querySelectorAll('tbody tr');
        const suggestions = [];
        
        tableRows.forEach(row => {
            const ticketCell = row.querySelector('td:first-child a');
            const phoneCell = row.querySelector('td:nth-child(4)'); // Adjust based on columns
            
            if (ticketCell) {
                const ticketNumber = ticketCell.textContent.trim();
                if (ticketNumber.toLowerCase().includes(query.toLowerCase())) {
                    suggestions.push({
                        value: ticketNumber,
                        label: `${ticketNumber} - تذكرة`
                    });
                }
            }
            
            if (phoneCell) {
                const phone = phoneCell.textContent.trim();
                if (phone.includes(query)) {
                    suggestions.push({
                        value: phone,
                        label: `${phone} - هاتف`
                    });
                }
            }
        });

        // Remove duplicates
        const uniqueSuggestions = suggestions.filter((suggestion, index, self) => 
            index === self.findIndex(s => s.value === suggestion.value)
        );

        displaySuggestions(uniqueSuggestions.slice(0, 5), container, 'search_term');
    }

    function displaySuggestions(suggestions, container, targetInputId) {
        if (suggestions.length === 0) {
            container.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">لا توجد اقتراحات</div>';
            container.classList.remove('hidden');
            return;
        }

        container.innerHTML = suggestions.map(suggestion => 
            `<div class="suggestion-item px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-b-0" data-value="${suggestion.value}">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                ${suggestion.label || suggestion.value}
            </div>`
        ).join('');

        container.classList.remove('hidden');

        // Add click handlers
        container.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                document.getElementById(targetInputId).value = item.dataset.value;
                container.classList.add('hidden');
                console.log('Suggestion selected:', item.dataset.value, '- Click Search to apply');
            });
        });
    }

    // User dropdown functionality
    function initializeUserDropdown() {
        const userSearchInput = document.getElementById('created_by_search');
        const userDropdown = document.getElementById('user-suggestions');
        const userDropdownSearch = document.getElementById('user_dropdown_search');
        const hiddenInput = document.getElementById('created_by');

        if (!userSearchInput || !userDropdown) {
            console.log('User dropdown elements not found');
            return; // Skip if user is agent or elements missing
        }

        console.log('Initializing user dropdown...');

        // Show/hide dropdown
        userSearchInput.addEventListener('click', () => {
            console.log('User dropdown clicked');
            userDropdown.classList.remove('hidden');
            if (userDropdownSearch) {
                userDropdownSearch.focus();
                userDropdownSearch.value = '';
            }
            // Reset all options visibility
            const options = userDropdown.querySelectorAll('.user-option');
            console.log('Found user options:', options.length);
            options.forEach(option => option.style.display = 'block');
        });

        // Search within users
        if (userDropdownSearch) {
            userDropdownSearch.addEventListener('input', (e) => {
                const searchValue = e.target.value.toLowerCase();
                const options = userDropdown.querySelectorAll('.user-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchValue) || option.getAttribute('data-id') === '') {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });

            // Prevent dropdown from closing when clicking on search input
            userDropdownSearch.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Handle user selection
        userDropdown.addEventListener('click', (e) => {
            const option = e.target.closest('.user-option');
            if (option) {
                const userId = option.getAttribute('data-id');
                const username = option.getAttribute('data-username');
                
                console.log('User selected:', userId, username, '- Click Search to apply');
                
                hiddenInput.value = userId;
                userSearchInput.value = username || 'جميع المستخدمين';
                userDropdown.classList.add('hidden');
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userSearchInput.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Initialize display value
        if (hiddenInput.value) {
            const selectedOption = userDropdown.querySelector(`[data-id="${hiddenInput.value}"]`);
            if (selectedOption) {
                userSearchInput.value = selectedOption.getAttribute('data-username') || 'جميع المستخدمين';
            }
        } else {
            userSearchInput.value = 'جميع المستخدمين';
        }
    }

    // Pagination functionality
    function initializePagination() {
        // Page buttons
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                goToPage(parseInt(btn.dataset.page));
            });
        });

        // Previous/Next buttons
        document.getElementById('prev-page')?.addEventListener('click', () => {
            const currentPage = <?= $data['pagination']['current_page'] ?>;
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });

        document.getElementById('next-page')?.addEventListener('click', () => {
            const currentPage = <?= $data['pagination']['current_page'] ?>;
            const totalPages = <?= $data['pagination']['total_pages'] ?>;
            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });

        // Page size selector
        document.getElementById('page-size')?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('limit', this.value);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        });
    }

    function goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    }

    // Filters functionality
    function initializeFilters() {
        // Clear filters button
        document.getElementById('clear-filters')?.addEventListener('click', () => {
            window.location.href = `${URLROOT}/listings/tickets`;
        });

        // Remove auto-filtering for select elements - manual submit only
        // No auto-filtering for better UX

        // Classification dropdown
        initializeClassificationDropdown();
        
        // Search input - suggestions only, no auto-submit
        document.getElementById('search_term')?.addEventListener('input', function() {
            console.log('Search input changed:', this.value);
            // Only show suggestions, no auto-submit
        });
    }

    function initializeClassificationDropdown() {
    const classificationSearch = document.getElementById('classification_search');
    const classificationDropdown = document.getElementById('classification_dropdown');
    const classificationDropdownSearch = document.getElementById('classification_dropdown_search');
    const classificationFilter = document.getElementById('classification_filter');
    
        if (!classificationSearch) return;

    classificationSearch.addEventListener('click', () => {
        classificationDropdown.classList.remove('hidden');
        classificationDropdownSearch.focus();
    });
    
    classificationDropdownSearch.addEventListener('input', (e) => {
        const searchValue = e.target.value.toLowerCase();
        const options = classificationDropdown.querySelectorAll('.classification-option');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchValue) ? 'block' : 'none';
            });
        });

    classificationDropdown.addEventListener('click', (e) => {
        if (e.target.classList.contains('classification-option')) {
            const value = e.target.getAttribute('data-value');
            const text = value || 'All Classifications';
            
            classificationFilter.value = value;
            classificationSearch.value = text;
            classificationDropdown.classList.add('hidden');
                console.log('Classification selected:', text, '- Click Search to apply');
            }
        });

    document.addEventListener('click', (e) => {
        if (!classificationSearch.contains(e.target) && !classificationDropdown.contains(e.target)) {
            classificationDropdown.classList.add('hidden');
        }
    });
    }

    function applyFilters() {
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.set('page', '1'); // Reset to first page
        
        console.log('applyFilters called');
        console.log('Form data:', Object.fromEntries(formData));
        console.log('URL params:', params.toString());
        console.log('Final URL:', `${URLROOT}/listings/tickets?${params.toString()}`);
        
        window.location.href = `${URLROOT}/listings/tickets?${params.toString()}`;
    }

    // Date range picker
    function initializeDateRangePicker() {
        const dateRangeInput = document.getElementById('date_range');
        
        if (!dateRangeInput) {
            console.log('Date range input not found');
            return;
        }

        console.log('Initializing date range picker...');

        // Make the input clickable
        dateRangeInput.style.cursor = 'pointer';
        dateRangeInput.removeAttribute('readonly');
        
        dateRangeInput._flatpickr = flatpickr(dateRangeInput, {
        mode: 'range',
        dateFormat: 'Y-m-d',
            locale: {
                rangeSeparator: ' إلى ',
                firstDayOfWeek: 6, // Saturday
                weekdays: {
                    shorthand: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
                    longhand: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
                },
                months: {
                    shorthand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                    longhand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
                }
            },
            allowInput: true,
            clickOpens: true,
        onChange: function(selectedDates, dateStr, instance) {
                console.log('Date changed:', selectedDates, dateStr);
                
            if (selectedDates.length === 2) {
                    const startDate = instance.formatDate(selectedDates[0], "Y-m-d");
                    const endDate = instance.formatDate(selectedDates[1], "Y-m-d");
                    
                    document.getElementById('start_date').value = startDate;
                    document.getElementById('end_date').value = endDate;
                    
                    console.log('Date range set:', startDate, 'to', endDate, '- Click Search to apply');
                    // No auto-submit - user must click Search button
                } else if (selectedDates.length === 0) {
                    // Clear dates
                    document.getElementById('start_date').value = '';
                    document.getElementById('end_date').value = '';
                }
            },
            onReady: function() {
                console.log('Flatpickr ready');
            }
        });

        // Initialize with existing values if any
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            dateRangeInput._flatpickr.setDate([startDate, endDate]);
            // Update display text
            dateRangeInput.value = `${startDate} إلى ${endDate}`;
            console.log('Initialized date range:', startDate, 'to', endDate);
        } else {
            console.log('No initial date range found');
        }
    }

    // Quick date buttons
    function initializeQuickDateButtons() {
        console.log('Initializing quick date buttons...');
        
        document.querySelectorAll('.quick-date-btn').forEach(btn => {
            console.log('Adding listener to button:', btn.textContent);
            
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const days = parseInt(btn.dataset.days);
                console.log('Quick date button clicked, days:', days);
                
                setQuickDateRange(days);
            });
        });

        // Add event listener for "كل الفترات" button
        const clearButton = document.querySelector('button[onclick="clearDateRange()"]');
        if (clearButton) {
            clearButton.removeAttribute('onclick');
            clearButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                clearDateRange();
            });
        }
    }

    function setQuickDateRange(days) {
        console.log('Setting quick date range for days:', days);
        
        const today = new Date();
        const startDate = new Date();
        
        if (days === 0) {
            // Today only
            startDate.setDate(today.getDate());
        } else {
            // Last X days
            startDate.setDate(today.getDate() - days);
        }

        const startDateStr = startDate.toISOString().split('T')[0];
        const endDateStr = today.toISOString().split('T')[0];
        
        console.log('Setting dates:', startDateStr, 'to', endDateStr);
        
        document.getElementById('start_date').value = startDateStr;
        document.getElementById('end_date').value = endDateStr;
        
        // Update flatpickr and display if it exists
        const dateRangeInput = document.getElementById('date_range');
        if (dateRangeInput && dateRangeInput._flatpickr) {
            dateRangeInput._flatpickr.setDate([startDateStr, endDateStr]);
        } else {
            // Fallback: update display manually
            dateRangeInput.value = `${startDateStr} إلى ${endDateStr}`;
        }
        
        console.log('Quick date range set - Click Search to apply');
    }

    window.clearDateRange = function() {
        console.log('Clearing date range');
        
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        document.getElementById('date_range').value = '';
        
        // Clear flatpickr if it exists
        const dateRangeInput = document.getElementById('date_range');
        if (dateRangeInput._flatpickr) {
            dateRangeInput._flatpickr.clear();
        }
        
        console.log('Date range cleared - Click Search to apply');
    }

    // Export handlers
    function initializeExportHandlers() {
    const exportButton = document.getElementById('export-button');
    const exportMenu = document.getElementById('export-menu');
    const exportExcel = document.getElementById('export-excel');
    const exportJson = document.getElementById('export-json');

        exportButton?.addEventListener('click', () => {
        exportMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
            if (!exportButton?.contains(event.target) && !exportMenu?.contains(event.target)) {
                exportMenu?.classList.add('hidden');
        }
    });

    const exportHandler = (format) => {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('export', format);
        window.location.href = currentUrl.href;
    };

        exportExcel?.addEventListener('click', (e) => {
        e.preventDefault();
        exportHandler('excel');
    });

        exportJson?.addEventListener('click', (e) => {
        e.preventDefault();
        exportHandler('json');
    });
    }
});
</script>

<!-- Debug Information -->
<script>
    console.log('=== TICKETS PAGE DEBUG INFO ===');
    console.log('Classification data:', <?= json_encode($data['ticket_categories'] ?? []) ?>);
    console.log('Users count:', <?= count($data['users'] ?? []) ?>);
    console.log('First 3 users:', <?= json_encode(array_slice($data['users'] ?? [], 0, 3)) ?>);
    console.log('Current filters:', <?= json_encode($data['filters'] ?? []) ?>);
    console.log('Pagination:', <?= json_encode($data['pagination'] ?? []) ?>);
    console.log('Stats:', <?= json_encode($data['stats'] ?? []) ?>);
    console.log('Total tickets found:', <?= $data['pagination']['total'] ?? 0 ?>);
    console.log('Current search term:', '<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>');
    
    // Debug info for search troubleshooting
    if ('<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>') {
        console.log('🔍 SEARCH DEBUGGING:');
        console.log('- Search term provided:', '<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>');
        console.log('- Results returned:', <?= $data['pagination']['total'] ?? 0 ?>);
        console.log('- Check PHP error logs for SQL queries and results');
        console.log('- Try test_db.php to verify database connection and data');
    }
    
    // Show sample ticket numbers for testing
    const sampleTickets = [
        <?php 
        if (!empty($data['tickets'])) {
            $sampleTickets = array_slice($data['tickets'], 0, 3);
            foreach ($sampleTickets as $index => $ticket) {
                echo '"' . htmlspecialchars($ticket['ticket_number']) . '"';
                if ($index < count($sampleTickets) - 1) echo ', ';
            }
        }
        ?>
    ];
    console.log('Sample ticket numbers for testing:', sampleTickets);
    console.log('Try searching for one of these ticket numbers to test the search feature.');
    console.log('================================');
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
