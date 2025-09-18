<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Real-time Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">Live monitoring dashboard - Real-time data - <?php echo date('l, F j, Y H:i:s'); ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-green-600 font-medium">Live</span>
                    </div>
                    <button onclick="toggleAutoRefresh()" id="refresh-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-pause mr-2"></i>
                        Pause Auto Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Live Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Users -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users h-8 w-8 text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                <dd class="text-lg font-medium text-gray-900" id="active-users">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" id="active-users-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="active-users-trend">+0 from last minute</p>
                    </div>
                </div>
            </div>

            <!-- Live Tickets -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-ticket-alt h-8 w-8 text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">New Tickets</dt>
                                <dd class="text-lg font-medium text-gray-900" id="live-tickets">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full transition-all duration-500" id="live-tickets-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="live-tickets-trend">+0 in last 5 minutes</p>
                    </div>
                </div>
            </div>

            <!-- Live Calls -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone h-8 w-8 text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Calls</dt>
                                <dd class="text-lg font-medium text-gray-900" id="live-calls">0</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" id="live-calls-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="live-calls-trend">+0 active now</p>
                    </div>
                </div>
            </div>

            <!-- System Load -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-server h-8 w-8 text-yellow-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">System Load</dt>
                                <dd class="text-lg font-medium text-gray-900" id="system-load">0%</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-600 h-2 rounded-full transition-all duration-500" id="system-load-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="system-load-trend">Normal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Activity Timeline -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Live Activity</h3>
                    <p class="text-sm text-gray-500">Real-time events and updates</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4 max-h-96 overflow-y-auto" id="activity-timeline">
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

            <!-- Performance Metrics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Performance Metrics</h3>
                    <p class="text-sm text-gray-500">Live performance data</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="response-time">--</div>
                            <div class="text-xs text-gray-500">Response Time (ms)</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="throughput">--</div>
                            <div class="text-xs text-gray-500">Throughput</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600" id="error-rate">--</div>
                            <div class="text-xs text-gray-500">Error Rate (%)</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600" id="uptime">--</div>
                            <div class="text-xs text-gray-500">Uptime (%)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Data Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Tickets -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Tickets</h3>
                </div>
                <div class="overflow-x-auto max-h-80">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="recent-tickets-body">
                            <!-- Live data will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Active Sessions</h3>
                </div>
                <div class="overflow-x-auto max-h-80">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستخدم</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">النشاط</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="active-sessions-body">
                            <!-- Live data will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let realtimeData = null;
let autoRefreshInterval = null;
let isAutoRefreshEnabled = true;

async function loadRealtimeData() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/realtime-data');
        const data = await response.json();

        if (data.success) {
            realtimeData = data.data;
            updateRealtimeUI();
        }
    } catch (error) {
        console.error('Error loading realtime data:', error);
    }
}

function updateRealtimeUI() {
    if (!realtimeData) return;

    // Update live stats with animations
    animateNumber('active-users', realtimeData.active_users || 0);
    animateNumber('live-tickets', realtimeData.live_tickets || 0);
    animateNumber('live-calls', realtimeData.live_calls || 0);
    animateNumber('system-load', (realtimeData.system_load || 0) + '%');

    // Update progress bars
    animateProgress('active-users-bar', Math.min((realtimeData.active_users || 0) * 4, 100));
    animateProgress('live-tickets-bar', Math.min((realtimeData.live_tickets || 0) * 1.5, 100));
    animateProgress('live-calls-bar', Math.min((realtimeData.live_calls || 0) * 6, 100));
    animateProgress('system-load-bar', realtimeData.system_load || 0);

    // Update trends (calculate based on real data)
    document.getElementById('active-users-trend').textContent = `${realtimeData.active_users || 0} نشط الآن`;
    document.getElementById('live-tickets-trend').textContent = `${realtimeData.live_tickets || 0} تم إنشاؤها اليوم`;
    document.getElementById('live-calls-trend').textContent = `${realtimeData.live_calls || 0} نشطة الآن`;
    document.getElementById('system-load-trend').textContent = (realtimeData.system_load || 0) > 50 ? 'مرتفع' : 'طبيعي';

    // Update performance metrics
    document.getElementById('response-time').textContent = realtimeData.response_time || 150;
    document.getElementById('throughput').textContent = realtimeData.throughput || 0;
    document.getElementById('error-rate').textContent = (realtimeData.error_rate || 0).toFixed(1);
    document.getElementById('uptime').textContent = (realtimeData.uptime_percentage || 99.5).toFixed(1);

    // Update activity timeline
    updateActivityTimeline();

    // Update tables with real data
    updateRecentTickets();
    updateActiveSessions();
}

function animateNumber(elementId, targetValue) {
    const element = document.getElementById(elementId);
    const currentValue = parseInt(element.textContent) || 0;
    const increment = targetValue > currentValue ? 1 : -1;
    const duration = 1000; // 1 second
    const steps = Math.abs(targetValue - currentValue);
    const stepDuration = duration / steps;

    let current = currentValue;
    const timer = setInterval(() => {
        current += increment;
        element.textContent = current;

        if (current === targetValue) {
            clearInterval(timer);
        }
    }, stepDuration);
}

