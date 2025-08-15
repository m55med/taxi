<?php
function getNavigationItems($role) {
    $items = [
        [
            'title' => 'Dashboard',
            'href' => '/dashboard',
            'icon' => 'fas fa-home',
        ],
        [
            'title' => 'Tickets',
            'icon' => 'fas fa-ticket-alt',
            'children' => [
                ['title' => 'All Tickets', 'href' => '/listings/tickets', 'icon' => 'fas fa-tags'],
                ['title' => 'Create Ticket', 'href' => '/create_ticket', 'icon' => 'fas fa-plus-circle'],
                ['title' => 'Search Tickets', 'href' => '/tickets/view', 'icon' => 'fas fa-search'],
            ]
        ],
        [
            'title' => 'Calls',
            'icon' => 'fas fa-headset',
            'children' => [
                ['title' => 'Call Center', 'href' => '/calls', 'icon' => 'fas fa-headset'],
                ['title' => 'All Calls', 'href' => '/listings/calls', 'icon' => 'fas fa-list-ol'],
            ]
        ],
        [
            'title' => 'Drivers',
            'icon' => 'fas fa-car',
            'children' => [
                ['title' => 'All Drivers', 'href' => '/listings/drivers', 'icon' => 'fas fa-users-cog'],
                ['title' => 'Search Drivers', 'href' => '/drivers/details', 'icon' => 'fas fa-search'],
                ['title' => 'Upload Drivers', 'href' => '/upload', 'icon' => 'fas fa-upload', 'permissions' => ['admin', 'developer']],
            ]
        ],
        [
            'title' => 'Quality & Discussions',
            'icon' => 'fas fa-star',
            'children' => [
                ['title' => 'Quality Reviews', 'href' => '/quality/reviews', 'icon' => 'fas fa-star-half-alt'],
                ['title' => 'Discussions', 'href' => '/discussions', 'icon' => 'fas fa-comments'],
            ]
        ],
        [
            'title' => "Referral & Marketing",
            'icon' => 'fas fa-bullseye',
            'permissions' => ['admin', 'developer', 'marketer'],
            'children' => [
                ['title' => "Referral Dashboard", 'href' => '/referral/dashboard', 'icon' => 'fas fa-chart-line'],
                ['title' => "Marketer Registration", 'href' => '/referral/register', 'icon' => 'fas fa-user-plus'],
            ]
        ],
        [
            'title' => 'Reports',
            'icon' => 'fas fa-chart-bar',
            'permissions' => ['admin', 'developer', 'quality_manager', 'Team_leader'],
            'children' => [
                 ['title' => 'My Activity', 'href' => '/reports/myactivity', 'icon' => 'fas fa-running'],
                 ['title' => 'Analytics', 'href' => '/reports/analytics', 'icon' => 'fas fa-chart-pie'],
                 ['title' => 'System Logs', 'href' => '/reports/system-logs', 'icon' => 'fas fa-database'],
                 ['title' => 'Team Leaderboard', 'href' => '/reports/team-leaderboard', 'icon' => 'fas fa-trophy'],
                 ['title' => 'Employee Score', 'href' => '/reports/employee-activity-score', 'icon' => 'fas fa-award'],
                 ['title' => 'Users Report', 'href' => '/reports/users', 'icon' => 'fas fa-users'],
                 ['title' => 'Drivers Report', 'href' => '/reports/drivers', 'icon' => 'fas fa-id-card'],
                 ['title' => 'Tickets Report', 'href' => '/reports/tickets', 'icon' => 'fas fa-file-invoice'],
                 ['title' => 'Custom Reports', 'href' => '/reports/custom', 'icon' => 'fas fa-wrench'],
            ]
        ],
        [
            'title' => 'Activity Logs',
            'icon' => 'fas fa-clipboard-list',
            'children' => [
                [
                    'title' => 'All Logs',
                    'href' => '/logs',
                    'icon' => 'fas fa-list'
                ],
                [
                    'title' => 'Ticket Logs',
                    'href' => '/logs?search=&activity_type=ticket',
                    'icon' => 'fas fa-ticket-alt'
                ],
                [
                    'title' => 'Outgoing Calls',
                    'href' => '/logs?search=&activity_type=outgoing_call',
                    'icon' => 'fas fa-phone-volume'
                ],
                [
                    'title' => 'Incoming Calls',
                    'href' => '/logs?search=&activity_type=incoming_call',
                    'icon' => 'fas fa-phone-square'
                ],
            ]
        ],
        
        
         [
            'title' => 'Help',
            'icon' => 'fas fa-question-circle',
            'children' => [
                ['title' => "Documentation", 'href' => '/documentation', 'icon' => 'fas fa-file-alt'],
                ['title' => "Knowledge Base", 'href' => '/knowledge_base', 'icon' => 'fas fa-lightbulb'],
        ]
    ],
  ];

    return array_filter($items, function ($item) use ($role) {
        if (empty($item['permissions'])) return true;
        return in_array($role, $item['permissions']);
    });
}

