<?php

// Mock user role for testing, replace with actual session data

$userRole = $_SESSION['user_role'] ?? 'agent';



function hasPermission($permissions, $userRole) {

    if (empty($permissions)) return true;

    return in_array($userRole, $permissions) || $userRole === 'admin' || $userRole === 'developer';

}



$navigationItems = [

    [

        'title' => 'لوحة التحكم',

        'href' => '/dashboard',

        'icon' => 'fa-home',

    ],

    [

        'title' => 'التذاكر',

        'icon' => 'fa-ticket-alt',

        'children' => [

            ['title' => 'جميع التذاكر', 'href' => '/tickets', 'icon' => 'fa-ticket-alt'],

            ['title' => 'إنشاء تذكرة', 'href' => '/tickets/create', 'icon' => 'fa-plus'],

            ['title' => 'البحث في التذاكر', 'href' => '/tickets/search', 'icon' => 'fa-search'],

            ['title' => 'قوائم التذاكر', 'href' => '/listings/tickets', 'icon' => 'fa-file-alt'],

            ['title' => 'محادثات Trengo', 'href' => '/tickets/trengo/viewer', 'icon' => 'fa-comments'],

        ]

    ],

    [

        'title' => 'المكالمات',

        'icon' => 'fa-phone',

        'children' => [

            ['title' => 'جميع المكالمات', 'href' => '/calls', 'icon' => 'fa-phone'],

            ['title' => 'قوائم المكالمات', 'href' => '/listings/calls', 'icon' => 'fa-file-alt'],

        ]

    ],

    [

        'title' => 'السائقين',

        'icon' => 'fa-car',

        'children' => [

            ['title' => 'جميع السائقين', 'href' => '/drivers', 'icon' => 'fa-car'],

            ['title' => 'البحث في السائقين', 'href' => '/drivers/search', 'icon' => 'fa-search'],

            ['title' => 'رفع السائقين', 'href' => '/upload', 'icon' => 'fa-upload', 'permissions' => ['admin', 'developer']],

            ['title' => 'قوائم السائقين', 'href' => '/listings/drivers', 'icon' => 'fa-file-alt'],

        ]

    ],

    [

        'title' => 'قاعدة المعرفة',

        'icon' => 'fa-book-open',

        'children' => [

            ['title' => 'قاعدة المعرفة', 'href' => '/knowledge_base', 'icon' => 'fa-book-open'],

            ['title' => 'إنشاء مقال', 'href' => '/knowledge_base/create', 'icon' => 'fa-plus'],

            ['title' => 'البحث', 'href' => '/knowledge_base/search', 'icon' => 'fa-search'],

            ['title' => 'التوثيق', 'href' => '/documentation', 'icon' => 'fa-file-alt'],

        ]

    ],

    [

        'title' => 'الجودة',

        'icon' => 'fa-star',

        'children' => [

            ['title' => 'مراجعات الجودة', 'href' => '/quality/reviews', 'icon' => 'fa-star'],

        ]

    ],

    ['title' => 'المناقشات', 'href' => '/discussions', 'icon' => 'fa-comments'],

    [

        'title' => 'الإحالة والتسويق',

        'icon' => 'fa-bullseye',

        'children' => [

            ['title' => 'لوحة الإحالة', 'href' => '/referral/dashboard', 'icon' => 'fa-chart-line'],

            ['title' => 'تسجيل مسوق', 'href' => '/referral/register', 'icon' => 'fa-user-check'],

        ]

    ],

    [

        'title' => 'التقارير',

        'icon' => 'fa-chart-bar',

        'children' => [

            ['title' => 'نشاطي', 'href' => '/reports/myactivity', 'icon' => 'fa-chart-pie'],

            ['title' => 'تقارير المستخدمين', 'href' => '/reports/users', 'icon' => 'fa-users'],

            ['title' => 'تقارير السائقين', 'href' => '/reports/drivers', 'icon' => 'fa-car'],

            ['title' => 'التحليلات', 'href' => '/reports/analytics', 'icon' => 'fa-chart-pie'],

             ['title' => 'سجلات النظام', 'href' => '/reports/system-logs', 'icon' => 'fa-database'],

        ]

    ],

    [

        'title' => 'الإعدادات',

        'icon' => 'fa-cogs',

        'permissions' => ['admin', 'developer', 'quality_manager', 'Team_leader'],

        'children' => [

            ['title' => 'إدارة المستخدمين', 'href' => '/admin/users', 'icon' => 'fa-users-cog'],

            ['title' => 'إدارة الفرق', 'href' => '/admin/teams', 'icon' => 'fa-users'],

            ['title' => 'الصلاحيات', 'href' => '/admin/permissions', 'icon' => 'fa-shield-alt', 'permissions' => ['admin', 'developer']],

            ['title' => 'إعدادات النظام', 'href' => '/admin/countries', 'icon' => 'fa-globe'],

        ]

    ],

];



