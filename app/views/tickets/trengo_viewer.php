<?php include_once __DIR__ . '/../includes/header.php'; ?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="container mx-auto px-4 py-8 max-w-7xl font-['Inter']">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

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
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 flex-1 flex flex-col overflow-hidden">
                
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
                    class="flex-1 overflow-y-auto px-6 py-6 scroll-smooth bg-[#f8fafc]/50"
                    onscroll="handleScroll()">
                    
                    <!-- Internal Container for grouping -->
                    <div id="messagesContent" class="space-y-6 flex flex-col">
                        <!-- Initial state / Empty state -->
                        <div id="emptyState" class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center mb-6">
                                <i class="fas fa-comment-medical text-3xl text-blue-400"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">No ticket loaded yet</h4>
                            <p class="text-gray-500 mt-2 max-w-xs mx-auto">Enter a valid Trengo ticket ID in the search box above to inspect the conversation details.</p>
                        </div>
                    </div>

                    <!-- Manual Load Button (Alternative to scroll) -->
                    <div id="manualLoadOlder" class="hidden py-4 text-center order-first border-b border-gray-50 mb-4">
                        <button onclick="handleManualLoadOlder()" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 bg-blue-50 px-4 py-2 rounded-full transition-all">
                            <i class="fas fa-history mr-2"></i>Load Older History
                        </button>
                    </div>

                    <!-- Loading older messages indicator (appears at TOP) -->
                    <div id="loadingIndicatorTop" class="hidden py-4 text-center order-first">
                        <div class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold shadow-sm ring-1 ring-blue-100">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Fetching older history...
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
    hasMoreOtherTickets: false
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

    // UI Reset
    dom.html('messagesContent', '');
    dom.show('loadingIndicatorTop');
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
        }

        // Fetch Messages
        await fetchMessages(parsed, 1);
        
    } catch (e) {
        console.error('Workflow Error:', e);
    }
}

/**
 * Message Fetching
 */
async function fetchMessages(ticketId, page = 1) {
    if (G.isLoading) return;
    G.isLoading = true;

    dom.show('loadingIndicatorTop');
    dom.hide('manualLoadOlder');
    
    const container = dom.get('messagesContainer');
    const hBefore = container ? container.scrollHeight : 0;

    try {
        const response = await fetch(`<?= URLROOT ?>/tickets/trengo/messages/${ticketId}?page=${page}`);
        const json = await response.json();

        if (json.success && json.data) {
            const msgs = json.data.messages || [];
            const mapped = msgs.map(m => ({ ...m, _ticket_id: ticketId }));
            
            G.loadedMessages = [...G.loadedMessages, ...mapped];
            G.hasMoreInCurrentTicket = json.data.meta && json.data.meta.current_page < json.data.meta.last_page;
            G.currentTicketPage = page;
            
            renderFeed();

            if (G.isInitialLoad) {
                scrollToBottom();
                G.isInitialLoad = false;
            } else if (container) {
                container.scrollTop = (container.scrollHeight - hBefore);
            }
        }
    } catch (e) {
        console.error('Fetch Error:', e);
    } finally {
        G.isLoading = false;
        dom.hide('loadingIndicatorTop');
        
        const hasMoreAny = G.hasMoreInCurrentTicket || G.ticketQueue.findIndex(id => String(id) === String(G.activeTicketId)) < G.ticketQueue.length - 1;
        if (hasMoreAny) dom.show('manualLoadOlder');
        
        dom.html('msgCountDisplay', `${G.loadedMessages.length} nodes active`);
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
        G.currentTicketPage = 1;
        await fetchMessages(nextId, 1);
    } else {
        G.hasMoreInCurrentTicket = false;
        dom.hide('manualLoadOlder');
    }
}

/**
 * Rendering Logic
 */
