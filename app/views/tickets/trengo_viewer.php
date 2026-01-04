<?php include_once __DIR__ . '/../includes/header.php'; ?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="container mx-auto px-4 py-8 max-w-7xl font-['Inter']">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <!-- Token Expiry Warning Modal -->
    <div id="tokenExpiryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm <?php echo (isset($data['token_expired']) && $data['token_expired'] && empty($data['ticket_number'])) ? '' : 'hidden'; ?>" style="<?php echo (isset($data['token_expired']) && $data['token_expired'] && empty($data['ticket_number'])) ? '' : 'display: none;'; ?>">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl border border-red-100">
            <div class="text-center">
                <!-- Warning Icon -->
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">التوكن منتهي الصلاحية</h3>

                <!-- Message -->
                <p class="text-gray-600 mb-6 leading-relaxed">
                    انتهت صلاحية التوكن الخاص بـ Trengo API.<br>
                    من فضلك اتصل بالمسؤول لتجديد التوكن.
                </p>

                <!-- Expiry Info -->
                <?php if (isset($data['token_expiry_date'])): ?>
                <div class="bg-gray-50 p-3 rounded-lg mb-6">
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-calendar-times mr-2"></i>
                        تاريخ الانتهاء: <strong class="text-gray-700"><?= htmlspecialchars($data['token_expiry_date']) ?></strong>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Action Button -->
                <button
                    onclick="window.location.href='mailto:admin@taxif.com?subject=Trengo Token Expired&body=The Trengo API token has expired. Please renew it.'"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl transition-all transform hover:scale-105 shadow-lg shadow-red-200 mb-4">
                    <i class="fas fa-envelope mr-2"></i>
                    اتصل بالمسؤول
                </button>

                <!-- Close Option -->
                <p class="text-xs text-gray-400 mt-4">
                    يمكنك إغلاق هذه النافذة لكن الوظائف لن تعمل بشكل صحيح
                </p>
                <button
                    onclick="closeTokenExpiryModal()"
                    class="text-xs text-gray-400 hover:text-gray-600 mt-2 underline">
                    إغلاق النافذة
                </button>
            </div>
        </div>
    </div>

    <!-- Token Warning Banner -->
    <?php if (isset($data['token_expired']) && $data['token_expired']): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    تحذير: التوكن منتهي الصلاحية
                </h3>
                <div class="mt-1 text-sm text-red-700">
                    <p>بعض الوظائف قد لا تعمل بشكل صحيح. يرجى تجديد التوكن من قبل المسؤول.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Premium Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center">
                <span class="bg-blue-600 text-white p-2 rounded-xl mr-3 shadow-lg shadow-blue-200">
                    <i class="fas fa-comment-alt-lines"></i>
                </span>
                Trengo <span class="text-blue-600 ml-2">Viewer</span>
            </h1>
            <p class="text-gray-500 mt-2 flex items-center text-sm" id="ticketInfo">
                <?php if (!empty($data['ticket_number'])): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                        Ticket #<?= htmlspecialchars($data['ticket_number']) ?>
                    </span>
                    Real-time conversation inspection
                <?php else: ?>
                    <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                    Input a ticket ID to begin exploration
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Premium Search Box -->
        <div class="flex-1 max-w-md">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-hashtag text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
                <input 
                    type="text" 
                    id="ticketSearch" 
                    placeholder="Enter ticket number (e.g. 913822465)"
                    value="<?= htmlspecialchars($data['ticket_number'] ?? '') ?>"
                    class="block w-full pl-10 pr-3 py-3 border-none bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 transition-all text-sm placeholder-gray-400"
                >
                <div class="absolute inset-y-0 right-1 flex items-center pr-1">
                    <button 
                        onclick="loadTicket()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all font-semibold text-sm shadow-md shadow-blue-100 flex items-center">
                        <i class="fas fa-search mr-2"></i>Load
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Messages Area (Main) -->
        <div class="lg:col-span-8 flex flex-col h-[calc(100vh-220px)] min-h-[600px]">
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 flex flex-col overflow-hidden" style="height: 100%;">
                
                <!-- Messages Top Static Nav -->
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-white z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-users-class text-lg"></i>
                        </div>
                        <div>
                            <h3 id="chatHeaderName" class="font-bold text-gray-800 text-sm">Conversation History</h3>
                            <p id="chatHeaderStatus" class="text-xs text-gray-400 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                                Active View
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openAssignModal()" class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm" title="Assign Ticket">
                            <i class="fas fa-user-plus"></i>
                            <span class="hidden md:inline">Assign</span>
                        </button>
                        <button onclick="scrollToBottom()" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all" title="Scroll to newest">
                            <i class="fas fa-arrow-down-to-line"></i>
                        </button>
                        <button onclick="loadTicket()" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Feed -->
                <div
                    id="messagesContainer"
                    class="flex-1 overflow-y-auto px-4 py-4 scroll-smooth bg-gradient-to-b from-gray-50/30 to-white/50 backdrop-blur-sm border border-gray-100/50 rounded-2xl shadow-inner relative"
                    style="min-height: 400px;">

                    <!-- Internal Container for grouping -->
                    <div id="messagesContent" class="space-y-4 flex flex-col min-h-full flex-1">
                        <!-- Initial state / Empty state -->
                        <div id="emptyState" class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center mb-6 shadow-sm border border-blue-100/50">
                                <i class="fas fa-comments text-2xl text-blue-500"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 mb-2">ابدأ المحادثة</h4>
                            <p class="text-gray-600 max-w-sm mx-auto leading-relaxed">أدخل رقم تذكرة Trengo في مربع البحث أعلاه لبدء استكشاف تفاصيل المحادثة والرسائل.</p>
                            <div class="mt-4 flex items-center gap-2 text-sm text-gray-400">
                                <i class="fas fa-lightbulb"></i>
                                <span>مثال: 867529236</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading indicator (centered) -->
                    <div id="loadingIndicator" class="hidden absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm z-10">
                        <div class="flex flex-col items-center gap-4">
                            <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">جاري تحميل جميع الرسائل...</p>
                        </div>
                    </div>
                </div>

                <!-- Footer / Status Bar -->
                <div class="px-6 py-3 border-t border-gray-50 bg-white flex items-center justify-between text-[11px] text-gray-400 font-medium">
                    <div id="msgCountDisplay">0 nodes active</div>
                    <div class="flex items-center gap-3">
                        <span class="flex items-center"><i class="fas fa-shield-check text-green-500 mr-1"></i>Secure View</span>
                        <span class="flex items-center"><i class="fas fa-bolt text-yellow-500 mr-1"></i>Optimized</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Contextual Sidebar -->
        <div class="lg:col-span-4 space-y-6 sticky top-8">
            
            <!-- Skeleton Sidebar Loading -->
            <div id="sidebarSkeleton" class="hidden space-y-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-50 animate-pulse">
                    <div class="h-4 bg-gray-100 rounded w-1/3 mb-6"></div>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="h-10 w-10 bg-gray-100 rounded-full"></div>
                            <div class="flex-1 space-y-2 py-1">
                                <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                            </div>
                        </div>
                        <div class="h-px bg-gray-100"></div>
                        <div class="h-10 bg-gray-100 rounded-xl"></div>
                        <div class="h-10 bg-gray-100 rounded-xl"></div>
                    </div>
                </div>
            </div>

            <!-- Contact Intelligence Card -->
            <div id="contactCard" class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 p-6 hidden transform transition-all duration-500 translate-y-4 opacity-0">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center">
                        <i class="fas fa-fingerprint text-blue-500 mr-2"></i>Contact Identity
                    </h3>
                    <span id="platformBadge" class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">Loading...</span>
                </div>
                
                <div class="flex items-center gap-4 mb-6">
                    <div id="contactInitials" class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-lg shadow-blue-100">
                        ??
                    </div>
                    <div>
                        <h4 id="contactNameDisplay" class="font-bold text-gray-800">Searching...</h4>
                        <div id="countryDisplay" class="text-xs text-gray-400 font-medium">Identifying origin...</div>
                    </div>
                </div>

                <div id="contactInfo" class="space-y-3">
                    <!-- Dynamic details -->
                </div>
            </div>

            <!-- Contextual Intelligence Card (Other Tickets) -->
            <div id="otherTicketsCard" class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 overflow-hidden hidden transform transition-all duration-500 translate-y-4 opacity-0">
                <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-sm">
                        <i class="fas fa-history text-indigo-500 mr-2"></i>Other Interactions
                    </h3>
                    <span id="otherTicketsCount" class="bg-white text-gray-500 px-2 py-0.5 rounded-lg text-[10px] border border-gray-100 font-bold">0</span>
                </div>
                
                <div id="otherTickets" class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto overscroll-contain">
                    <!-- Populated dynamically -->
                </div>

                <div id="otherTicketsFooter" class="p-4 bg-white border-t border-gray-50">
                    <button 
                        id="loadMoreTickets" 
                        class="w-full text-xs font-bold text-blue-600 hover:text-white hover:bg-blue-600 py-3 rounded-2xl transition-all border border-blue-50 flex items-center justify-center hidden"
                        onclick="loadMoreOtherTickets()">
                        View More Activities <i class="fas fa-chevron-right ml-2 text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Ticket Modal -->
