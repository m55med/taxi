<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفرق</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .toast {
            transition: opacity 0.5s, transform 0.5s;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <!-- Toast Notification -->
    <div id="toast" class="toast fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-md hidden opacity-0 transform -translate-y-10">
        <p id="toast-message"></p>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة الفرق</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">إضافة فريق جديد</h2>
                    <form action="<?= BASE_PATH ?>/admin/teams/store" method="POST">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">اسم الفريق</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mt-4">
                            <label for="team_leader_id" class="block text-sm font-medium text-gray-700">قائد الفريق</label>
                            <select name="team_leader_id" id="team_leader_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">اختر قائد للفريق</option>
                                <?php foreach ($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">الفرق الحالية</h2>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اسم الفريق</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قائد الفريق</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">أجراءات</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($data['teams'])): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                لا توجد فرق حاليًا.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['teams'] as $index => $team): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($team['name']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($team['team_leader_name']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                    <button onclick="openDeleteModal(<?= $team['id'] ?>)" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-right">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                حذف الفريق
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    هل أنت متأكد من رغبتك في حذف هذا الفريق؟ لا يمكن التراجع عن هذا الإجراء.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteForm" action="" method="POST">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            حذف
                        </button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.innerText = message;
            toast.classList.remove('hidden', 'opacity-0', 'bg-green-500', 'bg-red-500', '-translate-y-10');
            toast.classList.add(isError ? 'bg-red-500' : 'bg-green-500');

            setTimeout(() => {
                toast.classList.add('opacity-100', 'translate-y-0');
            }, 10);

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-10');
                setTimeout(() => toast.classList.add('hidden'), 500);
            }, 3000);
        }

        function openDeleteModal(id) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            form.action = `<?= BASE_PATH ?>/admin/teams/delete/${id}`;
            modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }

        window.onload = function() {
            <?php if (isset($data['message'])): ?>
                showToast('<?= addslashes(htmlspecialchars($data['message'])) ?>');
            <?php endif; ?>
            <?php if (isset($data['error'])): ?>
                showToast('<?= addslashes(htmlspecialchars($data['error'])) ?>', true);
            <?php endif; ?>
        };
    </script>

</body>
</html> 