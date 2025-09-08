<?php
if (isset($pagination) && $pagination['pages'] > 1):
    $filters = $filters ?? []; 
?>
<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-b-lg shadow-sm">
    <div class="flex flex-1 justify-between sm:hidden">
        <?php if ($pagination['page'] > 1): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])) ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">السابق</a>
        <?php endif; ?>
        <?php if ($pagination['page'] < $pagination['pages']): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])) ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">التالي</a>
        <?php endif; ?>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                عرض
                <span class="font-medium"><?= (($pagination['page'] - 1) * $pagination['limit']) + 1 ?></span>
                إلى
                <span class="font-medium"><?= min($pagination['page'] * $pagination['limit'], $pagination['total']) ?></span>
                من
                <span class="font-medium"><?= $pagination['total'] ?></span>
                نتائج
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <?php
                // Previous Button
                if ($pagination['page'] > 1) {
                    echo '<a href="?' . http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])) . '" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"><span class="sr-only">السابق</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg></a>';
                }

                $totalPages = $pagination['pages'];
                $currentPage = $pagination['page'];
                $window = 1; 

                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $window && $i <= $currentPage + $window)) {
                         if ($i == $currentPage) {
                            echo '<span aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">' . $i . '</span>';
                        } else {
                            echo '<a href="?' . http_build_query(array_merge($filters, ['page' => $i])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">' . $i . '</a>';
                        }
                    } elseif ($i == $currentPage - $window - 1 || $i == $currentPage + $window + 1) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
                    }
                }

                // Next Button
                 if ($pagination['page'] < $pagination['pages']) {
                    echo '<a href="?' . http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])) . '" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"><span class="sr-only">التالي</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 011.02 0l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.02-1.06L14.832 10l-2.042-1.92a.75.75 0 010-1.06z" clip-rule="evenodd" /></svg></a>';
                }
                ?>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?> 