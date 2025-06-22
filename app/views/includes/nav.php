<?php if (isset($_SESSION['user_id'])): ?>
<!-- نستخدم Alpine.js لإدارة حالة القائمة المخصصة للجوال -->
<nav class="bg-white shadow" x-data="{ isMobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/dashboard" class="text-2xl font-bold text-indigo-600 hover:text-indigo-700">تاكسي</a>
                </div>
                <!-- روابط القائمة للشاشات الكبيرة (تختفي في الشاشات الصغيرة) -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8 sm:space-x-reverse">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_PATH ?>/dashboard/users" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-users-cog ml-1"></i>
                        إدارة المستخدمين
                    </a>
                    <?php endif; ?>

                    <!-- Activities Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-tasks ml-2"></i>
                            <span>الأنشطة</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20" style="display: none;">
                            <a href="<?= BASE_PATH ?>/calls" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-headset ml-2"></i>مركز الاتصال</a>
                            <a href="<?= BASE_PATH ?>/tickets" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-ticket-alt ml-2"></i>إدارة التذاكر</a>
                            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
                            <a href="<?= BASE_PATH ?>/referral/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-bullhorn ml-2"></i>لوحة التسويق</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <!-- Reports Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-chart-bar ml-2"></i>
                            <span>التقارير</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full left-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden" style="display: none;">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- General Reports -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'general' ? '' : 'general')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير عامة</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'general'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'general'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/analytics" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">التحليلات</a>
                                        <a href="<?= BASE_PATH ?>/reports/system-logs" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">سجلات النظام</a>
                                    </div>
                                </div>
                                <!-- ... (بقية قوائم التقارير تبقى كما هي) ... -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'users' ? '' : 'users')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير المستخدمين والفرق</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'users'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'users'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/users" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير المستخدمين</a>
                                        <a href="<?= BASE_PATH ?>/reports/teamperformance" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">أداء الفريق</a>
                                        <a href="<?= BASE_PATH ?>/reports/team-leaderboard" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">لوحة صدارة الفرق</a>
                                        <a href="<?= BASE_PATH ?>/reports/employee-activity-score" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">أداء الموظفين</a>
                                        <a href="<?= BASE_PATH ?>/reports/myactivity" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">نشاطي</a>
                                    </div>
                                </div>
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'drivers' ? '' : 'drivers')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير السائقين</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'drivers'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'drivers'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/drivers" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير السائقين</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تعيينات السائقين</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">مكالمات السائقين</a>
                                        <a href="<?= BASE_PATH ?>/reports/driver-documents-compliance" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">امتثال مستندات السائقين</a>
                                    </div>
                                </div>
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'trips' ? '' : 'trips')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير الرحلات</span>
                                        <i class="fas fa-route text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'trips'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'trips'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/trips" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير الرحلات المفصل</a>
                                    </div>
                                </div>
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'tickets' ? '' : 'tickets')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير التذاكر</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'tickets'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'tickets'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/tickets-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">ملخص التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/tickets" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-reviews" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">مراجعات التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-discussions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">مناقشات التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">كوبونات التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/ticket-rework" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إعادة عمل التذاكر</a>
                                        <a href="<?= BASE_PATH ?>/reports/review-quality" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">جودة المراجعة</a>
                                    </div>
                                </div>
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'marketing' ? '' : 'marketing')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير التسويق</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'marketing'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'marketing'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/marketer-summary" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">ملخص المسوق</a>
                                        <a href="<?= BASE_PATH ?>/reports/referral-visits" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">زيارات الإحالة</a>
                                    </div>
                                </div>
                                <div>
                                    <button @click="openSubmenu = (openSubmenu === 'other' ? '' : 'other')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>تقارير أخرى</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'other'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'other'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/reports/calls" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير المكالمات</a>
                                        <a href="<?= BASE_PATH ?>/reports/assignments" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير التعيينات</a>
                                        <a href="<?= BASE_PATH ?>/reports/documents" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير المستندات</a>
                                        <a href="<?= BASE_PATH ?>/reports/coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقرير الكوبونات</a>
                                        <a href="<?= BASE_PATH ?>/reports/custom" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">تقارير مخصصة</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Center Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-upload ml-2"></i>
                            <span>مركز الرفع</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-56 bg-white rounded-md shadow-xl z-20" style="display: none;">
                             <a href="<?= BASE_PATH ?>/upload" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">رفع بيانات السائقين</a>
                             <a href="<?= BASE_PATH ?>/trips/upload" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">رفع بيانات الرحلات</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Logs & Tracking Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-history ml-2"></i>
                            <span>السجلات والمتابعة</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20" style="display: none;">
                            <a href="<?= BASE_PATH ?>/logs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-clipboard-list ml-2"></i>سجل الأنشطة</a>
                            <a href="<?= BASE_PATH ?>/discussions" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-comments ml-2"></i>المناقشات</a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                    <!-- Settings Dropdown (Desktop) -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-cogs ml-2"></i>
                            <span>الإعدادات</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full left-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden" style="display: none;">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- ... (قائمة الإعدادات تبقى كما هي) ... -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'system' ? '' : 'system')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>إعدادات النظام</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'system'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'system'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/permissions" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الصلاحيات</a>
                                        <a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إعدادات تليجرام</a>
                                        <a href="<?= BASE_PATH ?>/admin/platforms" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة المنصات</a>
                                        <a href="<?= BASE_PATH ?>/admin/roles" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الأدوار</a>
                                        <a href="<?= BASE_PATH ?>/admin/car_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة أنواع السيارات</a>
                                        <a href="<?= BASE_PATH ?>/admin/countries" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الدول</a>
                                        <a href="<?= BASE_PATH ?>/admin/document_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة أنواع المستندات</a>
                                    </div>
                                </div>
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'team' ? '' : 'team')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>إدارة الفرق</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'team'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'team'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/teams" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الفرق</a>
                                        <a href="<?= BASE_PATH ?>/admin/team_members" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة أعضاء الفرق</a>
                                    </div>
                                </div>
                                <div>
                                    <button @click="openSubmenu = (openSubmenu === 'tickets' ? '' : 'tickets')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>إعدادات التذاكر</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'tickets'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'tickets'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/ticket_categories" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة التصنيفات</a>
                                        <a href="<?= BASE_PATH ?>/admin/ticket_subcategories" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة التصنيفات الفرعية</a>
                                        <a href="<?= BASE_PATH ?>/admin/ticket_codes" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الأكواد</a>
                                        <a href="<?= BASE_PATH ?>/admin/coupons" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الكوبونات</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- معلومات المستخدم للشاشات الكبيرة -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="ml-3 relative">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <span class="text-sm text-gray-500">مرحباً، <?= htmlspecialchars($_SESSION['username']) ?></span>
                        <form method="POST" action="<?= BASE_PATH ?>/auth/logout" class="inline">
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sign-out-alt ml-1"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- زر القائمة للجوال (Hamburger button) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- أيقونة "الخطوط" عندما تكون القائمة مغلقة -->
                    <svg :class="{'hidden': isMobileMenuOpen, 'block': !isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- أيقونة "X" عندما تكون القائمة مفتوحة -->
                    <svg :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- القائمة المخصصة للجوال، تظهر/تختفي بناءً على حالة isMobileMenuOpen -->
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
                <i class="fas fa-users-cog ml-1"></i> إدارة المستخدمين
            </a>
            <?php endif; ?>
            
            <!-- قوائم الجوال المنسدلة باستخدام نفس منطق Alpine.js -->
            <div class="px-4 py-2" x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-tasks ml-2"></i> الأنشطة</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/calls" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-headset ml-2"></i>مركز الاتصال</a>
                    <a href="<?= BASE_PATH ?>/tickets" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-ticket-alt ml-2"></i>إدارة التذاكر</a>
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
                    <a href="<?= BASE_PATH ?>/referral/dashboard" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-bullhorn ml-2"></i>لوحة التسويق</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-4 py-2" x-data="{ open: false }">
                 <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-history ml-2"></i> السجلات والمتابعة</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                 <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <a href="<?= BASE_PATH ?>/logs" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-clipboard-list ml-2"></i>سجل الأنشطة</a>
                    <a href="<?= BASE_PATH ?>/discussions" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"><i class="fas fa-comments ml-2"></i>المناقشات</a>
                </div>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
             <div class="px-4 py-2" x-data="{ open: false }">
                 <button @click="open = !open" class="w-full flex justify-between items-center text-base font-medium text-gray-700 hover:text-indigo-600">
                    <span><i class="fas fa-cogs ml-2"></i> الإعدادات</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                </button>
                 <div x-show="open" x-collapse class="mt-2 space-y-1 pl-4">
                    <!-- هنا يمكنك إضافة قوائم الإعدادات المتداخلة بنفس طريقة التقارير إذا أردت -->
                    <a href="<?= BASE_PATH ?>/admin/permissions" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">إدارة الصلاحيات</a>
                    <a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">إعدادات تليجرام</a>
                    <a href="<?= BASE_PATH ?>/admin/platforms" class="block py-2 px-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">إدارة المنصات</a>
                    <!-- ... أضف بقية روابط الإعدادات هنا ... -->
                 </div>
            </div>
             <!-- يمكنك إضافة بقية القوائم المنسدلة للجوال بنفس الطريقة هنا -->
            <?php endif; ?>
        </div>
        <!-- معلومات المستخدم وتسجيل الخروج في قائمة الجوال -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">مرحباً، <?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                 <form method="POST" action="<?= BASE_PATH ?>/auth/logout">
                    <button type="submit" class="w-full text-right block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt ml-1"></i> تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</nav>
<?php endif; ?>