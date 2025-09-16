<?php
/**
 * Partial for displaying ticket detail logs
 *
 * @param array $logs Array of logs to display
 * @param string $title Title for the logs section
 * @param bool $compact Whether to show compact view
 */
$logs = $logs ?? [];
$title = $title ?? 'سجلات التعديلات والحذوفات';
$compact = $compact ?? false;
?>

<?php if (!empty($logs)): ?>
    <div class="mt-4">
        <?php if (!$compact): ?>
            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                <i class="fas fa-history text-gray-500 mr-2"></i>
                <?= htmlspecialchars($title) ?>
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">
                    <?= count($logs) ?>
                </span>
            </h4>
        <?php endif; ?>

        <div class="space-y-2 <?= $compact ? 'max-h-32 overflow-y-auto' : '' ?>">

            <?php foreach ($logs as $log): ?>
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 <?=
                    ($log['field_name'] === 'DELETED' ? 'border-l-red-500 bg-red-50' :
                    ($log['field_name'] === 'CREATED' ? 'border-l-green-500 bg-green-50' : 'border-l-blue-500'))
                ?>">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <?php if ($log['field_name'] === 'DELETED'): ?>
                                    <i class="fas fa-trash text-red-500 text-xs"></i>
                                    <span class="text-xs font-medium text-red-700">تم الحذف</span>
                                <?php elseif ($log['field_name'] === 'CREATED'): ?>
                                    <i class="fas fa-plus text-green-500 text-xs"></i>
                                    <span class="text-xs font-medium text-green-700">تم الإنشاء</span>
                                <?php else: ?>
                                    <i class="fas fa-edit text-blue-500 text-xs"></i>
                                    <span class="text-xs font-medium text-blue-700">تم التعديل</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-1 text-xs text-gray-600">
                                <span class="font-medium">بواسطة:</span>
                                <?= htmlspecialchars($log['editor_name'] ?? $log['editor_username'] ?? 'مستخدم غير معروف') ?>
                                <?php if (!empty($log['editor_username'])): ?>
                                    <span class="text-gray-400">(<?= htmlspecialchars($log['editor_username']) ?>)</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($log['field_name'] !== 'DELETED' && !empty($log['old_value']) && !empty($log['new_value'])): ?>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <div class="bg-red-50 p-2 rounded text-xs">
                                        <div class="text-red-600 font-medium mb-1">القيمة القديمة:</div>
                                        <div class="text-red-800 break-words">
                                            <?= htmlspecialchars(substr($log['old_value'], 0, 100)) ?>
                                            <?= strlen($log['old_value']) > 100 ? '...' : '' ?>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-2 rounded text-xs">
                                        <div class="text-green-600 font-medium mb-1">القيمة الجديدة:</div>
                                        <div class="text-green-800 break-words">
                                            <?= htmlspecialchars(substr($log['new_value'], 0, 100)) ?>
                                            <?= strlen($log['new_value']) > 100 ? '...' : '' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($log['field_name'] === 'DELETED'): ?>
                                <div class="mt-2 bg-red-50 p-2 rounded text-xs">
                                    <div class="text-red-600 font-medium mb-1">تفاصيل الحذف:</div>
                                    <div class="text-red-800 break-words">
                                        <?php
                                        $deleteDetails = json_decode($log['old_value'], true);
                                        if ($deleteDetails) {
                                            echo 'رقم التذكرة: ' . htmlspecialchars($deleteDetails['ticket_number'] ?? 'غير محدد') . '<br>';
                                            if (!empty($deleteDetails['phone'])) {
                                                echo 'الهاتف: ' . htmlspecialchars($deleteDetails['phone']) . '<br>';
                                            }
                                            if (!empty($deleteDetails['notes'])) {
                                                echo 'الملاحظات: ' . htmlspecialchars($deleteDetails['notes']);
                                            }
                                        } else {
                                            echo htmlspecialchars(substr($log['old_value'], 0, 200));
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-xs text-gray-500 ml-3 flex-shrink-0">
                            <i class="fas fa-clock mr-1"></i>
                            <?= htmlspecialchars($log['created_at']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif (!$compact): ?>
    <div class="mt-4 text-center py-4 text-gray-500">
        <i class="fas fa-history text-gray-300 text-2xl mb-2"></i>
        <p class="text-sm">لا توجد سجلات تعديلات</p>
    </div>
<?php endif; ?>