<div id="assignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all duration-300 scale-95" id="assignModalContent">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 flex items-center text-sm">
                <i class="fas fa-user-plus text-blue-500 mr-2"></i>Assign Ticket
            </h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Agent</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="userSearch" oninput="filterUsers()" placeholder="Search by name or email..." class="w-full pl-9 pr-4 py-2.5 bg-gray-50 rounded-xl border-none ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 text-sm outline-none">
                </div>
            </div>

            <div id="usersList" class="max-h-[300px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                <!-- Users will be loaded here -->
                <div class="py-10 text-center text-gray-400 text-xs">
                    <i class="fas fa-spinner fa-spin mb-2 text-blue-400"></i><br>Loading agents...
                </div>
            </div>

            <div id="assignLoadingIndicator" class="hidden mt-4 py-3 bg-blue-50 border border-blue-100 rounded-xl text-center text-blue-600 text-xs font-black uppercase tracking-wider">
                <i class="fas fa-spinner fa-spin mr-2"></i>Assigning ticket...
            </div>
        </div>
    </div>
</div>

<!-- Image View Modal (Premium) -->
<div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/95 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 p-4">
    <div class="absolute top-6 right-6 flex items-center gap-3">
        <a id="modalDownloadLink" href="#" download class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all">
            <i class="fas fa-download"></i>
        </a>
        <button onclick="closeImageModal()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="max-w-5xl w-full flex flex-col items-center">
        <img id="modalImage" src="" class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl object-contain message-image-anim" alt="Detailed view">
        <p id="modalCaption" class="mt-4 text-white/60 text-sm font-medium"></p>
    </div>