function renderFeed() {
    const list = dom.get('messagesContent');
    if (!list) return;

    const sorted = [...G.loadedMessages].reverse();
    let html = '';
    let lastDate = null;
    let lastTid = null;

    sorted.forEach(msg => {
        // Boundary check
        if (msg._ticket_id !== lastTid) {
            html += `<div class="flex items-center gap-4 my-10"><div class="h-px bg-gray-100 flex-1"></div><div class="px-3 py-1 bg-blue-50 border border-blue-100 rounded-full text-[9px] font-bold text-blue-600">REFERENCE #${msg._ticket_id}</div><div class="h-px bg-gray-100 flex-1"></div></div>`;
            lastTid = msg._ticket_id;
        }

        // Date check
        const d = utils.formatDate(msg.created_at);
        if (d !== lastDate) {
            html += `<div class="flex justify-center my-6 sticky top-0 z-30"><span class="px-4 py-1 bg-white/90 shadow-sm border border-gray-50 rounded-full text-[10px] font-bold text-gray-400">${d}</span></div>`;
            lastDate = d;
        }

        html += renderBubble(msg);
    });

    list.innerHTML = html;
}

function renderBubble(msg) {
    const isIn = msg.type === 'INBOUND';
    const t = isIn ? THEME.INBOUND : THEME.OUTBOUND;
    const name = isIn ? (msg.contact?.name || 'Customer') : (msg.agent?.name || 'Agent');
    
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

    return `
        <div class="flex flex-col ${t.align} mb-6">
            <div class="flex items-center gap-2 mb-1 px-1 ${isIn ? '' : 'flex-row-reverse'}"><span class="text-[9px] font-black uppercase text-gray-300">${utils.escape(name)}</span><span class="text-[8px] text-gray-200">${utils.formatTime(msg.created_at)}</span></div>
            <div class="max-w-[80%] md:max-w-[65%]"><div class="${t.bubble} rounded-2xl p-3 ${t.text} ${isIn ? 'rounded-tl-none' : 'rounded-tr-none'}">${media}${body}${atts}</div></div>
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
        const res = await fetch(`<?= URLROOT ?>/tickets/trengo/contact-tickets/${G.contactId}?page=${G.otherTicketsPage}&limit=12`);
        const json = await res.json();
        if (json.success && json.data) {
            const list = json.data.tickets || [];
            if (!append) G.ticketQueue = list.map(t => t.id);
            else G.ticketQueue = [...G.ticketQueue, ...list.map(t => t.id)];
            
            G.hasMoreOtherTickets = json.data.meta?.current_page < json.data.meta?.last_page;
            dom.get('loadMoreTickets').classList.toggle('hidden', !G.hasMoreOtherTickets);
            dom.html('otherTicketsCount', json.data.meta?.total || 0);
            
            await renderOtherList(list, append);
        }
    } catch (e) { console.error('History Fail:', e); }
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
    const c = dom.get('messagesContainer');
    if (c && c.scrollTop < 50 && !G.isLoading) {
        if (G.hasMoreInCurrentTicket) fetchMessages(G.activeTicketId, G.currentTicketPage + 1);
        else loadOlderTicketInStream();
    }
}

function handleManualLoadOlder() {
    if (G.hasMoreInCurrentTicket) fetchMessages(G.activeTicketId, G.currentTicketPage + 1);
    else loadOlderTicketInStream();
}

function scrollToBottom() { const c = dom.get('messagesContainer'); if(c) c.scrollTop = c.scrollHeight; }

function switchTicket(id) {
    const s = dom.get('ticketSearch');
    if(s) { s.value = id; loadTicket(id); }
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

const searchInput = dom.get('ticketSearch');
if (searchInput) searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') loadTicket(); });
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
    scrollbar-color: #e2e8f0 transparent;
}

#messagesContainer::-webkit-scrollbar {
    width: 5px;
}

#messagesContainer::-webkit-scrollbar-track {
    background: transparent;
}

#messagesContainer::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 20px;
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
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
