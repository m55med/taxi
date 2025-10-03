<?php require_once APPROOT . '/views/includes/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Enhanced Header -->
    <div class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">لوحة التحكم المباشرة</h1>
                        <p class="mt-1 text-sm text-gray-600 flex items-center space-x-2">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span>مراقبة مباشرة - بيانات حقيقية</span>
                            <span class="text-gray-400">•</span>
                            <span id="last-update-time" class="text-blue-600 font-medium"><?php echo date('H:i:s'); ?></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Connection Status -->
                    <div class="flex items-center space-x-2 px-3 py-2 bg-green-50 rounded-lg border border-green-200">
                        <div id="connection-indicator" class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span id="connection-status" class="text-sm text-green-700 font-medium">متصل</span>
                    </div>

                    <!-- Refresh Controls -->
                    <div class="flex items-center space-x-2">
                        <button onclick="loadRealtimeData()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            تحديث
                        </button>
                        <button onclick="toggleAutoRefresh()" id="refresh-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-pause mr-2"></i>
                            إيقاف التحديث
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Key Metrics Cards -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                المؤشرات الرئيسية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Active Users Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">المستخدمون النشطون</p>
                                    <p class="text-2xl font-bold text-gray-900" id="active-users">0</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" id="active-users-bar" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="active-users-trend">نشط الآن</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On Break Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-coffee text-orange-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">في البريك</p>
                                    <p class="text-2xl font-bold text-gray-900" id="on-break">0</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-500 rounded-full transition-all duration-1000" id="on-break-bar" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="on-break-trend">في استراحة</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Tickets Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ticket-alt text-green-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">التذاكر اليوم</p>
                                    <p class="text-2xl font-bold text-gray-900" id="live-tickets">0</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500 rounded-full transition-all duration-1000" id="live-tickets-bar" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="live-tickets-trend">تم إنشاؤها</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Calls Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-phone text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">المكالمات النشطة</p>
                                    <p class="text-2xl font-bold text-gray-900" id="live-calls">0</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-500 rounded-full transition-all duration-1000" id="live-calls-bar" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="live-calls-trend">نشطة الآن</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health & Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- System Health -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-server mr-3 text-yellow-600"></i>
                        حالة النظام
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">حمل النظام</span>
                        <span class="text-sm font-semibold text-gray-900" id="system-load">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-400 to-yellow-400 h-3 rounded-full transition-all duration-1000" id="system-load-bar" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-500 text-center" id="system-load-trend">طبيعي</p>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div class="text-center">
                            <div class="text-lg font-bold text-blue-600" id="response-time">--</div>
                            <div class="text-xs text-gray-500">استجابة (ms)</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600" id="uptime">--</div>
                            <div class="text-xs text-gray-500">توفر (%)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-3 text-blue-600"></i>
                        النشاط المباشر
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 max-h-80 overflow-y-auto" id="activity-timeline">
                        <div class="flex items-center space-x-3 animate-pulse">
                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                            <div class="flex-1">
                                <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                            </div>
                            <div class="h-3 bg-gray-300 rounded w-16"></div>
                        </div>
                        <div class="text-center text-gray-500 text-sm py-4">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            جاري تحميل النشاط...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-3 text-purple-600"></i>
                        إحصائيات سريعة
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-xl font-bold text-blue-600" id="throughput">--</div>
                            <div class="text-xs text-gray-600">الإنتاجية</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-lg">
                            <div class="text-xl font-bold text-red-600" id="error-rate">--</div>
                            <div class="text-xs text-gray-600">معدل الخطأ</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">آخر تحديث:</span>
                            <span class="font-medium text-gray-900" id="last-update-time-small">--:--:--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Recent Tickets Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list mr-3 text-green-600"></i>
                        التذاكر الأخيرة
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التذكرة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوقت</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="recent-tickets-body">
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    جاري تحميل البيانات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Users Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-check mr-3 text-blue-600"></i>
                        المستخدمون النشطون
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">آخر نشاط</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="active-sessions-body">
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    جاري تحميل البيانات...
                                </td>
                            </tr>
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
let connectionStatus = true;
let errorNotificationTimeout = null;

async function loadRealtimeData() {
    try {
        // Show loading state
        document.getElementById('last-update-time').textContent = 'جاري التحديث...';

        const response = await fetch('<?php echo URLROOT; ?>/performance/api/realtime-data');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            realtimeData = data.data;
            updateRealtimeUI();

            // Update connection status
            updateConnectionStatus(true);
        } else {
            throw new Error(data.error || 'فشل في تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading realtime data:', error);
        updateConnectionStatus(false);
        showErrorNotification('فشل في تحميل البيانات. سيتم إعادة المحاولة...');
    }
}

