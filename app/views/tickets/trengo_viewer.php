<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mx-auto px-4 py-6">
    <!-- Flash Messages -->
    <?php include_once __DIR__ . '/../includes/flash_messages.php'; ?>

    <!-- Header -->
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-comments text-blue-500 mr-2"></i>
            Trengo Ticket Viewer
        </h1>
        <p class="text-gray-600 text-sm mt-1" id="ticketInfo">
            <?php if (!empty($data['ticket_number'])): ?>
                Viewing Ticket #<?= htmlspecialchars($data['ticket_number']) ?>
            <?php else: ?>
                Enter a ticket number to view conversation
            <?php endif; ?>
        </p>
    </div>

    <!-- Search Box -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex gap-3">
            <div class="flex-1">
                <label for="ticketSearch" class="block text-xs font-medium text-gray-600 mb-1">
                    <i class="fas fa-ticket-alt mr-1"></i>Ticket Number
                </label>
                <input 
                    type="text" 
                    id="ticketSearch" 
                    placeholder="Paste or enter ticket number (e.g., 867529236)"
                    value="<?= htmlspecialchars($data['ticket_number'] ?? '') ?>"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div class="flex items-end">
                <button 
                    onclick="loadTicket()" 
                    class="bg-blue-500 text-white px-5 py-2 text-sm rounded-md hover:bg-blue-600 transition-colors shadow-sm">
                    <i class="fas fa-search mr-1"></i>Load
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Messages Area (8/12 width on large screens) -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-lg shadow-sm" style="height: calc(100vh - 280px); min-height: 500px; display: flex; flex-direction: column;">
                <!-- Messages Header -->
                <div class="border-b border-gray-200 px-4 py-3 bg-gradient-to-r from-blue-50 to-white">
                    <h3 class="font-semibold text-gray-700 text-sm">
                        <i class="fas fa-comment-dots text-blue-500 mr-2"></i>Conversation
                    </h3>
                </div>

                <!-- Loading Indicator (Top) -->
                <div id="loadingIndicatorTop" class="hidden p-3 text-center border-b border-gray-200 bg-blue-50">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                        <span class="ml-2 text-gray-600 text-xs">Loading older messages...</span>
                    </div>
                </div>

                <!-- Messages Container (scrollable) -->
                <div 
                    id="messagesContainer" 
                    class="flex-1 overflow-y-auto p-4"
                    onscroll="handleScroll()">
                    <div id="messagesContent" class="space-y-2">
                        <!-- Initial state -->
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-inbox fa-3x mb-4 text-gray-300"></i>
                            <p class="text-sm">Enter a ticket number and click "Load" to view messages</p>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator (Bottom) -->
                <div id="loadingIndicatorBottom" class="hidden p-3 text-center border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                        <span class="ml-2 text-gray-600 text-xs">Loading messages...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (4/12 width on large screens) -->
        <div class="lg:col-span-4">
            <!-- Contact Info Card -->
            <div id="contactCard" class="bg-white rounded-lg shadow-sm p-4 mb-4 hidden">
                <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b text-sm">
                    <i class="fas fa-user text-blue-500 mr-2"></i>Contact Info
                </h3>
                <div id="contactInfo" class="space-y-2 text-xs">
                    <!-- Will be populated dynamically -->
                </div>
            </div>

            <!-- Other Tickets Card -->
            <div id="otherTicketsCard" class="bg-white rounded-lg shadow-sm p-4 hidden" style="max-height: calc(100vh - 400px); overflow-y: auto;">
                <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b text-sm sticky top-0 bg-white">
                    <i class="fas fa-ticket-alt text-blue-500 mr-2"></i>Other Tickets
                </h3>
                <div id="otherTickets" class="space-y-2">
                    <!-- Will be populated dynamically -->
                </div>
                <button 
                    id="loadMoreTickets" 
                    class="w-full mt-3 text-xs text-blue-600 hover:text-blue-800 py-2 border border-blue-200 rounded-md hover:bg-blue-50 transition-colors hidden"
                    onclick="loadMoreOtherTickets()">
                    <i class="fas fa-chevron-down mr-1"></i>Load More
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global state
let currentTicketNumber = '<?= htmlspecialchars($data['ticket_number'] ?? '') ?>';
let currentPage = 1;
let hasMoreMessages = false;
let isLoading = false;
let contactId = null;
let otherTicketsPage = 1;
let hasMoreOtherTickets = false;
let allMessages = []; // Store all loaded messages

