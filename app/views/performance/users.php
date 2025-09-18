<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Performance</h1>
                    <p class="mt-1 text-sm text-gray-500">Users and employees performance - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-500">Loading user performance data...</p>
        </div>

        <!-- Users Container -->
        <div id="users-container" style="display: none;">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Active Users</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="total-active-users">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-trophy h-6 w-6 lg:h-8 lg:w-8 text-yellow-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Top Performer</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="top-performer">Calculating...</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg Daily Tickets</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="avg-productivity">Calculating...</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone h-6 w-6 lg:h-8 lg:w-8 text-purple-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Calls Today</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="total-calls-today">Calculating...</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Performance Table -->
            <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Users Performance - Today</h3>
                    <p class="text-sm text-gray-500">Daily user activity and performance metrics</p>
                </div>
                <div class="overflow-x-auto">
                    <!-- Mobile view -->
                    <div class="block md:hidden" id="mobile-users-table">
                        <!-- Mobile cards will be populated here -->
                    </div>

                    <!-- Tablet view (md to lg) -->
                    <table class="hidden md:table lg:hidden min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calls</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vs Yesterday</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tablet-users-table-body">
                            <!-- Tablet data will be loaded here -->
                        </tbody>
                    </table>

                    <!-- Desktop view -->
                    <table class="hidden lg:table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Tickets</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP Tickets</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incoming Calls</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outgoing Calls</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vs Yesterday</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="users-table-body">
                            <!-- Desktop data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" style="display: none;" class="text-center py-12">
            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Performance Data</h3>
            <p class="text-gray-500">No user performance data found</p>
        </div>
    </div>
</div>

<script>
let usersData = null;

async function loadUsersData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/user-performance');
        const data = await response.json();

        if (data.success) {
            usersData = data.data;
            updateUsersUI();
        } else {
            console.error('Failed to load users data:', data.error);
            showError('Failed to load user data');
        }
    } catch (error) {
        console.error('Error loading users data:', error);
        showError('Network error occurred');
    }
}

