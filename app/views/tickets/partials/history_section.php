<?php if (!empty($data['ticketHistory']) && count($data['ticketHistory']) > 1) : ?>
<div x-data="{ activeAccordion: <?= $data['ticketHistory'][0]['id'] ?? 'null' ?> }">
    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">سجل التغييرات</h3>
    <div class="space-y-2">
        <?php foreach ($data['ticketHistory'] as $index => $history) : ?>
            <div class="rounded-lg border border-gray-200 overflow-hidden">
                <button @click="activeAccordion = activeAccordion === <?= $history['id'] ?> ? null : <?= $history['id'] ?>" class="w-full text-left p-4 flex justify-between items-center transition <?= $index === 0 ? 'bg-green-100 hover:bg-green-200' : 'bg-gray-100 hover:bg-gray-200' ?>">
                    <div class="font-bold <?= $index === 0 ? 'text-green-800' : 'text-gray-800' ?>">
                        <i class="fas fa-history mr-2"></i>
                        <?= $index === 0 ? 'الحالة الحالية' : 'إصدار سابق' ?> (بواسطة: <?= htmlspecialchars($history['editor_name']) ?>)
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 mr-4">
                            <i class="fas fa-clock mr-1"></i>
                            <?= htmlspecialchars($history['created_at']) ?>
                        </span>
                        <i class="fas" :class="{ 'fa-chevron-down': activeAccordion !== <?= $history['id'] ?>, 'fa-chevron-up': activeAccordion === <?= $history['id'] ?> }"></i>
                    </div>
                </button>
                
                <div x-show="activeAccordion === <?= $history['id'] ?>" x-transition class="p-4 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-500 font-semibold">قائد الفريق</span>
                            <span><?= htmlspecialchars($history['leader_name']) ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-gray-500 font-semibold">المنصة</span>
                            <span><?= htmlspecialchars($history['platform_name']) ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-gray-500 font-semibold">الهاتف</span>
                            <span><?= htmlspecialchars($history['phone'] ?: 'N/A') ?></span>
                        </div>
                         <div class="flex flex-col">
                            <span class="text-gray-500 font-semibold">الدولة</span>
                            <span><?= htmlspecialchars($history['country_name'] ?: 'N/A') ?></span>
                        </div>
                        <div class="md:col-span-2 flex flex-col">
                            <span class="text-gray-500 font-semibold">التصنيف</span>
                            <div class="flex items-center space-x-2 text-gray-700">
                                <span><?= htmlspecialchars($history['category_name']) ?></span>
                                <i class="fas fa-angle-right text-gray-400"></i>
                                <span><?= htmlspecialchars($history['subcategory_name']) ?></span>
                                <i class="fas fa-angle-right text-gray-400"></i>
                                <span class="font-bold"><?= htmlspecialchars($history['code_name']) ?></span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-gray-500 font-semibold">ملاحظات</span>
                            <p class="whitespace-pre-wrap mt-1 p-2 bg-gray-50 rounded border text-gray-800"><?= htmlspecialchars($history['notes'] ?: 'لا يوجد') ?></p>
                        </div>

                        <!-- Coupons for this specific detail version -->
                        <?php if (!empty($history['coupons'])) : ?>
                        <div class="md:col-span-2">
                            <span class="text-gray-500 font-semibold">الكوبونات المضافة في هذا الإصدار</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <?php foreach ($history['coupons'] as $coupon) : ?>
                                    <div class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center">
                                        <i class="fas fa-tag mr-1.5"></i>
                                        <?= htmlspecialchars($coupon['code']) ?> (<?= htmlspecialchars($coupon['value']) ?>)
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?> 