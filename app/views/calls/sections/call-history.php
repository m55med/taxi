<?php
$call_history = $data['call_history'] ?? [];
$call_status_text = $data['call_status_text'] ?? [];
?>

<?php if (isset($data['driver'])): ?>
<!-- Call History -->
<div>
    <h3 class="text-xl font-semibold mb-6 text-gray-800">Activity History</h3>
    <?php if (empty($call_history)): ?>
        <div class="text-center py-12">
            <i class="fas fa-history text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 font-medium">No activity history found.</p>
        </div>
    <?php else: ?>
        <div class="relative border-l-2 border-gray-200 pl-8 space-y-8">
            <?php foreach ($call_history as $event): ?>
                <div class="history-item relative">
                    <?php if ($event['event_type'] === 'call'): ?>
                        <!-- Call Event -->
                        <div class="timeline-icon bg-indigo-100 ring-4 ring-white">
                            <i class="fas fa-phone-alt text-sm text-indigo-500"></i>
                        </div>
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($event['actor_name'] ?? 'Unknown Staff') ?></p>
                                <p class="text-sm text-gray-500">
                                    <?= date('d M Y, H:i', strtotime($event['event_date'])) ?>
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                <?php
                                $status = $event['details'];
                                switch($status) {
                                    case 'answered': echo 'bg-green-100 text-green-800'; break;
                                    case 'no_answer': echo 'bg-red-100 text-red-800'; break;
                                    case 'busy': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'not_available': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'wrong_number': echo 'bg-orange-100 text-orange-800'; break;
                                    case 'rescheduled': echo 'bg-purple-100 text-purple-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= $call_status_text[$status] ?? htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        </div>
                        <?php if (!empty($event['notes'])): ?>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-md text-sm mt-2"><?= nl2br(htmlspecialchars($event['notes'])) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($event['next_call_at'])): ?>
                            <div class="mt-2 text-sm text-indigo-600 font-medium flex items-center">
                                <i class="far fa-clock mr-2"></i>
                                <span>Next Call:</span>
                                <span class="ml-2 font-mono tracking-wide"><?= date('d M Y, H:i', strtotime($event['next_call_at'])) ?></span>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($event['event_type'] === 'assignment'): ?>
                        <!-- Assignment Event -->
                        <div class="timeline-icon bg-purple-100 ring-4 ring-white">
                             <i class="fas fa-exchange-alt text-sm text-purple-500"></i>
                        </div>
                        <div class="flex justify-between items-start mb-1">
                             <div>
                                <p class="font-semibold text-gray-800">Driver Transferred</p>
                                <p class="text-sm text-gray-500">
                                    <?= date('d M Y, H:i', strtotime($event['event_date'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-gray-700 bg-gray-50 p-3 rounded-md text-sm space-y-2 mt-2">
                            <p>From: <strong class="font-medium text-gray-900"><?= htmlspecialchars($event['actor_name'] ?? 'Unknown') ?></strong></p>
                            <p>To: <strong class="font-medium text-gray-900"><?= htmlspecialchars($event['recipient_name'] ?? 'Unknown') ?></strong></p>
                            <?php if (!empty($event['notes'])): ?>
                                <p class="pt-2 border-t border-gray-200 mt-2">Note: <?= nl2br(htmlspecialchars($event['notes'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?> 