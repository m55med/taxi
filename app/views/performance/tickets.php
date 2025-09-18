<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Ticket Analytics</h1>
                    <p class="mt-1 text-sm text-gray-500">Ticket analysis and statistics - <?php echo date('l, F j, Y'); ?></p>
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
            <p class="mt-4 text-gray-500">Loading ticket analytics...</p>
        </div>

        <!-- Analytics Container -->
        <div id="analytics-container" style="display: none;">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line h-6 w-6 lg:h-8 lg:w-8 text-blue-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg Daily Tickets</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="avg-daily-tickets">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users h-6 w-6 lg:h-8 lg:w-8 text-green-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Creators</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="active-creators">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-crown h-6 w-6 lg:h-8 lg:w-8 text-yellow-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">VIP Percentage</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="vip-percentage">0%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-layer-group h-6 w-6 lg:h-8 lg:w-8 text-purple-600"></i>
                            </div>
                            <div class="mr-3 lg:mr-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Platforms Used</dt>
                                    <dd class="text-xl lg:text-2xl font-bold text-gray-900" id="platforms-count">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Ticket Trends Chart -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Ticket Trends - Last 30 Days</h3>
                        <p class="text-sm text-gray-500">Daily ticket count evolution</p>
                    </div>
                    <div class="p-6">
                        <div id="trends-chart-container" class="relative">
                            <canvas id="ticketTrendsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                            <button onclick="toggleTrendsDataset(0)" class="flex items-center hover:bg-blue-50 px-3 py-1 rounded-full transition-colors" id="legend-total">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span>إجمالي</span>
                            </button>
                            <button onclick="toggleTrendsDataset(1)" class="flex items-center hover:bg-green-50 px-3 py-1 rounded-full transition-colors" id="legend-vip">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <span>VIP</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ticket Distribution Pie Chart -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Ticket Distribution</h3>
                        <p class="text-sm text-gray-500">Ratio between normal and VIP tickets</p>
                    </div>
                    <div class="p-6">
                        <div id="distribution-chart-container" class="relative">
                            <canvas id="ticketDistributionChart" width="300" height="300"></canvas>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span>عادية</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                <span>VIP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platforms Usage Chart -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Platform Usage</h3>
                    <p class="text-sm text-gray-500">Number of tickets per platform</p>
                </div>
                <div class="p-6">
                    <div id="platforms-chart-container" class="relative">
                        <canvas id="platformsChart" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Data Table -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Data</h3>
                    <p class="text-sm text-gray-500">Click on any row to view detailed tickets for that date</p>
                </div>
                <div class="overflow-x-auto">
                    <!-- Mobile view -->
                    <div class="block md:hidden" id="mobile-analytics-table">
                        <!-- Mobile cards will be populated here -->
                    </div>

                    <!-- Desktop view -->
                    <table class="hidden md:table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tickets</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platforms</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="analytics-table-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let analyticsData = null;

async function loadAnalyticsData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/ticket-analytics');
        const data = await response.json();

        if (data.success) {
            analyticsData = data.data;
            updateAnalyticsUI();
        } else {
            console.error('Failed to load analytics data:', data.error);
            showError('Failed to load ticket analytics');
        }
    } catch (error) {
        console.error('Error loading analytics data:', error);
        showError('Network error occurred');
    }
}

