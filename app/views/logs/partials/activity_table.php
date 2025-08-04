<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="p-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-5/12">Details</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Employee</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Team</th>
                <?php if (isset($showPoints) && $showPoints): ?>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Points</th>
                <?php endif; ?>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Date & Time</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($activities as $activity): ?>
                <?php $activity_uid = $activity->activity_type . '-' . $activity->activity_id; ?>
                <tr>
                    <td class="p-4">
                        <div class="flex items-center">
                            <input type="checkbox" value="<?= htmlspecialchars($activity_uid) ?>" class="activity-checkbox w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php 
                            $type_icon = '';
                            $type_text = '';
                            $type_color = 'gray';
                            switch ($activity->activity_type) {
                                case 'Ticket':
                                    $type_icon = 'fa-ticket-alt';
                                    $type_text = 'Ticket';
                                    $type_color = 'purple';
                                    break;
                                case 'Outgoing Call':
                                    $type_icon = 'fa-phone-alt fa-flip-horizontal';
                                    $type_text = 'Outgoing';
                                    $type_color = 'blue';
                                    break;
                                case 'Incoming Call':
                                    $type_icon = 'fa-phone-alt';
                                    $type_text = 'Incoming';
                                    $type_color = 'green';
                                    break;
                                case 'Assignment':
                                    $type_icon = 'fa-exchange-alt';
                                    $type_text = 'Assignment';
                                    $type_color = 'red';
                                    break;
                            }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?= $type_color ?>-100 text-<?= $type_color ?>-800" title="<?= $type_text ?>">
                            <i class="fas <?= $type_icon ?> mr-1 mt-1"></i>
                            <?= $type_text ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 truncate">
                             <a href="<?= URLROOT ?>/<?= $activity->link_prefix ?>/<?= $activity->link_id ?>" class="text-indigo-600 hover:text-indigo-900" title="<?= htmlspecialchars($activity->details_primary) ?>">
                                <?php if ($activity->activity_type === 'Ticket' && !empty($activity->is_vip)): ?>
                                    <i class="fas fa-crown text-yellow-500 mr-2" title="VIP Ticket"></i>
                                <?php endif; ?>
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
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($activity->team_name ?? 'N/A') ?></div>
                    </td>
                    <?php if (isset($showPoints) && $showPoints): ?>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                        <?php if (isset($activity->points) && $activity->points > 0): ?>
                            <span class="text-yellow-500">
                                <i class="fas fa-star"></i>
                                <?= htmlspecialchars(number_format($activity->points, 2)) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-400">0.00</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('Y-m-d H:i', strtotime($activity->activity_date)) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>