function updateRealtimeUI() {
    if (!realtimeData) return;

    // Update last update time
    document.getElementById('last-update-time').textContent = new Date().toLocaleString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        timeZone: 'Africa/Cairo'
    });

    // Update live stats with animations
    animateNumber('active-users', realtimeData.active_users || 0);
    animateNumber('on-break', realtimeData.on_break || 0);
    animateNumber('live-tickets', realtimeData.live_tickets || 0);
    animateNumber('live-calls', realtimeData.live_calls || 0);
    animateNumber('system-load', (realtimeData.system_load || 0) + '%');

    // Update progress bars with reasonable scaling
    animateProgress('active-users-bar', Math.min((realtimeData.active_users || 0) * 5, 100)); // Max 20 users = 100%
    animateProgress('on-break-bar', Math.min((realtimeData.on_break || 0) * 10, 100)); // Max 10 users = 100%
    animateProgress('live-tickets-bar', Math.min((realtimeData.live_tickets || 0) * 1, 100)); // Max 100 tickets = 100%
    animateProgress('live-calls-bar', Math.min((realtimeData.live_calls || 0) * 5, 100)); // Max 20 calls = 100%
    animateProgress('system-load-bar', realtimeData.system_load || 0);

    // Update trends with meaningful text
    const activeUsers = realtimeData.active_users || 0;
    const onBreak = realtimeData.on_break || 0;
    const liveTickets = realtimeData.live_tickets || 0;
    const liveCalls = realtimeData.live_calls || 0;

    document.getElementById('active-users-trend').textContent = activeUsers > 0 ? `${activeUsers} مستخدم نشط` : 'لم يتم العثور على مستخدمين نشطين';
    document.getElementById('on-break-trend').textContent = onBreak > 0 ? `${onBreak} في استراحة` : 'لا استراحات';
    document.getElementById('live-tickets-trend').textContent = liveTickets > 0 ? `${liveTickets} تم إنشاؤها` : 'لا تذاكر جديدة';
    document.getElementById('live-calls-trend').textContent = liveCalls > 0 ? `${liveCalls} نشطة الآن` : 'لا مكالمات نشطة';
    document.getElementById('system-load-trend').textContent = (realtimeData.system_load || 0) > 70 ? 'مرتفع' : (realtimeData.system_load || 0) > 40 ? 'متوسط' : 'طبيعي';

    // Update performance metrics
    document.getElementById('response-time').textContent = realtimeData.response_time || '--';
    document.getElementById('throughput').textContent = realtimeData.throughput || '--';
    document.getElementById('error-rate').textContent = (realtimeData.error_rate || 0).toFixed(1);
    document.getElementById('uptime').textContent = (realtimeData.uptime_percentage || 99.5).toFixed(1);

    // Update activity timeline with real data
    updateActivityTimeline();

    // Update tables with real data
    updateRecentTickets();
    updateActiveSessions();

    // Update small timestamp
    document.getElementById('last-update-time-small').textContent = new Date().toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
}

