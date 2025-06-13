<!-- Coupons -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
        <i class="fas fa-tags text-gray-400 mr-3"></i>
        الكوبونات المستخدمة
    </h2>
    <?php if (!empty($ticket['coupons'])): ?>
        <ul class="space-y-3">
            <?php foreach ($ticket['coupons'] as $coupon): ?>
                <li class="flex items-center justify-between p-2 bg-gray-50 rounded-md border">
                    <span class="font-mono text-sm text-green-600 font-bold"><?= htmlspecialchars($coupon['code']) ?></span>
                    <?php if (isset($coupon['value']) && $coupon['value']): ?>
                         <span class="text-sm text-gray-600">(القيمة: <?= htmlspecialchars($coupon['value']) ?>)</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-500">لا توجد كوبونات مستخدمة في هذه التذكرة.</p>
    <?php endif; ?>
</div>

<!-- Related Tickets -->
<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
        <i class="fas fa-history text-gray-400 mr-3"></i>
        تذاكر أخرى للعميل
    </h2>
    <?php if (!empty($relatedTickets)): ?>
         <ul class="space-y-3">
            <?php foreach ($relatedTickets as $relatedTicket): ?>
                 <li class="p-2 bg-gray-50 rounded-md border">
                    <a href="<?= BASE_PATH . '/tickets/details/' . $relatedTicket['id'] ?>" class="font-semibold text-blue-600 hover:underline">
                        تذكرة #<?= htmlspecialchars($relatedTicket['ticket_number']) ?>
                    </a>
                    <p class="text-xs text-gray-500 mt-1">
                        بتاريخ: <?= date('Y-m-d', strtotime($relatedTicket['created_at'])) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
         <p class="text-gray-500">لا توجد تذاكر أخرى مرتبطة بهذا الرقم.</p>
    <?php endif; ?>
</div> 