function renderNavItem($item, $userRole, $currentPath) {

    if (isset($item['permissions']) && !hasPermission($item['permissions'], $userRole)) {

        return '';

    }



    $hasChildren = !empty($item['children']);

    

    $output = '';



    if ($hasChildren) {

        // Check if any child is active

        $isGroupActive = false;

        foreach ($item['children'] as $child) {

            if (isset($child['href']) && $child['href'] === $currentPath) {

                $isGroupActive = true;

                break;

            }

        }

        

        $output .= '<div x-data="{ open: ' . ($isGroupActive ? 'true' : 'false') . ' }">';

        $output .= '<button @click="open = !open" class="w-full flex items-center justify-between text-gray-200 hover:bg-gray-700 px-4 py-2 rounded-md transition-colors duration-200">';

        $output .= '<div class="flex items-center">';

        $output .= '<i class="fas ' . $item['icon'] . ' w-6"></i>';

        $output .= '<span class="mx-4 font-medium">' . $item['title'] . '</span>';

        $output .= '</div>';

        $output .= '<i class="fas fa-chevron-down transform transition-transform duration-200" :class="{ \'rotate-180\': open }"></i>';

        $output .= '</button>';

        $output .= '<div x-show="open" x-collapse class="pl-8 mt-2 space-y-2">';

        foreach ($item['children'] as $child) {

            $output .= renderNavItem($child, $userRole, $currentPath);

        }

        $output .= '</div>';

        $output .= '</div>';

    } else {

        $isActive = isset($item['href']) && $item['href'] === $currentPath;

        $activeClasses = $isActive ? 'bg-gray-700' : '';

        $output .= '<a href="' . URLROOT . ($item['href'] ?? '#') . '" class="flex items-center text-gray-200 hover:bg-gray-700 px-4 py-2 rounded-md transition-colors duration-200 ' . $activeClasses . '">';

        $output .= '<i class="fas ' . $item['icon'] . ' w-6"></i>';

        $output .= '<span class="mx-4 font-medium">' . $item['title'] . '</span>';

        $output .= '</a>';

    }



    return $output;

}



$currentPath = '/' . ($_GET['url'] ?? '');



?>



<aside class="flex flex-col w-64 h-screen px-4 py-8 bg-gray-800 border-r rtl:border-r-0 rtl:border-l overflow-y-auto">

    <a href="<?php echo URLROOT; ?>/dashboard" class="flex items-center justify-center">

        <i class="fas fa-taxi text-white text-3xl"></i>

        <h2 class="text-2xl font-bold text-white ml-2">Taxi CS</h2>

    </a>



    <div class="flex flex-col justify-between flex-1 mt-6">

        <nav class="space-y-2">

            <?php

            foreach ($navigationItems as $item) {

                echo renderNavItem($item, $userRole, $currentPath);

            }

            ?>

        </nav>



        <div>

            <a href="<?php echo URLROOT; ?>/logout" class="flex items-center text-red-400 hover:bg-red-800 hover:text-white px-4 py-2 rounded-md transition-colors duration-200">

                <i class="fas fa-sign-out-alt w-6"></i>

                <span class="mx-4 font-medium">تسجيل الخروج</span>

            </a>

        </div>

    </div>

</aside>

