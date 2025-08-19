<?php include_once APPROOT . '/views/includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<div class="p-8 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">All Tickets</h1>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Filters</h2>
        <form id="filter-form" method="GET" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-2">
                    <label for="search_term" class="block text-sm font-medium text-gray-600 mb-1">Search (Ticket # or Phone)</label>
                    <input type="text" id="search_term" name="search_term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($data['filters']['search_term'] ?? '') ?>" placeholder="e.g., T-12345 or 968...">
                </div>
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-600 mb-1">Date Range</label>
                    <input type="text" id="date_range" name="date_range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($data['filters']['date_range'] ?? '') ?>" placeholder="Select date range">
                    <input type="hidden" id="start_date" name="start_date" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>">
                    <input type="hidden" id="end_date" name="end_date" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>">
                </div>
                <?php if (!\App\Core\Auth::hasRole('agent')): ?>
                <div>
                    <label for="created_by" class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                    <select id="created_by" name="created_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Users</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?= $user->id ?>" <?= (isset($data['filters']['created_by']) && $data['filters']['created_by'] == $user->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user->username) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label for="platform_id" class="block text-sm font-medium text-gray-600 mb-1">Platform</label>
                    <select id="platform_id" name="platform_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Platforms</option>
                        <?php foreach ($data['platforms'] as $platform): ?>
                            <option value="<?= $platform['id'] ?>" <?= (isset($data['filters']['platform_id']) && $data['filters']['platform_id'] == $platform['id']) ? 'selected' : '' ?>><?= htmlspecialchars($platform['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="is_vip" class="block text-sm font-medium text-gray-600 mb-1">VIP</label>
                    <select id="is_vip" name="is_vip" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="1" <?= (isset($data['filters']['is_vip']) && $data['filters']['is_vip'] == '1') ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= (isset($data['filters']['is_vip']) && $data['filters']['is_vip'] == '0') ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-between items-center">
                <div class="relative inline-block text-left">
                    <div>
                        <button type="button" id="export-button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500">
                            Export
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div id="export-menu" class="origin-top-right absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="export-button">
                        <div class="py-1" role="none">
                            <a href="#" id="export-excel" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem">
                                <i class="fas fa-file-excel mr-2 text-green-500"></i>Export to Excel
                            </a>
                            <a href="#" id="export-json" class="text-gray-700 block px-4 py-2 text-sm" role="menuitem">
                                <i class="fas fa-file-code mr-2 text-indigo-500"></i>Export to JSON
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="<?= URLROOT ?>/listings/tickets" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Reset</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Search</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php
        $is_agent = \App\Core\Auth::hasRole('agent');
        $is_editor = \App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('developer');
        $colspan = 6;
        if (!$is_agent) $colspan++;
        if ($is_editor) $colspan++;
        ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                    <?php if (!$is_agent): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creator</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Classification</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">VIP</th>
                    <?php if ($is_editor): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($data['tickets'])): ?>
                    <tr>
                        <td colspan="<?= $colspan ?>" class="text-center py-10 text-gray-500">No tickets found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['tickets'] as $ticket): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                <a href="<?= URLROOT ?>/tickets/view/<?= $ticket['ticket_id'] ?>" target="_blank" class="hover:underline"><?= htmlspecialchars($ticket['ticket_number']) ?></a>
                            </td>
                            <?php if (!$is_agent): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($ticket['created_by_username']) ?></td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($ticket['platform_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($ticket['phone'] ?? '') ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php 
                                $classification = implode(' > ', array_filter([
                                    $ticket['category_name'] ?? '',
                                    $ticket['subcategory_name'] ?? '',
                                    $ticket['code_name'] ?? ''
                                ]));
                                ?>
                                <span class="block truncate max-w-xs" title="<?= htmlspecialchars($classification) ?>"><?= htmlspecialchars($classification) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($ticket['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= $ticket['is_vip'] == 1 
                                    ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Yes</span>' 
                                    : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>' ?>
                            </td>
                            <?php if ($is_editor): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?= URLROOT ?>/tickets/edit/<?= $ticket['ticket_id'] ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filter-form');
    const ticketsTbody = document.querySelector('tbody');
    const initialTickets = <?= json_encode($data['tickets']) ?>;

    const updateTickets = (tickets) => {
        ticketsTbody.innerHTML = '';
        if (tickets.length === 0) {
            ticketsTbody.innerHTML = `<tr><td colspan="100%" class="text-center py-10 text-gray-500">No tickets found.</td></tr>`;
            return;
        }

        const isAgent = <?= json_encode(\App\Core\Auth::hasRole('agent')) ?>;
        const isEditor = <?= json_encode(\App\Core\Auth::hasRole('admin') || \App\Core\Auth::hasRole('developer')) ?>;

        tickets.forEach(ticket => {
            const classification = [ticket.category_name, ticket.subcategory_name, ticket.code_name].filter(Boolean).join(' > ');
            const createdAt = new Date(ticket.created_at).toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }).replace(',', '');

            const vipBadge = ticket.is_vip == 1 
                ? `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Yes</span>` 
                : `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>`;

            let rowHtml = `<tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                    <a href="<?= URLROOT ?>/tickets/view/${ticket.ticket_id}" target="_blank" class="hover:underline">${ticket.ticket_number}</a>
                </td>`;

            if (!isAgent) {
                rowHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.created_by_username}</td>`;
            }

            rowHtml += `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.platform_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${ticket.phone || ''}</td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <span class="block truncate max-w-xs" title="${classification}">${classification}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${createdAt}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">${vipBadge}</td>`;

            if (isEditor) {
                rowHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?= URLROOT ?>/tickets/edit/${ticket.ticket_id}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>`;
            }

            rowHtml += `</tr>`;
            ticketsTbody.innerHTML += rowHtml;
        });
    };

    const filterTickets = () => {
        const searchTerm = document.getElementById('search_term').value.toLowerCase();
        const createdBy = document.getElementById('created_by')?.value;
        
        const filteredTickets = initialTickets.filter(ticket => {
            const searchTermMatch = searchTerm === '' || 
                                    ticket.ticket_number.toLowerCase().includes(searchTerm) || 
                                    (ticket.phone && ticket.phone.toLowerCase().includes(searchTerm));

            const createdByMatch = !createdBy || ticket.created_by == createdBy;

            return searchTermMatch && createdByMatch;
        });

        updateTickets(filteredTickets);
    };

    let debounceTimer;
    filterForm.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(filterTickets, 300);
    });

    flatpickr("#date_range", {
        mode: 'range',
        dateFormat: 'Y-m-d',
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                document.getElementById('start_date').value = instance.formatDate(selectedDates[0], "Y-m-d");
                document.getElementById('end_date').value = instance.formatDate(selectedDates[1], "Y-m-d");
                filterForm.submit(); // Submit for server-side filtering
            }
        }
    });

    const exportButton = document.getElementById('export-button');
    const exportMenu = document.getElementById('export-menu');
    const exportExcel = document.getElementById('export-excel');
    const exportJson = document.getElementById('export-json');

    exportButton.addEventListener('click', () => {
        exportMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!exportButton.contains(event.target) && !exportMenu.contains(event.target)) {
            exportMenu.classList.add('hidden');
        }
    });

    const exportHandler = (format) => {
        const currentUrl = new URL(window.location.href);
        // Use the form's current state for export
        const formData = new FormData(filterForm);
        for (const [key, value] of formData.entries()) {
            currentUrl.searchParams.set(key, value);
        }
        currentUrl.searchParams.set('export', format);
        window.location.href = currentUrl.href;
    };

    exportExcel.addEventListener('click', (e) => {
        e.preventDefault();
        exportHandler('excel');
    });

    exportJson.addEventListener('click', (e) => {
        e.preventDefault();
        exportHandler('json');
    });
});
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