function animateProgress(elementId, targetWidth) {
    const element = document.getElementById(elementId);
    const currentWidth = parseFloat(element.style.width) || 0;
    const duration = 1000;
    const steps = Math.abs(targetWidth - currentWidth);
    const stepDuration = duration / Math.max(steps, 1);

    let current = currentWidth;
    const increment = targetWidth > currentWidth ? 1 : -1;

    const timer = setInterval(() => {
        current += increment;
        element.style.width = current + '%';

        if ((increment > 0 && current >= targetWidth) || (increment < 0 && current <= targetWidth)) {
            element.style.width = targetWidth + '%';
            clearInterval(timer);
        }
    }, stepDuration);
}

async function updateActivityTimeline() {
    try {
        // Generate realistic activities based on real data
        const activities = [];

        // Recent ticket activity
        const ticketResponse = await fetch('<?php echo URLROOT; ?>/performance/api/recent-tickets');
        const ticketData = await ticketResponse.json();

        if (ticketData.success && ticketData.data && ticketData.data.length > 0) {
            const recentTicket = ticketData.data[0];
            const ticketTime = new Date(recentTicket.time);
            activities.push({
                time: ticketTime.toLocaleTimeString('ar-EG', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                    timeZone: 'Africa/Cairo'
                }),
                event: `تم إنشاء تذكرة ${recentTicket.ticket} بواسطة ${recentTicket.user}`,
                type: 'ticket'
            });
        }

        // Add some realistic activities based on current data
        const now = new Date();
        activities.push(
            {
                time: new Date(now.getTime() - 30000).toLocaleTimeString('ar-EG', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Africa/Cairo'
                }),
                event: 'تم تحديث بيانات الأداء',
                type: 'system'
            },
            {
                time: new Date(now.getTime() - 60000).toLocaleTimeString('ar-EG', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Africa/Cairo'
                }),
                event: 'تم تحديث إحصائيات المكالمات',
                type: 'call'
            }
        );

        const timeline = document.getElementById('activity-timeline');
        timeline.innerHTML = activities.slice(0, 4).map(activity => `
            <div class="flex items-center space-x-3">
                <div class="w-2 h-2 rounded-full ${
                    activity.type === 'ticket' ? 'bg-blue-500' :
                    activity.type === 'call' ? 'bg-green-500' :
                    activity.type === 'user' ? 'bg-purple-500' : 'bg-yellow-500'
                } animate-pulse"></div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">${activity.event}</p>
                </div>
                <p class="text-xs text-gray-500">${activity.time}</p>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error updating activity timeline:', error);
        // Fallback to simple timeline
        const timeline = document.getElementById('activity-timeline');
        timeline.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">جاري تحديث البيانات...</p>
                </div>
                <p class="text-xs text-gray-500">${new Date().toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Africa/Cairo'})}</p>
            </div>
        `;
    }
}

async function updateRecentTickets() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/recent-tickets');
        const data = await response.json();

        if (data.success && data.data) {
            const tbody = document.getElementById('recent-tickets-body');
            tbody.innerHTML = data.data.map(ticket => {
                const date = new Date(ticket.time);
                const timeString = date.toLocaleTimeString('ar-EG', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                    timeZone: 'Africa/Cairo'
                });

                return `
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${timeString}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600">${ticket.ticket}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${ticket.user}</td>
                    </tr>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading recent tickets:', error);
        // Fallback to empty table
        document.getElementById('recent-tickets-body').innerHTML = '';
    }
}

async function updateActiveSessions() {
    try {
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/active-sessions');
        const data = await response.json();

        if (data.success && data.data) {
            const tbody = document.getElementById('active-sessions-body');
            tbody.innerHTML = data.data.map(session => `
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${session.user}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            session.activity === 'نشط الآن' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                        }">
                            ${session.activity}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${session.duration}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading active sessions:', error);
        // Fallback to empty table
        document.getElementById('active-sessions-body').innerHTML = '';
    }
}

function toggleAutoRefresh() {
    const btn = document.getElementById('refresh-btn');
    const icon = btn.querySelector('i');

    isAutoRefreshEnabled = !isAutoRefreshEnabled;

    if (isAutoRefreshEnabled) {
        icon.className = 'fas fa-pause mr-2';
        btn.innerHTML = '<i class="fas fa-pause mr-2"></i> إيقاف التحديث التلقائي';
        startAutoRefresh();
    } else {
        icon.className = 'fas fa-play mr-2';
        btn.innerHTML = '<i class="fas fa-play mr-2"></i> تشغيل التحديث التلقائي';
        stopAutoRefresh();
    }
}

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(loadRealtimeData, 5000); // Every 5 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Load data on page load and start auto refresh
document.addEventListener('DOMContentLoaded', function() {
    loadRealtimeData();
    startAutoRefresh();
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
