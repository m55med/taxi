<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أعضاء الفرق</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        /* Custom Toast Styles (from ticket system) */
        .toast {
            transition: all 0.5s ease-in-out;
            opacity: 0;
            transform: translateX(100%);
        }
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .toast.hide {
            opacity: 0;
            transform: translateX(100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-8 right-8 z-[100] w-full max-w-sm space-y-3"></div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة أعضاء الفرق</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">إضافة عضو جديد للفريق</h2>
                    <form action="<?= BASE_PATH ?>/admin/team_members/store" method="POST">
                        <div class="mt-4">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">العضو</label>
                            <select name="user_id" id="user_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">اختر العضو</option>
                                <?php foreach ($data['users'] as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-4">
                            <label for="team_id" class="block text-sm font-medium text-gray-700">الفريق</label>
                            <select name="team_id" id="team_id" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">اختر الفريق</option>
                                <?php foreach ($data['teams'] as $team): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
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
                        <h2 class="text-xl font-semibold text-gray-800">أعضاء الفرق الحاليين</h2>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اسم العضو</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفريق</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">أجراءات</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($data['team_members'])): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                لا يوجد أعضاء في الفرق حاليًا.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['team_members'] as $index => $member): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $index + 1 ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($member['user_name']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($member['team_name']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                    <form action="<?= BASE_PATH ?>/admin/team_members/delete/<?= $member['id'] ?>" method="POST" class="delete-form">
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
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
    
    <!-- SweetAlert2 for Delete Confirmation ONLY -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Custom Toast Function (from ticket system)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const toastId = 'toast-' + Date.now();
            toast.id = toastId;
            
            const icon = type === 'success' 
                ? '<i class="fas fa-check-circle"></i>' 
                : '<i class="fas fa-times-circle"></i>';
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

            toast.className = `toast ${bgColor} text-white font-bold rounded-lg shadow-lg p-4 flex items-center`;
            toast.innerHTML = `<div class="ml-3 text-sm font-medium">${icon} <span class="mr-2">${message}</span></div>`;
            
            container.appendChild(toast);

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Hide and remove toast
            setTimeout(() => {
                toast.classList.add('hide');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Check for PHP session messages and show toast
            <?php if (isset($data['message'])): ?>
                showToast('<?= addslashes(htmlspecialchars($data['message'])) ?>', 'success');
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($data['error'])): ?>
                showToast('<?= addslashes(htmlspecialchars($data['error'])) ?>', 'error');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            // Handle delete confirmation with SweetAlert2
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "هل تريد حقًا إزالة هذا العضو من الفريق؟",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'نعم، قم بالحذف!',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 