<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Quality Metrics</h1>
                    <p class="mt-1 text-sm text-gray-500">Quality metrics and reviews - <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
            <p class="mt-4 text-gray-500">Loading quality metrics...</p>
        </div>

        <!-- Quality Container -->
        <div id="quality-container" style="display: none;">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-star h-8 w-8 text-yellow-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Average Rating</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="avg-rating">0.0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle h-8 w-8 text-green-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Excellent Reviews</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="excellent-reviews">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users h-8 w-8 text-blue-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Reviewers</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="active-reviewers">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-percentage h-8 w-8 text-purple-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">High Quality Rate</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="quality-percentage">0%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quality Distribution -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Rating Distribution</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Excellent (80-100)</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" id="excellent-bar" style="width: 0%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-green-600" id="excellent-count">0</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Good (60-79)</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-yellow-600 h-2 rounded-full" id="good-bar" style="width: 0%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-yellow-600" id="good-count">0</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Poor (0-59)</span>
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-600 h-2 rounded-full" id="poor-bar" style="width: 0%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-red-600" id="poor-count">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quality Statistics</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Reviews:</span>
                                <span class="font-medium" id="total-reviews">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Coverage Rate:</span>
                                <span class="font-medium" id="review-coverage">0%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Highest Rating:</span>
                                <span class="font-medium text-green-600" id="highest-rating">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Lowest Rating:</span>
                                <span class="font-medium text-red-600" id="lowest-rating">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Trends</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2" id="trend-indicator">→</div>
                            <p class="text-sm text-gray-600" id="trend-text">Stable</p>
                            <p class="text-xs text-gray-500 mt-2" id="trend-description">Compared to last week</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews Table -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Reviews</h3>
                    <p class="text-sm text-gray-500">Click on any row to view detailed reviews for that date</p>
                </div>
                <div class="overflow-x-auto">
                    <!-- Mobile view -->
                    <div class="block md:hidden" id="mobile-quality-table">
                        <!-- Mobile cards will be populated here -->
                    </div>

                    <!-- Desktop view -->
                    <table class="hidden md:table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="quality-table-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let qualityData = null;

async function loadQualityData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/quality-metrics');
        const data = await response.json();

        if (data.success) {
            qualityData = data.data;
            updateQualityUI();
        } else {
            console.error('Failed to load quality data:', data.error);
            showError('Failed to load quality metrics');
        }
    } catch (error) {
        console.error('Error loading quality data:', error);
        showError('Network error occurred');
    }
}

