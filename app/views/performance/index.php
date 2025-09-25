<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Performance Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive performance analytics and monitoring - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div id="daily-date-selector" style="display: none;">
                        <input type="date" id="daily-stats-date" value="<?php echo date('Y-m-d'); ?>"
                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button onclick="refreshCurrentTab()" id="refresh-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <button onclick="switchTab('overview')" id="tab-overview" class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" data-tab="overview">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Overview
                    </button>
                    <button onclick="switchTab('calls')" id="tab-calls" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="calls">
                        <i class="fas fa-phone mr-2"></i>
                        Calls
                    </button>
                    <button onclick="switchTab('users')" id="tab-users" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="users">
                        <i class="fas fa-users mr-2"></i>
                        Users
                    </button>
                    <button onclick="switchTab('teams')" id="tab-teams" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="teams">
                        <i class="fas fa-users-cog mr-2"></i>
                        Teams
                    </button>
                    <button onclick="switchTab('tickets')" id="tab-tickets" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="tickets">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        Tickets
                    </button>
                    <button onclick="switchTab('daily')" id="tab-daily" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="daily">
                        <i class="fas fa-calendar-day mr-2"></i>
                        Daily
                    </button>
                    <button onclick="switchTab('quality')" id="tab-quality" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="quality">
                        <i class="fas fa-star mr-2"></i>
                        Quality
                    </button>
                    <button onclick="switchTab('health')" id="tab-health" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="health">
                        <i class="fas fa-heartbeat mr-2"></i>
                        Health
                    </button>
                    <button onclick="switchTab('realtime')" id="tab-realtime" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="realtime">
                        <i class="fas fa-bolt mr-2"></i>
                        Real-time
                    </button>
                    <button onclick="switchTab('reports')" id="tab-reports" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="reports">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Reports
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Tab -->
        <div id="content-overview" class="tab-content">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8" id="overview-loading-state">
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

            <div id="overview-stats-container" style="display: none;">
                <!-- Today's Stats -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 mb-8">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Today's Statistics</h3>
                        <p class="text-sm text-gray-500">Current day metrics</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-blue-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-blue-600" id="today-tickets">0</div>
                                <div class="text-sm text-gray-500 mt-1">Total Tickets</div>
                            </div>
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-green-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-green-600" id="today-calls">0</div>
                                <div class="text-sm text-gray-500 mt-1">Total Calls</div>
                            </div>
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-purple-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-purple-600" id="today-vip">0</div>
                                <div class="text-sm text-gray-500 mt-1">VIP Tickets</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-8">
                    <!-- Yesterday -->
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Yesterday</h3>
                            <p class="text-sm text-gray-500">Previous day comparison</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Tickets:</span>
                                    <span class="font-bold text-blue-600" id="yesterday-tickets">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Calls:</span>
                                    <span class="font-bold text-green-600" id="yesterday-calls">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">VIP Tickets:</span>
                                    <span class="font-bold text-purple-600" id="yesterday-vip">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Average -->
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Weekly Average</h3>
                            <p class="text-sm text-gray-500">Last 7 days performance</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Daily Tickets:</span>
                                    <span class="font-bold text-blue-700" id="week-avg-tickets">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Daily Calls:</span>
                                    <span class="font-bold text-green-700" id="week-avg-calls">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Total This Week:</span>
                                    <span class="font-bold text-purple-700" id="week-total">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trends Chart -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Trends - Last 30 Days</h3>
                        <p class="text-sm text-gray-500">Daily evolution of tickets and calls</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div id="trends-chart-container" class="relative" style="height: 300px;">
                            <canvas id="trendsChart"></canvas>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                            <button onclick="toggleDataset(0)" class="flex items-center hover:bg-blue-50 px-3 py-1 rounded-full transition-colors duration-200" id="legend-tickets">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span>Tickets</span>
                            </button>
                            <button onclick="toggleDataset(1)" class="flex items-center hover:bg-green-50 px-3 py-1 rounded-full transition-colors duration-200" id="legend-calls">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span>Calls</span>
                            </button>
                            <button onclick="toggleDataset(2)" class="flex items-center hover:bg-purple-50 px-3 py-1 rounded-full transition-colors duration-200" id="legend-vip">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <span>VIP Tickets</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mt-8">
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
                            <button onclick="switchTab('daily')" class="text-blue-600 hover:text-blue-500 text-sm font-medium transition-colors duration-200">
                                View Details →
                            </button>
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
                            <button onclick="switchTab('teams')" class="text-green-600 hover:text-green-500 text-sm font-medium transition-colors duration-200">
                                View Performance →
                            </button>
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
                            <button onclick="switchTab('quality')" class="text-yellow-600 hover:text-yellow-500 text-sm font-medium transition-colors duration-200">
                                View Metrics →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 mt-8">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Calls Tab -->
        <div id="content-calls" class="tab-content hidden">
            <!-- Loading State -->
            <div id="calls-loading-state" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Loading call center statistics...</p>
            </div>

            <!-- Stats Container -->
            <div id="calls-stats-container" style="display: none;">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="p-4 lg:p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-phone h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                                </div>
                                <div class="mr-3 lg:mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Calls Today</dt>
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="total-calls-today">0</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="p-4 lg:p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-phone-volume h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                                </div>
                                <div class="mr-3 lg:mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Incoming Calls</dt>
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="incoming-calls">0</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="p-4 lg:p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-phone-square h-6 w-6 lg:h-8 lg:w-8 text-purple-600"></i>
                                </div>
                                <div class="mr-3 lg:mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Outgoing Calls</dt>
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="outgoing-calls">0</dd>
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Duration</dt>
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="avg-duration">0s</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call Trends Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Call Trends - Last 7 Days</h3>
                            <p class="text-sm text-gray-500">Daily call volume evolution</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div id="calls-trends-chart-container" class="relative" style="height: 250px;">
                                <canvas id="callsTrendsChart"></canvas>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                                <button onclick="toggleCallsDataset(0)" class="flex items-center hover:bg-blue-50 px-3 py-1 rounded-full transition-colors duration-200" id="legend-incoming">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                    <span>Incoming</span>
                                </button>
                                <button onclick="toggleCallsDataset(1)" class="flex items-center hover:bg-green-50 px-3 py-1 rounded-full transition-colors duration-200" id="legend-outgoing">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span>Outgoing</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Call Types Distribution Pie Chart -->
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Call Types Distribution</h3>
                            <p class="text-sm text-gray-500">Ratio between incoming and outgoing calls</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div id="calls-distribution-chart-container" class="relative" style="height: 300px;">
                                <canvas id="callsDistributionChart"></canvas>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                    <span>Incoming</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span>Outgoing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call Duration Chart -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 mb-8">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Call Duration Trends</h3>
                        <p class="text-sm text-gray-500">Average call duration over time</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div id="calls-duration-chart-container" class="relative" style="height: 250px;">
                            <canvas id="callsDurationChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Data Table -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Statistics</h3>
                        <p class="text-sm text-gray-500">Last 7 days call metrics</p>
                    </div>
                    <div class="overflow-x-auto">
                        <!-- Mobile view -->
                        <div class="block md:hidden" id="mobile-calls-table">
                            <!-- Mobile cards will be populated here -->
                        </div>
                        <!-- Desktop view -->
                        <table class="hidden md:table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incoming</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outgoing</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Duration</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="calls-table-body">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="content-users" class="tab-content hidden">
            <!-- Loading State -->
            <div id="users-loading-state" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Loading user performance data...</p>
            </div>

            <!-- Users Container -->
            <div id="users-stats-container" style="display: none;">
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
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="users-total-active-users">0</dd>
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
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="users-top-performer">Calculating...</dd>
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
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="users-avg-productivity">Calculating...</dd>
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
                                        <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="users-total-calls-today">Calculating...</dd>
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
            <div id="users-empty-state" style="display: none;" class="text-center py-12">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Performance Data</h3>
                <p class="text-gray-500">No user performance data found</p>
            </div>
        </div>

        <!-- Teams Tab -->
        <div id="content-teams" class="tab-content hidden">
            <!-- Loading State -->
            <div id="teams-loading-state" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Loading team data...</p>
            </div>

            <!-- Teams Container -->
            <div id="teams-stats-container" style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6" id="teams-grid">
                    <!-- Teams will be loaded here -->
                </div>
            </div>

            <!-- Empty State -->
            <div id="teams-empty-state" style="display: none;" class="text-center py-12">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Teams Found</h3>
                <p class="text-gray-500">No teams were found in the system</p>
            </div>
        </div>

        <div id="content-tickets" class="tab-content hidden">
            <div class="text-center py-12">
                <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tickets Analytics Tab</h3>
                <p class="text-gray-500">Content will be loaded here</p>
            </div>
        </div>

        <!-- Daily Tab -->
        <div id="content-daily" class="tab-content hidden">
            <!-- Loading State -->
            <div id="daily-loading-state" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Loading daily statistics...</p>
            </div>

            <!-- Daily Stats Container -->
            <div id="daily-stats-container" style="display: none;">
                <!-- Today's Stats -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 mb-8">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Today's Statistics</h3>
                        <p class="text-sm text-gray-500">Current day metrics</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-blue-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-blue-600" id="daily-today-tickets">0</div>
                                <div class="text-sm text-gray-500 mt-1">Total Tickets</div>
                            </div>
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-green-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-green-600" id="daily-today-calls">0</div>
                                <div class="text-sm text-gray-500 mt-1">Total Calls</div>
                            </div>
                            <div class="text-center bg-gray-50 rounded-lg p-4 hover:bg-purple-50 transition-colors duration-200">
                                <div class="text-3xl lg:text-4xl font-bold text-purple-600" id="daily-today-vip">0</div>
                                <div class="text-sm text-gray-500 mt-1">VIP Tickets</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-8">
                    <!-- Yesterday -->
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Yesterday</h3>
                            <p class="text-sm text-gray-500">Previous day comparison</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Tickets:</span>
                                    <span class="font-bold text-blue-600" id="daily-yesterday-tickets">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Calls:</span>
                                    <span class="font-bold text-green-600" id="daily-yesterday-calls">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">VIP Tickets:</span>
                                    <span class="font-bold text-purple-600" id="daily-yesterday-vip">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Average -->
                    <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                        <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Weekly Average</h3>
                            <p class="text-sm text-gray-500">Last 7 days performance</p>
                        </div>
                        <div class="p-4 lg:p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Daily Tickets:</span>
                                    <span class="font-bold text-blue-700" id="daily-week-avg-tickets">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Daily Calls:</span>
                                    <span class="font-bold text-green-700" id="daily-week-avg-calls">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Total This Week:</span>
                                    <span class="font-bold text-purple-700" id="daily-week-total">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trends Chart -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Trends - Last 30 Days</h3>
                        <p class="text-sm text-gray-500">Daily evolution of tickets and calls</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div id="daily-trends-chart-container" class="relative" style="height: 300px;">
                            <canvas id="dailyTrendsChart"></canvas>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                            <button onclick="toggleDailyDataset(0)" class="flex items-center hover:bg-blue-50 px-3 py-1 rounded-full transition-colors duration-200" id="daily-legend-tickets">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span>Tickets</span>
                            </button>
                            <button onclick="toggleDailyDataset(1)" class="flex items-center hover:bg-green-50 px-3 py-1 rounded-full transition-colors duration-200" id="daily-legend-calls">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span>Calls</span>
                            </button>
                            <button onclick="toggleDailyDataset(2)" class="flex items-center hover:bg-purple-50 px-3 py-1 rounded-full transition-colors duration-200" id="daily-legend-vip">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <span>VIP Tickets</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="content-quality" class="tab-content hidden">
            <div class="text-center py-12">
                <i class="fas fa-star text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Quality Metrics Tab</h3>
                <p class="text-gray-500">Content will be loaded here</p>
            </div>
        </div>

        <div id="content-health" class="tab-content hidden">
            <div class="text-center py-12">
                <i class="fas fa-heartbeat text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">System Health Tab</h3>
                <p class="text-gray-500">Content will be loaded here</p>
            </div>
        </div>

        <div id="content-realtime" class="tab-content hidden">
            <div class="text-center py-12">
                <i class="fas fa-bolt text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Real-time Dashboard Tab</h3>
                <p class="text-gray-500">Content will be loaded here</p>
            </div>
        </div>

        <div id="content-reports" class="tab-content hidden">
            <div class="text-center py-12">
                <i class="fas fa-chart-bar text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Performance Reports Tab</h3>
                <p class="text-gray-500">Content will be loaded here</p>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentTab = 'overview';