</div>

<script>
/**
 * PREMIUM TRENGO VIEWER ENGINE - VERSION 2.2
 * Features: Infinite Scroll, Cross-Ticket Merging, High-Fidelity UI
 */

// 1. UI Configuration
const THEME = {
    INBOUND: { bubble: 'bg-white border-gray-100 shadow-sm', text: 'text-gray-800', align: 'items-start' },
    OUTBOUND: { bubble: 'bg-blue-600 border-blue-500 shadow-md shadow-blue-100', text: 'text-white', align: 'items-end' }
};

// 2. Global Application State
let G = {
    contactId: null,
    activeTicketId: null,
    ticketQueue: [], 
    loadedMessages: [], 
    currentTicketPage: 1,
    hasMoreInCurrentTicket: false,
    isLoading: false,
    isInitialLoad: true,
    otherTicketsPage: 1,
    hasMoreOtherTickets: false,
    trengoUsers: [],
    isLoadingUsers: false
};

// 3. DOM Abstraction Layer
const dom = {
    get: (id) => document.getElementById(id),
    show: (id) => { const e = dom.get(id); if(e) e.classList.remove('hidden'); },
    hide: (id) => { const e = dom.get(id); if(e) e.classList.add('hidden'); },
    html: (id, content) => { const e = dom.get(id); if(e) e.innerHTML = content; },
    val: (id) => { const e = dom.get(id); return e ? e.value : ''; }
};

// 4. Utility Functions
const utils = {
    parseId: (input) => {
        if (!input) return '';
        let cleaned = String(input).trim().replace(/^#/, '').trim();
        return /^\d+$/.test(cleaned) ? cleaned : '';
    },
    escape: (text) => {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    },
    formatTime: (dateStr) => {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    formatDate: (dateStr) => {
        const d = new Date(dateStr);
        const now = new Date();
        if (d.toDateString() === now.toDateString()) return 'Today';
        const yesterday = new Date(now); yesterday.setDate(now.getDate() - 1);
        if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
        return d.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
    }
};

/**
 * CORE WORKFLOWS
 */

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    const initialTicket = '<?= htmlspecialchars($data['ticket_number'] ?? '') ?>';
    if (initialTicket) {
        const searchInput = dom.get('ticketSearch');
        if (searchInput) searchInput.value = initialTicket;
        loadTicket(initialTicket);
    }
});

/**
 * Main switch - Loads a ticket and its context
 */
async function loadTicket(ticketId = null) {
    const idInput = ticketId || dom.val('ticketSearch');
    const parsed = utils.parseId(idInput);
    
    if (!parsed) {
        alert('Please enter a valid numeric Ticket ID');
        return;
    }

    // Prepare State
    G.activeTicketId = parsed;
    G.loadedMessages = [];
    G.currentTicketPage = 1;
    G.isInitialLoad = true;
    G.isLoading = false;

    // UI Reset with smooth transition
    dom.html('messagesContent', '');
    dom.hide('emptyState');
    dom.show('sidebarSkeleton');
    dom.hide('contactCard');
    dom.hide('otherTicketsCard');

    // Update Browser State
    const url = new URL(window.location);
    url.searchParams.set('ticket', idInput);
    window.history.pushState({}, '', url);

    dom.html('ticketInfo', `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white mr-2">Ticket #${parsed}</span> Synchronizing...`);

    try {
        // Fetch Context
        const ctxRes = await fetch(`<?= URLROOT ?>/tickets/trengo/context/${parsed}`);
        const ctx = await ctxRes.json();

        if (ctx.success && ctx.data) {
            G.contactId = ctx.data.contact_id;
            renderContactInfo(ctx.data);
            await loadOtherInteractions(false);
        } else if (ctx.error) {
            // Handle context API errors
            let errorMessage = 'فشل في تحميل معلومات التذكرة';

            if (ctx.error.includes('ticket not found')) {
                errorMessage = 'التذكرة غير موجودة في Trengo';
            } else if (ctx.error.includes('Token expired') || ctx.error.includes('Trengo API is not available')) {
                errorMessage = 'التوكن منتهي الصلاحية - يرجى الاتصال بالمسؤول لتجديده';
                showTokenExpiryModal();
            } else {
                errorMessage = ctx.error;
            }

            showErrorMessage(errorMessage);
        }

        // Fetch Messages (all at once) and merge with older tickets
        await fetchMessagesWithHistory(parsed);

    } catch (e) {
        console.error('Workflow Error:', e);
        showErrorMessage('خطأ في الاتصال بالخادم');
    }
}

/**
 * Fetch messages with history - Load current ticket and merge older tickets
 */