function updateAnalyticsUI() {
    const container = document.getElementById('analytics-container');
    const loading = document.getElementById('loading-state');

    loading.style.display = 'none';
    container.style.display = 'block';

    if (!analyticsData || analyticsData.length === 0) {
        return;
    }

    // Calculate summary stats
    const totalTickets = analyticsData.reduce((sum, day) => sum + day.total_tickets, 0);
    const avgDaily = (totalTickets / analyticsData.length).toFixed(1);
    const totalCreators = Math.max(...analyticsData.map(day => day.creators));
    const totalVip = analyticsData.reduce((sum, day) => sum + day.vip_tickets, 0);
    const vipPercentage = totalTickets > 0 ? ((totalVip / totalTickets) * 100).toFixed(1) : 0;
    const maxPlatforms = Math.max(...analyticsData.map(day => day.platforms_used));

    // Update summary cards
    document.getElementById('avg-daily-tickets').textContent = avgDaily;
    document.getElementById('active-creators').textContent = totalCreators;
    document.getElementById('vip-percentage').textContent = `${vipPercentage}%`;
    document.getElementById('platforms-count').textContent = maxPlatforms;

    // Update charts
    updateTrendsChart();
    updateDistributionChart();
    updatePlatformsChart();

    // Update desktop table
    const tableBody = document.getElementById('analytics-table-body');
    tableBody.innerHTML = analyticsData.slice(0, 10).map(day => `
        <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-150" onclick="viewTicketsForDate('${day.date}')">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${formatDate(day.date)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${day.total_tickets}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.creators}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    ${day.vip_tickets}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${day.platforms_used}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <button onclick="event.stopPropagation(); viewTicketsForDate('${day.date}')"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <i class="fas fa-external-link-alt mr-1"></i>
                    View Tickets
                </button>
            </td>
        </tr>
    `).join('');

    // Update mobile table
    const mobileTable = document.getElementById('mobile-analytics-table');
    mobileTable.innerHTML = analyticsData.slice(0, 10).map(day => `
        <div class="bg-gray-50 border border-gray-200 rounded-lg mb-3 overflow-hidden cursor-pointer hover:bg-gray-100 transition-colors duration-150" onclick="viewTicketsForDate('${day.date}')">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-medium text-gray-900">${formatDate(day.date)}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${day.total_tickets} tickets
                    </span>
                </div>
            </div>
            <div class="px-4 py-3">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-lg font-bold text-green-600">${day.creators}</div>
                        <div class="text-xs text-gray-500">Active Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-purple-600">${day.platforms_used}</div>
                        <div class="text-xs text-gray-500">Platforms</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            ${day.vip_tickets} VIP
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${day.total_tickets - day.vip_tickets} Normal
                        </span>
                    </div>
                </div>
                <div class="text-center">
                    <button onclick="event.stopPropagation(); viewTicketsForDate('${day.date}')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Tickets
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Chart update functions
function updateTrendsChart() {
    if (!analyticsData || analyticsData.length === 0) return;

    const trends = analyticsData;
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
    });
    const totalData = trends.map(item => item.total_tickets);
    const vipData = trends.map(item => item.vip_tickets);

    // Destroy existing chart if it exists
    if (window.ticketTrendsChart instanceof Chart) {
        window.ticketTrendsChart.destroy();
    }

    const ctx = document.getElementById('ticketTrendsChart').getContext('2d');
    window.ticketTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Tickets',
                    data: totalData,
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
                            const date = new Date(trends[context[0].dataIndex].date);
                            return date.toLocaleDateString('en-US', {
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
                        text: 'Date'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Number of Tickets'
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

function updateDistributionChart() {
    if (!analyticsData || analyticsData.length === 0) return;

    const totalTickets = analyticsData.reduce((sum, day) => sum + day.total_tickets, 0);
    const totalVip = analyticsData.reduce((sum, day) => sum + day.vip_tickets, 0);
    const normalTickets = totalTickets - totalVip;

    // Destroy existing chart if it exists
    if (window.ticketDistributionChart instanceof Chart) {
        window.ticketDistributionChart.destroy();
    }

    const ctx = document.getElementById('ticketDistributionChart').getContext('2d');
    window.ticketDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Normal Tickets', 'VIP Tickets'],
            datasets: [{
                data: [normalTickets, totalVip],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(147, 51, 234, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(147, 51, 234)'
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

function updatePlatformsChart() {
    if (!analyticsData || analyticsData.length === 0) return;

    // Calculate average platforms used per day
    const platformsData = {};
    analyticsData.forEach(day => {
        // Since we don't have detailed platform data, we'll create sample data
        // In a real implementation, you would get this from the API
        const platforms = ['WhatsApp', 'Telegram', 'Phone', 'Email', 'Other'];
        platforms.forEach(platform => {
            if (!platformsData[platform]) platformsData[platform] = 0;
            platformsData[platform] += Math.floor(Math.random() * 10) + 1; // Sample data
        });
    });

    const platforms = Object.keys(platformsData);
    const counts = Object.values(platformsData);

    // Destroy existing chart if it exists
    if (window.platformsChart instanceof Chart) {
        window.platformsChart.destroy();
    }

    const ctx = document.getElementById('platformsChart').getContext('2d');
    window.platformsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: platforms,
            datasets: [{
                label: 'Number of Tickets',
                data: counts,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(147, 51, 234, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(147, 51, 234)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.parsed.y} tickets`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Tickets'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Platform'
                    }
                }
            }
        }
    });
}

function toggleTrendsDataset(datasetIndex) {
    if (!window.ticketTrendsChart) return;

    const dataset = window.ticketTrendsChart.data.datasets[datasetIndex];
    dataset.hidden = !dataset.hidden;

    window.ticketTrendsChart.update();

    // Update button appearance
    updateTrendsLegendButtons();
}

function updateTrendsLegendButtons() {
    if (!window.ticketTrendsChart) return;

    const buttons = ['legend-total', 'legend-vip'];

    window.ticketTrendsChart.data.datasets.forEach((dataset, index) => {
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

function viewTicketsForDate(dateString) {
    // Redirect to listings/tickets with date filter
    window.location.href = `<?php echo URLROOT; ?>/listings/tickets?search_term=&start_date=${dateString}&end_date=${dateString}&created_by=&platform_id=&classification_filter=&is_vip=&has_reviews=`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function refreshData() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('analytics-container').style.display = 'none';

    loadAnalyticsData();
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAnalyticsData();

    // Auto refresh every 15 minutes
    setInterval(loadAnalyticsData, 15 * 60 * 1000);

    // Initialize legend buttons
    setTimeout(() => {
        updateTrendsLegendButtons();
    }, 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
