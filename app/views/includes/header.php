<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi CS</title>

    <!-- Favicon -->
    <link rel="icon" href="https://taxif.om/assets/images/PROFILE.png" type="image/png" sizes="32x32">
    <link rel="shortcut icon" href="https://taxif.om/assets/images/PROFILE.png" type="image/png">
    <link rel="apple-touch-icon" href="https://taxif.om/assets/images/PROFILE.png">
    <meta name="msapplication-TileImage" content="https://taxif.om/assets/images/PROFILE.png">
    <meta name="msapplication-TileColor" content="#ffffff">

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
        function pageState() {
            return {
                // Sidebar state
                isSidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',

                // Break timer state
                onBreak: false,
                startTime: null,
                timer: '00:00:00',
                interval: null,
                showModal: false,

                init() {
                    this.checkStatus();
                    this.$watch('isSidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val));
                },

                checkStatus() {
                    fetch('<?= URLROOT ?>/breaks/status')
                        .then(res => res.json())
                        .then(data => {
                            if (data.on_break) {
                                this.startLocalTimer(data.break.start_time);
                            }
                        });
                },

                startLocalTimer(startTime) {
                    this.onBreak = true;
                    this.startTime = new Date(startTime); // This startTime is already UTC from backend
                    this.showModal = true;

                    // Validate startTime is not in the future (which would cause negative timer)
                    const now = new Date();
                    if (this.startTime > now) {
                        console.warn('Start time is in the future, using current time instead');
                        this.startTime = now;
                    }

                    this.startTimer();
                },

                startTimer() {
                    if (this.interval) clearInterval(this.interval);

                    // Set timer to 00:00:00 initially when starting
                    this.timer = '00:00:00';

                    this.interval = setInterval(() => {
                        const now = new Date();

                        if (!this.startTime) {
                            console.warn('No startTime set, cannot calculate timer');
                            return;
                        }

                        const diff = Math.floor((now.getTime() - this.startTime.getTime()) / 1000);

                        // Ensure diff is not negative (in case of time sync issues)
                        const safeDiff = Math.max(0, diff);

                        // Debugging logs (only log occasionally to avoid console spam)
                        if (safeDiff % 10 === 0) { // Log every 10 seconds
                            console.log('Timer diff (seconds):', safeDiff);
                        }

                        const h = String(Math.floor(safeDiff / 3600)).padStart(2, '0');
                        const m = String(Math.floor((safeDiff % 3600) / 60)).padStart(2, '0');
                        const s = String(safeDiff % 60).padStart(2, '0');
                        this.timer = `${h}:${m}:${s}`;
                    }, 1000);
                },

                toggleBreak() {
                    if (this.onBreak) {
                        this.showModal = !this.showModal;
                    } else {
                        // Start break locally (show modal, set onBreak) but don't start timer yet
                        this.onBreak = true;
                        this.showModal = true;
                        this.timer = '00:00:00'; // Reset timer to 00:00:00 initially
                        // Do NOT set this.startTime here, wait for server response

                        fetch('<?= URLROOT ?>/breaks/start', { method: 'POST' })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success' && data.break) {
                                    this.startTime = new Date(data.break.start_time); // Set actual UTC start time from server

                                    // Validate startTime is not in the future
                                    const now = new Date();
                                    if (this.startTime > now) {
                                        console.warn('Server start time is in the future, using current time instead');
                                        this.startTime = now;
                                    }

                                    this.startTimer(); // Start timer ONLY after getting the correct time
                                } else {
                                    this.onBreak = false;
                                    this.showModal = false;
                                    this.timer = '00:00:00';
                                    if (this.interval) clearInterval(this.interval);
                                    alert(data.message || 'Failed to start break.');
                                }
                            }).catch(error => {
                                console.error("Failed to start break:", error);
                                this.onBreak = false;
                                this.showModal = false;
                                this.timer = '00:00:00';
                                if (this.interval) clearInterval(this.interval);
                                alert('An error occurred while starting your break.');
                            });
                    }
                },

                stopBreak() {
                    fetch('<?= URLROOT ?>/breaks/stop', { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.onBreak = false;
                                this.showModal = false;
                                if (this.interval) clearInterval(this.interval);
                                this.timer = '00:00:00'; // Reset timer to 00:00:00
                                this.startTime = null; // Clear start time
                            } else {
                                alert(data.message);
                            }
                        }).catch(error => {
                            console.error("Failed to stop break:", error);
                            alert('An error occurred while stopping your break.');
                        });
                },

                getCurrentMinutes() {
                    if (!this.startTime) return 0;
                    const now = new Date();
                    const diffSeconds = Math.floor((now - this.startTime) / 1000);
                    return Math.max(0, Math.floor(diffSeconds / 60)); // Ensure non-negative result
                }
            }
        }
        document.addEventListener('alpine:init', () => {
            Alpine.data('pageState', pageState);
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
            }            ));
        });

        // Simple Search for Header
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('header-search');
            const suggestionsContainer = document.getElementById('header-suggestions');
            let searchTimeout = null;

            if (!searchInput || !suggestionsContainer) {
                console.log('Header search elements not found');
                return;
            }

            searchInput.addEventListener('input', function() {
                const term = this.value.trim();

                if (term.length < 2) {
                    suggestionsContainer.innerHTML = '';
                    suggestionsContainer.classList.add('hidden');
                    return;
                }

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Debounce search
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(term);
                }, 300);
            });

            function fetchSuggestions(term) {
                console.log('Fetching suggestions for:', term);

                fetch(`${URLROOT}/tickets/ajaxSearch?term=${encodeURIComponent(term)}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);

                        if (Array.isArray(data) && data.length > 0) {
                            let suggestionsHtml = '<div class="p-2">';
                            suggestionsHtml += '<div class="text-xs text-gray-500 mb-2 px-2">Search Results:</div>';

                            data.forEach(item => {
                                suggestionsHtml += `
                                    <a href="${URLROOT}/tickets/view/${item.id}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-ticket-alt text-blue-500"></i>
                                            <span class="font-medium text-gray-900">${item.label}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">Ticket</div>
                                    </a>
                                `;
                            });

                            suggestionsHtml += `
                                <div class="border-t border-gray-200 p-2">
                                    <a href="${URLROOT}/tickets/search" class="block w-full text-center py-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        View all results →
                                    </a>
                                </div>
                            `;

                            suggestionsHtml += '</div>';

                            suggestionsContainer.innerHTML = suggestionsHtml;
                            suggestionsContainer.classList.remove('hidden');
                        } else {
                            suggestionsContainer.innerHTML = '<div class="px-4 py-2 text-gray-500">No suggestions found</div>';
                            suggestionsContainer.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        suggestionsContainer.innerHTML = '<div class="px-4 py-2 text-red-500">Error loading suggestions</div>';
                        suggestionsContainer.classList.remove('hidden');
                    });
            }

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== searchInput && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });

            // Submit form on Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    // Check if there are suggestions visible
                    const suggestions = suggestionsContainer.querySelectorAll('a[href]');
                    if (suggestions.length > 0 && !suggestionsContainer.classList.contains('hidden')) {
                        // If there are suggestions, go to first result
                        const firstLink = suggestions[0];
                        if (firstLink && firstLink.href) {
                            window.location.href = firstLink.href;
                            return;
                        }
                    }

                    // If no suggestions or hidden, submit the form for general search
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100" x-data="pageState()">
    
    <!-- Break Timer Modal -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak 
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="showModal = false" 
             class="bg-white rounded-lg p-6 shadow-xl text-center transform transition-all w-full max-w-sm" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             :class="{ 'bg-red-50 border-red-300 border-2': getCurrentMinutes() >= 25 }">
            <h3 class="text-lg font-bold mb-4" :class="{'text-red-700': getCurrentMinutes() >= 25}">
                <i class="fas fa-coffee mr-2"></i>On a Break
            </h3>
            <div class="text-5xl font-mono bg-gray-100 p-4 rounded-lg mb-6" :class="{'bg-red-100 text-red-800': getCurrentMinutes() >= 25}" x-text="timer"></div>
            <button @click="stopBreak()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-full w-full transition-colors text-lg">
                <i class="fas fa-stop-circle mr-2"></i>Stop Break
            </button>
            <p x-show="getCurrentMinutes() >= 25" class="text-red-600 text-sm mt-3 animate-pulse">Your break is longer than 25 minutes.</p>
        </div>
    </div>


    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <?php require_once APPROOT . '/views/includes/nav.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Nav -->
            <header class="sticky top-0 z-30 w-full border-b bg-white bg-opacity-80 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 lg:px-6">
                    <div class="flex-1 max-w-md">
                        <form action="<?= URLROOT ?>/tickets/search" method="get" class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="search" id="header-search" name="search_term"
                                   placeholder="e.g., T-12345 or 968..."
                                   class="w-full pl-10 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   autocomplete="off">

                            <!-- Search Suggestions Dropdown -->
                            <div id="header-suggestions" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto hidden">
                                <!-- Suggestions will be populated by JavaScript -->
                            </div>
                        </form>
                    </div>
                    <div class="flex items-center gap-4" x-data="notifications">

                        <!-- Break Button -->
                        <div>
                            <button @click="toggleBreak()" class="relative text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md transition-colors" :class="{ 'bg-yellow-100 text-yellow-700': onBreak }">
                                <i class="fas fa-coffee fa-lg"></i>
                                <span x-show="onBreak" class="ml-2 font-mono text-sm" x-text="timer"></span>
                            </button>
                        </div>


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