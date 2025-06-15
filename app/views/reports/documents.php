<?php require_once APPROOT . '/app/views/inc/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">تقرير المستندات</h2>

        <!-- Filters -->
        <form method="GET" class="mb-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">نوع المستند</label>
                <select name="document_type" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <option value="id" <?= isset($_GET['document_type']) && $_GET['document_type'] == 'id' ? 'selected' : '' ?>>بطاقة الهوية</option>
                    <option value="license" <?= isset($_GET['document_type']) && $_GET['document_type'] == 'license' ? 'selected' : '' ?>>رخصة القيادة</option>
                    <option value="vehicle_registration" <?= isset($_GET['document_type']) && $_GET['document_type'] == 'vehicle_registration' ? 'selected' : '' ?>>تسجيل المركبة</option>
                    <option value="insurance" <?= isset($_GET['document_type']) && $_GET['document_type'] == 'insurance' ? 'selected' : '' ?>>التأمين</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">حالة التحقق</label>
                <select name="verification_status" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <option value="pending" <?= isset($_GET['verification_status']) && $_GET['verification_status'] == 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                    <option value="verified" <?= isset($_GET['verification_status']) && $_GET['verification_status'] == 'verified' ? 'selected' : '' ?>>تم التحقق</option>
                    <option value="rejected" <?= isset($_GET['verification_status']) && $_GET['verification_status'] == 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">تم التحقق بواسطة</label>
                <select name="verified_by" class="shadow border rounded w-full py-2 px-3">
                    <option value="">الكل</option>
                    <?php foreach ($data['staff_members'] as $staff): ?>
                    <option value="<?= $staff['id'] ?>" <?= isset($_GET['verified_by']) && $_GET['verified_by'] == $staff['id'] ? 'selected' : '' ?>>
                        <?= $staff['username'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">من تاريخ</label>
                <input type="date" name="date_from" value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>" class="shadow border rounded w-full py-2 px-3">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">إلى تاريخ</label>
                <input type="date" name="date_to" value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>" class="shadow border rounded w-full py-2 px-3">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">عدد النتائج</label>
                <select name="limit" class="shadow border rounded w-full py-2 px-3">
                    <option value="25" <?= ($data['pagination']['limit'] ?? 25) == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($data['pagination']['limit'] ?? 25) == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($data['pagination']['limit'] ?? 25) == 100 ? 'selected' : '' ?>>100</option>
                    <option value="500" <?= ($data['pagination']['limit'] ?? 25) == 500 ? 'selected' : '' ?>>500</option>
                </select>
            </div>

            <div class="flex items-end col-span-full">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    تصفية
                </button>
                <a href="<?= BASE_PATH ?>/reports/documents/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                    تصدير Excel
                </a>
            </div>
        </form>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            السائق
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            نوع المستند
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            حالة التحقق
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            تم التحقق بواسطة
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ التحقق
                        </th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-right text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            ملاحظات
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['documents'] as $document): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $document['driver_name'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $document['document_type'] == 'id' ? 'بطاقة الهوية' : 
                                ($document['document_type'] == 'license' ? 'رخصة القيادة' : 
                                ($document['document_type'] == 'vehicle_registration' ? 'تسجيل المركبة' : 'التأمين')) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $document['verification_status'] == 'verified' ? 'bg-green-100 text-green-800' : 
                                    ($document['verification_status'] == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= $document['verification_status'] == 'verified' ? 'تم التحقق' : 
                                    ($document['verification_status'] == 'rejected' ? 'مرفوض' : 'قيد الانتظار') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $document['verified_by_name'] ?? '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $document['verified_at'] ? date('Y-m-d H:i', strtotime($document['verified_at'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                            <?= $document['verification_notes'] ?? '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
        <?php
            $pagination = $data['pagination'];
            if ($pagination['total_records'] > 0) {
                $filters = $data['filters'];
                $filters = array_filter($filters);
                $queryParams = http_build_query(array_merge($filters, ['limit' => $pagination['limit']]));
                $currentPage = $pagination['page'];
                $totalPages = $pagination['total_pages'];

                $startRecord = ($currentPage - 1) * $pagination['limit'] + 1;
                $endRecord = $startRecord + count($data['documents']) - 1;
        ?>
            <div class="flex flex-col sm:flex-row items-center justify-between">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    عرض <span class="font-medium"><?= $startRecord ?></span> إلى <span class="font-medium"><?= $endRecord ?></span> من <span class="font-medium"><?= $pagination['total_records'] ?></span> نتيجة
                </div>
                
                <?php if ($totalPages > 1): ?>
                <nav class="flex items-center space-x-1 space-x-reverse">
                    <a href="?page=1&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage == 1 ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-double-right"></i></a>
                    <a href="?page=<?= max(1, $currentPage - 1) ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage <= 1 ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-right"></i></a>
                    
                    <?php
                        $window = 1;
                        if ($totalPages > 10) {
                             if($currentPage > $window + 2) {
                                echo '<a href="?page=1&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-600 hover:bg-gray-50">1</a>';
                                if($currentPage > $window + 3) {
                                    echo '<span class="px-4 py-2 text-sm font-medium text-gray-500">...</span>';
                                }
                            }

                            for ($i = max(1, $currentPage - $window); $i <= min($totalPages, $currentPage + $window); $i++) {
                                echo '<a href="?page='.$i.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium '.($i == $currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50').'">'.$i.'</a>';
                            }
                            
                            if($currentPage < $totalPages - ($window + 2)) {
                                if($currentPage < $totalPages - ($window + 3)) {
                                     echo '<span class="px-4 py-2 text-sm font-medium text-gray-500">...</span>';
                                }
                                echo '<a href="?page='.$totalPages.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-600 hover:bg-gray-50">'.$totalPages.'</a>';
                            }
                        } else {
                             for ($i = 1; $i <= $totalPages; $i++) {
                                echo '<a href="?page='.$i.'&'.$queryParams.'" class="px-4 py-2 rounded-md text-sm font-medium '.($i == $currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50').'">'.$i.'</a>';
                            }
                        }
                    ?>

                    <a href="?page=<?= min($totalPages, $currentPage + 1) ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage >= $totalPages ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-left"></i></a>
                    <a href="?page=<?= $totalPages ?>&<?= $queryParams ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= $currentPage == $totalPages ? 'bg-gray-200 text-gray-400 pointer-events-none' : 'bg-white text-gray-600 hover:bg-gray-50' ?>"><i class="fas fa-angle-double-left"></i></a>
                </nav>
                <?php endif; ?>
            </div>
        <?php } ?>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/app/views/inc/footer.php'; ?> 