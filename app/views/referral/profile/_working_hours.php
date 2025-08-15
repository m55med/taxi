<div class="space-y-4">
    <?php
    $hours_to_display = $working_hours_data ?? []; 
    $days_ar = ['Saturday' => 'السبت', 'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة'];
    
    foreach ($days_ar as $day_en => $day_ar):
        $details = $hours_to_display[$day_en] ?? ['start_time' => '', 'end_time' => '', 'is_closed' => true];
    ?>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center" x-data="{ isClosed: <?= $details['is_closed'] ? 'true' : 'false'; ?> }">
        <label class="font-medium text-gray-700 md:col-span-1 text-sm"><?= $day_ar; ?></label>
        <div class="flex items-center space-x-2 space-x-reverse md:col-span-3">
            <input name="working_hours[<?= $day_en; ?>][start_time]" type="time" class="form-input w-full border-gray-300 rounded-md shadow-sm text-sm" value="<?= htmlspecialchars($details['start_time'] ?? ''); ?>" :disabled="isClosed">
            <span class="text-gray-500">-</span>
            <input name="working_hours[<?= $day_en; ?>][end_time]" type="time" class="form-input w-full border-gray-300 rounded-md shadow-sm text-sm" value="<?= htmlspecialchars($details['end_time'] ?? ''); ?>" :disabled="isClosed">
        </div>
        <div class="flex items-center justify-self-start md:justify-self-center md:col-span-1">
            <input type="hidden" name="working_hours[<?= $day_en; ?>][is_closed]" :value="isClosed ? '1' : '0'">
            <input id="closed_<?= $day_en; ?>" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" x-model="isClosed">
            <label for="closed_<?= $day_en; ?>" class="mr-2 text-sm text-gray-800">إجازة</label>
        </div>
    </div>
    <?php endforeach; ?>
</div> 