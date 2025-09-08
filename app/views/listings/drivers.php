<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-100 p-6">
  
  <!-- Page Title -->
  <header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Driver Management</h1>
    <p class="text-gray-600 mt-1">A comprehensive view to filter, search, and manage all drivers.</p>
  </header>

  <!-- Statistics Cards -->
  <section aria-labelledby="stats-heading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <h2 id="stats-heading" class="sr-only">Driver Status Statistics</h2>
        <?php
      $stats       = $data['stats'];
      $statOrder   = ['total','pending','completed','needs_documents','rescheduled','blocked'];
      $statusLabels= [
        'total'            => 'Total Drivers',
        'pending'          => 'Pending',
        'completed'        => 'Completed',
        'needs_documents'  => 'Needs Docs',
        'rescheduled'      => 'Rescheduled',
        'blocked'          => 'Blocked',
      ];
    ?>
    <?php foreach ($statOrder as $key): 
      $active = ($data['filters']['main_system_status'] ?? '') === $key;
    ?>
      <a href="?main_system_status=<?= $key==='total' ? '' : $key ?>"
         class="flex flex-col justify-between p-4 rounded-lg shadow transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $active ? 'bg-blue-600 text-white' : 'bg-white' ?>"
         aria-pressed="<?= $active ? 'true' : 'false' ?>"
         role="button">
        <dt class="text-sm font-medium <?= $active ? 'text-blue-200' : 'text-gray-500' ?>">
          <?= $statusLabels[$key] ?>
        </dt>
        <dd class="mt-2 text-2xl font-bold <?= $active ? 'text-white' : 'text-gray-800' ?>">
          <?= number_format($stats[$key] ?? 0) ?>
        </dd>
            </a>
        <?php endforeach; ?>
  </section>

  <!-- Filter & Search Panel -->
  <section aria-labelledby="filter-heading" class="bg-white rounded-2xl shadow-lg divide-y divide-gray-200 mb-8">
    <div class="p-6">
      <h2 id="filter-heading" class="text-xl font-semibold text-gray-800">Filter &amp; Search</h2>
      <p class="mt-1 text-sm text-gray-500">Refine your search results with the options below.</p>
    </div>
    <form id="filter-form" method="GET" class="p-6 space-y-8">

      <!-- Search & Selectors -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Search Field -->
        <div>
          <label for="search_term" class="block text-sm font-medium text-gray-700">Search Drivers</label>
          <div class="relative mt-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search_term" id="search_term"
                   class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Name, phone, email, ID…"
                   value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>">
                    </div>
                </div>

        <!-- Main Status -->
                <div>
          <label for="main_system_status" class="block text-sm font-medium text-gray-700">Main Status</label>
          <select name="main_system_status" id="main_system_status"
                  class="mt-1 block w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Statuses</option>
            <?php foreach (['pending','completed','needs_documents','rescheduled','blocked'] as $s): ?>
              <option value="<?= $s ?>"
                      <?= (isset($data['filters']['main_system_status']) && $data['filters']['main_system_status']===$s) ? 'selected' : '' ?>>
                <?= $statusLabels[$s] ?>
              </option>
                        <?php endforeach; ?>
                    </select>
                </div>

        <!-- Car Type -->
                <div>
          <label for="car_type_id" class="block text-sm font-medium text-gray-700">Car Type</label>
          <select name="car_type_id" id="car_type_id"
                  class="mt-1 block w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Car Types</option>
            <?php foreach ($data['car_types'] as $type): ?>
              <option value="<?= $type->id ?>"
                      <?= (isset($data['filters']['car_type_id']) && $data['filters']['car_type_id']==$type->id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($type->name) ?>
              </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Country -->
                <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                    <select name="country_id" id="country_id"
                            class="mt-1 block w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Countries</option>
                        <?php foreach ($data['countries'] as $country): ?>
                            <option value="<?= $country->id ?>"
                                    <?= (isset($data['filters']['country_id']) && $data['filters']['country_id'] == $country->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

      <!-- Date Range Picker -->
      <div class="bg-gray-50 p-6 rounded-xl">
        <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>

        <!-- Quick Buttons -->
        <div class="flex flex-wrap gap-2 mb-4" role="group" aria-label="Quick date filters">
          <?php foreach (['all'=>'All Time','today'=>'Today','week'=>'Last 7 Days','month'=>'This Month'] as $key=>$label): ?>
            <button type="button"
                    class="quick-date-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    data-range="<?= $key ?>">
              <?= $label ?>
            </button>
          <?php endforeach; ?>
        </div>

        <!-- Manual Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="date_from" class="block text-sm font-medium text-gray-600">From</label>
            <input type="date" name="date_from" id="date_from"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   value="<?= htmlspecialchars($data['filters']['date_from'] ?? '') ?>">
          </div>
          <div>
            <label for="date_to" class="block text-sm font-medium text-gray-600">To</label>
            <input type="date" name="date_to" id="date_to"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   value="<?= htmlspecialchars($data['filters']['date_to'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-200">
        <a href="<?= URLROOT ?>/listings/drivers"
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
          <i class="fas fa-undo mr-2"></i>
          Reset Filters
        </a>
        <div class="flex items-center gap-3">
          <button type="submit"
                  class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-filter mr-2"></i>
            Apply Filters
          </button>
          <div class="relative">
            <button type="button" id="export-menu-button"
                    class="inline-flex items-center px-6 py-3 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
              <i class="fas fa-download mr-2"></i>
              Export
              <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
            </button>
            <div id="export-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg divide-y divide-gray-100 z-10">
              <?php foreach (['excel'=>'Excel (.xlsx)','csv'=>'CSV (.csv)','json'=>'JSON (.json)'] as $val=>$label): ?>
                <button type="submit" name="export" value="<?= $val ?>"
                        class="flex items-center w-full px-4 py-2 text-sm hover:bg-gray-50 focus:bg-gray-50">
                  <i class="fas fa-file-<?= $val==='excel'?'excel':($val==='csv'?'csv':($val==='json'?'code':('alt'))) ?> mr-2"></i>
                  <?= $label ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
            </div>
        </form>
  </section>

  <!-- Drivers Table -->
  <section aria-labelledby="results-heading" class="bg-white rounded-lg shadow-lg overflow-hidden">
    <h2 id="results-heading" class="sr-only">Driver Results</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200" role="table">
        <thead class="bg-gray-50">
            <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Driver</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Main Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">App Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php if (empty($data['drivers']['data'])): ?>
          <tr>
            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
              <i class="fas fa-search text-4xl mb-3"></i>
              <p class="text-lg font-medium">No drivers found</p>
              <p class="text-sm">Try adjusting your filters above.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($data['drivers']['data'] as $driver): ?>
            <tr class="hover:bg-gray-50 focus-within:bg-gray-50 transition">
              <!-- Driver Info -->
              <td class="px-6 py-4 whitespace-nowrap">
                <a href="<?= URLROOT ?>/drivers/details/<?= $driver['id'] ?>"
                   class="block group focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                  <p class="font-semibold text-blue-600 group-hover:text-blue-800"><?= htmlspecialchars($driver['name']) ?></p>
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($driver['phone']) ?></p>
                </a>
              </td>
              <!-- Main Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <?php if ($driver['main_system_status']): ?>
                  <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                               <?= 'status-' . str_replace('_','-',$driver['main_system_status']) ?>">
                    <?= $statusLabels[$driver['main_system_status']] ?? htmlspecialchars($driver['main_system_status']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <!-- App Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <?php if ($driver['app_status']): ?>
                  <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                               <?= 'status-' . str_replace('_','-',$driver['app_status']) ?>">
                    <?= htmlspecialchars($driver['app_status']) ?>
                  </span>
                <?php endif; ?>
                    </td>
              <!-- Details -->
              <td class="px-6 py-4 text-sm text-gray-700">
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="font-medium text-gray-500">Calls:</dt>
                    <dd class="font-semibold"><?= $driver['call_count'] ?? 0 ?></dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="font-medium text-gray-500">Missing Docs:</dt>
                    <dd class="font-semibold <?= ($driver['missing_documents_count'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' ?>">
                      <?= $driver['missing_documents_count'] ?? 0 ?>
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="font-medium text-gray-500">Car:</dt>
                    <dd class="font-semibold"><?= htmlspecialchars($driver['car_type_name'] ?? '-') ?></dd>
                  </div>
                </dl>
                    </td>
              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap">
                <a href="<?= URLROOT ?>/drivers/details/<?= $driver['id'] ?>"
                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <i class="fas fa-eye mr-1.5"></i>
                  View
                </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($data['drivers']['total'])):
      $total       = $data['drivers']['total'];
            $currentPage = $data['pagination']['current_page'];
      $limit       = $data['pagination']['limit'];
      $totalPages  = $data['pagination']['total_pages'];
      $start       = ($currentPage-1)*$limit + 1;
      $end         = min($currentPage*$limit, $total);
    ?>
    <div class="px-6 py-4 bg-white border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-sm text-gray-700">
        Showing <span class="font-medium"><?= $start ?></span> to <span class="font-medium"><?= $end ?></span> of <span class="font-medium"><?= $total ?></span> results
      </p>
      <nav class="flex space-x-2 mt-4 sm:mt-0" aria-label="Pagination">
        <a href="<?= $currentPage>1 ? '?' . http_build_query(array_merge($data['filters'],['page'=>$currentPage-1])) : '#' ?>"
           class="px-4 py-2 text-sm font-medium bg-white border rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $currentPage<=1?'opacity-50 cursor-not-allowed':'' ?>">
          Previous
        </a>
        <a href="<?= $currentPage<$totalPages ? '?' . http_build_query(array_merge($data['filters'],['page'=>$currentPage+1])) : '#' ?>"
           class="px-4 py-2 text-sm font-medium bg-white border rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $currentPage>=$totalPages?'opacity-50 cursor-not-allowed':'' ?>">
          Next
        </a>
      </nav>
    </div>
    <?php endif; ?>

  </section>
</div>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>

<!-- Scripts -->
<script>
  // File: public/js/driver-management.js

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const exportButton = document.getElementById('export-menu-button');
  const exportMenu   = document.getElementById('export-menu');
  const dateButtons  = document.querySelectorAll('.quick-date-btn');
  const dateFrom     = document.getElementById('date_from');
  const dateTo       = document.getElementById('date_to');
  const filterForm   = document.getElementById('filter-form');

  // --- Export Menu Toggle ---
  if (exportButton && exportMenu) {
    exportButton.addEventListener('click', e => {
      e.stopPropagation();
      exportMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', e => {
      if (!exportButton.contains(e.target) && !exportMenu.contains(e.target)) {
        exportMenu.classList.add('hidden');
      }
    });
  }

  // Utility to get YYYY-MM-DD string from Date
  function toDateInputString(date) {
    const yyyy = date.getFullYear();
    const mm   = String(date.getMonth() + 1).padStart(2, '0');
    const dd   = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  // --- Quick Date Buttons ---
  dateButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      // Clear previous active states
      dateButtons.forEach(b => b.classList.remove('active'));

      // Mark current as active
      btn.classList.add('active');

      const range = btn.dataset.range;
      const today = new Date();
      let from, to;

      switch (range) {
        case 'today':
          from = to = toDateInputString(today);
          break;
        case 'week': {
          // Start from Monday of this week
          const dayOfWeek = today.getDay(); // 0 (Sun) to 6 (Sat)
          const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1; 
          const monday = new Date(today);
          monday.setDate(today.getDate() - diff);
          from = toDateInputString(monday);
          to   = toDateInputString(today);
          break;
        }
        case 'month':
          from = toDateInputString(new Date(today.getFullYear(), today.getMonth(), 1));
          to   = toDateInputString(today);
          break;
        case 'all':
        default:
          from = '';
          to   = '';
      }

      dateFrom.value = from;
      dateTo.value   = to;

      // Submit form automatically
      filterForm.submit();
    });
  });

  // --- Initialize Active State on Load ---
  (function initDateButtons() {
    const curFrom = dateFrom.value;
    const curTo   = dateTo.value;
    const today   = toDateInputString(new Date());

    if (!curFrom && !curTo) {
      document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
    } else if (curFrom === today && curTo === today) {
      document.querySelector('.quick-date-btn[data-range="today"]')?.classList.add('active');
    } else if (curFrom && curTo) {
      const weekBtn  = document.querySelector('.quick-date-btn[data-range="week"]');
      const monthBtn = document.querySelector('.quick-date-btn[data-range="month"]');
      // Compare against computed week/month if desired
      // (optional: add logic to auto-select 'week' / 'month' if matches)
    }
  })();
});

</script>