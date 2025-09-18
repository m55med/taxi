<?php require_once APPROOT . '/views/includes/header.php'; ?>

<!-- Chart.js for interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Performance Reports</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive performance reports - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <input type="date" id="report-date-from" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>"
                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <span class="text-gray-500">to</span>
                        <input type="date" id="report-date-to" value="<?php echo date('Y-m-d'); ?>"
                               class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button onclick="generateReport()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Report Summary -->
        <div id="report-summary" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" style="display: none;">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-trophy h-8 w-8 text-yellow-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Best Performer</dt>
                                <dd class="text-lg font-medium text-gray-900" id="best-performer">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line h-8 w-8 text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Growth Rate</dt>
                                <dd class="text-lg font-medium text-gray-900" id="growth-rate">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-star h-8 w-8 text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Average Quality</dt>
                                <dd class="text-lg font-medium text-gray-900" id="avg-quality">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock h-8 w-8 text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Avg Response Time</dt>
                                <dd class="text-lg font-medium text-gray-900" id="avg-response">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Sections -->
        <div id="report-content" style="display: none;">
            <!-- Executive Summary -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Executive Summary</h3>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none" id="executive-summary">
                        <p class="text-gray-600">Preparing executive summary...</p>
                    </div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-8">
                <!-- Productivity Trends -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Productivity Trends</h3>
                        <p class="text-sm text-gray-500">Daily productivity analysis</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div id="productivity-chart-container" class="relative" style="height: 300px;">
                            <canvas id="productivity-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Quality Metrics -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quality Distribution</h3>
                        <p class="text-sm text-gray-500">Review quality breakdown</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div id="quality-chart-container" class="relative" style="height: 300px;">
                            <canvas id="quality-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-8">
                <!-- Top Performers -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Top Performers</h3>
                        <p class="text-sm text-gray-500">Highest scoring users</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="top-performers-body">
                                <!-- Data will be loaded -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Efficiency Metrics -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Efficiency Metrics</h3>
                        <p class="text-sm text-gray-500">Performance indicators</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="space-y-4" id="efficiency-metrics">
                            <p class="text-gray-600">Calculating efficiency metrics...</p>
                        </div>
                    </div>
                </div>

                <!-- Areas for Improvement -->
                <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Improvement Areas</h3>
                        <p class="text-sm text-gray-500">Focus areas for enhancement</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <div class="space-y-3" id="improvement-areas">
                            <p class="text-gray-600">Analyzing improvement areas...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white shadow-sm hover:shadow-md rounded-lg transition-shadow duration-200">
                <div class="px-4 lg:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recommendations</h3>
                    <p class="text-sm text-gray-500">Suggested improvements and actions</p>
                </div>
                <div class="p-4 lg:p-6">
                    <div class="space-y-4" id="recommendations">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-yellow-500 mt-1"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Preparing recommendations...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-500">Generating report...</p>
            <p class="text-sm text-gray-400 mt-2">This may take a few seconds</p>
        </div>

        <!-- Empty State -->
        <div id="empty-state" style="display: none;" class="text-center py-12">
            <i class="fas fa-chart-bar text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Data Available</h3>
            <p class="text-gray-500">Please ensure you have selected a valid date range</p>
        </div>
    </div>
</div>

<script>
let reportData = null;