let overviewData = null;
let callsData = null;
let usersData = null;
let teamsData = null;
let dailyData = null;

// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');

    currentTab = tabName;

    // Show/hide date selector for daily tab
    const dateSelector = document.getElementById('daily-date-selector');
    if (tabName === 'daily') {
        dateSelector.style.display = 'block';
    } else {
        dateSelector.style.display = 'none';
    }

    // Load data for the selected tab if not already loaded
    loadTabData(tabName);
}

function loadTabData(tabName) {
    switch(tabName) {
        case 'overview':
            if (!overviewData) loadOverviewData();
            break;
        case 'calls':
            if (!callsData) loadCallsData();
            break;
        case 'users':
            if (!usersData) loadUsersData();
            break;
        case 'teams':
            if (!teamsData) loadTeamsData();
            break;
        case 'daily':
            if (!dailyData) loadDailyData();
            break;
        // Add other tabs as needed
    }
}

function refreshCurrentTab() {
    const refreshBtn = document.getElementById('refresh-btn');
    const icon = refreshBtn.querySelector('i');

    // Show loading state
    refreshBtn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin mr-2';

    // Reload current tab data
    loadTabData(currentTab);

    // Reset button after 2 seconds
    setTimeout(() => {
        refreshBtn.disabled = false;
        icon.className = 'fas fa-sync-alt mr-2';
    }, 2000);
}