// Load ticket when page loads if ticket number is provided
document.addEventListener('DOMContentLoaded', function() {
    if (currentTicketNumber) {
        loadTicket();
    }
});

function loadTicket() {
    const ticketNumber = document.getElementById('ticketSearch').value.trim();
    
    if (!ticketNumber) {
        alert('Please enter a ticket number');
        return;
    }
    
    // Reset state
    currentTicketNumber = ticketNumber;
    currentPage = 1;
    allMessages = [];
    contactId = null;
    otherTicketsPage = 1;
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('ticket', ticketNumber);
    window.history.pushState({}, '', url);
    
    // Update ticket info
    document.getElementById('ticketInfo').textContent = `Viewing Ticket #${ticketNumber}`;
    
    // Load context and messages
    loadTicketContext();
    loadMessages(1);
}

async function loadTicketContext() {
    try {
        const response = await fetch(`<?= URLROOT ?>/tickets/trengo/context/${currentTicketNumber}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            contactId = data.data.contact_id;
            displayContactInfo(data.data);
            loadOtherTickets();
        }
    } catch (error) {
        console.error('Error loading ticket context:', error);
    }
}

async function loadMessages(page = 1) {
    if (isLoading) return;
    
    isLoading = true;
    const isLoadingOlder = page > 1;
    showLoading(true, isLoadingOlder);
    
    // Save scroll metrics before loading older messages
    const container = document.getElementById('messagesContainer');
    const scrollHeightBeforeLoad = container.scrollHeight;
    const scrollTopBeforeLoad = container.scrollTop;
    
    try {
        const response = await fetch(`<?= URLROOT ?>/tickets/trengo/messages/${currentTicketNumber}?page=${page}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const newMessages = data.data.messages || [];

            if (page === 1) {
                // First load - replace all messages
                allMessages = newMessages;
            } else {
                // Loading older messages - add to end (since we display newest first)
                allMessages = [...allMessages, ...newMessages];
            }
            
            // Check if there are more pages
            const meta = data.data.meta;
            hasMoreMessages = meta && meta.current_page < (meta.last_page || Infinity);
            currentPage = page;
            
            // Render all messages
            renderMessages();
            
            // Handle scroll position WITHOUT any animation or delay
            if (page === 1) {
                // First load - scroll to top immediately (newest messages are at top)
                container.scrollTop = 0;
            } else {
                // Loading older messages - maintain scroll position (no change needed since we add to bottom)
                // The scroll position should remain the same since we're adding content to the bottom
                container.scrollTop = scrollTopBeforeLoad;
            }
        } else {
            if (page === 1) {
                document.getElementById('messagesContent').innerHTML = `
                    <div class="text-center text-red-500 py-12">
                        <i class="fas fa-exclamation-triangle fa-3x mb-4"></i>
                        <p class="text-sm">Failed to load messages</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        if (page === 1) {
            document.getElementById('messagesContent').innerHTML = `
                <div class="text-center text-red-500 py-12">
                    <i class="fas fa-times-circle fa-3x mb-4"></i>
                    <p class="text-sm">Error: ${error.message}</p>
                </div>
            `;
        }
    } finally {
        isLoading = false;
        showLoading(false, isLoadingOlder);
    }
}

function renderMessages() {
    const container = document.getElementById('messagesContent');

    if (allMessages.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-12">
                <i class="fas fa-inbox fa-3x mb-4 text-gray-300"></i>
                <p class="text-sm">No messages found</p>
            </div>
        `;
        return;
    }

    const totalMessages = allMessages.length;

    // Render messages (newest first at top, oldest at bottom)
    // Add data-message-id to each message for scroll positioning
    container.innerHTML = allMessages.slice().reverse().map((msg, idx) => {
        const messageNumber = totalMessages - idx; // Reverse numbering: newest = 1, oldest = total
        const messageHtml = renderMessage(msg, messageNumber, totalMessages);
        // Wrap in div with data attribute
        return `<div data-message-id="${msg.id}" data-index="${idx}">${messageHtml}</div>`;
    }).join('');
}

function renderMessage(msg, messageNumber, totalMessages) {
    const isInbound = msg.type === 'INBOUND';
    const bgColor = isInbound ? 'bg-blue-100 border-blue-300' : 'bg-white border-gray-200';
    const alignment = isInbound ? 'ml-auto' : 'mr-auto';
    const icon = isInbound ? 'fa-user-circle' : 'fa-headset';
    const iconColor = isInbound ? 'text-blue-600' : 'text-green-600';
    
    const senderName = isInbound 
        ? (msg.contact?.name || msg.contact?.display_name || 'Customer')
        : (msg.agent?.name || 'Agent');
    
    const time = new Date(msg.created_at).toLocaleString('en-GB', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Render media based on body_type
    let mediaContent = '';
    if (msg.body_type === 'IMAGE' && msg.file_url) {
        mediaContent = renderImage(msg.file_url, msg.file_name);
    } else if (msg.body_type === 'VIDEO' && msg.file_url) {
        mediaContent = renderVideo(msg.file_url);
    } else if (msg.body_type === 'BUTTONS' && msg.meta) {
        mediaContent = renderButtons(msg.meta);
    }
    
    // Show message text only if it's not just "Image" or "Video"
    const shouldShowText = msg.message && 
                          msg.message !== 'Image' && 
                          msg.message !== 'Video' &&
                          msg.message !== '*Message type unknown*';
    
    return `
        <div class="flex ${isInbound ? 'justify-end' : 'justify-start'} mb-2">
            <div class="max-w-[75%]">
                <div class="flex items-center gap-2 mb-1 ${isInbound ? 'flex-row-reverse' : ''}">
                    <i class="fas ${icon} ${iconColor}"></i>
                    <span class="font-medium text-xs text-gray-700">${escapeHtml(senderName)}</span>
                    <span class="text-xs text-gray-400">${time}</span>
                </div>
                <div class="${bgColor} border rounded-lg ${shouldShowText || mediaContent ? 'px-3 py-2' : 'p-1'} shadow-sm">
                    ${mediaContent}
                    ${shouldShowText ? `<div class="text-sm text-gray-800 whitespace-pre-wrap ${mediaContent ? 'mt-2' : ''}">${escapeHtml(msg.message)}</div>` : ''}
                    ${msg.attachments && msg.attachments.length > 0 ? renderAttachments(msg.attachments) : ''}
                </div>
            </div>
        </div>
    `;
}

function renderImage(imageUrl, fileName) {
    return `
        <div class="rounded-lg overflow-hidden bg-gray-100">
            <a href="${imageUrl}" target="_blank" class="block" onclick="openImageModal(event, '${imageUrl}')">
                <img 
                    src="${imageUrl}" 
                    alt="${escapeHtml(fileName || 'Image')}"
                    class="max-w-full h-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity message-image"
                    style="max-height: 300px; object-fit: contain; display: block; margin: 0 auto;"
                    loading="lazy"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3EImage not available%3C/text%3E%3C/svg%3E'"
                >
            </a>
        </div>
    `;
}

function renderVideo(videoUrl) {
    return `
        <div class="rounded-lg overflow-hidden">
            <video 
                controls 
                class="max-w-full h-auto rounded-lg"
                style="max-height: 400px;"
                preload="metadata">
                <source src="${videoUrl}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    `;
}

function renderButtons(meta) {
    if (!meta || !meta.buttons) return '';
    
    const guidingText = meta.button_guiding_text || '';
    const buttons = meta.buttons || [];
    
    return `
        <div class="space-y-2">
            ${guidingText ? `<p class="text-sm font-medium text-gray-700">${escapeHtml(guidingText)}</p>` : ''}
            <div class="flex flex-wrap gap-2">
                ${buttons.map((btn, idx) => `
                    <div class="px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-gray-700">
                        ${idx + 1}. ${escapeHtml(btn.text || '')}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function renderAttachments(attachments) {
    if (!attachments || attachments.length === 0) return '';
    
    return `
        <div class="mt-2 space-y-1">
            ${attachments.map(att => `
                <div class="flex items-center gap-2 text-xs bg-gray-100 rounded px-2 py-1">
                    <i class="fas fa-paperclip text-gray-500"></i>
                    <a href="${att.url || '#'}" target="_blank" class="hover:text-blue-600 hover:underline">${escapeHtml(att.name || 'Attachment')}</a>
                </div>
            `).join('')}
        </div>
    `;
}

function displayContactInfo(contextData) {
    const contactCard = document.getElementById('contactCard');
    const contactInfo = document.getElementById('contactInfo');
    
    const phone = contextData.phone || 'N/A';
    const platform = contextData.platform ? contextData.platform.name : 'N/A';
    const country = contextData.country ? contextData.country.name : 'N/A';
    
    contactInfo.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-phone text-gray-400"></i>
            <span class="text-gray-700">${escapeHtml(phone)}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-desktop text-gray-400"></i>
            <span class="text-gray-700">${escapeHtml(platform)}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-globe text-gray-400"></i>
            <span class="text-gray-700">${escapeHtml(country)}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-id-badge text-gray-400"></i>
            <span class="text-gray-700 text-xs">ID: ${contextData.contact_id}</span>
        </div>
    `;
    
    contactCard.classList.remove('hidden');
}

async function loadOtherTickets(append = false) {
    if (!contactId) return;
    
    try {
        const response = await fetch(`<?= URLROOT ?>/tickets/trengo/contact-tickets/${contactId}?page=${otherTicketsPage}&limit=10`);
        const data = await response.json();
        
        if (data.success && data.data) {
            await displayOtherTickets(data.data.tickets, !append);
            
            const meta = data.data.meta;
            hasMoreOtherTickets = meta && meta.current_page < (meta.last_page || Infinity);
            
            document.getElementById('loadMoreTickets').classList.toggle('hidden', !hasMoreOtherTickets);
        }
    } catch (error) {
        console.error('Error loading other tickets:', error);
    }
}

async function displayOtherTickets(tickets, clearFirst = true) {
    const container = document.getElementById('otherTickets');
    const card = document.getElementById('otherTicketsCard');
    
    if (clearFirst) {
        container.innerHTML = '';
    }
    
    if (tickets.length === 0 && clearFirst) {
        container.innerHTML = '<p class="text-xs text-gray-500 text-center py-3">No other tickets found</p>';
        card.classList.remove('hidden');
        return;
    }
    
    // Check which tickets exist in our database
    const ticketNumbers = tickets.map(t => t.id).join(',');
    let existingTickets = {};
    
    try {
        const checkResponse = await fetch(`<?= URLROOT ?>/tickets/trengo/check-exists?ticket_numbers=${ticketNumbers}`);
        const checkData = await checkResponse.json();
        if (checkData.success) {
            existingTickets = checkData.data;
            console.log('Existing tickets mapping:', existingTickets);
        }
    } catch (error) {
        console.error('Error checking ticket existence:', error);
    }
    
    const ticketsHtml = tickets.map(ticket => {
        const status = ticket.status || 'UNKNOWN';
        const statusColor = status === 'OPEN' ? 'bg-green-100 text-green-800' : 
                           status === 'CLOSED' ? 'bg-gray-100 text-gray-600' : 'bg-yellow-100 text-yellow-800';
        
        const latestMsg = ticket.latest_received_message?.message || 'No messages';
        const truncatedMsg = latestMsg.length > 35 ? latestMsg.substring(0, 35) + '...' : latestMsg;
        
        const createdAt = new Date(ticket.created_at).toLocaleDateString('en-GB', {
            month: 'short',
            day: 'numeric'
        });
        
        // existingTickets is a mapping: trengoTicketNumber => databaseTicketId
        const dbTicketId = existingTickets[ticket.id];
        
        // Debug log
        if (dbTicketId) {
            console.log(`Ticket #${ticket.id} exists in DB with ID: ${dbTicketId}`);
        }
        
        return `
            <div class="border ${dbTicketId ? 'border-green-300 bg-green-50' : 'border-gray-200'} rounded-lg p-2 transition-all hover:shadow-md"
                 style="position: relative;">
                <!-- Main clickable area - loads in Trengo viewer -->
                <div class="cursor-pointer" onclick="loadOtherTicket('${ticket.id}')">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-mono text-gray-700">#${ticket.id}</span>
                            ${dbTicketId ? `<span class="text-xs text-green-600">(DB: ${dbTicketId})</span>` : ''}
                        </div>
                        <span class="text-xs px-1.5 py-0.5 rounded-full ${statusColor}">${status}</span>
                    </div>
                    <p class="text-xs text-gray-700 mb-1 line-clamp-2">${escapeHtml(truncatedMsg)}</p>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span><i class="far fa-calendar mr-1"></i>${createdAt}</span>
                        <span><i class="fas fa-comment mr-1"></i>${ticket.messages_count || 0}</span>
                    </div>
                </div>
                
                <!-- Action buttons -->
                <div class="mt-2 pt-2 border-t ${dbTicketId ? 'border-green-300' : 'border-gray-200'} flex gap-1.5">
                    <button 
                        onclick="event.stopPropagation(); loadOtherTicket('${ticket.id}')"
                        class="flex-1 text-xs px-2 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors shadow-sm"
                        title="View messages in Trengo">
                        <i class="fas fa-comments mr-1"></i>Trengo
                    </button>
                    ${dbTicketId ? `
                        <button 
                            onclick="event.stopPropagation(); window.location.href='<?= URLROOT ?>/tickets/view/${dbTicketId}'"
                            class="flex-1 text-xs px-2 py-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition-colors shadow-sm"
                            title="Open saved ticket in database (ID: ${dbTicketId})">
                            <i class="fas fa-database mr-1"></i>View
                        </button>
                    ` : `
                        <div 
                            class="flex-1 text-xs px-2 py-1.5 bg-gray-100 text-gray-400 rounded text-center border border-dashed border-gray-300"
                            title="This ticket is not saved in database">
                            <i class="fas fa-ban mr-1"></i>Not Saved
                        </div>
                    `}
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML += ticketsHtml;
    card.classList.remove('hidden');
}

function loadOtherTicket(ticketId) {
    document.getElementById('ticketSearch').value = ticketId;
    loadTicket();
}

function loadMoreOtherTickets() {
    otherTicketsPage++;
    loadOtherTickets(true); // append = true
}

function handleScroll() {
    const container = document.getElementById('messagesContainer');

    // When scrolled to bottom, load more (older) messages
    const isNearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
    if (isNearBottom && hasMoreMessages && !isLoading) {
        const nextPage = currentPage + 1;
        loadMessages(nextPage);
    }
}

function showLoading(show, isTop = false) {
    const topIndicator = document.getElementById('loadingIndicatorTop');
    const bottomIndicator = document.getElementById('loadingIndicatorBottom');
    
    if (isTop) {
        // Show/hide top indicator
        if (show) {
            topIndicator.classList.remove('hidden');
            bottomIndicator.classList.add('hidden');
        } else {
            topIndicator.classList.add('hidden');
        }
    } else {
        // Show/hide bottom indicator
        if (show) {
            bottomIndicator.classList.remove('hidden');
            topIndicator.classList.add('hidden');
        } else {
            bottomIndicator.classList.add('hidden');
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Keyboard shortcuts
const ticketInput = document.getElementById('ticketSearch');

// Enter to load
ticketInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        loadTicket();
    }
});

// Auto-load on paste
ticketInput.addEventListener('paste', function(e) {
    setTimeout(() => {
        const pastedValue = ticketInput.value.trim();
        if (pastedValue && /^\d+$/.test(pastedValue)) {
            loadTicket();
        }
    }, 100);
});

// Image modal functionality
function openImageModal(event, imageUrl) {
    event.preventDefault();
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4';
    modal.onclick = function() { document.body.removeChild(modal); };
    
    modal.innerHTML = `
        <div class="relative max-w-6xl max-h-full" onclick="event.stopPropagation()">
            <button 
                class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors z-10"
                onclick="document.body.removeChild(this.closest('.fixed'))">
                <i class="fas fa-times text-gray-700"></i>
            </button>
            <img 
                src="${imageUrl}" 
                class="max-w-full max-h-[90vh] rounded-lg shadow-2xl"
                style="object-fit: contain;"
            >
            <a href="${imageUrl}" download target="_blank" 
               class="absolute bottom-2 right-2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-download text-gray-700"></i>
            </a>
        </div>
    `;
    
    document.body.appendChild(modal);
}
</script>

<style>
/* Custom scrollbar for messages */
#messagesContainer::-webkit-scrollbar,
#otherTicketsCard::-webkit-scrollbar {
    width: 6px;
}

#messagesContainer::-webkit-scrollbar-track,
#otherTicketsCard::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#messagesContainer::-webkit-scrollbar-thumb,
#otherTicketsCard::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

#messagesContainer::-webkit-scrollbar-thumb:hover,
#otherTicketsCard::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Disable smooth scrolling to prevent jumps */
#messagesContainer {
    scroll-behavior: auto;
}

/* Line clamp for truncated text */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Image hover effect */
img.message-image {
    transition: transform 0.2s, box-shadow 0.2s;
}

img.message-image:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Video styling */
video {
    background: #000;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .max-w-\[75\%\] {
        max-width: 90%;
    }
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