async function fetchMessagesWithHistory(ticketId) {
    if (G.isLoading) return;
    G.isLoading = true;

    // Show loading indicator
    const container = dom.get('messagesContainer');
    const loadingIndicator = dom.get('loadingIndicator');
    
    if (container) {
        container.style.position = 'relative';
    }
    
    if (loadingIndicator) {
        loadingIndicator.classList.remove('hidden');
        dom.html('loadingIndicator', `
            <div class="flex flex-col items-center gap-4">
                <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-semibold text-gray-700">جاري تحميل جميع الرسائل والتذاكر القديمة...</p>
            </div>
        `);
    }
    
    dom.hide('emptyState');

    try {
        // Step 1: Load current ticket messages
        const currentResponse = await fetch(`<?= URLROOT ?>/tickets/trengo/messages/${ticketId}?all=true`);
        const currentJson = await currentResponse.json();

        if (!currentJson.success || !currentJson.data) {
            throw new Error(currentJson.error || 'Failed to load current ticket');
        }

        const allMessages = [];
        const currentMsgs = currentJson.data.messages || [];
        // Add current ticket messages with metadata
        allMessages.push(...currentMsgs.map(m => ({ 
            ...m, 
            _ticket_id: ticketId,
            _is_current_ticket: true
        })));

        // Extract contactId from messages if not already set
        if (!G.contactId && currentMsgs.length > 0) {
            const firstInbound = currentMsgs.find(m => m.type === 'INBOUND' && m.contact?.id);
            if (firstInbound) {
                G.contactId = firstInbound.contact.id;
                console.log('Extracted contactId from messages:', G.contactId);
            }
        }

        // Step 2: Load older tickets and merge their messages intelligently
        if (G.contactId) {
            try {
                // Load all pages of tickets
                let allOlderTickets = [];
                let page = 1;
                let hasMore = true;
                
                while (hasMore && page <= 10) { // Safety limit
                    const ticketsResponse = await fetch(`<?= URLROOT ?>/tickets/trengo/contact-tickets/${G.contactId}?page=${page}&limit=50`);
                    const ticketsJson = await ticketsResponse.json();

                    if (ticketsJson.success && ticketsJson.data) {
                        const tickets = ticketsJson.data.tickets || [];
                        allOlderTickets.push(...tickets);
                        
                        hasMore = ticketsJson.data.meta?.current_page < ticketsJson.data.meta?.last_page;
                        page++;
                    } else {
                        hasMore = false;
                    }
                }
                
                // Filter out current ticket and sort by created_at (oldest first)
                const ticketsToLoad = allOlderTickets
                    .filter(t => String(t.id) !== String(ticketId))
                    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                console.log(`Found ${ticketsToLoad.length} older tickets to merge`);

                // Update loading indicator
                if (loadingIndicator && ticketsToLoad.length > 0) {
                    dom.html('loadingIndicator', `
                        <div class="flex flex-col items-center gap-4">
                            <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">جاري دمج ${ticketsToLoad.length} تذكرة قديمة...</p>
                        </div>
                    `);
                }

                // Load messages from each older ticket
                for (let i = 0; i < ticketsToLoad.length; i++) {
                    const ticket = ticketsToLoad[i];
                    try {
                        const msgResponse = await fetch(`<?= URLROOT ?>/tickets/trengo/messages/${ticket.id}?all=true`);
                        const msgJson = await msgResponse.json();

                        if (msgJson.success && msgJson.data && msgJson.data.messages) {
                            const olderMsgs = msgJson.data.messages.map(m => ({ 
                                ...m, 
                                _ticket_id: ticket.id,
                                _ticket_status: ticket.status,
                                _ticket_created_at: ticket.created_at
                            }));
                            allMessages.push(...olderMsgs);
                            console.log(`✅ [${i + 1}/${ticketsToLoad.length}] Loaded ${olderMsgs.length} messages from ticket #${ticket.id}`);
                        }
                    } catch (e) {
                        console.warn(`Failed to load messages from ticket #${ticket.id}:`, e);
                    }
                }
            } catch (e) {
                console.warn('Failed to load older tickets:', e);
            }
        }

        if (allMessages.length === 0) {
            dom.show('emptyState');
            dom.html('messagesContent', '');
            return;
        }

        // Store all messages
        G.loadedMessages = allMessages;
        G.hasMoreInCurrentTicket = false;
        G.currentTicketPage = 1;

        // Render all messages at once
        renderFeed();

        // Hide loading indicator
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }

        // Scroll to bottom to show latest messages
        if (G.isInitialLoad) {
            setTimeout(() => {
                scrollToBottom();
                G.isInitialLoad = false;
            }, 100);
        }

        console.log(`✅ Loaded ${allMessages.length} total messages (current + history)`);
    } catch (e) {
        console.error('Fetch Error:', e);
        showErrorMessage('خطأ في تحميل الرسائل');
        dom.show('emptyState');
    } finally {
        G.isLoading = false;
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }
        updateMessageCount();
    }
}

/**
 * Message Fetching - Load all messages at once (for backward compatibility)
 */