// Overview tab functions
async function loadOverviewData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/overview');
        const data = await response.json();

        if (data.success) {
            overviewData = data.data;
            updateOverviewUI();
        } else {
            console.error('Failed to load overview data:', data.error);
            showError('Failed to load overview data');
        }
    } catch (error) {
        console.error('Error loading overview data:', error);
        showError('Network error occurred');
    }
}

function updateOverviewUI() {
    if (!overviewData) return;

    // Hide loading, show stats
    document.getElementById('overview-loading-state').style.display = 'none';
    document.getElementById('overview-stats-container').style.display = 'grid';

    // Update stats
    document.getElementById('today-tickets').textContent = overviewData.today.tickets || 0;
    document.getElementById('today-calls').textContent = overviewData.today.calls || 0;
    document.getElementById('today-vip').textContent = overviewData.today.vip_tickets || 0;

    // Update yesterday's stats
    document.getElementById('yesterday-tickets').textContent = overviewData.yesterday.tickets || 0;
    document.getElementById('yesterday-calls').textContent = overviewData.yesterday.calls || 0;
    document.getElementById('yesterday-vip').textContent = overviewData.yesterday.vip_tickets || 0;

    // Update weekly average
    document.getElementById('week-avg-tickets').textContent = overviewData.last_week_avg.avg_tickets_per_day || 0;
    document.getElementById('week-avg-calls').textContent = overviewData.last_week_avg.avg_calls_per_day || 0;
    document.getElementById('week-total').textContent = overviewData.last_week_avg.total_tickets || 0;

    // Update trends chart
    updateTrendsChart();

    // Update recent activity
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
                <p class="text-sm text-gray-900">Loaded ${overviewData.today.tickets} tickets</p>
            </div>
            <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('en-US')}</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">${overviewData.today.calls} active calls today</p>
            </div>
            <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('en-US')}</p>
        </div>
    `;
}

function updateTrendsChart() {
    if (!overviewData || !overviewData.trends) return;

    const trends = overviewData.trends;
    const labels = trends.map(item => item.formatted_date);
    const ticketsData = trends.map(item => item.tickets);
    const callsData = trends.map(item => item.calls);
    const vipData = trends.map(item => item.vip_tickets);

    // Destroy existing chart if it exists
    if (window.trendsChart instanceof Chart) {
        window.trendsChart.destroy();
    }

    const ctx = document.getElementById('trendsChart').getContext('2d');
    window.trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tickets',
                    data: ticketsData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                },
                {
                    label: 'Calls',
                    data: callsData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                },
                {
                    label: 'VIP Tickets',
                    data: vipData,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        title: function(context) {
                            return 'التاريخ: ' + trends[context[0].dataIndex].formatted_date;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'التاريخ'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'العدد'
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            elements: {
                point: {
                    hoverBorderWidth: 3
                }
            }
        }
    });

    // Update legend buttons based on current visibility
    updateLegendButtons();
}

function toggleDataset(datasetIndex) {
    if (!window.trendsChart) return;

    const dataset = window.trendsChart.data.datasets[datasetIndex];
    dataset.hidden = !dataset.hidden;

    window.trendsChart.update();

    // Update button appearance
    updateLegendButtons();
}

function updateLegendButtons() {
    if (!window.trendsChart) return;

    const buttons = ['legend-tickets', 'legend-calls', 'legend-vip'];

    window.trendsChart.data.datasets.forEach((dataset, index) => {
        const button = document.getElementById(buttons[index]);
        if (button) {
            if (dataset.hidden) {
                button.classList.add('opacity-50', 'line-through');
            } else {
                button.classList.remove('opacity-50', 'line-through');
            }
        }
    });
}

// Calls tab functions
async function loadCallsData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/call-stats');
        const data = await response.json();

        if (data.success) {
            callsData = data.data;
            updateCallsUI();
        } else {
            console.error('Failed to load calls data:', data.error);
            showError('فشل في تحميل إحصائيات المكالمات');
        }
    } catch (error) {
        console.error('Error loading calls data:', error);
        showError('خطأ في الشبكة');
    }
}

function updateCallsUI() {
    const container = document.getElementById('calls-stats-container');
    const loading = document.getElementById('calls-loading-state');

    loading.style.display = 'none';
    container.style.display = 'block';

    if (!callsData || callsData.length === 0) {
        return;
    }

    // Calculate today's stats
    const today = callsData[0] || {};
    const totalToday = (today.incoming_calls || 0) + (today.outgoing_calls || 0);
    const avgDuration = today.avg_incoming_duration || 0;

    // Update summary cards
    document.getElementById('total-calls-today').textContent = totalToday;
    document.getElementById('incoming-calls').textContent = today.incoming_calls || 0;
    document.getElementById('outgoing-calls').textContent = today.outgoing_calls || 0;
    document.getElementById('avg-duration').textContent = `${avgDuration}s`;

    // Update charts
    updateCallsTrendsChart();
    updateCallsDistributionChart();
    updateCallsDurationChart();

    // Update table
    const tableBody = document.getElementById('calls-table-body');
    tableBody.innerHTML = callsData.slice(0, 7).map(day => `
        <tr>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(day.date)}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.incoming_calls || 0}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.outgoing_calls || 0}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${(day.incoming_calls || 0) + (day.outgoing_calls || 0)}</td>
            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.avg_incoming_duration || 0}s</td>
        </tr>
    `).join('');

    // Update mobile view
    const mobileTable = document.getElementById('mobile-calls-table');
    mobileTable.innerHTML = callsData.slice(0, 7).map(day => `
        <div class="bg-gray-50 p-4 mb-2 rounded-lg">
            <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-gray-900">${formatDate(day.date)}</span>
                <span class="text-sm text-gray-500">Total: ${(day.incoming_calls || 0) + (day.outgoing_calls || 0)}</span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div class="text-center">
                    <div class="font-medium text-blue-600">${day.incoming_calls || 0}</div>
                    <div class="text-xs text-gray-500">Incoming</div>
                </div>
                <div class="text-center">
                    <div class="font-medium text-green-600">${day.outgoing_calls || 0}</div>
                    <div class="text-xs text-gray-500">Outgoing</div>
                </div>
                <div class="text-center">
                    <div class="font-medium text-purple-600">${day.avg_incoming_duration || 0}s</div>
                    <div class="text-xs text-gray-500">Avg Duration</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Chart update functions for calls
