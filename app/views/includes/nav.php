<?php if (isset($_SESSION['user_id'])): ?>
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?= BASE_PATH ?>/dashboard" class="text-2xl font-bold text-indigo-600">تاكسي</a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8 sm:space-x-reverse">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_PATH ?>/dashboard/users" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-users-cog ml-1"></i>
                        إدارة المستخدمين
                    </a>
                    <a href="<?= BASE_PATH ?>/upload" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 hover:text-indigo-600">
                        <i class="fas fa-file-upload ml-1"></i>
                        رفع بيانات السائقين
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_PATH ?>/dashboard"
                       class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt ml-2"></i>
                        لوحة التحكم
                    </a>
                    <a href="<?= BASE_PATH ?>/tickets"
                       class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                        <i class="fas fa-ticket-alt ml-2"></i>
                        إدارة التذاكر
                    </a>
                    <a href="<?= BASE_PATH ?>/call"
                       class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                        <i class="fas fa-headset ml-2"></i>
                        مركز الاتصال
                    </a>

                    <!-- Logs Dropdown -->
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-history ml-2"></i>
                            <span>السجلات</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 transform -translate-y-2" 
                             x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 transform translate-y-0" 
                             x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20"
                             style="display: none;">
                            <a href="<?= BASE_PATH ?>/logs" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-clipboard-list ml-2"></i>
                                <span>سجل الأنشطة</span>
                            </a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'marketer'])): ?>
                    <a href="<?= BASE_PATH ?>/referral/dashboard" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                        <i class="fas fa-bullhorn ml-2"></i>
                        <span>لوحة التسويق</span>
                    </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                    <div class="relative flex items-center" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-cogs ml-2"></i>
                            <span>الإعدادات</span>
                            <i class="fas fa-chevron-down mr-2 text-xs transition-transform duration-300" :class="{'transform rotate-180': open}"></i>
                        </button>
                        <div @click.away="open = false" x-show="open" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 transform -translate-y-2" 
                             x-transition:enter-end="opacity-100 transform translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 transform translate-y-0" 
                             x-transition:leave-end="opacity-0 transform -translate-y-2" 
                             class="absolute top-full left-0 mt-2 w-72 bg-white rounded-md shadow-xl z-20 overflow-hidden"
                             style="display: none;">
                            <div x-data="{ openSubmenu: '' }">
                                <!-- System Settings -->
                                <div class="border-b border-gray-200">
                                    <button @click="openSubmenu = (openSubmenu === 'system' ? '' : 'system')" class="w-full text-right flex justify-between items-center px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none">
                                        <span>إعدادات النظام</span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'transform rotate-180': openSubmenu === 'system'}"></i>
                                    </button>
                                    <div x-show="openSubmenu === 'system'" x-collapse class="bg-gray-50">
                                        <a href="<?= BASE_PATH ?>/admin/telegram_settings" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إعدادات تليجرام</a>
                                        <a href="<?= BASE_PATH ?>/admin/platforms" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة المنصات</a>
                                        <a href="<?= BASE_PATH ?>/admin/roles" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الأدوار</a>
                                        <a href="<?= BASE_PATH ?>/admin/car_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة أنواع السيارات</a>
                                        <a href="<?= BASE_PATH ?>/admin/countries" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة الدول</a>
                                        <a href="<?= BASE_PATH ?>/admin/document_types" class="block px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900">إدارة أنواع المستندات</a>
                                    </div>
                                </div>

                                <!-- Team Management -->
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

                                <!-- Ticket Settings -->
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
            <div class="flex items-center">
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
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</nav>
<?php endif; ?> 