function getAdminNavItems($role) {
    $items = [
        [
            'title' => 'Admin Settings',
            'icon' => 'fas fa-cogs',
            'permissions' => ['admin', 'developer', 'quality_manager', 'Team_leader'],
            'children' => [
                ['title' => 'User Management', 'href' => '/admin/users', 'icon' => 'fas fa-users-cog'],
                ['title' => 'Teams Management', 'href' => '/admin/teams', 'icon' => 'fas fa-sitemap'],
                ['title' => 'Team Members', 'href' => '/admin/team_members', 'icon' => 'fas fa-user-friends'],
                [
                    'title' => 'Ticket Settings',
                    'icon' => 'fas fa-cogs',
                    'children' => [
                        ['title' => 'Categories', 'href' => '/admin/ticket_categories', 'icon' => 'fas fa-list-alt'],
                        ['title' => 'Subcategories', 'href' => '/admin/ticket_subcategories', 'icon' => 'fas fa-indent'],
                        ['title' => 'Codes', 'href' => '/admin/ticket_codes', 'icon' => 'fas fa-code'],
                    ]
                ],
                ['title' => 'Bonus Granting', 'href' => '/admin/bonus', 'icon' => 'fas fa-award'],
                ['title' => 'Bonus Settings', 'href' => '/admin/bonus/settings', 'icon' => 'fas fa-gift'],
                ['title' => 'Car Types', 'href' => '/admin/car_types', 'icon' => 'fas fa-car'],
                ['title' => 'Countries', 'href' => '/admin/countries', 'icon' => 'fas fa-globe-americas'],
                ['title' => 'Coupons', 'href' => '/admin/coupons', 'icon' => 'fas fa-percent'],
                ['title' => 'Delegation Types', 'href' => '/delegation-types', 'icon' => 'fas fa-user-tie'],
                ['title' => 'Document Types', 'href' => '/admin/document_types', 'icon' => 'fas fa-file-alt'],
                ['title' => 'Employee Evaluations', 'href' => '/employee-evaluations', 'icon' => 'fas fa-user-check'],
                ['title' => 'Help Videos', 'href' => '/admin/help-videos', 'icon' => 'fas fa-video'],
                ['title' => 'Permissions', 'href' => '/admin/permissions', 'icon' => 'fas fa-shield-alt', 'permissions' => ['admin', 'developer']],
                ['title' => 'Platforms', 'href' => '/admin/platforms', 'icon' => 'fas fa-layer-group'],
                ['title' => 'Points', 'href' => '/admin/points', 'icon' => 'fas fa-star'],
                ['title' => 'Restaurants', 'href' => '/admin/restaurants', 'icon' => 'fas fa-utensils'],
                ['title' => 'Roles', 'href' => '/admin/roles', 'icon' => 'fas fa-user-tag'],
                ['title' => 'System Logs', 'href' => '/logs', 'icon' => 'fas fa-clipboard-list', 'permissions' => ['admin', 'developer']],
                ['title' => 'Telegram Settings', 'href' => '/admin/telegram_settings', 'icon' => 'fab fa-telegram-plane', 'permissions' => ['admin', 'developer']],
                ['title' => 'User Delegations', 'href' => '/admin/user-delegations', 'icon' => 'fas fa-people-arrows'],
            ]
        ]
        
    ];
    return array_filter($items, function ($item) use ($role) {
        if (empty($item['permissions'])) return true;
        return in_array($role, $item['permissions']);
    });
}

