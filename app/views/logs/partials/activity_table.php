<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 table-fixed">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">النوع</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-5/12">التفاصيل</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">الموظف</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">الفريق</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">التاريخ والوقت</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php 
                            $type_icon = '';
                            $type_text = '';
                            $type_color = 'gray';
                            switch ($activity->activity_type) {
                                case 'ticket':
                                    $type_icon = 'fa-ticket-alt';
                                    $type_text = 'تذكرة';
                                    $type_color = 'purple';
                                    break;
                                case 'call':
                                    $type_icon = 'fa-phone-alt';
                                    $type_text = 'مكالمة';
                                    $type_color = 'blue';
                                    break;
                                case 'assignment':
                                    $type_icon = 'fa-exchange-alt';
                                    $type_text = 'تحويل';
                                    $type_color = 'red';
                                    break;
                            }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?= $type_color ?>-100 text-<?= $type_color ?>-800">
                            <i class="fas <?= $type_icon ?> ml-1 mt-1"></i>
                            <?= $type_text ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 truncate">
                             <a href="<?= BASE_PATH ?>/<?= $activity->link_prefix ?>/<?= $activity->link_id ?>" class="text-indigo-600 hover:text-indigo-900" title="<?= htmlspecialchars($activity->details_primary) ?>">
                                <?= htmlspecialchars($activity->details_primary) ?>
                            </a>
                        </div>
                        <div class="text-sm text-gray-500 truncate" title="<?= htmlspecialchars($activity->details_secondary) ?>">
                            <?= htmlspecialchars($activity->details_secondary) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($activity->username) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($activity->team_name ?? 'بدون فريق') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" dir="ltr">
                        <?= date('Y-m-d H:i', strtotime($activity->activity_date)) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 