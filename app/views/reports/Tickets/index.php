<?php require APPROOT . '/views/includes/header.php'; ?>


<div class="container mx-auto p-6 bg-gray-50 min-h-screen">

    <!-- Header -->

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">

        <div>

            <h1 class="text-4xl font-bold text-gray-800">Detailed Tickets Report</h1>

            <p class="text-lg text-gray-600">A comprehensive log of all created tickets.</p>

        </div>

        <div class="flex items-center mt-4 md:mt-0">

             <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition mr-2">

                <i class="fas fa-file-excel mr-2"></i>Excel

            </a>

            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'json'])) ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">

                <i class="fas fa-file-code mr-2"></i>JSON

            </a>

        </div>

    </div>



    <!-- Filter Bar -->

    <div class="bg-white p-4 rounded-lg shadow-md mb-8">

        <form action="" method="get" id="filter-form">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">

                <div>

                    <label for="search" class="block text-sm font-medium text-gray-700">Search Notes/Phone</label>

                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300">

                </div>

                <div>

                    <label for="user_id" class="block text-sm font-medium text-gray-700">Created By</label>

                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300">

                        <option value="">All Users</option>

                        <?php foreach($data['filter_options']['users'] as $user): ?>

                            <option value="<?= $user['id'] ?>" <?= ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>

                    <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300">

                        <option value="">All Categories</option>

                        <?php foreach($data['filter_options']['categories'] as $cat): ?>

                            <option value="<?= $cat['id'] ?>" <?= ($data['filters']['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>

                        <?php endforeach; ?>

                    </select>

                </div>

                 <div>

                    <label for="is_vip" class="block text-sm font-medium text-gray-700">Ticket Type</label>

                    <select name="is_vip" class="mt-1 block w-full rounded-md border-gray-300">

                        <option value="">All Types</option>

                        <option value="1" <?= ($data['filters']['is_vip'] ?? '') === '1' ? 'selected' : '' ?>>VIP</option>

                        <option value="0" <?= ($data['filters']['is_vip'] ?? '') === '0' ? 'selected' : '' ?>>Standard</option>

                    </select>

                </div>

                <div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>

                </div>

            </div>

        </form>

    </div>



    <!-- Tickets Table -->

    <div class="bg-white shadow-md rounded-lg overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ticket #</th>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Created By</th>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Category</th>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Subcategory / Code</th>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Platform</th>

                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Created At</th>

                    </tr>

                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                    <?php if (empty($data['tickets'])): ?>

                        <tr><td colspan="6" class="text-center py-10">No tickets found.</td></tr>

                    <?php else: ?>

                        <?php foreach ($data['tickets'] as $ticket): ?>

                            <tr class="hover:bg-gray-50">

                                <td class="px-6 py-4">

                                    <a href="/tickets/view/<?= $ticket['id'] ?>" class="text-indigo-600 hover:underline font-semibold"><?= htmlspecialchars($ticket['ticket_number']) ?></a>

                                    <?php if($ticket['is_vip']): ?><span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-400 text-white">VIP</span><?php endif; ?>

                                </td>

                                <td class="px-6 py-4"><?= htmlspecialchars($ticket['created_by_user']) ?></td>

                                <td class="px-6 py-4"><?= htmlspecialchars($ticket['category_name']) ?></td>

                                <td class="px-6 py-4"><?= htmlspecialchars($ticket['subcategory_name']) ?> / <?= htmlspecialchars($ticket['code_name']) ?></td>

                                <td class="px-6 py-4"><?= htmlspecialchars($ticket['platform_name']) ?></td>

                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($ticket['created_at']) ?></td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="p-4"><?php require APPROOT . '/views/includes/pagination_controls.php'; ?></div>

    </div>

</div>



<?php require APPROOT . '/views/includes/footer.php'; ?> 