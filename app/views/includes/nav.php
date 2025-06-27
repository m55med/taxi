<?php if (isset($_SESSION['user_id'])): ?>
<!-- Using Alpine.js to manage mobile menu state -->
<nav class="bg-white shadow" x-data="{ isMobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/dashboard" class="text-2xl font-bold text-indigo-600 hover:text-indigo-700">Taxi</a>
                </div>
                <!-- Desktop menu links (hidden on small screens) -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_PATH ?>/dashboard/users" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-users-cog mr-1"></i>
                        User Management
                    </a>
                    <?php endif; ?>

                    <!-- Activities Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-tasks mr-2"></i>
                            <span>Activities</span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20" style="display: none;">
                            <a href="<?= BASE_PATH ?>/calls" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-headset mr-2"></i>Call Center</a>
                            <a href="<?= BASE_PATH ?>/create_ticket" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-plus-circle mr-2"></i>Create Ticket</a>
                            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
                            <a href="<?= BASE_PATH ?>/referral/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-bullhorn mr-2"></i>Marketing</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <!-- Reports Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-chart-bar mr-2"></i>
                            <span>Reports</span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full left-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden" style="display: none;">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- General Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'general' ? '' : 'general')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>General Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'general'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'general'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/analytics" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Analytics</a>
                                        <a href="<?= BASE_PATH ?>/reports/system-logs" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">System Logs</a>
                                        <a href="<?= BASE_PATH ?>/reports/notifications" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Notifications Report</a>
                                    </div>
                                </div>
                                <!-- User & Team Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'users' ? '' : 'users')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>User & Team Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'users'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'users'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/users" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Users</a>
                                        <a href="<?= BASE_PATH ?>/reports/teamperformance" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Team Performance</a>
                                        <a href="<?= BASE_PATH ?>/reports/team-leaderboard" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Team Leaderboard</a>
                                        <a href="<?= BASE_PATH ?>/reports/employee-activity-score" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Employee Score</a>
                                        <a href="<?= BASE_PATH ?>/reports/myactivity" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">My Activity</a>
                                    </div>
                                </div>
                                <!-- Driver Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'drivers' ? '' : 'drivers')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Driver Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'drivers'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'drivers'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/drivers" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Drivers</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Driver Assignments</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Driver Calls</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-documents-compliance" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Docs Compliance</a>
                                    </div>
                                </div>
                                <!-- Trips Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'trips' ? '' : 'trips')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Trips Reports</span>
                                        <i class="fas fa-route text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'trips'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'trips'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/trips" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Detailed Trips Report</a>
                                    </div>
                                </div>
                                <!-- Tickets Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'tickets' ? '' : 'tickets')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Tickets Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'tickets'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'tickets'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/tickets-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Tickets</a>
                                        <a href="<?= BASE_PATH ?>/reports/tickets" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Tickets</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-reviews" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Ticket Reviews</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-discussions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Ticket Discussions</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Ticket Coupons</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-rework" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Ticket Rework</a>
                                        <a href="<?= BASE_PATH ?>/reports/review-quality" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Review Quality</a>
                                    </div>
                                </div>
                                <!-- Marketing Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'marketing' ? '' : 'marketing')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Marketing Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'marketing'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'marketing'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/marketer-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Marketer Summary</a>
                                        <a href="<?= BASE_PATH ?>/reports/referral-visits" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Referral Visits</a>
                                    </div>
                                </div>
                                <!-- Other Reports -->
                                <div>
                                    <button @click="openSubmenu = (openSubmenu === 'other' ? '' : 'other')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Other Reports</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'other'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'other'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Calls</a>
                                        <a href="<?= BASE_PATH ?>/reports/assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Assignments</a>
                                        <a href="<?= BASE_PATH ?>/reports/documents" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Documents</a>
                                        <a href="<?= BASE_PATH ?>/reports/coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Coupons</a>
                                        <a href="<?= BASE_PATH ?>/reports/custom" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Custom Reports</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Center Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            <span>Upload Center</span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-56 bg-white rounded-md shadow-xl z-20" style="display: none;">
                             <a href="<?= BASE_PATH ?>/upload" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Upload Drivers Data</a>
                             <a href="<?= BASE_PATH ?>/trips/upload" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Upload Trips Data</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Logs & Tracking Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-history mr-2"></i>
                            <span>Logs & Tracking</span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20" style="display: none;">
                            <a href="<?= BASE_PATH ?>/logs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-clipboard-list mr-2"></i>Activity Log</a>
                            <a href="<?= BASE_PATH ?>/discussions" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-comments mr-2"></i>Discussions</a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                    <!-- Settings Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-cogs mr-2"></i>
                            <span>Settings</span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full right-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden" style="display: none;">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- System Settings -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'system' ? '' : 'system')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>System Settings</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'system'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'system'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/permissions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Permissions</a>
                                        <a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Telegram Settings</a>
                                        <a href="<?= BASE_PATH ?>/admin/platforms" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Platforms</a>
                                        <a href="<?= BASE_PATH ?>/admin/roles" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Roles</a>
                                        <a href="<?= BASE_PATH ?>/admin/car_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Car Types</a>
                                        <a href="<?= BASE_PATH ?>/admin/countries" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Countries</a>
                                        <a href="<?= BASE_PATH ?>/admin/document_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Document Types</a>
                                    </div>
                                </div>
                                <!-- Points & Rewards -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'points' ? '' : 'points')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Points & Rewards</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'points'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'points'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/points" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Point Weights</a>
                                        <a href="<?= BASE_PATH ?>/admin/bonus" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Grant Monthly Bonus</a>
                                    </div>
                                </div>
                                <!-- Team Management -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'team' ? '' : 'team')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Team Management</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'team'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'team'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/teams" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Teams</a>
                                        <a href="<?= BASE_PATH ?>/admin/team_members" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Team Members</a>
                                    </div>
                                </div>
                                <!-- Ticket Settings -->
                                <div>
                                    <button @click="openSubmenu = (openSubmenu === 'tickets' ? '' : 'tickets')" class="w-full text-left flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>Ticket Settings</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'tickets'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'tickets'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/ticket_categories" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Categories</a>
                                        <a href="<?= BASE_PATH ?>/admin/ticket_subcategories" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Subcategories</a>
                                        <a href="<?= BASE_PATH ?>/admin/ticket_codes" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Codes</a>
                                        <a href="<?= BASE_PATH ?>/admin/coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">Coupons</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
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

                <div class="ml-3 relative">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-500">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                        <form method="POST" action="<?= BASE_PATH ?>/auth/logout" class="inline">
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sign-out-alt mr-1"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger button -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Icon when menu is closed -->
                    <svg :class="{'hidden': isMobileMenuOpen, 'block': !isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Icon when menu is open -->
                    <svg :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div x-show="isMobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="sm:hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="<?= BASE_PATH ?>/dashboard/users" class="block py-2 px-4 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600">
                <i class="fas fa-users-cog mr-1"></i> User Management
            </a>
            <?php endif; ?>
            
            <!-- Mobile dropdowns -->
            <div class="px-4 py-2" x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-tasks mr-2"></i> Activities</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/calls" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-headset mr-2"></i>Call Center</a>
                    <a href="<?= BASE_PATH ?>/call_log" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-phone-alt mr-2"></i>Log Incoming Call</a>
                    <a href="<?= BASE_PATH ?>/create_ticket" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-plus-circle mr-2"></i>Create Ticket</a>
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
                    <a href="<?= BASE_PATH ?>/referral/dashboard" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-bullhorn mr-2"></i>Marketing</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
            <div class="px-4 py-2" x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-chart-bar mr-2"></i> Reports</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/reports/analytics" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Analytics</a>
                    <a href="<?= BASE_PATH ?>/reports/users" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Users</a>
                    <a href="<?= BASE_PATH ?>/reports/drivers" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Drivers</a>
                     <a href="<?= BASE_PATH ?>/reports/trips" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Trips</a>
                    <a href="<?= BASE_PATH ?>/reports/tickets-summary" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Tickets</a>
                    <!-- Add other report links if needed -->
                </div>
            </div>

            <div class="px-4 py-2" x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-upload mr-2"></i> Upload Center</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/upload" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Upload Drivers Data</a>
                    <a href="<?= BASE_PATH ?>/trips/upload" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Upload Trips Data</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="px-4 py-2" x-data="{ open: false }">
                 <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-history mr-2"></i> Logs & Tracking</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                 <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/logs" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-clipboard-list mr-2"></i>Activity Log</a>
                    <a href="<?= BASE_PATH ?>/discussions" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-comments mr-2"></i>Discussions</a>
                </div>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
             <div class="px-4 py-2" x-data="{ open: false }">
                 <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-cogs mr-2"></i> Settings</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                 <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/admin/permissions" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Permissions</a>
                    <a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Telegram Settings</a>
                    <a href="<?= BASE_PATH ?>/admin/platforms" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Platforms</a>
                    <a href="<?= BASE_PATH ?>/admin/roles" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Roles</a>
                    <a href="<?= BASE_PATH ?>/admin/teams" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Teams</a>
                 </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- User info and logout in mobile menu -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                 <form method="POST" action="<?= BASE_PATH ?>/auth/logout">
                    <button type="submit" class="w-full text-left block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

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
</nav>
<?php endif; ?>