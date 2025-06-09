<?php if (isset($driver)): ?>
<!-- Call History -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-6">سجل المكالمات</h3>
    <?php if (empty($call_history)): ?>
        <div class="text-center py-8">
            <i class="fas fa-history text-4xl text-gray-300 mb-2"></i>
            <p class="text-gray-500">لا يوجد سجل مكالمات سابق</p>
        </div>
    <?php else: ?>
        <div class="relative border-r-2 border-gray-200 pr-8 space-y-8">
            <?php foreach ($call_history as $call): ?>
                <div class="call-history-item">
                    <div class="timeline-icon">
                        <i class="fas fa-phone-alt text-sm text-indigo-500"></i>
                    </div>
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($call['staff_name']) ?></span>
                            <p class="text-sm text-gray-500">
                                <?= date('Y/m/d H:i', strtotime($call['created_at'])) ?>
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            <?php
                            switch($call['call_status']) {
                                case 'answered': echo 'bg-green-100 text-green-800'; break;
                                case 'no_answer': echo 'bg-red-100 text-red-800'; break;
                                case 'busy': echo 'bg-yellow-100 text-yellow-800'; break;
                                case 'not_available': echo 'bg-orange-100 text-orange-800'; break;
                                case 'wrong_number': echo 'bg-red-100 text-red-800'; break;
                                case 'rescheduled': echo 'bg-blue-100 text-blue-800'; break;
                                default: echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <?= $call_status_text[$call['call_status']] ?? $call['call_status'] ?>
                        </span>
                    </div>
                    <?php if ($call['notes']): ?>
                        <p class="text-gray-600 bg-gray-50 p-3 rounded-md text-sm"><?= nl2br(htmlspecialchars($call['notes'])) ?></p>
                    <?php endif; ?>
                    <?php if ($call['next_call_at']): ?>
                        <div class="mt-2 text-sm text-indigo-600 font-medium">
                            <i class="far fa-clock ml-1"></i>
                            <span>المكالمة القادمة:</span>
                            <span class="font-mono tracking-wide"><?= date('Y/m/d H:i', strtotime($call['next_call_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?> 