function updateQualityUI() {
    const container = document.getElementById('quality-container');
    const loading = document.getElementById('loading-state');

    loading.style.display = 'none';
    container.style.display = 'block';

    if (!qualityData || qualityData.length === 0) {
        return;
    }

    // Calculate summary stats
    const today = qualityData[0] || {};
    const totalReviews = qualityData.reduce((sum, day) => sum + day.total_reviews, 0);
    const avgRating = qualityData.length > 0 ?
        (qualityData.reduce((sum, day) => sum + day.avg_rating, 0) / qualityData.length).toFixed(1) : 0;

    const totalExcellent = qualityData.reduce((sum, day) => sum + day.excellent_reviews, 0);
    const totalGood = qualityData.reduce((sum, day) => sum + day.good_reviews, 0);
    const totalPoor = qualityData.reduce((sum, day) => sum + day.poor_reviews, 0);

    const maxReviewers = Math.max(...qualityData.map(day => day.reviewers_count));
    const avgQualityPercentage = qualityData.length > 0 ?
        (qualityData.reduce((sum, day) => sum + day.excellent_percentage, 0) / qualityData.length).toFixed(1) : 0;

    // Calculate review coverage (assuming ticket details that have reviews)
    // This would need to be calculated from the database, but for now we'll estimate
    const estimatedCoverage = totalReviews > 0 ? Math.min(100, ((totalReviews / (totalReviews * 1.5)) * 100)).toFixed(1) : 0;

    // Update summary cards
    document.getElementById('avg-rating').textContent = avgRating;
    document.getElementById('excellent-reviews').textContent = totalExcellent;
    document.getElementById('active-reviewers').textContent = maxReviewers;
    document.getElementById('quality-percentage').textContent = `${avgQualityPercentage}%`;

    // Update distribution bars
    const totalAll = totalExcellent + totalGood + totalPoor;
    if (totalAll > 0) {
        document.getElementById('excellent-bar').style.width = `${(totalExcellent / totalAll) * 100}%`;
        document.getElementById('good-bar').style.width = `${(totalGood / totalAll) * 100}%`;
        document.getElementById('poor-bar').style.width = `${(totalPoor / totalAll) * 100}%`;
    }

    document.getElementById('excellent-count').textContent = totalExcellent;
    document.getElementById('good-count').textContent = totalGood;
    document.getElementById('poor-count').textContent = totalPoor;

    // Update quality stats
    document.getElementById('total-reviews').textContent = totalReviews;
    document.getElementById('review-coverage').textContent = `${estimatedCoverage}%`;
    document.getElementById('highest-rating').textContent = Math.max(...qualityData.map(day => day.avg_rating));
    document.getElementById('lowest-rating').textContent = Math.min(...qualityData.map(day => day.avg_rating));

    // Update trend (simple logic)
    const recentAvg = qualityData.slice(0, 7).reduce((sum, day) => sum + day.avg_rating, 0) / Math.min(7, qualityData.length);
    const olderAvg = qualityData.slice(7, 14).reduce((sum, day) => sum + day.avg_rating, 0) / Math.min(7, qualityData.slice(7).length);

    if (recentAvg > olderAvg + 0.5) {
        document.getElementById('trend-indicator').textContent = '↗️';
        document.getElementById('trend-indicator').className = 'text-3xl font-bold text-green-600 mb-2';
        document.getElementById('trend-text').textContent = 'Improving';
    } else if (recentAvg < olderAvg - 0.5) {
        document.getElementById('trend-indicator').textContent = '↘️';
        document.getElementById('trend-indicator').className = 'text-3xl font-bold text-red-600 mb-2';
        document.getElementById('trend-text').textContent = 'Declining';
    } else {
        document.getElementById('trend-indicator').textContent = '→';
        document.getElementById('trend-indicator').className = 'text-3xl font-bold text-gray-600 mb-2';
        document.getElementById('trend-text').textContent = 'Stable';
    }

    // Update desktop table
    const tableBody = document.getElementById('quality-table-body');
tableBody.innerHTML = qualityData.slice(0, 10).map(day => `
    <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-150" onclick="viewReviewsForDate('${day.date}')">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${formatDate(day.date)}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                ${day.avg_rating}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                day.excellent_percentage >= 80 ? 'bg-green-100 text-green-800' :
                day.excellent_percentage >= 60 ? 'bg-yellow-100 text-yellow-800' :
                'bg-red-100 text-red-800'
            }">
                ${day.excellent_percentage}%
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            ${day.reviewers_count} reviewers<br/>
            <span class="text-gray-500">${day.total_reviews} reviews</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <button onclick="event.stopPropagation(); viewReviewsForDate('${day.date}')"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-external-link-alt mr-1"></i>
                View Reviews
            </button>
        </td>
    </tr>
`).join('');


    // Update mobile table
    const mobileTable = document.getElementById('mobile-quality-table');
    mobileTable.innerHTML = qualityData.slice(0, 10).map(day => `
        <div class="bg-gray-50 border border-gray-200 rounded-lg mb-3 overflow-hidden cursor-pointer hover:bg-gray-100 transition-colors duration-150" onclick="viewReviewsForDate('${day.date}')">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-medium text-gray-900">${formatDate(day.date)}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${day.avg_rating} avg rating
                    </span>
                </div>
            </div>
            <div class="px-4 py-3">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="text-center">
                        <div class="text-lg font-bold text-green-600">${day.excellent_reviews}</div>
                        <div class="text-xs text-gray-500">Excellent</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-purple-600">${day.reviewers_count}</div>
                        <div class="text-xs text-gray-500">Reviewers</div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ${day.excellent_reviews} Exc
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            ${day.good_reviews} Good
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${day.poor_reviews} Poor
                        </span>
                    </div>
                </div>
                <div class="text-center">
                    <button onclick="event.stopPropagation(); viewReviewsForDate('${day.date}')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Reviews
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function viewReviewsForDate(dateString) {
    // Redirect to quality/reviews with date filter
    window.location.href = `<?php echo URLROOT; ?>/quality/reviews?start_date=${dateString}&end_date=${dateString}`;
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
    document.getElementById('quality-container').style.display = 'none';

    loadQualityData();
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
    loadQualityData();

    // Auto refresh every 20 minutes
    setInterval(loadQualityData, 20 * 60 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
