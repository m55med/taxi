<?php require_once APPROOT . '/views/includes/header.php'; ?>

<main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" x-data="evaluationsPage()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Employee Evaluations</h1>
        <button @click="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Evaluation
        </button>
    </div>

    <!-- Evaluations Table -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluator</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($data['evaluations'])) : ?>
                        <?php foreach ($data['evaluations'] as $evaluation) : ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($evaluation['user_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($evaluation['evaluator_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold <?= $evaluation['score'] >= 5 ? 'text-green-600' : 'text-red-600' ?>"><?= htmlspecialchars($evaluation['score']) ?> / 10</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date('F', mktime(0, 0, 0, $evaluation['applicable_month'], 10)) . ', ' . htmlspecialchars($evaluation['applicable_year']) ?></td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-600 max-w-xs break-words"><?= htmlspecialchars($evaluation['comment']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($evaluation['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="<?= URLROOT ?>/employee-evaluations/delete" method="post" x-ref="deleteForm" id="deleteForm-<?= $evaluation['id'] ?>">
                                        <input type="hidden" name="id" value="<?= $evaluation['id'] ?>">
                                        <button type="button" @click="confirmDelete(<?= $evaluation['id'] ?>)" class="text-red-600 hover:text-red-800 transition duration-150">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>No evaluations found. Click "Add Evaluation" to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Evaluation Modal -->
    <div x-show="isModalOpen" x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="closeModal()"
             class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white"
             x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 transform -translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Add New Evaluation</h3>
                
                <form action="<?= URLROOT ?>/employee-evaluations/create" method="post" class="text-left">
                    <!-- Employee Select -->
                    <div class="mb-4">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Employee</label>
                        <select name="user_id" id="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                            <option value="" disabled selected>Select an employee...</option>
                            <?php foreach ($data['users'] as $user) : ?>
                                <option value="<?= $user->id ?>"><?= htmlspecialchars($user->username) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Score -->
                    <div class="mb-4">
                        <label for="score" class="block text-sm font-medium text-gray-700">Score (0-10)</label>
                        <input type="number" name="score" id="score" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" min="0" max="10" step="0.1" required>
                    </div>

                    <!-- Period -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                            <select name="month" id="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                <?php for ($m = 1; $m <= 12; $m++) : ?>
                                    <option value="<?= $m ?>" <?= ($m == date('n')) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 10)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                            <input type="number" name="year" id="year" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="<?= date('Y') ?>" required>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div class="mb-6">
                        <label for="comment" class="block text-sm font-medium text-gray-700">Comment (Optional)</label>
                        <textarea name="comment" id="comment" rows="4" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse -mx-5 -mb-5 rounded-b-md">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Evaluation
                        </button>
                        <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="isDeleteModalOpen" x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full flex items-center justify-center z-50"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="closeDeleteModal()"
             class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-2xl bg-white"
             x-show="isDeleteModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle fa-lg text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mt-4" id="modal-title">Delete Evaluation</h3>
                <p class="text-sm text-gray-500 mt-2 px-4">
                    Are you sure you want to delete this evaluation? This action is irreversible and cannot be undone.
                </p>
            </div>

            <div class="mt-6 flex justify-center gap-4">
                <button type="button" @click="closeDeleteModal()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 px-4 py-2 bg-white text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Cancel
                </button>
                <button type="button" @click="submitDeleteForm()" class="w-full inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-red-600 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once APPROOT . '/views/includes/footer.php'; ?> 