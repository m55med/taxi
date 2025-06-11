<?php if (isset($driver)): ?>
<!-- Call History -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-6">سجل النشاطات</h3>
    <?php if (empty($call_history)): ?>
        <div class="text-center py-8">
            <i class="fas fa-history text-4xl text-gray-300 mb-2"></i>
            <p class="text-gray-500">لا يوجد سجل نشاطات سابق</p>
        </div>
    <?php else: ?>
        <div class="relative border-r-2 border-gray-200 pr-8 space-y-8">
            <?php foreach ($call_history as $event): ?>
                <div class="history-item">
                    <?php if ($event['event_type'] === 'call'): ?>
                        <!-- Call Event -->
                        <div class="timeline-icon bg-indigo-100">
                            <i class="fas fa-phone-alt text-sm text-indigo-500"></i>
                        </div>
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($event['actor_name'] ?? 'موظف غير معروف') ?></p>
                                <p class="text-sm text-gray-500">
                                    <?= date('Y/m/d H:i', strtotime($event['event_date'])) ?>
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                <?php
                                $status = $event['details'];
                                switch($status) {
                                    case 'answered': echo 'bg-green-100 text-green-800'; break;
                                    case 'no_answer': echo 'bg-red-100 text-red-800'; break;
                                    case 'busy': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'not_available': echo 'bg-orange-100 text-orange-800'; break;
                                    case 'wrong_number': echo 'bg-gray-100 text-gray-800'; break;
                                    case 'rescheduled': echo 'bg-blue-100 text-blue-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= $call_status_text[$status] ?? htmlspecialchars($status) ?>
                            </span>
                        </div>
                        <?php if (!empty($event['notes'])): ?>
                            <p class="text-gray-600 bg-gray-50 p-3 rounded-md text-sm"><?= nl2br(htmlspecialchars($event['notes'])) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($event['next_call_at'])): ?>
                            <div class="mt-2 text-sm text-indigo-600 font-medium">
                                <i class="far fa-clock ml-1"></i>
                                <span>المكالمة القادمة:</span>
                                <span class="font-mono tracking-wide"><?= date('Y/m/d H:i', strtotime($event['next_call_at'])) ?></span>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($event['event_type'] === 'assignment'): ?>
                        <!-- Assignment Event -->
                        <div class="timeline-icon bg-purple-100">
                             <i class="fas fa-exchange-alt text-sm text-purple-500"></i>
                        </div>
                        <div class="flex justify-between items-start mb-1">
                             <div>
                                <p class="font-semibold text-gray-800">عملية تحويل</p>
                                <p class="text-sm text-gray-500">
                                    <?= date('Y/m/d H:i', strtotime($event['event_date'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-gray-600 bg-gray-50 p-3 rounded-md text-sm space-y-1">
                            <p>تم التحويل من: <strong class="font-medium"><?= htmlspecialchars($event['actor_name'] ?? 'غير معروف') ?></strong></p>
                            <p>إلى: <strong class="font-medium"><?= htmlspecialchars($event['recipient_name'] ?? 'غير معروف') ?></strong></p>
                            <?php if (!empty($event['notes'])): ?>
                                <p class="pt-2 border-t border-gray-200 mt-2">الملاحظة: <?= nl2br(htmlspecialchars($event['notes'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?> 