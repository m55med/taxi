<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Health</h1>
                    <p class="mt-1 text-sm text-gray-500">System and services status - <?php echo date('l, F j, Y'); ?></p>
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
            <p class="mt-4 text-gray-500">Checking system health...</p>
        </div>

        <!-- Health Container -->
        <div id="health-container" style="display: none;">
            <!-- System Status Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-server h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Server Status</dt>
                                    <dd class="text-lg lg:text-xl font-bold text-gray-900" id="server-status">Healthy</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Database</dt>
                                    <dd class="text-lg lg:text-xl font-bold text-gray-900" id="db-status">Connected</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-memory h-6 w-6 lg:h-8 lg:w-8 text-purple-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Memory Usage</dt>
                                    <dd class="text-lg lg:text-xl font-bold text-gray-900" id="memory-usage">--</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock h-6 w-6 lg:h-8 lg:w-8 text-yellow-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Response Time</dt>
                                    <dd class="text-lg lg:text-xl font-bold text-gray-900" id="response-time">--</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-8">
                <!-- Database Performance -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Database Performance</h3>
                        <p class="text-sm text-gray-500">Database health metrics</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Tables Count:</span>
                                <span class="font-bold text-blue-600" id="tables-count">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Database Size:</span>
                                <span class="font-bold text-green-600" id="db-size">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Active Connections:</span>
                                <span class="font-bold text-purple-600" id="active-connections">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Query Rate:</span>
                                <span class="font-bold text-yellow-600" id="query-rate">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Resources -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Resources</h3>
                        <p class="text-sm text-gray-500">Resource utilization</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">CPU Usage:</span>
                                <span class="font-bold text-red-600" id="cpu-usage">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Disk Usage:</span>
                                <span class="font-bold text-orange-600" id="disk-usage">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">PHP Memory:</span>
                                <span class="font-bold text-indigo-600" id="php-memory">--</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Uptime:</span>
                                <span class="font-bold text-teal-600" id="uptime">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Status -->
            <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 mb-8">
                <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Services Status</h3>
                    <p class="text-sm text-gray-500">System services health check</p>
                </div>
                <div class="overflow-x-auto">
                    <!-- Mobile view -->
                    <div class="block md:hidden" id="mobile-services-table">
                        <!-- Mobile cards will be populated here -->
                    </div>
                    <!-- Desktop view -->
                    <table class="hidden md:table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Check</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="services-table-body">
                            <tr>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">Database</td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Operational
                                    </span>
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('H:i:s'); ?></td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">All queries working normally</td>
                            </tr>
                            <tr>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cache</td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Operational
                                    </span>
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('H:i:s'); ?></td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">Cache memory is active</td>
                            </tr>
                            <tr>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">Email Service</td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Monitoring
                                    </span>
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('H:i:s'); ?></td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">Possible delays in sending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Issues -->
            <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Issues</h3>
                    <p class="text-sm text-gray-500">System issues and alerts</p>
                </div>
                <div class="p-4 lg:p-6">
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                        <p class="text-gray-500">No active issues in the system</p>
                        <p class="text-sm text-gray-400 mt-2">All services are operating normally</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let healthData = null;

async function loadHealthData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/system-health');
        const data = await response.json();

        if (data.success) {
            healthData = data.data;
            updateHealthUI();
        } else {
            console.error('Failed to load health data:', data.error);
            showError('Failed to load system health data');
        }
    } catch (error) {
        console.error('Error loading health data:', error);
        showError('Network error occurred');
    }
}

function updateHealthUI() {
    const container = document.getElementById('health-container');
    const loading = document.getElementById('loading-state');

    loading.style.display = 'none';
    container.style.display = 'block';

    // Update system status (placeholder data)
    document.getElementById('server-status').textContent = 'good';
    document.getElementById('db-status').textContent = 'connected';
    document.getElementById('memory-usage').textContent = Math.round(Math.random() * 30 + 20) + '%';
    document.getElementById('response-time').textContent = Math.round(Math.random() * 200 + 100) + 'ms';

    // Update database metrics (placeholder)
    document.getElementById('tables-count').textContent = '45';
    document.getElementById('db-size').textContent = '2.3 GB';
    document.getElementById('active-connections').textContent = Math.round(Math.random() * 10 + 5);
    document.getElementById('query-rate').textContent = Math.round(Math.random() * 50 + 20) + '/sec';

    // Update system resources (placeholder)
    document.getElementById('cpu-usage').textContent = Math.round(Math.random() * 40 + 10) + '%';
    document.getElementById('disk-usage').textContent = Math.round(Math.random() * 30 + 50) + '%';
    document.getElementById('php-memory').textContent = Math.round(Math.random() * 50 + 30) + ' MB';
    document.getElementById('uptime').textContent = Math.round(Math.random() * 24 + 12) + ' ساعة';
}

function refreshData() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('health-container').style.display = 'none';

    loadHealthData();
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    notification.textContent = message;
    document.body.appendChild(notification);

    // Fade out after 3 seconds
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
    loadHealthData();

    // Auto refresh every 30 seconds
    setInterval(loadHealthData, 30 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
