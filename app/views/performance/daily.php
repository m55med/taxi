<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Daily Statistics</h1>
                    <p class="mt-1 text-sm text-gray-500">Daily metrics and analytics - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <input type="date" id="stats-date" value="<?php echo $data['today']; ?>"
                           class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <button onclick="loadDailyStats()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>
                        View
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
            <p class="mt-4 text-gray-500">Loading daily statistics...</p>
        </div>

        <!-- Stats Container -->
        <div id="stats-container" style="display: none;">
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
    </div>
</div>

<script>
let dailyStatsData = null;

async function loadDailyStats() {
    const dateInput = document.getElementById('stats-date');
    const selectedDate = dateInput.value;

    // Show loading
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('stats-container').style.display = 'none';

    try {
        const response = await fetch(`<?php echo URLROOT; ?>/performance/api/daily-stats?date=${selectedDate}`);
        const data = await response.json();

        if (data.success) {
            dailyStatsData = data.data;
            updateDailyUI();
        } else {
            console.error('Failed to load daily stats:', data.error);
            showError('Failed to load daily statistics');
        }
    } catch (error) {
        console.error('Error loading daily stats:', error);
        showError('Network error occurred');
    }

    // Hide loading
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('stats-container').style.display = 'block';
}

function updateDailyUI() {
    if (!dailyStatsData) return;

    // Update today's stats
    document.getElementById('today-tickets').textContent = dailyStatsData.today.tickets || 0;
    document.getElementById('today-calls').textContent = dailyStatsData.today.calls || 0;
    document.getElementById('today-vip').textContent = dailyStatsData.today.vip_tickets || 0;

    // Update yesterday's stats
    document.getElementById('yesterday-tickets').textContent = dailyStatsData.yesterday.tickets || 0;
    document.getElementById('yesterday-calls').textContent = dailyStatsData.yesterday.calls || 0;
    document.getElementById('yesterday-vip').textContent = dailyStatsData.yesterday.vip_tickets || 0;

    // Update weekly average
    document.getElementById('week-avg-tickets').textContent = dailyStatsData.last_week_avg.avg_tickets_per_day || 0;
    document.getElementById('week-avg-calls').textContent = dailyStatsData.last_week_avg.avg_calls_per_day || 0;
    document.getElementById('week-total').textContent = dailyStatsData.last_week_avg.total_tickets || 0;

    // Update trends chart
    updateTrendsChart();
}

function updateTrendsChart() {
    if (!dailyStatsData || !dailyStatsData.trends) return;

    const trends = dailyStatsData.trends;
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
                    display: false // Hide legend as we have custom legend below
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
            },
            onClick: (event, activeElements) => {
                // Handle chart clicks if needed
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
    loadDailyStats();

    // Reload when date changes
    document.getElementById('stats-date').addEventListener('change', loadDailyStats);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
