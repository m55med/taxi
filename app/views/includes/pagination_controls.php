<?php if (isset($data['total_pages']) && $data['total_pages'] > 1): ?>
<div class="mt-6 flex justify-between items-center font-sans">
    <div class="flex-1 flex justify-start">
        <span class="text-sm text-gray-600">
            Page <?= $data['current_page'] ?> of <?= $data['total_pages'] ?>
        </span>
    </div>

    <div class="flex-1 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <!-- Previous Page Link -->
            <a href="?page=<?= $data['current_page'] > 1 ? $data['current_page'] - 1 : 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>"
               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $data['current_page'] <= 1 ? 'cursor-not-allowed opacity-50' : '' ?>">
                Previous
            </a>

            <?php 
                $start = max(1, $data['current_page'] - 2);
                $end = min($data['total_pages'], $data['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="?page=1&' . http_build_query(array_diff_key($_GET, ['page'=>''])) . '" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>';
                    if ($start > 2) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                    }
                }

                for ($i = $start; $i <= $end; $i++): 
            ?>
                <a href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>" 
                   class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i == $data['current_page'] ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php
                if ($end < $data['total_pages']) {
                    if ($end < $data['total_pages'] - 1) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                    }
                    echo '<a href="?page=' . $data['total_pages'] . '&' . http_build_query(array_diff_key($_GET, ['page'=>''])) . '" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $data['total_pages'] . '</a>';
                }
            ?>

            <!-- Next Page Link -->
            <a href="?page=<?= $data['current_page'] < $data['total_pages'] ? $data['current_page'] + 1 : $data['total_pages'] ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>"
               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $data['current_page'] >= $data['total_pages'] ? 'cursor-not-allowed opacity-50' : '' ?>">
                Next
            </a>
        </nav>
    </div>
    <div class="flex-1 flex justify-end"></div>
</div>
<?php endif; ?> 