function updateUsersUI() {
    const container = document.getElementById('users-container');
    const loading = document.getElementById('loading-state');
    const empty = document.getElementById('empty-state');

    loading.style.display = 'none';

    if (!usersData || usersData.length === 0) {
        empty.style.display = 'block';
        container.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    container.style.display = 'block';

    // Calculate real statistics
    const totalUsers = usersData.length;
    const totalTickets = usersData.reduce((sum, user) => sum + ((user.normal_tickets || 0) + (user.vip_tickets || 0)), 0);
    const totalCalls = usersData.reduce((sum, user) => sum + ((user.incoming_calls || 0) + (user.outgoing_calls || 0)), 0);

    const avgProductivity = totalUsers > 0 ? Math.round(totalTickets / totalUsers) : 0;
    const topPerformer = usersData.reduce((top, user) => {
        const userTotal = (user.normal_tickets || 0) + (user.vip_tickets || 0) + (user.incoming_calls || 0) + (user.outgoing_calls || 0);
        const topTotal = (top.normal_tickets || 0) + (top.vip_tickets || 0) + (top.incoming_calls || 0) + (top.outgoing_calls || 0);
        return userTotal > topTotal ? user : top;
    }, {});

    // Update summary
    document.getElementById('total-active-users').textContent = totalUsers;
    document.getElementById('top-performer').textContent = topPerformer.name || 'N/A';
    document.getElementById('avg-productivity').textContent = avgProductivity;
    document.getElementById('total-calls-today').textContent = totalCalls;

    // Update tablet table
    const tabletTableBody = document.getElementById('tablet-users-table-body');
    tabletTableBody.innerHTML = usersData.map((user, index) => `
        <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-150" onclick="viewUserDetails(${user.id})">
            <td class="px-3 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div>
                        <div class="text-sm font-medium text-gray-900">${user.name || 'User ' + (index + 1)}</div>
                        <div class="text-sm text-gray-500">${user.role || 'Not specified'}</div>
                    </div>
                </div>
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-blue-600 font-medium">${user.normal_tickets || 0}</span>
                        <span class="text-xs text-gray-500">Normal</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-yellow-600 font-medium">${user.vip_tickets || 0}</span>
                        <span class="text-xs text-gray-500">VIP</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Total: ${(user.normal_tickets || 0) + (user.vip_tickets || 0)}
                    </div>
                </div>
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-green-600 font-medium">${user.incoming_calls || 0}</span>
                        <span class="text-xs text-gray-500">In</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-purple-600 font-medium">${user.outgoing_calls || 0}</span>
                        <span class="text-xs text-gray-500">Out</span>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Total: ${(user.incoming_calls || 0) + (user.outgoing_calls || 0)}
                    </div>
                </div>
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm">
                <div class="space-y-2">
                    <div class="text-center">
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                            (user.tickets_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                            (user.tickets_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                            'bg-gray-100 text-gray-800'
                        }">
                            <i class="fas ${(user.tickets_change || 0) > 0 ? 'fa-arrow-up' : (user.tickets_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                            ${(user.tickets_change || 0) > 0 ? '+' : ''}${(user.tickets_change || 0)}%
                        </div>
                        <div class="text-xs text-gray-600 mt-1">T: ${user.yesterday_tickets || 0}</div>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                            (user.calls_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                            (user.calls_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                            'bg-gray-100 text-gray-800'
                        }">
                            <i class="fas ${(user.calls_change || 0) > 0 ? 'fa-arrow-up' : (user.calls_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                            ${(user.calls_change || 0) > 0 ? '+' : ''}${(user.calls_change || 0)}%
                        </div>
                        <div class="text-xs text-gray-600 mt-1">C: ${user.yesterday_calls || 0}</div>
                    </div>
                </div>
            </td>
        </tr>
    `).join('');

    // Update desktop table
    const tableBody = document.getElementById('users-table-body');
    tableBody.innerHTML = usersData.map((user, index) => `
        <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-150" onclick="viewUserDetails(${user.id})">
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${user.name || 'User ' + (index + 1)}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.role || 'Not specified'}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.normal_tickets || 0}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    ${user.vip_tickets || 0}
                </span>
            </td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.incoming_calls || 0}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.outgoing_calls || 0}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm">
                <div class="space-y-3">
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1 font-medium">Tickets vs Yesterday</div>
                        <div class="space-y-1">
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                (user.tickets_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                                (user.tickets_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }">
                                <i class="fas ${(user.tickets_change || 0) > 0 ? 'fa-arrow-up' : (user.tickets_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                                ${(user.tickets_change || 0) > 0 ? '+' : ''}${(user.tickets_change || 0)}%
                            </div>
                            <div class="text-xs text-gray-600">
                                Yesterday: ${user.yesterday_tickets || 0} tickets
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1 font-medium">Calls vs Yesterday</div>
                        <div class="space-y-1">
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                (user.calls_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                                (user.calls_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }">
                                <i class="fas ${(user.calls_change || 0) > 0 ? 'fa-arrow-up' : (user.calls_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                                ${(user.calls_change || 0) > 0 ? '+' : ''}${(user.calls_change || 0)}%
                            </div>
                            <div class="text-xs text-gray-600">
                                Yesterday: ${user.yesterday_calls || 0} calls
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    `).join('');

    // Update mobile view
    const mobileTable = document.getElementById('mobile-users-table');
    mobileTable.innerHTML = usersData.map((user, index) => `
        <div class="bg-white border border-gray-200 rounded-lg mb-3 overflow-hidden">
            <!-- Header -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors duration-150" onclick="viewUserDetails(${user.id})">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">${user.name || 'User ' + (index + 1)}</h4>
                        <p class="text-xs text-gray-500">${user.role || 'Not specified'}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-medium text-gray-600">Today</div>
                        <div class="text-sm font-bold text-gray-900">${(user.normal_tickets || 0) + (user.vip_tickets || 0)}T ${(user.incoming_calls || 0) + (user.outgoing_calls || 0)}C</div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="px-4 py-3">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-lg font-bold text-blue-600">${user.normal_tickets || 0}</div>
                        <div class="text-xs text-gray-500">Normal Tickets</div>
                        <div class="text-lg font-bold text-yellow-600 mt-1">${user.vip_tickets || 0}</div>
                        <div class="text-xs text-gray-500">VIP Tickets</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-green-600">${user.incoming_calls || 0}</div>
                        <div class="text-xs text-gray-500">Incoming Calls</div>
                        <div class="text-lg font-bold text-purple-600 mt-1">${user.outgoing_calls || 0}</div>
                        <div class="text-xs text-gray-500">Outgoing Calls</div>
                    </div>
                </div>

                <!-- Comparison Section -->
                <div class="border-t border-gray-200 pt-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-center">
                            <div class="text-xs text-gray-500 mb-1">Tickets vs Yesterday</div>
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                (user.tickets_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                                (user.tickets_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }">
                                <i class="fas ${(user.tickets_change || 0) > 0 ? 'fa-arrow-up' : (user.tickets_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                                ${(user.tickets_change || 0) > 0 ? '+' : ''}${(user.tickets_change || 0)}%
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Yesterday: ${user.yesterday_tickets || 0}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs text-gray-500 mb-1">Calls vs Yesterday</div>
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                (user.calls_change || 0) > 5 ? 'bg-green-100 text-green-800' :
                                (user.calls_change || 0) < -5 ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            }">
                                <i class="fas ${(user.calls_change || 0) > 0 ? 'fa-arrow-up' : (user.calls_change || 0) < 0 ? 'fa-arrow-down' : 'fa-minus'} mr-1"></i>
                                ${(user.calls_change || 0) > 0 ? '+' : ''}${(user.calls_change || 0)}%
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Yesterday: ${user.yesterday_calls || 0}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function refreshData() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('users-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';

    loadUsersData();
}

function viewUserDetails(userId) {
    // Get current date for filtering
    const today = new Date();
    const dateFrom = today.toISOString().split('T')[0];
    const dateTo = dateFrom; // Same day for today filter

    // Redirect to users reports with user filter and today's date
    window.location.href = `<?php echo URLROOT; ?>/reports/users?user_id=${userId}&date_from=${dateFrom}&date_to=${dateTo}`;
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUsersData();

    // Auto refresh every 10 minutes
    setInterval(loadUsersData, 10 * 60 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
