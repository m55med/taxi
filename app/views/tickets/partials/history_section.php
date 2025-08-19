<?php
?>

<?php if (!empty($data['ticketHistory'])) : ?>
<div class="mt-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-5 border-b pb-3">
        <i class="fas fa-history text-gray-400 mr-2"></i>
        Ticket Updates History
    </h3>
    <div class="space-y-6">
        <?php foreach ($data['ticketHistory'] as $index => $history) : ?>
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 <?= $index === 0 ? 'border-green-500' : 'border-blue-500' ?>">
                <!-- History Item Header -->
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h4 class="text-lg font-bold <?= $index === 0 ? 'text-green-700' : 'text-blue-700' ?>">
                        <?= $index === 0 ? 'Current Status' : 'Previous Version' ?>
                    </h4>
                    
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-user-edit mr-1"></i>
                            Updated by: <strong><?= htmlspecialchars($history['editor_name']) ?></strong>
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock mr-1"></i>
                            <?= date('Y-m-d H:i', strtotime($history['created_at'])) ?>
                        </div>

                        <?php if ($index === 0 && (\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('developer'))): ?>
                            <a href="<?= BASE_URL ?>/tickets/edit/<?= $history['id'] ?>" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 text-xs font-medium flex items-center">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Details
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- History Item Body -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div class="flex items-center">
                        <?php if ($history['is_vip']): ?>
                            <span class="bg-yellow-400 text-white text-xs font-bold mr-3 px-2.5 py-0.5 rounded-full">VIP</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center md:col-start-2">
                        <i class="fas fa-desktop text-gray-400 mr-3 w-5 text-center"></i><strong>Platform:</strong>
                        <span class="ml-2"><?= htmlspecialchars($history['platform_name']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-phone text-gray-400 mr-3 w-5 text-center"></i><strong>Phone:</strong>
                        <span class="ml-2" dir="ltr"><?= htmlspecialchars($history['phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-globe-africa text-gray-400 mr-3 w-5 text-center"></i><strong>Country:</strong>
                        <span class="ml-2"><?= htmlspecialchars($history['country_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-user-tie text-gray-400 mr-3 w-5 text-center"></i><strong>Team Leader:</strong>
                        <span class="ml-2"><?= htmlspecialchars($history['leader_username'] ?? 'N/A') ?></span>
                    </div>

                    <?php if (!empty($history['marketer_name'])) : ?>
                        <div class="flex items-center">
                            <i class="fas fa-bullhorn text-purple-400 mr-3 w-5 text-center"></i><strong>Assigned Marketer:</strong>
                            <span class="ml-2 font-semibold text-purple-700"><?= htmlspecialchars($history['marketer_name']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center col-span-1 md:col-span-2">
                        <i class="fas fa-sitemap text-gray-400 mr-3 w-5 text-center"></i><strong>Classification:</strong>
                        <span class="ml-2 text-sm"><?= htmlspecialchars($history['category_name']) ?> / <?= htmlspecialchars($history['subcategory_name']) ?> / <?= htmlspecialchars($history['code_name']) ?></span>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <strong class="flex items-center"><i class="far fa-file-alt text-gray-400 mr-3 w-5 text-center"></i>Notes:</strong>
                        <p class="text-gray-800 mt-2 bg-gray-50 p-3 rounded-md whitespace-pre-wrap border"><?= nl2br(htmlspecialchars($history['notes'] ?? 'None')) ?></p>
                    </div>

                    <!-- Coupons for this specific detail version -->
                    <?php if (!empty($history['coupons'])) : ?>
                    <div class="md:col-span-2 mt-4">
                        <h5 class="font-semibold text-gray-600 flex items-center mb-2">
                            <i class="fas fa-tags text-gray-400 mr-3"></i>
                            Coupons used in this update
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($history['coupons'] as $coupon) : ?>
                                <div class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full flex items-center border border-blue-200">
                                    <i class="fas fa-tag mr-2"></i>
                                    <span><?= htmlspecialchars($coupon['code']) ?> (Value: <?= htmlspecialchars($coupon['value']) ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Review Section for this specific detail version -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <?php
                    // We now pass the entire $data array so the partial has access to currentUser
                    render_partial('tickets/partials/reviews_section.php', [
                        'reviews' => $history['reviews'] ?? [],
                        'add_review_url' => BASE_URL . "/review/add/ticket_detail/" . $history['id'],
                        'can_add_review' => in_array($data['currentUser']['role'], ['Quality', 'Team_leader', 'admin', 'developer']),
                        'currentUser' => $data['currentUser'],
                        'ticket_categories' => $data['ticket_categories'] ?? [] // Pass categories down
                    ]);
                    ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>
<?php endif; ?> 