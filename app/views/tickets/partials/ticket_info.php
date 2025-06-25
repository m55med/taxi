<h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
    <i class="fas fa-ticket-alt text-gray-400 mr-3"></i>
    Ticket Information
</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
    <div class="flex items-center">
        <i class="fas fa-ticket-alt text-gray-400 mr-3 w-5 text-center"></i><strong>Ticket #:</strong>
        <span class="ml-2"><?= htmlspecialchars($data['ticket']['ticket_number']) ?></span>
        <?php if ($data['ticket']['is_vip']): ?>
            <span class="bg-yellow-400 text-white text-xs font-bold ml-2 px-2.5 py-0.5 rounded-full">VIP</span>
        <?php endif; ?>
    </div>
    <div class="flex items-center">
        <i class="fas fa-desktop text-gray-400 mr-3 w-5 text-center"></i><strong>Platform:</strong>
        <span class="ml-2"><?= htmlspecialchars($data['ticket']['platform_name']) ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-phone text-gray-400 mr-3 w-5 text-center"></i><strong>Phone:</strong>
        <span class="ml-2" dir="ltr"><?= htmlspecialchars($data['ticket']['phone'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-globe-africa text-gray-400 mr-3 w-5 text-center"></i><strong>Country:</strong>
        <span class="ml-2"><?= htmlspecialchars($data['ticket']['country_name'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-user text-gray-400 mr-3 w-5 text-center"></i><strong>Created By:</strong>
        <span class="ml-2"><?= htmlspecialchars($data['ticket']['creator_username']) ?></span>
    </div>
    <div class="flex items-center">
        <i class="fas fa-user-tie text-gray-400 mr-3 w-5 text-center"></i><strong>Team Leader:</strong>
        <span class="ml-2"><?= htmlspecialchars($data['ticket']['leader_username'] ?? 'N/A') ?></span>
    </div>
    <div class="flex items-center col-span-1 md:col-span-2">
        <i class="fas fa-sitemap text-gray-400 mr-3 w-5 text-center"></i><strong>Classification:</strong>
        <span class="ml-2 text-sm"><?= htmlspecialchars($data['ticket']['category_name']) ?> / <?= htmlspecialchars($data['ticket']['subcategory_name']) ?> / <?= htmlspecialchars($data['ticket']['code_name']) ?></span>
    </div>
    <div class="flex items-center col-span-1 md:col-span-2">
        <i class="far fa-clock text-gray-400 mr-3 w-5 text-center"></i><strong>Created At:</strong>
        <span class="ml-2" dir="ltr"><?= date('Y-m-d H:i', strtotime($data['ticket']['created_at'])) ?></span>
    </div>
    <div class="col-span-1 md:col-span-2">
        <strong class="flex items-center"><i class="far fa-file-alt text-gray-400 mr-3 w-5 text-center"></i>Notes:</strong>
        <p class="text-gray-800 mt-2 bg-gray-50 p-3 rounded-md whitespace-pre-wrap border"><?= nl2br(htmlspecialchars($data['ticket']['notes'] ?? 'None')) ?></p>
    </div>
</div> 