function animateNumber(elementId, targetValue) {
    const element = document.getElementById(elementId);
    // Extract only numbers from the current text, remove any non-numeric characters
    const currentText = element.textContent.replace(/[^\d.-]/g, '');
    const currentValue = parseInt(currentText) || 0;

    // Ensure targetValue is a valid number
    const target = parseInt(targetValue) || 0;

    // If values are the same, no animation needed
    if (currentValue === target) {
        element.textContent = target;
        return;
    }

    const increment = target > currentValue ? 1 : -1;
    const duration = 1000; // 1 second
    const steps = Math.abs(target - currentValue);
    const stepDuration = Math.max(duration / steps, 50); // Minimum 50ms per step

    let current = currentValue;
    const timer = setInterval(() => {
        current += increment;

        // Handle percentage values
        if (elementId.includes('system-load')) {
            element.textContent = current + '%';
        } else {
            element.textContent = current;
        }

        if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
            // Ensure final value is exact
            if (elementId.includes('system-load')) {
                element.textContent = target + '%';
            } else {
                element.textContent = target;
            }
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
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/realtime-activity');
        const data = await response.json();

        const timeline = document.getElementById('activity-timeline');

        if (data.success && data.data && data.data.length > 0) {
            timeline.innerHTML = data.data.map(activity => {
                const activityTime = new Date(activity.time);
                const timeString = activityTime.toLocaleTimeString('ar-EG', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                    timeZone: 'Africa/Cairo'
                });

                let iconColor = 'bg-gray-500';
                switch(activity.type) {
                    case 'ticket': iconColor = 'bg-blue-500'; break;
                    case 'call': iconColor = 'bg-green-500'; break;
                    case 'login': iconColor = 'bg-purple-500'; break;
                    case 'system': iconColor = 'bg-yellow-500'; break;
                }

                return `
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full ${iconColor} animate-pulse"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">${activity.message}</p>
                        </div>
                        <p class="text-xs text-gray-500">${timeString}</p>
                    </div>
                `;
            }).join('');
        } else {
            // No activity data available
            timeline.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500">لا توجد أنشطة حديثة</p>
                    </div>
                    <p class="text-xs text-gray-400">${new Date().toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Africa/Cairo'})}</p>
                </div>
            `;
        }

    } catch (error) {
        console.error('Error updating activity timeline:', error);
        // Fallback to loading message
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
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">${timeString}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600 text-right">${ticket.ticket}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">${ticket.user}</td>
                    </tr>
                `;
            }).join('');
        } else {
            // No tickets available
            document.getElementById('recent-tickets-body').innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">
                        لا توجد تذاكر حديثة
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading recent tickets:', error);
        // Fallback to empty table
        document.getElementById('recent-tickets-body').innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">
                    خطأ في تحميل البيانات
                </td>
            </tr>
        `;
    }
}

async function updateActiveSessions() {
    try {
        // Get detailed active users list from separate API
        const response = await fetch('<?php echo URLROOT; ?>/performance/api/active-users-list');
        const data = await response.json();

        const tbody = document.getElementById('active-sessions-body');

        if (data.success && data.data && data.data.length > 0) {
            // Show detailed list of active users
            tbody.innerHTML = data.data.map((user, index) => {
                // Determine status color based on activity
                let statusColor = 'bg-green-100 text-green-800 border-green-200';
                let statusIcon = 'fas fa-circle text-green-400';
                let statusText = user.activity;

                if (user.activity.includes('غير نشط')) {
                    statusColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    statusIcon = 'fas fa-clock text-yellow-400';
                } else if (user.activity.includes('نشط')) {
                    statusColor = 'bg-green-100 text-green-800 border-green-200';
                    statusIcon = 'fas fa-circle text-green-400 animate-pulse';
                }

                // Alternate row colors
                const rowClass = index % 2 === 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-white';

                return `
                    <tr class="${rowClass} transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                            <div class="flex items-center">
                                <i class="fas fa-user text-blue-600 ml-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">${user.user}</p>
                                    <p class="text-xs text-gray-500">${user.duration}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${statusColor} border">
                                <i class="${statusIcon} ml-1"></i>
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-gray-400 ml-2"></i>
                                <span>${new Date().toLocaleTimeString('ar-EG', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false
                                })}</span>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            // Show empty state with better messaging
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-user-clock text-4xl mb-3 text-gray-400"></i>
                            <p class="text-sm font-medium mb-2">لا يوجد مستخدمون نشطون حالياً</p>
                            <div class="space-y-1 text-xs text-gray-400">
                                <p>• المستخدمون الذين سجلوا دخول مؤخراً</p>
                                <p>• المستخدمون الذين لديهم نشاط في آخر ساعتين</p>
                                <p>• المستخدمون ذوي الحالة النشطة</p>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading active sessions:', error);
        document.getElementById('active-sessions-body').innerHTML = `
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-red-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p class="text-sm font-medium">خطأ في تحميل البيانات</p>
                        <p class="text-xs mt-1">يرجى المحاولة مرة أخرى</p>
                    </div>
                </td>
            </tr>
        `;
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

// Connection status management
function updateConnectionStatus(isConnected) {
    connectionStatus = isConnected;
    const indicator = document.querySelector('.w-3.h-3');
    const statusText = document.querySelector('.text-green-600');

    if (isConnected) {
        indicator.className = 'w-3 h-3 bg-green-500 rounded-full animate-pulse';
        statusText.textContent = 'مباشر';
        statusText.className = 'text-sm text-green-600 font-medium';
    } else {
        indicator.className = 'w-3 h-3 bg-red-500 rounded-full';
        statusText.textContent = 'غير متصل';
        statusText.className = 'text-sm text-red-600 font-medium';
    }
}

// Error notification system
function showErrorNotification(message) {
    // Clear existing timeout
    if (errorNotificationTimeout) {
        clearTimeout(errorNotificationTimeout);
    }

    // Create notification element if it doesn't exist
    let notification = document.getElementById('error-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'error-notification';
        notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 max-w-sm';
        document.body.appendChild(notification);
    }

    notification.textContent = message;
    notification.style.display = 'block';

    // Auto hide after 5 seconds
    errorNotificationTimeout = setTimeout(() => {
        notification.style.display = 'none';
    }, 5000);
}

// Enhanced auto refresh with better error handling
function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);

    // Start with more frequent updates for critical data
    autoRefreshInterval = setInterval(async () => {
        if (isAutoRefreshEnabled) {
            try {
                await loadRealtimeData();
            } catch (error) {
                console.error('Auto refresh failed:', error);
                // Don't show error notification for every failed auto refresh
                updateConnectionStatus(false);
            }
        }
    }, 30000); // Update every 30 seconds instead of 5 for better performance
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Keyboard shortcuts for better UX
document.addEventListener('keydown', function(event) {
    // Ctrl/Cmd + R to force refresh
    if ((event.ctrlKey || event.metaKey) && event.key === 'r') {
        event.preventDefault();
        loadRealtimeData();
    }

    // Space to pause/resume
    if (event.code === 'Space' && !event.target.matches('input, textarea')) {
        event.preventDefault();
        toggleAutoRefresh();
    }
});

// Page visibility API - pause when tab is not visible
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Pause updates when tab is not visible to save resources
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    } else {
        // Resume updates when tab becomes visible again
        if (isAutoRefreshEnabled) {
            startAutoRefresh();
        }
    }
});

// Load data on page load and start auto refresh
document.addEventListener('DOMContentLoaded', function() {
    loadRealtimeData();
    startAutoRefresh();
});
</script>

<?php require_once APPROOT . '/views/includes/footer.php'; ?>
