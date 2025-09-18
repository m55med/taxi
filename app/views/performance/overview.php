<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Overview</h1>
                    <p class="mt-1 text-sm text-gray-500">System overview and key metrics - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div id="loading-state" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-300 rounded animate-pulse"></div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Loading...</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Repeat for other cards -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-300 rounded animate-pulse"></div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Loading...</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-300 rounded animate-pulse"></div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Loading...</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-300 rounded animate-pulse"></div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Loading...</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div id="stats-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8" style="display: none;">
            <!-- Tickets Today -->
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 cursor-pointer" onclick="viewTicketsToday()">
                <div class="p-4 lg:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-ticket-alt h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Tickets Today</dt>
                                <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="tickets-today">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm">
                            <span class="text-green-600 font-medium" id="tickets-change">+0%</span>
                            <span class="text-gray-500 ml-2">vs yesterday</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calls Today -->
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 cursor-pointer" onclick="viewCallsToday()">
                <div class="p-4 lg:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Calls Today</dt>
                                <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="calls-today">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm">
                            <span class="text-green-600 font-medium" id="calls-change">+0%</span>
                            <span class="text-gray-500 ml-2">vs yesterday</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 cursor-pointer" onclick="viewActiveUsers()">
                <div class="p-4 lg:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users h-6 w-6 lg:h-8 lg:w-8 text-purple-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="active-users">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm">
                            <span class="text-green-600 font-medium" id="users-change">+0%</span>
                            <span class="text-gray-500 ml-2">vs yesterday</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 cursor-pointer" onclick="viewSystemHealth()">
                <div class="p-4 lg:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-server h-6 w-6 lg:h-8 lg:w-8 text-yellow-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">System Health</dt>
                                <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="system-status">Good</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm">
                            <span class="text-green-600 font-medium">●</span>
                            <span class="text-gray-500 ml-2">All services running</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="p-4 lg:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-4 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Daily Analytics</h3>
                            <p class="mt-1 text-sm text-gray-500">View detailed statistics and trends</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo URLROOT; ?>/performance/daily" class="text-blue-600 hover:text-blue-500 text-sm font-medium transition-colors duration-200">
                            View Details →
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="p-4 lg:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-4 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Team Performance</h3>
                            <p class="mt-1 text-sm text-gray-500">Monitor teams and employee performance</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo URLROOT; ?>/performance/teams" class="text-green-600 hover:text-green-500 text-sm font-medium transition-colors duration-200">
                            View Performance →
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="p-4 lg:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-star h-6 w-6 lg:h-8 lg:w-8 text-yellow-600"></i>
                        </div>
                        <div class="mr-3 lg:mr-4 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Quality Metrics</h3>
                            <p class="mt-1 text-sm text-gray-500">Review quality and ratings</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo URLROOT; ?>/performance/quality" class="text-yellow-600 hover:text-yellow-500 text-sm font-medium transition-colors duration-200">
                            View Metrics →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
            <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                <p class="mt-1 text-sm text-gray-500">Latest system operations</p>
            </div>
            <div class="p-4 lg:p-6">
                <div id="recent-activity" class="space-y-4">
                    <div class="flex items-center space-x-3 animate-pulse">
                        <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                        </div>
                        <div class="h-3 bg-gray-300 rounded w-16"></div>
                    </div>
                    <div class="flex items-center space-x-3 animate-pulse">
                        <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-300 rounded w-1/2"></div>
                        </div>
                        <div class="h-3 bg-gray-300 rounded w-20"></div>
                    </div>
                    <div class="flex items-center space-x-3 animate-pulse">
                        <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-300 rounded w-2/3"></div>
                        </div>
                        <div class="h-3 bg-gray-300 rounded w-14"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let overviewData = null;

async function loadOverviewData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/overview');
        const data = await response.json();

        if (data.success) {
            overviewData = data.data;
            updateUI();
        } else {
            console.error('Failed to load overview data:', data.error);
            showError('Failed to load overview data');
        }
    } catch (error) {
        console.error('Error loading overview data:', error);
        showError('Network error occurred');
    }
}

function updateUI() {
    if (!overviewData) return;

    // Hide loading, show stats
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('stats-container').style.display = 'grid';

    // Update stats
    document.getElementById('tickets-today').textContent = overviewData.tickets.total || 0;
    document.getElementById('calls-today').textContent = (overviewData.calls.incoming + overviewData.calls.outgoing) || 0;
    document.getElementById('active-users').textContent = overviewData.users.active_users || 0;

    // Update recent activity (placeholder for now)
    const activityContainer = document.getElementById('recent-activity');
    activityContainer.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">Data updated successfully</p>
            </div>
            <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('en-US')}</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">Loaded ${overviewData.tickets.total} tickets</p>
            </div>
            <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('en-US')}</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">${overviewData.users.active_users} active users today</p>
            </div>
            <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('en-US')}</p>
        </div>
    `;
}

function viewTicketsToday() {
    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    // Redirect to tickets listings with today's date filters
    window.location.href = `<?php echo URLROOT; ?>/listings/tickets?search_term=&start_date=${today}&end_date=${today}&created_by=&platform_id=&classification_filter=&is_vip=&has_reviews=`;
}

function viewCallsToday() {
    // Redirect to calls listings or performance calls page
    window.location.href = `<?php echo URLROOT; ?>/performance/calls`;
}

function viewActiveUsers() {
    // Redirect to users performance page
    window.location.href = `<?php echo URLROOT; ?>/performance/users`;
}

function viewSystemHealth() {
    // Redirect to health page
    window.location.href = `<?php echo URLROOT; ?>/performance/health`;
}

function refreshData() {
    // Show loading state
    document.getElementById('loading-state').style.display = 'grid';
    document.getElementById('stats-container').style.display = 'none';

    // Reload data
    loadOverviewData();
}

function showError(message) {
    // Simple error notification
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
    loadOverviewData();

    // Auto refresh every 5 minutes
    setInterval(loadOverviewData, 5 * 60 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