async function fetchMessages(ticketId) {
    if (G.isLoading) return;
    G.isLoading = true;

    // Show loading indicator (centered overlay)
    const container = dom.get('messagesContainer');
    const loadingIndicator = dom.get('loadingIndicator');
    
    if (container) {
        container.style.position = 'relative';
    }
    
    if (loadingIndicator) {
        loadingIndicator.classList.remove('hidden');
    }
    
    // Hide empty state
    dom.hide('emptyState');

    try {
        // Load all messages at once
        const response = await fetch(`<?= URLROOT ?>/tickets/trengo/messages/${ticketId}?all=true`);
        const json = await response.json();

        if (json.success && json.data) {
            const msgs = json.data.messages || [];
            
            if (msgs.length === 0) {
                dom.show('emptyState');
                dom.html('messagesContent', '');
                return;
            }

            // Map all messages with ticket ID
            G.loadedMessages = msgs.map(m => ({ ...m, _ticket_id: ticketId }));
            G.hasMoreInCurrentTicket = false; // No pagination needed
            G.currentTicketPage = 1;

            // Render all messages at once
            renderFeed();

            // Hide loading indicator
            if (loadingIndicator) {
                loadingIndicator.classList.add('hidden');
            }

            // Scroll to bottom to show latest messages
            if (G.isInitialLoad) {
                setTimeout(() => {
                    scrollToBottom();
                    G.isInitialLoad = false;
                }, 100);
            }

            console.log(`✅ Loaded ${msgs.length} messages for ticket ${ticketId} (all at once)`);
        } else {
            // Handle API errors
            let errorMessage = 'فشل في تحميل الرسائل';

            if (json.error) {
                if (json.error.includes('ticket not found') || json.error.includes('no messages')) {
                    errorMessage = 'التذكرة غير موجودة أو لا تحتوي على رسائل';
                    dom.show('emptyState');
                } else if (json.error.includes('Token expired') || json.error.includes('Trengo API is not available')) {
                    errorMessage = 'التوكن منتهي الصلاحية - يرجى الاتصال بالمسؤول لتجديده';
                    showTokenExpiryModal();
                } else {
                    errorMessage = json.error;
                }
            }

            showErrorMessage(errorMessage);
        }
    } catch (e) {
        console.error('Fetch Error:', e);
        showErrorMessage('خطأ في الاتصال بالخادم');
        dom.show('emptyState');
    } finally {
        G.isLoading = false;
        const loadingIndicator = dom.get('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }
        updateMessageCount();
    }
}

/**
 * Cross-Ticket Merging Logic
 */
async function loadOlderTicketInStream() {
    if (G.isLoading) return;
    
    const currentIndex = G.ticketQueue.findIndex(id => String(id) === String(G.activeTicketId));
    const nextId = G.ticketQueue[currentIndex + 1];

    if (nextId) {
        console.log(`Merging history with older ticket #${nextId}`);
        G.activeTicketId = nextId;
        // Use fetchMessagesWithHistory to merge with even older tickets
        await fetchMessagesWithHistory(nextId);
    } else {
        G.hasMoreInCurrentTicket = false;
    }
}

/**
 * Rendering Logic
 */
function renderFeed() {
    const list = dom.get('messagesContent');
    if (!list) {
        console.error('messagesContent element not found');
        return;
    }

    console.log('Rendering feed with', G.loadedMessages.length, 'messages');

    // Sort messages by created_at (oldest first) - this is the natural conversation flow
    const sorted = [...G.loadedMessages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    let html = '';
    let lastDate = null;
    let lastTid = null;

    console.log('Sorted messages:', sorted.length);

    sorted.forEach(msg => {
        // Boundary check - show REFERENCE for different tickets (but not for current ticket)
        if (msg._ticket_id !== lastTid && lastTid !== null && !msg._is_current_ticket) {
            const ticketStatus = msg._ticket_status || 'UNKNOWN';
            const ticketDate = msg._ticket_created_at ? utils.formatDate(msg._ticket_created_at) : '';
            const statusColor = ticketStatus === 'CLOSED' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-green-100 text-green-700 border-green-200';
            
            html += `
                <div class="my-12 animate-fade-in">
                    <div class="relative">
                        <!-- Top divider line -->
                        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                        
                        <!-- Ticket reference card -->
                        <div class="flex items-center justify-center my-5">
                            <div class="px-5 py-3 bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border-2 border-blue-200/60 rounded-xl shadow-md backdrop-blur-sm cursor-pointer hover:bg-blue-100/80 hover:border-blue-300 transition-all duration-300 group transform hover:scale-105" onclick="switchTicket('${msg._ticket_id}')" title="انقر للانتقال إلى هذه التذكرة">
                                <div class="flex items-center gap-2.5">
                                    <i class="fas fa-history text-blue-600 text-sm group-hover:text-blue-700"></i>
                                    <span class="text-[12px] font-bold text-blue-800">تذكرة قديمة #${msg._ticket_id}</span>
                                    <span class="px-2 py-1 rounded-lg text-[9px] font-bold ${statusColor} border">${ticketStatus}</span>
                                </div>
                                ${ticketDate ? `<div class="text-[9px] text-gray-600 mt-1.5 text-center font-medium"><i class="fas fa-calendar-alt mr-1"></i>${ticketDate}</div>` : ''}
                            </div>
                        </div>
                        
                        <!-- Bottom divider line -->
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                    </div>
                </div>
            `;
            lastTid = msg._ticket_id;
        } else if (lastTid === null) {
            lastTid = msg._ticket_id;
        }

        // Date check
        const d = utils.formatDate(msg.created_at);
        if (d !== lastDate) {
            html += `<div class="flex justify-center my-6 sticky top-0 z-30"><span class="px-4 py-2 bg-white/95 shadow-lg border border-gray-100 rounded-full text-[11px] font-bold text-gray-600 backdrop-blur-sm border-opacity-50">${d}</span></div>`;
            lastDate = d;
        }

        html += renderBubble(msg);
    });

    list.innerHTML = html;
    console.log('Feed rendered successfully, HTML length:', html.length);

    // Hide empty state if we have messages
    if (sorted.length > 0) {
        dom.hide('emptyState');
    }

    // Force layout recalculation
    list.style.display = 'none';
    list.offsetHeight; // Trigger reflow
    list.style.display = '';

    // Update message count display
    updateMessageCount();
}

function renderBubble(msg) {
    const isIn = msg.type === 'INBOUND';
    const t = isIn ? THEME.INBOUND : THEME.OUTBOUND;
    
    // Get sender name - try multiple sources
    let name = 'Unknown';
    let senderRole = '';
    
    if (isIn) {
        name = msg.contact?.name || 
               msg.contact?.full_name || 
               msg.contact?.identifier || 
               'Customer';
    } else {
        // For outbound messages, try multiple sources for agent/support name
        // Trengo API usually has: msg.user or msg.agent
        const user = msg.user || msg.agent || msg.support || {};
        
        // Try different name fields
        name = user.full_name || 
               user.name || 
               (user.first_name ? `${user.first_name} ${user.last_name || ''}`.trim() : '') ||
               user.email?.split('@')[0] ||
               user.username ||
               'Agent';
        
        // Get role if available
        senderRole = user.role || 
                     user.role_name || 
                     user.type ||
                     (user.is_support ? 'Support' : '') ||
                     (user.is_agent ? 'Agent' : '') ||
                     '';
        
        // If name is still empty or 'Agent', try to get from message metadata
        if (!name || name === 'Agent') {
            // Sometimes the name is in a different structure
            name = msg.sender?.name || 
                   msg.sender?.full_name ||
                   msg.from?.name ||
                   'Support';
        }
    }
    
    let media = '';
    if (msg.body_type === 'IMAGE' && msg.file_url) {
        media = `<div class="mb-2 rounded-xl overflow-hidden cursor-zoom-in" onclick="openImageModal('${msg.file_url}', 'Media')"><img src="${msg.file_url}" class="max-w-full h-auto" style="max-height: 280px"></div>`;
    } else if (msg.body_type === 'VIDEO' && msg.file_url) {
        media = `<video controls class="rounded-xl mb-2 w-full max-h-[280px]"><source src="${msg.file_url}" type="video/mp4"></video>`;
    }

    const hasMsg = msg.message && !['Image', 'Video'].includes(msg.message);
    const body = hasMsg ? `<p class="whitespace-pre-wrap text-[13px] leading-relaxed">${utils.escape(msg.message)}</p>` : '';

    let atts = '';
    if (msg.attachments?.length > 0) {
        atts = `<div class="mt-2 pt-2 border-t border-white/10">${msg.attachments.map(a => `<a href="${a.url}" target="_blank" class="flex items-center gap-2 text-[10px] font-bold py-1 px-2 bg-black/5 rounded mb-1"><i class="fas fa-file-download"></i> ${utils.escape(a.name)}</a>`).join('')}</div>`;
    }

    // Get role/badge for outbound messages
    let roleBadge = '';
    if (!isIn && senderRole) {
        roleBadge = `<span class="text-[8px] px-1.5 py-0.5 rounded bg-white/20 text-white/80 font-semibold uppercase">${utils.escape(senderRole)}</span>`;
    } else if (!isIn && name !== 'Agent') {
        roleBadge = `<span class="text-[8px] px-1.5 py-0.5 rounded bg-white/20 text-white/80 font-semibold">Support</span>`;
    }

    // Clean name (remove extra spaces)
    const cleanName = name.trim() || (isIn ? 'Customer' : 'Support');
    
    return `
        <div class="flex flex-col ${t.align} mb-6 animate-fade-in">
            <div class="flex items-center gap-2 mb-1.5 px-1 ${isIn ? '' : 'flex-row-reverse'}">
                <div class="flex items-center gap-2 ${isIn ? '' : 'flex-row-reverse'}">
                    <div class="flex items-center gap-1.5">
                        ${!isIn ? '<i class="fas fa-user-headset text-[10px] text-blue-500"></i>' : ''}
                        <span class="text-[11px] font-bold ${isIn ? 'text-gray-700' : 'text-blue-700'}" title="${utils.escape(cleanName)}">${utils.escape(cleanName)}</span>
                    </div>
                    ${roleBadge}
                </div>
                <span class="text-[9px] text-gray-400">${utils.formatTime(msg.created_at)}</span>
            </div>
            <div class="max-w-[80%] md:max-w-[65%]">
                <div class="${t.bubble} rounded-2xl p-3 ${t.text} ${isIn ? 'rounded-tl-none' : 'rounded-tr-none'} shadow-sm hover:shadow-md transition-shadow">
                    ${media}${body}${atts}
                </div>
            </div>
        </div>`;
}

/**
 * SIDEBAR LOGIC
 */
function renderContactInfo(data) {
    dom.html('contactInitials', (data.name || '??').substring(0, 2).toUpperCase());
    dom.html('contactNameDisplay', data.name || 'Unidentified');
    dom.html('countryDisplay', data.country?.name || 'Unknown');
    dom.html('platformBadge', data.platform?.name || 'Channel');

    const details = [
        { icon: 'fa-phone', label: 'Primary Contact', val: data.phone || 'N/A' },
        { icon: 'fa-id-badge', label: 'Trengo UID', val: data.contact_id }
    ];

    dom.html('contactInfo', details.map(d => `<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl border border-transparent hover:border-blue-100"><div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-blue-500"><i class="fas ${d.icon} text-xs"></i></div><div><div class="text-[8px] font-black text-gray-300 uppercase">${d.label}</div><div class="text-xs font-bold text-gray-600">${d.val}</div></div></div>`).join(''));
    dom.hide('sidebarSkeleton');
    dom.get('contactCard').classList.remove('hidden', 'opacity-0', 'translate-y-4');
}

async function loadOtherInteractions(append = false) {
    if (!G.contactId) return;
    try {
        // Load all tickets (not just 12) for better history merging
        const res = await fetch(`<?= URLROOT ?>/tickets/trengo/contact-tickets/${G.contactId}?page=${G.otherTicketsPage}&limit=50`);
        const json = await res.json();
        if (json.success && json.data) {
            const list = json.data.tickets || [];
            if (!append) G.ticketQueue = list.map(t => t.id);
            else G.ticketQueue = [...G.ticketQueue, ...list.map(t => t.id)];
            
            G.hasMoreOtherTickets = json.data.meta?.current_page < json.data.meta?.last_page;
            const loadMoreBtn = dom.get('loadMoreTickets');
            if (loadMoreBtn) {
                loadMoreBtn.classList.toggle('hidden', !G.hasMoreOtherTickets);
            }
            const countDisplay = dom.get('otherTicketsCount');
            if (countDisplay) {
                dom.html('otherTicketsCount', json.data.meta?.total || 0);
            }
            
            await renderOtherList(list, append);
        }
    } catch (e) { 
        console.error('History Fail:', e); 
    }
}

async function renderOtherList(tickets, append) {
    const box = dom.get('otherTickets');
    if (!box) return;
    if (!append) box.innerHTML = '';

    const ids = tickets.map(t => t.id).join(',');
    let dbMap = {};
    try {
        const res = await fetch(`<?= URLROOT ?>/tickets/trengo/check-exists?ticket_numbers=${ids}`);
        const d = await res.json();
        if (d.success) dbMap = d.data;
    } catch(e) {}

    const html = tickets.map(t => {
        const dbId = dbMap[t.id];
        const stat = t.status === 'CLOSED' ? 'bg-gray-100 text-gray-400' : 'bg-green-100 text-green-600';
        return `
            <div class="px-6 py-4 hover:bg-blue-50/50 cursor-pointer group relative border-b border-gray-50" onclick="switchTicket('${t.id}')">
                <div class="flex justify-between items-center mb-1"><span class="text-xs font-black text-gray-800">#${t.id}</span><span class="px-1.5 py-0.5 rounded text-[8px] font-black ${stat}">${t.status}</span></div>
                <p class="text-[10px] text-gray-400 truncate mb-1 italic group-hover:text-blue-500">"${utils.escape(t.latest_received_message?.message || 'New Ticket')}"</p>
                <div class="flex justify-between text-[9px] text-gray-300 font-bold"><span>${utils.formatDate(t.created_at)}</span>${dbId ? `<span class="text-green-500"><i class="fas fa-database mr-1"></i>Saved</span>` : ''}</div>
                ${dbId ? `<div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity"><button onclick="event.stopPropagation(); window.open('<?= URLROOT ?>/tickets/view/${dbId}', '_blank')" class="w-7 h-7 bg-blue-600 text-white rounded-lg shadow-lg flex items-center justify-center"><i class="fas fa-external-link-alt text-[9px]"></i></button></div>` : ''}
            </div>`;
    }).join('');

    box.insertAdjacentHTML('beforeend', html);
    dom.get('otherTicketsCard').classList.remove('hidden', 'opacity-0', 'translate-y-4');
}

/**
 * ACTIONS
 */
function handleScroll() {
    // Scroll handling removed - all messages load at once now
    // This prevents unwanted scroll jumps
}

function handleManualLoadOlder() {
    // Only load older tickets in stream, not pagination
    loadOlderTicketInStream();
}

function scrollToBottom() { const c = dom.get('messagesContainer'); if(c) c.scrollTop = c.scrollHeight; }

function updateMessageCount() {
    const count = G.loadedMessages.length;
    const display = dom.get('msgCountDisplay');
    if (display) {
        display.textContent = `${count} messages loaded`;
    }
}

function switchTicket(id) {
    const s = dom.get('ticketSearch');
    if(s) {
        s.value = id;
        // Smooth scroll to top before loading
        const container = dom.get('messagesContainer');
        if (container) {
            container.scrollTo({ top: 0, behavior: 'smooth' });
        }
        setTimeout(() => loadTicket(id), 200);
    }
}

function loadMoreOtherTickets() { G.otherTicketsPage++; loadOtherInteractions(true); }

function openImageModal(url, cap) {
    const i = dom.get('modalImage'); if(i) i.src = url;
    const l = dom.get('modalDownloadLink'); if(l) l.href = url;
    dom.html('modalCaption', cap);
    const m = dom.get('imageModal'); if(m) { m.classList.remove('pointer-events-none', 'opacity-0'); document.body.style.overflow = 'hidden'; }
}

function closeImageModal() {
    const m = dom.get('imageModal'); if(m) { m.classList.add('pointer-events-none', 'opacity-0'); document.body.style.overflow = ''; }
}

/**
 * ASSIGNMENT LOGIC
 */
async function openAssignModal() {
    if (!G.activeTicketId) {
        alert('Please load a ticket first');
        return;
    }
    
    dom.show('assignModal');
    dom.get('assignModal').classList.remove('pointer-events-none', 'opacity-0');
    dom.get('assignModalContent').classList.remove('scale-95');
    dom.get('assignModalContent').classList.add('scale-100');
    
    if (G.trengoUsers.length === 0) {
        await loadTrengoUsers();
    } else {
        renderUsersList();
    }
}

function closeAssignModal() {
    const m = dom.get('assignModal');
    if(m) {
        m.classList.add('pointer-events-none', 'opacity-0');
        dom.get('assignModalContent').classList.add('scale-95');
        dom.get('assignModalContent').classList.remove('scale-100');
    }
}

async function loadTrengoUsers(page = 1) {
    if (G.isLoadingUsers && page === 1) return;
    G.isLoadingUsers = true;
    
    try {
        const res = await fetch(`<?= URLROOT ?>/tickets/trengo/users?page=${page}`);
        const json = await res.json();
        
        if (json.success && json.data) {
            const newUsers = json.data.data || [];
            G.trengoUsers = [...G.trengoUsers, ...newUsers];
            
            const meta = json.data.meta;
            if (meta && meta.current_page < meta.last_page) {
                await loadTrengoUsers(meta.current_page + 1);
            } else {
                renderUsersList();
            }
        }
    } catch (e) {
        console.error('Failed to load users:', e);
    } finally {
        if (page === 1) G.isLoadingUsers = false;
    }
}

function renderUsersList(filtered = null) {
    const list = dom.get('usersList');
    if (!list) return;
    
    const users = (filtered || G.trengoUsers).filter(u => u.status === 'ACTIVE');
    
    if (users.length === 0) {
        list.innerHTML = '<div class="py-10 text-center text-gray-400 text-xs">No active agents found</div>';
        return;
    }
    
    list.innerHTML = users.map(user => `
        <div onclick="performAssign(${user.id}, '${utils.escape(user.name)}')" class="flex items-center justify-between p-3 rounded-2xl hover:bg-blue-50 cursor-pointer border border-transparent hover:border-blue-100 transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden border border-white shadow-sm">
                    ${user.profile_image ? `<img src="${user.profile_image}" class="w-full h-full object-cover">` : `<span class="font-bold text-gray-400 text-[10px]">${user.abbr}</span>`}
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-800">${utils.escape(user.name)}</div>
                    <div class="text-[9px] text-gray-400 font-medium">${utils.escape(user.email)}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full ${user.is_online ? 'bg-green-500' : 'bg-gray-300'}"></span>
                <i class="fas fa-chevron-right text-gray-200 group-hover:text-blue-400 transition-colors text-[10px]"></i>
            </div>
        </div>
    `).join('');
}

function filterUsers() {
    const term = dom.val('userSearch').toLowerCase();
    if (!term) {
        renderUsersList();
        return;
    }
    
    const filtered = G.trengoUsers.filter(u => 
        (u.name && u.name.toLowerCase().includes(term)) || 
        (u.email && u.email.toLowerCase().includes(term))
    );
    renderUsersList(filtered);
}

async function performAssign(userId, userName) {
    if (!confirm(`Assign ticket #${G.activeTicketId} to ${userName}?`)) return;
    
    dom.show('assignLoadingIndicator');
    
    try {
        const res = await fetch(`<?= URLROOT ?>/tickets/trengo/assign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ticket_number: G.activeTicketId,
                user_id: userId,
            })
        });
        
        const json = await res.json();
        
        if (json.success) {
            // Success micro-feedback
            const btn = document.querySelector(`[onclick="performAssign(${userId}, '${userName}')"]`);
            if (btn) btn.innerHTML = '<div class="w-full py-2 text-center text-green-500 font-bold"><i class="fas fa-check mr-2"></i>Assigned!</div>';
            
            setTimeout(() => {
                closeAssignModal();
                loadTicket(); // Refresh
            }, 1000);
        } else {
            alert('Failed: ' + (json.error || 'Unknown error'));
        }
    } catch (e) {
        console.error('Assign failed:', e);
        alert('Assign failed. Check console for details.');
    } finally {
        dom.hide('assignLoadingIndicator');
    }
}

const searchInput = dom.get('ticketSearch');
if (searchInput) searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') loadTicket(); });

/**
 * Close token expiry modal
 */
function closeTokenExpiryModal() {
    const modal = document.getElementById('tokenExpiryModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Show error message in UI
 */
function showErrorMessage(message) {
    // Create error notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg z-50 max-w-md';
    errorDiv.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-800 font-medium">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-400 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(errorDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

/**
 * Show token expiry modal
 */
function showTokenExpiryModal() {
    const modal = document.getElementById('tokenExpiryModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = '';
    }
}
</script>


<style>
/* Custom Layout & Animations */
.message-image-anim {
    animation: zoom-in 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes zoom-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

#messagesContainer {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

#messagesContainer::-webkit-scrollbar {
    width: 6px;
}

#messagesContainer::-webkit-scrollbar-track {
    background: transparent;
    margin: 4px;
}

#messagesContainer::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s ease;
}

#messagesContainer::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Glassmorphism for bubbles if needed */
.msg-glass {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Smooth animations */
.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading state for container */
#messagesContainer.loading {
    opacity: 0.7;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
