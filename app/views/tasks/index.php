<?php require APPROOT . '/views/includes/header.php'; ?>

<div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Task Management</h1>
                        <p class="text-gray-600">Track and manage your tasks and assignments.</p>
                    </div>
                    <?php if (in_array($data['role'], ['admin', 'developer', 'Team_leader'])): ?>
                    <a href="<?= URLROOT ?>/tasks/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>New Task</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Admin Filters -->
                <?php if ($data['role'] === 'admin' || $data['role'] === 'developer'): ?>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
                    <form action="<?= URLROOT ?>/tasks" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= ($data['filters']['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="in_progress" <?= ($data['filters']['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= ($data['filters']['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= ($data['filters']['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Assignee</label>
                            <select name="user_id" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Assignees</option>
                                <?php foreach ($data['users'] as $user): ?>
                                <option value="<?= $user->id ?>" <?= ($data['filters']['user_id'] ?? '') == $user->id ? 'selected' : '' ?>><?= htmlspecialchars($user->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Project</label>
                            <input type="text" name="project" value="<?= htmlspecialchars($data['filters']['project'] ?? '') ?>" placeholder="Project name" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-gray-900 text-white text-sm font-bold py-2 rounded-lg hover:bg-gray-800 transition-colors">Filter</button>
                            <a href="<?= URLROOT ?>/tasks" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center">
                                <i class="fas fa-undo text-sm"></i>
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 text-blue-600 mb-1">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="text-sm font-medium uppercase tracking-wider">Total Tasks</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900"><?= count($data['tasks']) ?></div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 text-yellow-600 mb-1">
                            <i class="fas fa-clock"></i>
                            <span class="text-sm font-medium uppercase tracking-wider">Pending</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($data['tasks'], fn($t) => $t['status'] === 'pending')) ?>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 text-indigo-600 mb-1">
                            <i class="fas fa-spinner"></i>
                            <span class="text-sm font-medium uppercase tracking-wider">In Progress</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($data['tasks'], fn($t) => $t['status'] === 'in_progress')) ?>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 text-green-600 mb-1">
                            <i class="fas fa-check-circle"></i>
                            <span class="text-sm font-medium uppercase tracking-wider">Completed</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($data['tasks'], fn($t) => $t['status'] === 'completed')) ?>
                        </div>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Task Info</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assignees</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($data['tasks'])): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-tasks text-4xl text-gray-200"></i>
                                            <p>No tasks found.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($data['tasks'] as $task): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900"><?= htmlspecialchars($task['title']) ?></span>
                                            <span class="text-xs text-gray-500 truncate max-w-xs"><?= htmlspecialchars($task['description']) ?></span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 font-medium"><?= htmlspecialchars($task['project']) ?></span>
                                                <span class="text-[10px] text-gray-400">by <?= htmlspecialchars($task['creator_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-900"><?= date('M d, Y', strtotime($task['deadline'])) ?></span>
                                            <span class="text-xs text-gray-400"><?= date('H:i', strtotime($task['deadline'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-full">
                                            <?= htmlspecialchars($task['source']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex -space-x-2 overflow-hidden">
                                            <?php 
                                            $assigneeList = explode(',', $task['assignees']);
                                            foreach (array_slice($assigneeList, 0, 3) as $user): 
                                            ?>
                                            <div class="h-8 w-8 rounded-full bg-blue-500 border-2 border-white flex items-center justify-center text-[10px] text-white font-bold uppercase" title="<?= htmlspecialchars($user) ?>">
                                                <?= substr($user, 0, 1) ?>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php if (count($assigneeList) > 3): ?>
                                            <div class="h-8 w-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] text-gray-600 font-bold">
                                                +<?= count($assigneeList) - 3 ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusClass = $statusClasses[$task['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $task['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if ($data['role'] === 'admin' || $data['role'] === 'developer' || $task['created_by'] == $_SESSION['user_id']): ?>
                                            <a href="<?= URLROOT ?>/tasks/edit/<?= $task['id'] ?>" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Task">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            <?php elseif (in_array($data['role'], ['Team_leader', 'Quality'])): ?>
                                            <a href="<?= URLROOT ?>/tasks/show/<?= $task['id'] ?>?action=edit_assignees" class="text-orange-400 hover:text-orange-600 transition-colors" title="Edit Assignees">
                                                <i class="fas fa-user-friends text-sm"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?= URLROOT ?>/tasks/show/<?= $task['id'] ?>" class="text-blue-600 hover:text-blue-900 font-medium text-sm">View</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
</div>
<?php require APPROOT . '/views/includes/footer.php'; ?>
