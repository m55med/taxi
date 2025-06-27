<?php
// Function to generate query string with existing filters
function get_pagination_query_string($new_params = []) {
    // Start with existing filters from the URL
    $params = $_GET;

    // Unset current page and limit to avoid duplication
    unset($params['page'], $params['url']);

    // Merge new params (like a new page number)
    $params = array_merge($params, $new_params);

    // Build and return the query string
    return http_build_query($params);
}

$currentPage = $pagination['currentPage'];
$totalPages = $pagination['totalPages'];
$limit = $pagination['limit'];
$limitOptions = $pagination['limitOptions'];
$totalRecords = $pagination['totalRecords'];
$offset = ($currentPage - 1) * $limit; // Calculate offset here

// Don't show pagination if there's only one page
if ($totalPages <= 1) {
    return;
}
?>

<div class="flex items-center justify-between mt-6">
    <!-- Results per page selector -->
    <div class="flex items-center">
        <span class="text-sm text-gray-700 mr-2">Show</span>
        <form action="" method="GET" class="inline-flex">
            <!-- Hidden inputs for existing filters -->
            <?php foreach ($_GET as $key => $value): ?>
                <?php if ($key !== 'limit' && $key !== 'page' && $key !== 'url'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <select name="limit" onchange="this.form.submit()" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <?php foreach ($limitOptions as $option): ?>
                    <option value="<?= $option ?>" <?= $limit == $option ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <span class="text-sm text-gray-700 ml-2">results per page</span>
    </div>

    <!-- Pagination Links -->
    <nav class="flex items-center space-x-1" aria-label="Pagination">
        <!-- Previous Page Link -->
        <?php if ($currentPage > 1): ?>
            <a href="?<?= get_pagination_query_string(['page' => $currentPage - 1]) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Previous
            </a>
        <?php else: ?>
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-md">
                Previous
            </span>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php
            // Logic to display a limited number of page links
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);

            if ($start > 1) {
                echo '<a href="?' . get_pagination_query_string(['page' => 1]) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">1</a>';
                if ($start > 2) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>';
                }
            }
            
            for ($i = $start; $i <= $end; $i++):
        ?>
            <a href="?<?= get_pagination_query_string(['page' => $i]) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium <?= $i == $currentPage ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' ?> rounded-md">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php
            if ($end < $totalPages) {
                if ($end < $totalPages - 1) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>';
                }
                echo '<a href="?' . get_pagination_query_string(['page' => $totalPages]) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">' . $totalPages . '</a>';
            }
        ?>

        <!-- Next Page Link -->
        <?php if ($currentPage < $totalPages): ?>
            <a href="?<?= get_pagination_query_string(['page' => $currentPage + 1]) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Next
            </a>
        <?php else: ?>
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-md">
                Next
            </span>
        <?php endif; ?>
    </nav>
    
    <!-- Page Info -->
    <div>
        <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium"><?= $offset + 1 ?></span>
            to
            <span class="font-medium"><?= min($offset + $limit, $totalRecords) ?></span>
            of
            <span class="font-medium"><?= $totalRecords ?></span>
            results
        </p>
    </div>
</div> 