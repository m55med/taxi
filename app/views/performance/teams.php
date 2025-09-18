<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Team Performance</h1>
                    <p class="mt-1 text-sm text-gray-500">Teams and employee performance - <?php echo date('l, F j, Y'); ?></p>
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
            <p class="mt-4 text-gray-500">Loading team data...</p>
        </div>

        <!-- Teams Container -->
        <div id="teams-container" style="display: none;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6" id="teams-grid">
                <!-- Teams will be loaded here -->
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" style="display: none;" class="text-center py-12">
            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Teams Found</h3>
            <p class="text-gray-500">No teams were found in the system</p>
        </div>
    </div>
</div>

<script>
let teamsData = null;

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
    const container = document.getElementById('teams-container');
    const grid = document.getElementById('teams-grid');
    const loading = document.getElementById('loading-state');
    const empty = document.getElementById('empty-state');

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

function refreshData() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('teams-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';

    loadTeamsData();
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
    loadTeamsData();

    // Auto refresh every 10 minutes
    setInterval(loadTeamsData, 10 * 60 * 1000);
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