function updateCallsTrendsChart() {
    if (!callsData || callsData.length === 0) return;

    const trends = callsData.slice(0, 7);
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
    });
    const incomingData = trends.map(item => item.incoming_calls || 0);
    const outgoingData = trends.map(item => item.outgoing_calls || 0);

    if (window.callsTrendsChart instanceof Chart) {
        window.callsTrendsChart.destroy();
    }

    const ctx = document.getElementById('callsTrendsChart').getContext('2d');
    window.callsTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Incoming Calls',
                    data: incomingData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                },
                {
                    label: 'Outgoing Calls',
                    data: outgoingData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        title: function(context) {
                            const date = new Date(trends[context[0].dataIndex].date);
                            return date.toLocaleDateString('ar-EG', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'التاريخ'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'عدد المكالمات'
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            }
        }
    });
}

function updateCallsDistributionChart() {
    if (!callsData || callsData.length === 0) return;

    const totalIncoming = callsData.reduce((sum, day) => sum + (day.incoming_calls || 0), 0);
    const totalOutgoing = callsData.reduce((sum, day) => sum + (day.outgoing_calls || 0), 0);

    if (window.callsDistributionChart instanceof Chart) {
        window.callsDistributionChart.destroy();
    }

    const ctx = document.getElementById('callsDistributionChart').getContext('2d');
    window.callsDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Incoming Calls', 'Outgoing Calls'],
            datasets: [{
                data: [totalIncoming, totalOutgoing],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function updateCallsDurationChart() {
    if (!callsData || callsData.length === 0) return;

    const trends = callsData.slice(0, 7);
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
    });
    const incomingDuration = trends.map(item => item.avg_incoming_duration || 0);
    const outgoingDuration = trends.map(item => item.avg_outgoing_duration || 0);

    if (window.callsDurationChart instanceof Chart) {
        window.callsDurationChart.destroy();
    }

    const ctx = document.getElementById('callsDurationChart').getContext('2d');
    window.callsDurationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Incoming Call Duration',
                    data: incomingDuration,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Outgoing Call Duration',
                    data: outgoingDuration,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y} ثانية`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'المدة (ثانية)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'التاريخ'
                    }
                }
            }
        }
    });
}

function toggleCallsDataset(datasetIndex) {
    if (!window.callsTrendsChart) return;

    const dataset = window.callsTrendsChart.data.datasets[datasetIndex];
    dataset.hidden = !dataset.hidden;

    window.callsTrendsChart.update();

    // Update button appearance
    updateCallsLegendButtons();
}

function updateCallsLegendButtons() {
    if (!window.callsTrendsChart) return;

    const buttons = ['legend-incoming', 'legend-outgoing'];

    window.callsTrendsChart.data.datasets.forEach((dataset, index) => {
        const button = document.getElementById(buttons[index]);
        if (button) {
            if (dataset.hidden) {
                button.classList.add('opacity-50', 'line-through');
            } else {
                button.classList.remove('opacity-50', 'line-through');
            }
        }
    });
}

// Users tab functions
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
    const container = document.getElementById('users-stats-container');
    const loading = document.getElementById('users-loading-state');
    const empty = document.getElementById('users-empty-state');

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
    document.getElementById('users-total-active-users').textContent = totalUsers;
    document.getElementById('users-top-performer').textContent = topPerformer.name || 'N/A';
    document.getElementById('users-avg-productivity').textContent = avgProductivity;
    document.getElementById('users-total-calls-today').textContent = totalCalls;

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

function viewUserDetails(userId) {
    // Get current date for filtering
    const today = new Date();
    const dateFrom = today.toISOString().split('T')[0];
    const dateTo = dateFrom; // Same day for today filter

    // Redirect to users reports with user filter and today's date
    window.location.href = `<?php echo URLROOT; ?>/reports/users?user_id=${userId}&date_from=${dateFrom}&date_to=${dateTo}`;
}

// Teams tab functions
async function loadTeamsData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/team-performance');
        const data = await response.json();

        if (data.success) {
            teamsData = data.data;
            updateTeamsUI();
        } else {
            console.error('Failed to load teams data:', data.error);
            showError('Failed to load team data');
        }
    } catch (error) {
        console.error('Error loading teams data:', error);
        showError('Network error occurred');
    }
}

function updateTeamsUI() {
    const container = document.getElementById('teams-stats-container');
    const grid = document.getElementById('teams-grid');
    const loading = document.getElementById('teams-loading-state');
    const empty = document.getElementById('teams-empty-state');

    loading.style.display = 'none';

    if (!teamsData || teamsData.length === 0) {
        empty.style.display = 'block';
        container.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    container.style.display = 'block';

    grid.innerHTML = teamsData.map(team => `
        <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200 cursor-pointer" onclick="viewTeamDetails(${team.team_id})">
            <div class="p-4 lg:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="mr-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900 hover:text-blue-600 transition-colors">${team.team_name}</h3>
                        <p class="text-sm text-gray-500">Leader: ${team.leader_name}</p>
                    </div>
                    <div class="text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">${team.members_count}</div>
                        <div class="text-xs text-gray-500">Members</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${team.tickets_today}</div>
                        <div class="text-xs text-gray-500">Total Tickets</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">${team.calls_today}</div>
                        <div class="text-xs text-gray-500">Calls Today</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-sm font-semibold text-yellow-600">${team.vip_tickets_today}</div>
                        <div class="text-xs text-gray-500">VIP Tickets</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm font-semibold text-gray-600">${parseInt(team.tickets_today) - parseInt(team.vip_tickets_today)}</div>
                        <div class="text-xs text-gray-500">Normal Tickets</div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-500" style="width: ${team.members_count > 0 ? Math.min((team.tickets_today / team.members_count) * 25, 100) : 0}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Productivity Rate</p>
                </div>
            </div>
        </div>
    `).join('');
}

function viewTeamDetails(teamId) {
    // Get current date
    const now = new Date();

    // Get first day of current month
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const dateFrom = firstDay.toISOString().split('T')[0];

    // Get last day of current month
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    const dateTo = lastDay.toISOString().split('T')[0];

    // Redirect to users reports with team filter and current month date range
    window.location.href = `<?php echo URLROOT; ?>/reports/users?team_id=${teamId}&date_from=${dateFrom}&date_to=${dateTo}`;
}

// Daily tab functions
async function loadDailyData() {
    const dateInput = document.getElementById('daily-stats-date');
    const selectedDate = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];

    try {
        const response = await fetch(`<?php echo URLROOT; ?>/performance/api/daily-stats?date=${selectedDate}`);
        const data = await response.json();

        if (data.success) {
            dailyData = data.data;
            updateDailyUI();
        } else {
            console.error('Failed to load daily stats:', data.error);
            showError('Failed to load daily statistics');
        }
    } catch (error) {
        console.error('Error loading daily stats:', error);
        showError('Network error occurred');
    }
}

function updateDailyUI() {
    if (!dailyData) return;

    // Update today's stats
    document.getElementById('daily-today-tickets').textContent = dailyData.today.tickets || 0;
    document.getElementById('daily-today-calls').textContent = dailyData.today.calls || 0;
    document.getElementById('daily-today-vip').textContent = dailyData.today.vip_tickets || 0;

    // Update yesterday's stats
    document.getElementById('daily-yesterday-tickets').textContent = dailyData.yesterday.tickets || 0;
    document.getElementById('daily-yesterday-calls').textContent = dailyData.yesterday.calls || 0;
    document.getElementById('daily-yesterday-vip').textContent = dailyData.yesterday.vip_tickets || 0;

    // Update weekly average
    document.getElementById('daily-week-avg-tickets').textContent = dailyData.last_week_avg.avg_tickets_per_day || 0;
    document.getElementById('daily-week-avg-calls').textContent = dailyData.last_week_avg.avg_calls_per_day || 0;
    document.getElementById('daily-week-total').textContent = dailyData.last_week_avg.total_tickets || 0;

    // Update trends chart
    updateDailyTrendsChart();
}

function updateDailyTrendsChart() {
    if (!dailyData || !dailyData.trends) return;

    const trends = dailyData.trends;
    const labels = trends.map(item => item.formatted_date);
    const ticketsData = trends.map(item => item.tickets);
    const callsData = trends.map(item => item.calls);
    const vipData = trends.map(item => item.vip_tickets);

    // Destroy existing chart if it exists
    if (window.dailyTrendsChart instanceof Chart) {
        window.dailyTrendsChart.destroy();
    }

    const ctx = document.getElementById('dailyTrendsChart').getContext('2d');
    window.dailyTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tickets',
                    data: ticketsData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                },
                {
                    label: 'Calls',
                    data: callsData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                },
                {
                    label: 'VIP Tickets',
                    data: vipData,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        title: function(context) {
                            return 'التاريخ: ' + trends[context[0].dataIndex].formatted_date;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'التاريخ'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'العدد'
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            elements: {
                point: {
                    hoverBorderWidth: 3
                }
            }
        }
    });

    // Update legend buttons based on current visibility
    updateDailyLegendButtons();
}

function toggleDailyDataset(datasetIndex) {
    if (!window.dailyTrendsChart) return;

    const dataset = window.dailyTrendsChart.data.datasets[datasetIndex];
    dataset.hidden = !dataset.hidden;

    window.dailyTrendsChart.update();

    // Update button appearance
    updateDailyLegendButtons();
}

function updateDailyLegendButtons() {
    if (!window.dailyTrendsChart) return;

    const buttons = ['daily-legend-tickets', 'daily-legend-calls', 'daily-legend-vip'];

    window.dailyTrendsChart.data.datasets.forEach((dataset, index) => {
        const button = document.getElementById(buttons[index]);
        if (button) {
            if (dataset.hidden) {
                button.classList.add('opacity-50', 'line-through');
            } else {
                button.classList.remove('opacity-50', 'line-through');
            }
        }
    });
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadOverviewData();

    // Add event listener for daily date change
    const dailyDateInput = document.getElementById('daily-stats-date');
    if (dailyDateInput) {
        dailyDateInput.addEventListener('change', function() {
            if (currentTab === 'daily') {
                loadDailyData();
            }
        });
    }

    // Auto refresh every 5 minutes for overview
    setInterval(() => {
        if (currentTab === 'overview') {
            loadOverviewData();
        }
    }, 5 * 60 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
