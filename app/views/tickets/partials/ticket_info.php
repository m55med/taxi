<h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
    <i class="fas fa-ticket-alt text-gray-400 mr-3"></i>
    بيانات التذكرة
</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
    <div class="flex items-center">
        <i class="fas fa-ticket-alt text-gray-400 ml-3 w-5 text-center"></i><strong>رقم التذكرة:</strong>
        <span class="mr-2"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
        <?php if ($ticket['is_vip']): ?>
            <span class="bg-yellow-400 text-white text-xs font-bold mr-2 px-2.5 py-0.5 rounded-full">VIP</span>
        <?php endif; ?>
    </div>
    <div class="flex items-center">
        <i class="fas fa-desktop text-gray-400 ml-3 w-5 text-center"></i><strong>المنصة:</strong>
        <span class="mr-2"><?= htmlspecialchars($ticket['platform_name']) ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-phone text-gray-400 ml-3 w-5 text-center"></i><strong>رقم الهاتف:</strong>
        <span class="mr-2" dir="ltr"><?= htmlspecialchars($ticket['phone'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-globe-africa text-gray-400 ml-3 w-5 text-center"></i><strong>الدولة:</strong>
        <span class="mr-2"><?= htmlspecialchars($ticket['country_name'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-user text-gray-400 ml-3 w-5 text-center"></i><strong>الموظف:</strong>
        <span class="mr-2"><?= htmlspecialchars($ticket['creator_username']) ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-user-tie text-gray-400 ml-3 w-5 text-center"></i><strong>قائد الفريق:</strong>
        <span class="mr-2"><?= htmlspecialchars($ticket['leader_username'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center col-span-1 md:col-span-2">
        <i class="fas fa-sitemap text-gray-400 ml-3 w-5 text-center"></i><strong>التصنيف:</strong>
        <span class="mr-2 text-sm"><?= htmlspecialchars($ticket['category_name']) ?> / <?= htmlspecialchars($ticket['subcategory_name']) ?> / <?= htmlspecialchars($ticket['code_name']) ?></span>
    </div>
    <div class="flex items-center col-span-1 md:col-span-2">
        <i class="far fa-clock text-gray-400 ml-3 w-5 text-center"></i><strong>تاريخ الإنشاء:</strong>
        <span class="mr-2" dir="ltr"><?= date('Y-m-d H:i', strtotime($ticket['created_at'])) ?></span>
    </div>
    <div class="col-span-1 md:col-span-2">
        <strong class="flex items-center"><i class="far fa-file-alt text-gray-400 ml-3 w-5 text-center"></i>الملاحظات:</strong>
        <p class="text-gray-800 mt-2 bg-gray-50 p-3 rounded-md whitespace-pre-wrap border"><?= nl2br(htmlspecialchars($ticket['notes'] ?? 'لا يوجد')) ?></p>
    </div>
</div> 