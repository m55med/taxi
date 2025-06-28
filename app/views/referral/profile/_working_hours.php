<div class="space-y-4">
    <?php
    // Use the variable passed from the parent view
    $hours_to_display = $working_hours_data ?? []; 
    $days_ar = ['Saturday' => 'السبت', 'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة'];
    
    foreach ($days_ar as $day_en => $day_ar):
        $details = $hours_to_display[$day_en] ?? ['start_time' => '', 'end_time' => '', 'is_closed' => true];
    ?>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center p-3 rounded-md bg-gray-50 border border-gray-200" x-data="{ isClosed: <?php echo $details['is_closed'] ? 'true' : 'false'; ?> }">
        <label class="font-medium text-gray-700 md:col-span-1"><?php echo $day_ar; ?></label>
        <div class="flex items-center space-x-4 space-x-reverse md:col-span-2">
            <input name="working_hours[<?php echo $day_en; ?>][start_time]" type="time" class="form-input w-full border-gray-300 rounded-md shadow-sm" value="<?php echo htmlspecialchars($details['start_time'] ?? ''); ?>" :disabled="isClosed">
            <span class="text-gray-500">-</span>
            <input name="working_hours[<?php echo $day_en; ?>][end_time]" type="time" class="form-input w-full border-gray-300 rounded-md shadow-sm" value="<?php echo htmlspecialchars($details['end_time'] ?? ''); ?>" :disabled="isClosed">
        </div>
        <div class="flex items-center justify-self-start md:justify-self-end md:col-span-1">
            <input type="hidden" name="working_hours[<?php echo $day_en; ?>][is_closed]" :value="isClosed ? '1' : '0'">
            <input id="closed_<?php echo $day_en; ?>" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" x-model="isClosed">
            <label for="closed_<?php echo $day_en; ?>" class="mr-2 text-sm text-gray-800">إجازة</label>
        </div>
    </div>
    <?php endforeach; ?>
</div> 