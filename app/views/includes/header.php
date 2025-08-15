<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi CS</title>

    <script>var URLROOT = "<?= URLROOT ?>";</script>
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- AlpineJS Collapse Plugin & Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom App-wide CSS -->
    <link rel="stylesheet" href="<?= URLROOT; ?>/css/app.css">

    <style>
            [x-cloak] { display: none !important; }
    </style>
<!-- AlpineJS Data Store for Notifications -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notifications', () => ({
                open: false,
                unreadCount: 0,
                notifications: [],
                init() {
                    this.fetchNotifications();
                    setInterval(() => this.fetchNotifications(), 60000); // Refresh every minute
                },
                fetchNotifications() {
                    fetch('<?= URLROOT ?>/notifications/getNavNotifications')
                        .then(res => {
                            if (!res.ok) {
                                // If response is not ok (like a 401 or 500), throw an error to be caught by the .catch block
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data && data.notifications) {
                                this.notifications = data.notifications.map(n => ({...n, timeAgo: this.formatTimeAgo(n.created_at)}));
                                this.unreadCount = data.unread_count;
                            } else {
                                // Handle cases where data is not in the expected format, but the request was successful
                                this.notifications = [];
                                this.unreadCount = 0;
                            }
                        }).catch(err => {
                            console.error('Error fetching nav notifications:', err);
                            this.notifications = [];
                            this.unreadCount = 0;
                        });
                },
                markAsRead(id) {
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification && !notification.is_read) {
                        fetch('<?= URLROOT ?>/notifications/markRead/' + id, { method: 'POST' })
                            .then(() => this.fetchNotifications());
                    }
                    if (notification && notification.link) {
                        window.location.href = notification.link;
                    }
                },
                markAllAsRead() {
                    fetch('<?= URLROOT ?>/notifications/markAllRead', { method: 'POST' })
                            .then(() => this.fetchNotifications());
                },
                formatTimeAgo(dateString) {
                    const now = new Date();
                    const past = new Date(dateString);
                    const seconds = Math.floor((now - past) / 1000);
                    let interval = seconds / 31536000;
                    if (interval > 1) return Math.floor(interval) + " years ago";
                    interval = seconds / 2592000;
                    if (interval > 1) return Math.floor(interval) + " months ago";
                    interval = seconds / 86400;
                    if (interval > 1) return Math.floor(interval) + " days ago";
                    interval = seconds / 3600;
                    if (interval > 1) return Math.floor(interval) + " hours ago";
                    interval = seconds / 60;
                    if (interval > 1) return Math.floor(interval) + " minutes ago";
                    return Math.floor(seconds) + " seconds ago";
                }
            }));
        });
    </script>
</head>
<body class="bg-gray-100" x-data="{ isSidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('isSidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <?php require_once APPROOT . '/views/includes/nav.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Nav -->
            <header class="sticky top-0 z-30 w-full border-b bg-white bg-opacity-80 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 lg:px-6">
                    <div class="flex-1 max-w-md">
                        <form action="/search" method="get" class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="search" name="q" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </form>
                    </div>
                    <div class="flex items-center gap-4" x-data="notifications">
                        <!-- Notification Center -->
                        <div class="relative">
                             <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700">
                                <i class="fas fa-bell fa-lg"></i>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                                </template>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-20 p-0" x-cloak>
                                 <div class="flex items-center justify-between p-4 border-b">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-bell"></i>
                                        <h3 class="font-semibold">Notifications</h3>
                                        <template x-if="unreadCount > 0">
                                            <span class="bg-gray-200 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-full" x-text="unreadCount + ' new'"></span>
                                        </template>
                                    </div>
                                    <template x-if="unreadCount > 0">
                                        <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:underline">Mark all as read</button>
                                    </template>
                                 </div>
                                 <div class="max-h-96 overflow-y-auto p-2">
                                    <template x-if="notifications.length === 0">
                                        <p class="text-gray-500 text-sm text-center p-8">No new notifications.</p>
                                    </template>
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markAsRead(notification.id)" class="p-3 rounded-lg cursor-pointer transition-colors hover:bg-gray-100" :class="{ 'bg-blue-50': !notification.is_read }">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 mt-1 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-sm font-bold">
                                                    <i class="fas fa-user"></i> <!-- Placeholder Icon -->
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h4 class="text-sm font-medium" :class="{ 'font-bold': !notification.is_read }" x-text="notification.title"></h4>
                                                        <template x-if="!notification.is_read">
                                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                        </template>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1" x-text="notification.message"></p>
                                                    <div class="flex items-center gap-1 text-xs text-gray-400 mt-2">
                                                        <i class="fas fa-clock h-3 w-3"></i>
                                                        <span x-text="notification.timeAgo"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                 </div>
                                 <div class="border-t p-2">
                                    <a href="/notifications/history" class="block text-center w-full py-2 text-sm font-medium text-blue-600 hover:bg-gray-100 rounded-md">View all notifications</a>
                                 </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative h-10 w-10 rounded-full">
                                <div class="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
    <?php 
                                        $name = $_SESSION['user']['username'] ?? 'U';
                                        $initials = '';
                                        $parts = explode(' ', $name);
                                        foreach ($parts as $part) {
                                            if (!empty($part)) $initials .= strtoupper($part[0]);
                                        }
                                        echo substr($initials, 0, 2);
                                    ?>
                                </div>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute top-full right-0 mt-2 w-56 bg-white rounded-md shadow-xl z-20" x-cloak>
                                <div class="px-4 py-3 border-b">
                                    <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?></p>
                                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($_SESSION['user']['role_name'] ?? 'Role') ?></p>
                                </div>
                                <div class="py-1">
                                    <a href="/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user-circle fa-fw mr-2 text-gray-500"></i> Your Profile
                                    </a>
                                    <a href="/logout" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-100">
                                        <i class="fas fa-sign-out-alt fa-fw mr-2"></i> Sign out
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-auto p-4 lg:p-6">
                <?php include_once APPROOT . '/views/includes/flash_messages.php'; ?>
                <!-- The view content will be loaded here -->