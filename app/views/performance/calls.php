<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-6 space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Call Center Statistics</h1>
                    <p class="mt-1 text-sm text-gray-500">Call center metrics and analytics - <?php echo date('l, F j, Y'); ?></p>
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
            <p class="mt-4 text-gray-500">Loading call center statistics...</p>
        </div>

        <!-- Stats Container -->
        <div id="stats-container" style="display: none;">
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

            <!-- Interactive Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Call Trends Chart -->
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
</div>

<script>
let callsData = null;

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
    const container = document.getElementById('stats-container');
    const loading = document.getElementById('loading-state');

    loading.style.display = 'none';
    container.style.display = 'block';

    if (!callsData || callsData.length === 0) {
        return;
    }

    // Calculate today's stats (first item in array)
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

    // Update table - desktop view
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

// Chart update functions
function updateCallsTrendsChart() {
    if (!callsData || callsData.length === 0) return;

    const trends = callsData.slice(0, 7); // Last 7 days
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
    });
    const incomingData = trends.map(item => item.incoming_calls || 0);
    const outgoingData = trends.map(item => item.outgoing_calls || 0);

    // Destroy existing chart if it exists
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

    // Calculate total for the period
    const totalIncoming = callsData.reduce((sum, day) => sum + (day.incoming_calls || 0), 0);
    const totalOutgoing = callsData.reduce((sum, day) => sum + (day.outgoing_calls || 0), 0);

    // Destroy existing chart if it exists
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

    const trends = callsData.slice(0, 7); // Last 7 days
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
    });
    const incomingDuration = trends.map(item => item.avg_incoming_duration || 0);
    const outgoingDuration = trends.map(item => item.avg_outgoing_duration || 0);

    // Destroy existing chart if it exists
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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function refreshData() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('stats-container').style.display = 'none';

    loadCallsData();
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
    loadCallsData();

    // Auto refresh every 10 minutes
    setInterval(loadCallsData, 10 * 60 * 1000);

    // Initialize legend buttons
    setTimeout(() => {
        updateCallsLegendButtons();
    }, 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