function renderNavItem($item, $currentPath) {
        $hasChildren = !empty($item['children']);
    $isActive = false;
    if ($hasChildren) {
        foreach ($item['children'] as $child) {
            if (isset($child['href']) && $child['href'] === $currentPath) {
                $isActive = true;
                break;
            }
        }
            
    } else {
        $isActive = ($item['href'] === $currentPath);
    }

        if ($hasChildren) {
        echo "<div x-data='{ open: " . ($isActive ? 'true' : 'false') . " }'>";
        echo "<button @click='open = !open' class='w-full flex justify-between items-center px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-md transition-colors duration-200'>";
        echo "<div class='flex items-center gap-3'><i class='{$item['icon']} w-5 text-center'></i><span x-show='!isSidebarCollapsed' class='text-sm font-medium'>{$item['title']}</span></div>";
        echo "<i class='fas fa-chevron-down w-4 h-4 transition-transform' x-show='!isSidebarCollapsed' :class='{ \"rotate-180\": open }'></i>";
        echo "</button>";
        echo "<div x-show='open && !isSidebarCollapsed' x-collapse class='pl-4 mt-1 space-y-1'>";
        foreach ($item['children'] as $child) {
            renderNavItem($child, $currentPath);
        }
        echo "</div></div>";
    } else {
        $href = $item['href'] ?? '#';
        $activeClass = $isActive ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100';
        echo "<a href='" . URLROOT . "{$href}' class='w-full flex items-center gap-3 px-3 py-2 rounded-md transition-colors duration-200 {$activeClass}'>";
                echo "<i class='{$item['icon']} w-5 text-center'></i>";
        echo "<span x-show='!isSidebarCollapsed' class='text-sm font-medium'>{$item['title']}</span>";
        echo "</a>";
    }
}

$userRole = $_SESSION['user']['role_name'] ?? 'agent';
$navigationItems = getNavigationItems($userRole);
$adminNavItems = getAdminNavItems($userRole);
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<aside class="relative bg-white border-r transition-all duration-300 z-40 flex flex-col" :class="isSidebarCollapsed ? 'w-20' : 'w-64'">
    <div class="flex items-center p-4 border-b h-16" :class="isSidebarCollapsed ? 'justify-center' : 'justify-between'">
        <div x-show="!isSidebarCollapsed" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-taxi text-white"></i>
        </div>
            <div>
                <h2 class="font-bold text-lg text-gray-800">Taxi CS</h2>
                <p class="text-xs text-gray-500">Customer Service</p>
            </div>
        </div>
        <button @click="isSidebarCollapsed = !isSidebarCollapsed" class="p-2 rounded-md hover:bg-gray-100">
            <i class="fas" :class="isSidebarCollapsed ? 'fa-align-right' : 'fa-align-left'"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-2 py-4">
        <nav class="space-y-1">
            <?php 
            foreach ($navigationItems as $item) {
                renderNavItem($item, $currentPath);
            } 
            if (!empty($adminNavItems)) {
                echo '<div class="my-4 border-t"></div>';
                foreach ($adminNavItems as $item) {
                    renderNavItem($item, $currentPath);
                } 
            }
            ?>
</nav>
    </div>

    <div class="border-t p-2">
         <a href="<?= URLROOT ?>/profile" class="w-full flex items-center gap-3 px-3 py-2 rounded-md transition-colors duration-200 text-gray-600 hover:bg-gray-100">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span x-show="!isSidebarCollapsed" class="text-sm font-medium">My Profile</span>
        </a>
         <a href="<?= URLROOT ?>/logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-md transition-colors duration-200 text-red-600 hover:bg-red-100">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span x-show="!isSidebarCollapsed" class="text-sm font-medium">Logout</span>
        </a>
    </div>
</aside>