async function generateReport() {
    const dateFrom = document.getElementById('report-date-from').value;
    const dateTo = document.getElementById('report-date-to').value;

    // Show loading, hide others
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('report-summary').style.display = 'none';
    document.getElementById('report-content').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';

    try {
        const response = await fetch(`<?php echo URLROOT; ?>/performance/api/performance-reports?from=${dateFrom}&to=${dateTo}`);
        const data = await response.json();

        if (data.success && data.data) {
            reportData = data.data;
            updateReportUI();
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showError('Failed to generate report');
        showEmptyState();
    }

    document.getElementById('loading-state').style.display = 'none';
}

function updateReportUI() {
    if (!reportData) return;

    // Show report sections
    document.getElementById('report-summary').style.display = 'grid';
    document.getElementById('report-content').style.display = 'block';

    // Update summary cards with real data
    document.getElementById('best-performer').textContent = reportData.summary.best_performer;
    document.getElementById('growth-rate').textContent = reportData.summary.growth_rate;
    document.getElementById('avg-quality').textContent = reportData.summary.avg_quality;
    document.getElementById('avg-response').textContent = reportData.summary.avg_response;

    // Update executive summary
    document.getElementById('executive-summary').innerHTML = `
        <p class="text-gray-700 mb-4">
            The report shows performance trends for the period from ${formatDate(document.getElementById('report-date-from').value)}
            to ${formatDate(document.getElementById('report-date-to').value)}.
        </p>
        <ul class="list-disc list-inside text-gray-600 space-y-2">
            <li>Growth rate: ${reportData.summary.growth_rate}</li>
            <li>Average quality rating: ${reportData.summary.avg_quality}</li>
            <li>Average response time: ${reportData.summary.avg_response}</li>
            <li>Best performer: ${reportData.summary.best_performer}</li>
        </ul>
    `;

    // Update charts
    updateProductivityChart();
    updateQualityChart();

    // Update top performers table
    const performersBody = document.getElementById('top-performers-body');
    const dateFrom = document.getElementById('report-date-from').value;
    const dateTo = document.getElementById('report-date-to').value;

    performersBody.innerHTML = reportData.top_performers.map(performer => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                            <span class="text-xs font-medium text-white">${performer.name.charAt(0).toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">${performer.name}</div>
                        <div class="text-xs text-gray-500">${performer.team_name || 'No Team'}</div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <i class="fas fa-star mr-1"></i>
                    ${performer.points}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                <button onclick="viewUserDetails('${performer.user_id}', '${dateFrom}', '${dateTo}')"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-external-link-alt mr-1"></i>
                    View Details
                </button>
            </td>
        </tr>
    `).join('');

    // Update efficiency metrics
    document.getElementById('efficiency-metrics').innerHTML = `
        <div class="flex justify-between">
            <span class="text-sm text-gray-600">Completion Rate:</span>
            <span class="font-medium text-green-600">${reportData.efficiency_metrics.completion_rate}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-sm text-gray-600">Satisfaction Rate:</span>
            <span class="font-medium text-blue-600">${reportData.efficiency_metrics.satisfaction_rate}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-sm text-gray-600">Time Efficiency:</span>
            <span class="font-medium text-purple-600">${reportData.efficiency_metrics.time_efficiency}</span>
        </div>
    `;

    // Update improvement areas
    document.getElementById('improvement-areas').innerHTML = `
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
            <div>
                <p class="text-sm text-gray-900">Optimize response times</p>
                <p class="text-xs text-gray-500">Goal: Reduce response time by 20%</p>
            </div>
        </div>
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
            <div>
                <p class="text-sm text-gray-900">Staff training enhancement</p>
                <p class="text-xs text-gray-500">Goal: Reduce training period</p>
            </div>
        </div>
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
            <div>
                <p class="text-sm text-gray-900">Improve tracking systems</p>
                <p class="text-xs text-gray-500">Goal: Increase accuracy by 15%</p>
            </div>
        </div>
    `;

    // Update recommendations with better content
    const recommendations = reportData.recommendations && reportData.recommendations.length > 0 ? reportData.recommendations : [
        'Implement automated task distribution system to improve efficiency',
        'Enhance training program for new employees to reduce onboarding time',
        'Apply continuous performance monitoring system for real-time insights',
        'Optimize response time protocols to improve customer satisfaction',
        'Develop skill-based routing for complex issues'
    ];

    document.getElementById('recommendations').innerHTML = recommendations.map((rec, index) => {
        const icons = ['fa-cogs', 'fa-users', 'fa-chart-line', 'fa-clock', 'fa-route'];
        const colors = ['text-blue-500', 'text-green-500', 'text-purple-500', 'text-orange-500', 'text-red-500'];
        const priorities = ['High', 'Medium', 'Medium', 'High', 'Medium'];
        const priorityColors = ['bg-red-100 text-red-800', 'bg-yellow-100 text-yellow-800', 'bg-yellow-100 text-yellow-800', 'bg-red-100 text-red-800', 'bg-yellow-100 text-yellow-800'];

        return `
            <div class="flex items-start space-x-3 mb-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex-shrink-0">
                    <i class="fas ${icons[index] || 'fa-lightbulb'} ${colors[index] || 'text-gray-500'} mt-1"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-900">${rec}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${priorityColors[index] || 'bg-gray-100 text-gray-800'}">
                            ${priorities[index] || 'Medium'} Priority
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">Expected impact: ${['High efficiency gain', 'Improved quality', 'Better monitoring', 'Faster response', 'Optimized routing'][index] || 'Performance improvement'}</p>
                </div>
            </div>
        `;
    }).join('');
}

function updateProductivityChart() {
    if (!reportData || !reportData.charts_data.productivity_trends.length) return;

    const trends = reportData.charts_data.productivity_trends;
    const labels = trends.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });

    // Destroy existing chart if it exists
    if (window.productivityChart instanceof Chart) {
        window.productivityChart.destroy();
    }

    const ctx = document.getElementById('productivity-chart').getContext('2d');
    window.productivityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tickets',
                    data: trends.map(item => item.tickets),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Active Users',
                    data: trends.map(item => item.users),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
}

function updateQualityChart() {
    if (!reportData || !reportData.charts_data.quality_distribution.length) return;

    const qualityData = reportData.charts_data.quality_distribution;
    const labels = qualityData.map(item => item.level);
    const data = qualityData.map(item => item.count);

    // Destroy existing chart if it exists
    if (window.qualityChart instanceof Chart) {
        window.qualityChart.destroy();
    }

    const ctx = document.getElementById('quality-chart').getContext('2d');
    window.qualityChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Reviews',
                data: data,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)'
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
                            return `${context.label}: ${context.parsed.y} reviews`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Reviews'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Quality Level'
                    }
                }
            }
        }
    });
}

function viewUserDetails(userId, dateFrom, dateTo) {
    // Redirect to user reports page with filters
    const url = `<?php echo URLROOT; ?>/reports/users?role_id=&user_id=${userId}&team_id=&status=&date_from=${dateFrom}&date_to=${dateTo}&per_page=25`;
    window.open(url, '_blank');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function showEmptyState() {
    document.getElementById('empty-state').style.display = 'block';
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

// Load initial report on page load
document.addEventListener('DOMContentLoaded', function() {
    generateReport();
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
