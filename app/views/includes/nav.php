<!-- 
  This navigation bar has been simplified to remove all PHP permission logic.
  It now displays all available links directly.
-->
<?php if (isset($_SESSION['user_id'])): ?>
<nav class="bg-white shadow" x-data="{ isMobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/dashboard" class="text-2xl font-bold text-indigo-600 hover:text-indigo-700">Taxi</a>
                </div>
                <!-- Desktop menu links -->
                <div class="hidden lg:ml-6 lg:flex lg:space-x-4 flex-wrap">
                    
                    <!-- Top Level Links -->

                    <!-- Listings Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-list-ul mr-2"></i><span>Listings</span><i class="fas fa-chevron-down ml-2 text-xs" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" x-cloak class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                            <a href="<?= BASE_PATH ?>/listings/tickets" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-ticket-alt mr-2"></i>All Tickets</a>
                            <a href="<?= BASE_PATH ?>/listings/drivers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-tie mr-2"></i>All Drivers</a>
                            <a href="<?= BASE_PATH ?>/listings/calls" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-phone-alt mr-2"></i>All Calls</a>
                        </div>
                    </div>
                    
                    <!-- Collaboration Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-users-cog mr-2"></i><span>Collaboration</span><i class="fas fa-chevron-down ml-2 text-xs" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" x-cloak class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                            <a href="<?= BASE_PATH ?>/discussions" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-comments mr-2"></i>Discussions</a>
                            <a href="<?= BASE_PATH ?>/logs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-book mr-2"></i>System Logs</a>
                        </div>
                    </div>

                    <!-- Activity Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-tasks mr-2"></i><span>Activity</span><i class="fas fa-chevron-down ml-2 text-xs" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" x-cloak class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                            <a href="<?= BASE_PATH ?>/tickets/create" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-plus-circle mr-2"></i>Create Ticket</a>
                            <a href="<?= BASE_PATH ?>/calls" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-headset mr-2"></i>Call Center</a>
                            <a href="<?= BASE_PATH ?>/referral/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-users mr-2"></i>Referral Dashboard</a>
                        </div>
                    </div>

                    <!-- Reports Mega Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-chart-bar mr-2"></i><span>Reports</span><i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" x-cloak class="absolute top-full left-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- Report groups -->
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'general' ? '' : 'general')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>General</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'general'}"></i></button><div x-show="openSubmenu === 'general'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/analytics" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Analytics</a><a href="<?= BASE_PATH ?>/reports/system-logs" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">System Logs</a><a href="<?= BASE_PATH ?>/reports/notifications" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Notifications</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'users' ? '' : 'users')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>User & Team</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'users'}"></i></button><div x-show="openSubmenu === 'users'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/users" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Users</a><a href="<?= BASE_PATH ?>/reports/team-leaderboard" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Team Leaderboard</a><a href="<?= BASE_PATH ?>/reports/employee-activity-score" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Employee Score</a><a href="<?= BASE_PATH ?>/reports/myactivity" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">My Activity</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'drivers' ? '' : 'drivers')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Drivers</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'drivers'}"></i></button><div x-show="openSubmenu === 'drivers'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/drivers" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Drivers</a><a href="<?= BASE_PATH ?>/reports/driver-assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Driver Assignments</a><a href="<?= BASE_PATH ?>/reports/driver-calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Driver Calls</a><a href="<?= BASE_PATH ?>/reports/driver-documents-compliance" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Docs Compliance</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'trips' ? '' : 'trips')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Trips</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'trips'}"></i></button><div x-show="openSubmenu === 'trips'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/trips" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Detailed Trips</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'tickets' ? '' : 'tickets')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Tickets</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'tickets'}"></i></button><div x-show="openSubmenu === 'tickets'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/tickets-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Summary</a><a href="<?= BASE_PATH ?>/reports/tickets" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Details</a><a href="<?= BASE_PATH ?>/reports/ticket-reviews" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Reviews</a><a href="<?= BASE_PATH ?>/reports/ticket-discussions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Discussions</a><a href="<?= BASE_PATH ?>/reports/ticket-coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Coupons</a><a href="<?= BASE_PATH ?>/reports/ticket-rework" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Rework</a><a href="<?= BASE_PATH ?>/reports/review-quality" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Review Quality</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'marketing' ? '' : 'marketing')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Marketing</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'marketing'}"></i></button><div x-show="openSubmenu === 'marketing'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/marketer-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Marketer Summary</a><a href="<?= BASE_PATH ?>/reports/referral-visits" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Referral Visits</a></div></div>
                                <div><button @click="openSubmenu = (openSubmenu === 'other' ? '' : 'other')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Other</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'other'}"></i></button><div x-show="openSubmenu === 'other'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/reports/calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Calls</a><a href="<?= BASE_PATH ?>/reports/assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Assignments</a><a href="<?= BASE_PATH ?>/reports/documents" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Documents</a><a href="<?= BASE_PATH ?>/reports/coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Coupons</a><a href="<?= BASE_PATH ?>/reports/custom" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Custom</a></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Mega Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-cogs mr-2"></i><span>Settings</span><i class="fas fa-chevron-down ml-2 text-xs" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" x-cloak class="absolute top-full right-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden">
                             <div x-data="{ openSubmenu: '' }">
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'system' ? '' : 'system')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>System</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'system'}"></i></button><div x-show="openSubmenu === 'system'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/admin/users" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Users</a><a href="<?= BASE_PATH ?>/admin/roles" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Roles</a><a href="<?= BASE_PATH ?>/admin/permissions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Permissions</a><a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Telegram</a><a href="<?= BASE_PATH ?>/admin/platforms" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Platforms</a><a href="<?= BASE_PATH ?>/admin/car_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Car Types</a><a href="<?= BASE_PATH ?>/admin/countries" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Countries</a><a href="<?= BASE_PATH ?>/admin/document_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Document Types</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'teams' ? '' : 'teams')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Teams</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'teams'}"></i></button><div x-show="openSubmenu === 'teams'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/admin/teams" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Manage Teams</a><a href="<?= BASE_PATH ?>/admin/team_members" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Team Members</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'rewards' ? '' : 'rewards')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Rewards & Evaluations</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'rewards'}"></i></button><div x-show="openSubmenu === 'rewards'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/admin/points" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Points System</a><a href="<?= BASE_PATH ?>/admin/bonus/settings" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Bonus Settings</a><a href="<?= BASE_PATH ?>/delegation-types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Delegation Types</a><a href="<?= BASE_PATH ?>/user-delegations" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">User Delegations</a><a href="<?= BASE_PATH ?>/employee-evaluations" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Employee Evaluations</a></div></div>
                                <div class="border-b"><button @click="openSubmenu = (openSubmenu === 'uploads' ? '' : 'uploads')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50"><span>Uploads</span><i class="fas fa-chevron-down text-xs" :class="{'rotate-180': openSubmenu === 'uploads'}"></i></button><div x-show="openSubmenu === 'uploads'" x-collapse x-cloak><a href="<?= BASE_PATH ?>/upload" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Upload Drivers</a><a href="<?= BASE_PATH ?>/trips/upload" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100">Upload Trips</a></div></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- User info for large screens -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Notification Bell (Desktop) -->
                <div class="relative flex items-center mr-4" x-data="navNotifications()">
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none relative">
                        <i class="fas fa-bell"></i>
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-2 -right-2 flex h-5 w-5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 justify-center items-center text-white text-xs" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </span>
                        </template>
                    </button>
                    <div @click.away="open = false" x-show="open" 
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute top-full right-0 mt-2 w-80 bg-white rounded-md shadow-xl z-20" style="display: none;">
                        <div class="p-2 flex justify-between items-center border-b">
                            <span class="font-bold text-gray-700">Notifications</span>
                            <a href="<?= BASE_PATH ?>/notifications/history" class="text-sm text-blue-600 hover:underline">View All</a>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <p class="text-gray-500 text-sm text-center p-4">You have no new notifications.</p>
                            </template>
                            <template x-for="notification in notifications" :key="notification.id">
                                <a :href="`<?= BASE_PATH ?>/notifications/history#notification-${notification.id}`" class="block px-4 py-3 hover:bg-gray-100 border-b">
                                    <div class="flex items-start">
                                        <div class="w-8 text-center">
                                            <i class="fas fa-info-circle" :class="!notification.is_read ? 'text-blue-500' : 'text-gray-400'"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800 text-sm" x-text="notification.title"></p>
                                            <p class="text-xs text-gray-500 mt-1" x-text="new Date(notification.created_at).toLocaleString()"></p>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Profile dropdown -->
                <div class="ml-3 relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-bold text-sm border-2 border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-label="User menu" aria-haspopup="true">
                        <?php
                            $name = $_SESSION['username'] ?? 'U';
                            $initials = '';
                            $parts = explode(' ', $name);
                            foreach ($parts as $part) {
                                if (!empty($part)) {
                                    $initials .= strtoupper($part[0]);
                                }
                                if (strlen($initials) >= 2) break;
                            }
                        ?>
                        <span><?= htmlspecialchars($initials) ?></span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-semibold text-gray-900">Signed in as</p>
                            <p class="text-sm text-gray-600 truncate" title="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
                        </div>
                        <div class="py-1">
                            <a href="<?= BASE_PATH ?>/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-circle fa-fw mr-2 text-gray-500"></i>
                                Your Profile
                            </a>
                            <form action="<?= BASE_PATH ?>/auth/logout" method="POST" class="w-full">
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt fa-fw mr-2 text-gray-500"></i>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center lg:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                    <svg :class="{'hidden': isMobileMenuOpen, 'block': !isMobileMenuOpen }" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg :class="{'hidden': !isMobileMenuOpen, 'block': isMobileMenuOpen }" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen}" class="lg:hidden" x-cloak>
        <div class="pt-2 pb-3 space-y-1">
            <!-- Mobile Dropdowns -->
            <div x-data="{ open: false }"><button @click="open = !open" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50"><span>Listings</span><i class="fas fa-chevron-down text-sm" :class="{'transform rotate-180': open}"></i></button><div x-show="open" x-collapse x-cloak class="pl-4 space-y-1"><a href="<?= BASE_PATH ?>/listings/tickets" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">All Tickets</a><a href="<?= BASE_PATH ?>/listings/drivers" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">All Drivers</a><a href="<?= BASE_PATH ?>/listings/calls" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">All Calls</a></div></div>
            <div x-data="{ open: false }"><button @click="open = !open" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50"><span>Collaboration</span><i class="fas fa-chevron-down text-sm" :class="{'transform rotate-180': open}"></i></button><div x-show="open" x-collapse x-cloak class="pl-4 space-y-1"><a href="<?= BASE_PATH ?>/discussions" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">Discussions</a><a href="<?= BASE_PATH ?>/logs" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">System Logs</a></div></div>
            <div x-data="{ open: false }"><button @click="open = !open" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50"><span>Activity</span><i class="fas fa-chevron-down text-sm" :class="{'transform rotate-180': open}"></i></button><div x-show="open" x-collapse x-cloak class="pl-4 space-y-1"><a href="<?= BASE_PATH ?>/tickets/create" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">Create Ticket</a><a href="<?= BASE_PATH ?>/calls" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">Call Center</a><a href="<?= BASE_PATH ?>/referral/dashboard" class="block py-2 text-sm text-gray-600 hover:bg-gray-50">Referral Dashboard</a></div></div>
            <div x-data="{ open: false }"><button @click="open = !open" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50"><span>Reports</span><i class="fas fa-chevron-down text-sm" :class="{'transform rotate-180': open}"></i></button><div x-show="open" x-collapse x-cloak class="pl-4 space-y-1"><a href="<?= BASE_PATH ?>/reports/main" class="block py-2 text-sm">Main Reports</a><a href="<?= BASE_PATH ?>/reports/referral-visits" class="block py-2 text-sm">Referral Visits</a></div></div>
            <div x-data="{ open: false }"><button @click="open = !open" class="w-full flex justify-between items-center pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-50"><span>Settings</span><i class="fas fa-chevron-down text-sm" :class="{'transform rotate-180': open}"></i></button><div x-show="open" x-collapse x-cloak class="pl-4 space-y-1"><a href="<?= BASE_PATH ?>/admin/users" class="block py-2 text-sm">Users</a><a href="<?= BASE_PATH ?>/admin/roles" class="block py-2 text-sm">Roles</a><a href="<?= BASE_PATH ?>/admin/teams" class="block py-2 text-sm">Teams</a></div></div>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gray-200 flex items-center justify-center rounded-full">
                        <i class="fas fa-user text-gray-600 text-lg"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                    <div class="text-sm font-medium text-gray-500"><?= htmlspecialchars($_SESSION['email'] ?? 'user@example.com') ?></div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="<?= BASE_PATH ?>/profile" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-50">Your Profile</a>
                <form action="<?= BASE_PATH ?>/auth/logout" method="POST"><button type="submit" class="w-full text-left block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-50">Sign out</button></form>
            </div>
        </div>
    </div>
</nav>
<script>
    function navNotifications() {
        return {
            open: false,
            unreadCount: 0,
            notifications: [],
            init() {
                // Assign to window so modal can call it after marking as read
                window.updateNavNotifications = this.fetchNotifications.bind(this);
                this.fetchNotifications();
                // Refresh notifications every 2 minutes
                setInterval(() => this.fetchNotifications(), 120000);
            },
            fetchNotifications() {
                fetch('<?= BASE_PATH ?>/notifications/getNavNotifications')
                    .then(res => res.json())
                    .then(data => {
                        if(data) {
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        }
                    })
                    .catch(err => console.error('Error fetching nav notifications:', err));
            }
        }
    }
    </script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php else: ?>
<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/" class="text-xl font-bold text-indigo-600">Taxi</a>
                </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <a href="<?= BASE_PATH ?>/login" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Login</a>
                <a href="<?= BASE_PATH ?>/register" class="ml-4 text-sm font-medium text-gray-700 hover:text-indigo-600">Register</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>