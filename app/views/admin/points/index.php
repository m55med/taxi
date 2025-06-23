<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة نظام النقاط</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include_once __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة نظام النقاط</h1>

        <?php flash('points_message'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Section for Ticket Code Points -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">نقاط أكواد التذاكر</h2>
                
                <form action="<?= BASE_PATH ?>/admin/points/setTicketPoints" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="code_id" class="block text-sm font-medium text-gray-700">الكود</label>
                            <select name="code_id" id="code_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">اختر كود...</option>
                                <?php foreach($data['ticket_codes'] as $code): ?>
                                    <option value="<?= $code['id'] ?>"><?= htmlspecialchars($code['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="points" class="block text-sm font-medium text-gray-700">النقاط</label>
                            <input type="number" name="points" id="points" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="valid_from" class="block text-sm font-medium text-gray-700">صالح من تاريخ</label>
                            <input type="date" name="valid_from" id="valid_from" required value="<?= date('Y-m-d') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_vip" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="mr-2 text-sm text-gray-600">تذكرة VIP؟</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="mt-4 w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-save ml-2"></i>حفظ نقاط الكود
                    </button>
                </form>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">القواعد الحالية</h3>
                    <div class="max-h-64 overflow-y-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الكود</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">النوع</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">النقاط</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الفترة</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['ticket_points'])): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">لا توجد قواعد حالية.</td></tr>
                                <?php else: ?>
                                    <?php foreach($data['ticket_points'] as $rule): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($rule['code_name']) ?></td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $rule['is_vip'] ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                                                    <?= $rule['is_vip'] ? 'VIP' : 'عادي' ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm font-bold"><?= $rule['points'] ?></td>
                                            <td class="px-4 py-2 text-sm text-left" dir="ltr">
                                                <?= date('Y-m-d', strtotime($rule['valid_from'])) ?> → 
                                                <?php if($rule['valid_to']): ?>
                                                    <?= date('Y-m-d', strtotime($rule['valid_to'])) ?>
                                                <?php else: ?>
                                                    <span class="text-green-600 font-semibold">حالي</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section for Call Points -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">نقاط المكالمات</h2>
                
                <form action="<?= BASE_PATH ?>/admin/points/setCallPoints" method="POST">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="call_points" class="block text-sm font-medium text-gray-700">النقاط لكل مكالمة</label>
                            <input type="number" name="points" id="call_points" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="call_valid_from" class="block text-sm font-medium text-gray-700">صالح من تاريخ</label>
                            <input type="date" name="valid_from" id="call_valid_from" required value="<?= date('Y-m-d') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                        <i class="fas fa-save ml-2"></i>حفظ نقاط المكالمات
                    </button>
                </form>

                 <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">القواعد الحالية</h3>
                    <div class="max-h-64 overflow-y-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">النقاط</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الفترة</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['call_points'])): ?>
                                    <tr><td colspan="2" class="text-center py-4 text-gray-500">لا توجد قواعد حالية.</td></tr>
                                <?php else: ?>
                                    <?php foreach($data['call_points'] as $rule): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-bold"><?= $rule['points'] ?></td>
                                            <td class="px-4 py-2 text-sm text-left" dir="ltr">
                                                <?= date('Y-m-d', strtotime($rule['valid_from'])) ?> → 
                                                <?php if($rule['valid_to']): ?>
                                                    <?= date('Y-m-d', strtotime($rule['valid_to'])) ?>
                                                <?php else: ?>
                                                    <span class="text-green-600 font-semibold">حالي</span>
                                                <?php endif; ?>
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
</body>
</html> 