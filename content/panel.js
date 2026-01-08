// CS Taxif Panel Logic

// Constants
const MESSAGE_TIMEOUT_MS = 5000;
const IFRAME_LOAD_DELAY_MS = 300;
const DEBUG_MODE = false; // Set to true for development

// State management
let currentUser = null;
let ticketOptions = null;
let currentTicket = null;
let categoryData = null;
let categoryCodeOptions = []; // Store all category > subcategory > code options
let selectedCategoryCode = null; // Store selected option {categoryId, subcategoryId, codeId, isVip}

// DOM Elements
const elements = {
    loginView: null,
    appView: null,
    messageContainer: null,
    loginForm: null,
    loginBtn: null,
    tokenInput: null,
    userAvatar: null,
    userName: null,
    userTeam: null,
    ticketNumberInput: null,
    pasteBtn: null,
    searchTicketBtn: null,
    ticketDetailsSection: null,
    ticketDetailsDisplay: null,
    ticketFormSection: null,
    ticketForm: null,
    platformSelect: null,
    categorySelect: null,
    subcategorySelect: null,
    codeSelect: null,
    phoneInput: null,
    pastePhoneBtn: null,
    countrySelect: null,
    vipSwitch: null,
    marketerGroup: null,
    marketerSelect: null,
    notesInput: null,
    createTicketBtn: null,
    clearFormBtn: null,
    logoutBtn: null,
    closeBtn: null,
    reportModal: null,
    reportForm: null,
    submitReportBtn: null,
    openReportBtn: null,
    reportCloseBtn: null,
    reportCancelBtn: null
};

// Helper function to show loading state on buttons
function setLoading(button, isLoading) {
    if (!button) return;

    const span = button.querySelector('span');
    const loader = button.querySelector('.loading');

    if (isLoading) {
        button.disabled = true;
        if (span) span.style.display = 'none';
        if (loader) loader.classList.remove('hidden');
    } else {
        button.disabled = false;
        if (span) span.style.display = 'inline';
        if (loader) loader.classList.add('hidden');
    }
}

// Helper function for safe console logging
function debugLog(...args) {
    if (DEBUG_MODE) {
        debugLog(...args);
    }
}

// Helper function to show messages
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;

    elements.messageContainer.innerHTML = '';
    elements.messageContainer.appendChild(messageDiv);

    // Auto-remove after timeout
    setTimeout(() => {
        messageDiv.remove();
    }, MESSAGE_TIMEOUT_MS);
}

// Helper function to clean phone number (remove spaces, keep + and digits)
function cleanPhoneNumber(phone) {
    if (!phone) return '';
    // Remove all spaces and keep only + and digits
    return phone.replace(/\s+/g, '').replace(/[^\d+]/g, '');
}

// Initialize DOM elements
function initElements() {
    debugLog('Initializing DOM elements...');
    elements.loginView = document.getElementById('loginView');
    elements.appView = document.getElementById('appView');

    // Log critical elements
    debugLog('loginView:', elements.loginView ? 'found' : 'NOT FOUND');
    debugLog('appView:', elements.appView ? 'found' : 'NOT FOUND');
    elements.messageContainer = document.getElementById('messageContainer');
    elements.loginForm = document.getElementById('loginForm');
    elements.loginBtn = document.getElementById('loginBtn');
    elements.tokenInput = document.getElementById('tokenInput');
    elements.userAvatar = document.getElementById('userAvatar');
    elements.userName = document.getElementById('userName');
    elements.userTeam = document.getElementById('userTeam');
    elements.ticketNumberInput = document.getElementById('ticketNumberInput');
    elements.pasteBtn = document.getElementById('pasteBtn');
    elements.searchTicketBtn = document.getElementById('searchTicketBtn');
    elements.ticketDetailsSection = document.getElementById('ticketDetailsSection');
    elements.ticketDetailsDisplay = document.getElementById('ticketDetailsDisplay');
    elements.ticketFormSection = document.getElementById('ticketFormSection');
    elements.ticketForm = document.getElementById('ticketForm');
    elements.platformSelect = document.getElementById('platformSelect');
    elements.categoryCodeSelect = document.getElementById('categoryCodeSelect');
    elements.categoryCodeValue = document.getElementById('categoryCodeValue');
    elements.categoryCodeSearch = document.getElementById('categoryCodeSearch');
    elements.categoryCodeOptionsList = document.getElementById('categoryCodeOptionsList');
    elements.phoneInput = document.getElementById('phoneInput');
    elements.pastePhoneBtn = document.getElementById('pastePhoneBtn');
    elements.countrySelect = document.getElementById('countrySelect');
    elements.vipSwitch = document.getElementById('vipSwitch');
    elements.marketerGroup = document.getElementById('marketerGroup');
    elements.marketerSelect = document.getElementById('marketerSelect');
    elements.notesInput = document.getElementById('notesInput');
    elements.createTicketBtn = document.getElementById('createTicketBtn');
    elements.clearFormBtn = document.getElementById('clearFormBtn');
    elements.logoutBtn = document.getElementById('logoutBtn');
    elements.closeBtn = document.getElementById('closeBtn');
    elements.reportModal = document.getElementById('reportModal');
    elements.reportForm = document.getElementById('reportForm');
    elements.submitReportBtn = document.getElementById('submitReportBtn');
    elements.openReportBtn = document.getElementById('openReportBtn');
    elements.reportCloseBtn = document.getElementById('reportCloseBtn');
    elements.reportCancelBtn = document.getElementById('reportCancelBtn');

    // Log critical form elements
    debugLog('ticketDetailsSection:', elements.ticketDetailsSection ? 'found' : 'NOT FOUND');
    debugLog('ticketDetailsDisplay:', elements.ticketDetailsDisplay ? 'found' : 'NOT FOUND');
    debugLog('ticketFormSection:', elements.ticketFormSection ? 'found' : 'NOT FOUND');
    debugLog('searchTicketBtn:', elements.searchTicketBtn ? 'found' : 'NOT FOUND');
}

// Get user initials from name
function getUserInitials(name) {
    if (!name) return '--';
    const words = name.trim().split(' ').filter(word => word.length > 0);
    if (words.length >= 2 && words[0].length > 0 && words[1].length > 0) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    if (words.length > 0 && words[0].length >= 2) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

// Display user profile
function displayUserProfile(user) {
    currentUser = user;

    const initials = getUserInitials(user.name || user.username);
    elements.userAvatar.textContent = initials;
    elements.userName.textContent = user.name || user.username;

    if (user.team) {
        elements.userTeam.textContent = `Team: ${user.team.name}`;
    } else {
        elements.userTeam.textContent = `User: ${user.username}`;
    }

    // Display ticket statistics if available
    updateTicketStats(user.ticket_details_stats);
}

// Update ticket statistics display
function updateTicketStats(stats) {
    const statsContainer = document.getElementById('ticketStats');
    if (!statsContainer) return;

    if (stats) {
        const statCurrentHour = document.getElementById('statCurrentHour');
        const statLastHour = document.getElementById('statLastHour');
        const statToday = document.getElementById('statToday');

        if (statCurrentHour) statCurrentHour.textContent = stats.current_hour || 0;
        if (statLastHour) statLastHour.textContent = stats.last_hour || 0;
        if (statToday) statToday.textContent = stats.today || 0;

        statsContainer.style.display = 'block';
    } else {
        statsContainer.style.display = 'none';
    }
}

// Refresh user statistics from API
async function refreshUserStats() {
    try {
        // Use makeRequest directly to get fresh user data with stats
        const userData = await API.makeRequest('/me');

        // Handle both array and object responses
        const user = Array.isArray(userData) && userData.length > 0
            ? userData[0]
            : (userData && typeof userData === 'object' && userData.id ? userData : null);

        if (user && user.ticket_details_stats) {
            // Update current user data
            if (currentUser) {
                currentUser.ticket_details_stats = user.ticket_details_stats;
            }
            // Update display
            updateTicketStats(user.ticket_details_stats);
            debugLog('User stats refreshed:', user.ticket_details_stats);
        }
    } catch (error) {
        console.error('Error refreshing user stats:', error);
        // Don't show error to user - stats refresh is silent
    }
}

// Load ticket options and populate selects
async function loadTicketOptions() {
    try {
        // Try to get cached options first
        let options = await Storage.getOptions();

        // If no cached options or user just logged in, fetch fresh data
        if (!options) {
            showMessage('Loading ticket options...', 'info');
            options = await API.getTicketOptions();
            await Storage.setOptions(options);
        }

        ticketOptions = options;
        populateSelects();

    } catch (error) {
        console.error('Error loading options:', error);

        if (error.message === 'TOKEN_EXPIRED') {
            showMessage('Token expired. Please login again.', 'error');
            await handleLogout();
        } else {
            showMessage('Failed to load ticket options. Please try again.', 'error');
        }
    }
}

// Populate select elements with options
function populateSelects() {
    if (!ticketOptions) return;

    // Platforms
    elements.platformSelect.innerHTML = '<option value="">Select Platform</option>';
    ticketOptions.platforms.forEach(platform => {
        const option = document.createElement('option');
        option.value = platform.id;
        option.textContent = platform.name;
        elements.platformSelect.appendChild(option);
    });

    // Build combined Category > Subcategory > Code options
    buildCategoryCodeOptions();

    // Countries
    elements.countrySelect.innerHTML = '<option value="">Select Country</option>';
    ticketOptions.countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.id;
        option.textContent = country.name;
        elements.countrySelect.appendChild(option);
    });

    // Marketers
    elements.marketerSelect.innerHTML = '<option value="">Select Marketer</option>';
    ticketOptions.marketers.forEach(marketer => {
        const option = document.createElement('option');
        option.value = marketer.id;
        option.textContent = marketer.name;
        elements.marketerSelect.appendChild(option);
    });
}

// Build combined Category > Subcategory > Code options
function buildCategoryCodeOptions() {
    if (!ticketOptions || !ticketOptions.categories) return;

    categoryData = ticketOptions.categories;
    categoryCodeOptions = [];

    // Build options for each Category > Subcategory > Code combination
    ticketOptions.categories.forEach(category => {
        if (category.subcategories && category.subcategories.length > 0) {
            category.subcategories.forEach(subcategory => {
                if (subcategory.codes && subcategory.codes.length > 0) {
                    subcategory.codes.forEach(code => {
                        categoryCodeOptions.push({
                            categoryId: category.id,
                            categoryName: category.name,
                            subcategoryId: subcategory.id,
                            subcategoryName: subcategory.name,
                            codeId: code.id,
                            codeName: code.name,
                            displayText: `${category.name} > ${subcategory.name} > ${code.name}`,
                            searchText: `${category.name} ${subcategory.name} ${code.name}`.toLowerCase(),
                            isVip: false
                        });
                    });
                }
            });
        }
    });

    // Note: VIP is handled separately via platform_id = 5, not in this dropdown
    // VIP option removed from dropdown because API requires category_id even for VIP tickets

    // Render options
    renderCategoryCodeOptions();
}

// Render category code options in the dropdown
function renderCategoryCodeOptions(filterText = '') {
    if (!elements.categoryCodeOptionsList) return;

    elements.categoryCodeOptionsList.innerHTML = '';

    const filteredOptions = filterText
        ? categoryCodeOptions.filter(opt => opt.searchText.includes(filterText.toLowerCase()))
        : categoryCodeOptions;

    filteredOptions.forEach((option, index) => {
        const optionElement = document.createElement('div');
        optionElement.className = `custom-select-option ${option.isVip ? 'vip-option' : ''}`;
        optionElement.dataset.index = index;
        optionElement.dataset.categoryId = option.categoryId || '';
        optionElement.dataset.subcategoryId = option.subcategoryId || '';
        optionElement.dataset.codeId = option.codeId || '';
        optionElement.dataset.isVip = option.isVip ? 'true' : 'false';

        const pathSpan = document.createElement('span');
        pathSpan.className = 'option-path';
        pathSpan.textContent = option.displayText;
        optionElement.appendChild(pathSpan);

        optionElement.addEventListener('click', () => selectCategoryCodeOption(option, index));

        elements.categoryCodeOptionsList.appendChild(optionElement);
    });

    // If no results, show message
    if (filteredOptions.length === 0) {
        const noResults = document.createElement('div');
        noResults.className = 'custom-select-option';
        noResults.style.padding = '16px';
        noResults.style.textAlign = 'center';
        noResults.style.color = 'var(--text-secondary)';
        noResults.textContent = 'No results found';
        elements.categoryCodeOptionsList.appendChild(noResults);
    }
}

// Handle category code option selection
function selectCategoryCodeOption(option, index) {
    selectedCategoryCode = option;

    // Update display
    const trigger = elements.categoryCodeSelect.querySelector('.custom-select-trigger .custom-select-value');
    if (trigger) {
        trigger.textContent = option.displayText;
    }

    // Update hidden input
    if (elements.categoryCodeValue) {
        elements.categoryCodeValue.value = JSON.stringify({
            categoryId: option.categoryId,
            subcategoryId: option.subcategoryId,
            codeId: option.codeId,
            isVip: false // VIP is now handled separately via vipSwitch
        });
    }

    // Close dropdown
    elements.categoryCodeSelect.classList.remove('active');

    // VIP is now handled separately via vipSwitch checkbox
    // No need to handle VIP here

    debugLog('Selected option:', option);
}

// Handle category selection change
function handleCategoryChange() {
    const categoryId = parseInt(elements.categorySelect.value);

    // Reset subcategory and code
    elements.subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    elements.codeSelect.innerHTML = '<option value="">Select Code</option>';
    elements.subcategorySelect.disabled = true;
    elements.codeSelect.disabled = true;

    if (!categoryId) return;

    // Find selected category
    const category = categoryData.find(cat => cat.id === categoryId);
    if (!category || !category.subcategories) return;

    // Populate subcategories
    elements.subcategorySelect.disabled = false;
    category.subcategories.forEach(sub => {
        const option = document.createElement('option');
        option.value = sub.id;
        option.textContent = sub.name;
        option.dataset.codes = JSON.stringify(sub.codes);
        elements.subcategorySelect.appendChild(option);
    });
}

// Handle subcategory selection change
function handleSubcategoryChange() {
    const subcategoryOption = elements.subcategorySelect.selectedOptions[0];

    // Reset code
    elements.codeSelect.innerHTML = '<option value="">Select Code</option>';
    elements.codeSelect.disabled = true;

    if (!subcategoryOption || !subcategoryOption.value) return;

    // Get codes from data attribute with safe JSON parsing
    let codes = [];
    try {
        codes = JSON.parse(subcategoryOption.dataset.codes || '[]');
    } catch (error) {
        console.error('Error parsing subcategory codes:', error);
        showMessage('Error loading codes', 'error');
        return;
    }

    // Populate codes
    elements.codeSelect.disabled = false;
    codes.forEach(code => {
        const option = document.createElement('option');
        option.value = code.id;
        option.textContent = code.name;
        elements.codeSelect.appendChild(option);
    });
}

// Handle VIP switch change
function handleVipChange() {
    if (elements.vipSwitch.checked) {
        elements.marketerGroup.classList.remove('hidden');
        elements.marketerSelect.required = true;
    } else {
        elements.marketerGroup.classList.add('hidden');
        elements.marketerSelect.required = false;
        elements.marketerSelect.value = '';
    }
}

// Handle platform selection change - auto-detect VIP platform
function handlePlatformChange() {
    const platformId = parseInt(elements.platformSelect.value);

    // Platform ID 5 is VIP 👑
    // Note: VIP platform (platform_id = 5) is separate from VIP customer flag (is_vip)
    // VIP platform doesn't automatically mean VIP customer - user must check VIP switch
    debugLog('Platform changed:', platformId === 5 ? 'VIP Platform' : 'Regular Platform');

    // No automatic VIP selection - user must manually check VIP switch if needed
}

// Login handler
async function handleLogin(e) {
    e.preventDefault();

    const token = elements.tokenInput.value.trim();

    if (!token) {
        showMessage('Please enter a token', 'error');
        return;
    }

    setLoading(elements.loginBtn, true);

    try {
        showMessage('Verifying token...', 'info');

        // Validate token
        const userData = await API.validateToken(token);

        // Save user data
        await Storage.setUser(userData);

        // Load ticket options
        await loadTicketOptions();

        // Show success message
        showMessage(`Welcome ${userData.name || userData.username}! Login successful 🎉`, 'success');

        // Display user profile
        displayUserProfile(userData);

        // Switch to app view
        elements.loginView.classList.add('hidden');
        elements.appView.classList.remove('hidden');

    } catch (error) {
        console.error('Login error:', error);

        if (error.message === 'TOKEN_EXPIRED' || error.message.includes('Invalid')) {
            showMessage('Invalid token. Please check the token and try again.', 'error');
        } else {
            showMessage('Login failed. Please try again.', 'error');
        }
    } finally {
        setLoading(elements.loginBtn, false);
    }
}

// Logout handler
async function handleLogout(event) {
    try {
        await Storage.removeToken();
        currentUser = null;
        currentTicket = null;

        elements.appView.classList.add('hidden');
        elements.loginView.classList.remove('hidden');
        elements.tokenInput.value = '';

        clearForm();

        // If triggered by user click, show logout message
        if (event && event.type === 'click') {
            elements.messageContainer.innerHTML = '';
            showMessage('Logged out successfully', 'info');
        }
    } catch (error) {
        console.error('Logout error:', error);
    }
}

// Report handlers
function handleOpenReport() {
    if (elements.reportModal) {
        elements.reportModal.classList.add('active');
        // Reset form
        elements.reportForm.reset();
    }
}

function handleCloseReport() {
    if (elements.reportModal) {
        elements.reportModal.classList.remove('active');
    }
}

async function handleSubmitReport(e) {
    e.preventDefault();

    const type = elements.reportForm.querySelector('input[name="reportType"]:checked').value;
    const title = document.getElementById('reportTitle').value.trim();
    const description = document.getElementById('reportDescription').value.trim();

    if (!title || !description) {
        showMessage('Please fill in all required fields', 'warning');
        return;
    }

    setLoading(elements.submitReportBtn, true);

    try {
        await API.submitReport({
            type,
            title,
            description
        });

        showMessage(`Thank you! Your ${type} report has been submitted successfully.`, 'success');
        handleCloseReport();
    } catch (error) {
        console.error('Report submission error:', error);
        showMessage('Failed to submit report. Please try again.', 'error');
    } finally {
        setLoading(elements.submitReportBtn, false);
    }
}


// Paste from clipboard with fallback method
async function pasteFromClipboard() {
    try {
        // Try modern Clipboard API first
        if (navigator.clipboard && navigator.clipboard.readText) {
            return await navigator.clipboard.readText();
        }
    } catch (error) {
        console.warn('Clipboard API failed, trying fallback method:', error);
    }

    // Fallback: Use document.execCommand (requires user interaction)
    try {
        // Create a temporary textarea to paste into
        const textarea = document.createElement('textarea');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        const success = document.execCommand('paste');
        const text = textarea.value;
        document.body.removeChild(textarea);

        if (success && text) {
            return text;
        }
    } catch (error) {
        console.error('Fallback paste method failed:', error);
    }

    throw new Error('Unable to access clipboard');
}

// Paste ticket number from clipboard
async function handlePaste() {
    try {
        const text = await pasteFromClipboard();
        const ticketNumber = text.trim().replace(/\D/g, '');

        if (ticketNumber) {
            elements.ticketNumberInput.value = ticketNumber;
            showMessage('Ticket number pasted', 'success');
        } else {
            showMessage('No number found in clipboard', 'warning');
        }
    } catch (error) {
        console.error('Paste error:', error);
        showMessage('Paste failed. Please use Ctrl+V (Cmd+V on Mac) to paste manually.', 'error');
    }
}

// Paste phone number from clipboard
async function handlePastePhone() {
    try {
        const text = await pasteFromClipboard();
        const phoneNumber = text.trim();

        if (phoneNumber) {
            elements.phoneInput.value = phoneNumber;
            showMessage('Phone number pasted', 'success');
        } else {
            showMessage('No text found in clipboard', 'warning');
        }
    } catch (error) {
        console.error('Paste phone error:', error);
        showMessage('Paste failed. Please use Ctrl+V (Cmd+V on Mac) to paste manually.', 'error');
    }
}

// Search for ticket
// Populate form with helper data
function populateFormWithHelper(helper) {
    if (!helper) return;

    debugLog('Populating form with helper data:', helper);

    // Platform
    if (helper.platform && helper.platform.id) {
        elements.platformSelect.value = helper.platform.id;
        handlePlatformChange();
    }

    // Phone
    if (helper.phone) {
        elements.phoneInput.value = helper.phone;
    }

    // Country
    if (helper.country && helper.country.id) {
        elements.countrySelect.value = helper.country.id;
    }
}

// Search for ticket
async function handleSearchTicket() {
    const ticketNumber = elements.ticketNumberInput.value.trim();

    if (!ticketNumber) {
        showMessage('Please enter a ticket number', 'warning');
        return;
    }

    setLoading(elements.searchTicketBtn, true);

    try {
        showMessage('Searching for ticket...', 'info');

        const result = await API.getTicketDetails(ticketNumber);
        debugLog('Ticket search result:', result);

        if (result.success && result.ticket_detail) {
            currentTicket = result;
            debugLog('Displaying ticket details...');
            displayTicketDetails(result.ticket_detail);
            debugLog('Populating form with ticket data...');
            populateFormWithTicket(result.ticket_detail);
            debugLog('Ticket details displayed successfully');
            showMessage('Ticket found successfully', 'success');
        } else if (result.helper) {
            // Helper found
            debugLog('Ticket not found, but helper data found');
            currentTicket = null;
            elements.ticketDetailsSection.classList.add('hidden');
            elements.ticketFormSection.classList.remove('hidden');
            clearForm();

            populateFormWithHelper(result.helper);

            showMessage('Ticket not found. Form pre-filled with available data.', 'info');
        } else {
            // Ticket not found - show empty form
            debugLog('Ticket not found, showing empty form');
            currentTicket = null;
            elements.ticketDetailsSection.classList.add('hidden');
            elements.ticketFormSection.classList.remove('hidden');
            clearForm();
            showMessage('Ticket not found. You can create a new one.', 'info');
        }

    } catch (error) {
        console.error('Search error:', error);

        if (error.message === 'TOKEN_EXPIRED') {
            showMessage('Token expired. Please login again.', 'error');
            await handleLogout();
        } else if (error.message.includes('not found')) {
            // Ticket not found - show empty form
            debugLog('Ticket not found (error), showing empty form');
            currentTicket = null;
            elements.ticketDetailsSection.classList.add('hidden');
            elements.ticketFormSection.classList.remove('hidden');
            clearForm();
            showMessage('Ticket not found. You can create a new one.', 'info');
        } else {
            showMessage('Failed to search for ticket. Please try again.', 'error');
        }
    } finally {
        setLoading(elements.searchTicketBtn, false);
    }
}

// Display ticket details
function displayTicketDetails(ticket) {
    debugLog('displayTicketDetails called with ticket:', ticket);

    if (!ticket) {
        console.error('Ticket is null or undefined!');
        return;
    }

    if (!elements.ticketDetailsDisplay) {
        console.error('ticketDetailsDisplay element not found!');
        return;
    }

    const vipBadge = ticket.is_vip ? '<span class="vip-badge">👑 VIP</span>' : '';

    const html = `
    <div class="ticket-details-grid">
      <div class="ticket-detail-item">
        <label>Ticket Number</label>
        <span>${ticket.name || ticket.id || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Platform</label>
        <span>${ticket.platform?.name || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Category</label>
        <span>${ticket.category?.name || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Subcategory</label>
        <span>${ticket.subcategory?.name || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Code</label>
        <span>${ticket.code?.name || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Phone</label>
        <span>${ticket.phone || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>Country</label>
        <span>${ticket.country?.name || '-'}</span>
      </div>
      <div class="ticket-detail-item">
        <label>VIP</label>
        <span>${vipBadge || 'No'}</span>
      </div>
      ${ticket.marketer ? `
        <div class="ticket-detail-item">
          <label>Marketer</label>
          <span>${ticket.marketer.name}</span>
        </div>
      ` : ''}
      ${ticket.created_by ? `
        <div class="ticket-detail-item">
          <label>Created By</label>
          <span>${ticket.created_by.name || '-'}</span>
        </div>
      ` : ''}
      <div class="ticket-detail-item">
        <label>Created At</label>
        <span>${ticket.created_at || '-'}</span>
      </div>
    </div>
  `;

    // Add action buttons
    const actionButtonsHtml = `
      <div class="btn-group" style="margin-top: 16px;">
        <button class="btn btn-success" id="quickCloneBtn" style="flex: 1;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 8px;">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
          </svg>
          Quick Clone
        </button>
        <button class="btn btn-secondary" id="editTicketBtn" style="flex: 1;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 8px;">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
          Edit Ticket
        </button>
      </div>
    `;

    elements.ticketDetailsDisplay.innerHTML = html + actionButtonsHtml;
    debugLog('Ticket details HTML set, now showing section...');

    if (elements.ticketDetailsSection) {
        elements.ticketDetailsSection.classList.remove('hidden');
        debugLog('Ticket details section shown');
    } else {
        console.error('ticketDetailsSection element not found!');
    }

    if (elements.ticketFormSection) {
        elements.ticketFormSection.classList.add('hidden');
        debugLog('Ticket form section hidden');
    } else {
        console.error('ticketFormSection element not found!');
    }

    // Add event listeners for action buttons
    const quickCloneBtn = document.getElementById('quickCloneBtn');
    if (quickCloneBtn) {
        debugLog('Adding click listener to quick clone button');
        quickCloneBtn.addEventListener('click', handleQuickClone);
    } else {
        console.error('Quick clone button not found!');
    }

    const editBtn = document.getElementById('editTicketBtn');
    if (editBtn) {
        debugLog('Adding click listener to edit button');
        editBtn.addEventListener('click', () => {
            debugLog('Edit button clicked');
            // Hide ticket details section
            if (elements.ticketDetailsSection) {
                elements.ticketDetailsSection.classList.add('hidden');
                debugLog('Ticket details section hidden');
            }
            // Show form section
            if (elements.ticketFormSection) {
                elements.ticketFormSection.classList.remove('hidden');
                elements.ticketFormSection.scrollIntoView({ behavior: 'smooth' });
                debugLog('Form section shown');
            }
        });
    } else {
        console.error('Edit button not found!');
    }
}

// Populate form with ticket data
function populateFormWithTicket(ticket) {
    debugLog('populateFormWithTicket called with ticket:', ticket);

    if (!ticket) {
        console.error('Cannot populate form - ticket is null or undefined');
        return;
    }

    // Set values
    if (ticket.platform?.id) {
        elements.platformSelect.value = ticket.platform.id;
        debugLog('Platform set:', ticket.platform.id);
    }

    // Set category code select
    if (ticket.category?.id && ticket.subcategory?.id && ticket.code?.id) {
        // Find matching option
        const matchingOption = categoryCodeOptions.find(opt =>
            opt.categoryId === ticket.category.id &&
            opt.subcategoryId === ticket.subcategory.id &&
            opt.codeId === ticket.code.id &&
            !opt.isVip
        );

        if (matchingOption) {
            const index = categoryCodeOptions.indexOf(matchingOption);
            selectCategoryCodeOption(matchingOption, index);
            debugLog('Category code option selected:', matchingOption.displayText);
        }
    }

    // Set VIP switch
    if (elements.vipSwitch) {
        elements.vipSwitch.checked = ticket.is_vip || false;
        handleVipChange();
        debugLog('VIP set:', ticket.is_vip || false);
    }

    if (ticket.phone) {
        elements.phoneInput.value = ticket.phone;
        debugLog('Phone set:', ticket.phone);
    }

    if (ticket.country?.id) {
        elements.countrySelect.value = ticket.country.id;
        debugLog('Country set:', ticket.country.id);
    }

    // Handle marketer for VIP
    if (ticket.is_vip && ticket.marketer?.id && elements.marketerSelect) {
        elements.marketerSelect.value = ticket.marketer.id;
        debugLog('Marketer set:', ticket.marketer.id);
    }

    if (ticket.notes) {
        elements.notesInput.value = ticket.notes;
        debugLog('Notes set');
    }

    debugLog('Form populated successfully');
}

// Create ticket
async function handleCreateTicket(e) {
    e.preventDefault();

    const ticketNumber = elements.ticketNumberInput.value.trim();

    if (!ticketNumber) {
        showMessage('Please enter a ticket number', 'error');
        return;
    }

    // Get selected category code option
    if (!selectedCategoryCode || !elements.categoryCodeValue.value) {
        showMessage('Please select Category > Subcategory > Code', 'error');
        return;
    }

    // Safe JSON parsing
    let categoryCodeData;
    try {
        categoryCodeData = JSON.parse(elements.categoryCodeValue.value);
    } catch (error) {
        console.error('Error parsing category code data:', error);
        showMessage('Invalid category selection. Please try again.', 'error');
        return;
    }

    // VIP is now handled separately via vipSwitch checkbox, not in dropdown
    // API requires category_id even for VIP tickets, so VIP is just a flag

    // Gather form data
    const ticketData = {
        ticket_number: ticketNumber.replace(/\D/g, ''), // Remove non-digits only
        platform_id: parseInt(elements.platformSelect.value),
        category_id: parseInt(categoryCodeData.categoryId),
        subcategory_id: parseInt(categoryCodeData.subcategoryId),
        code_id: parseInt(categoryCodeData.codeId),
        phone: cleanPhoneNumber(elements.phoneInput.value.trim()) || '', // Clean phone number (remove spaces)
        notes: elements.notesInput ? (elements.notesInput.value.trim() || '') : '',
        country_id: parseInt(elements.countrySelect.value),
        is_vip: elements.vipSwitch ? elements.vipSwitch.checked : false
    };

    // Add marketer if VIP is checked and marketer is selected
    if (ticketData.is_vip && elements.marketerSelect && elements.marketerSelect.value) {
        ticketData.marketer_id = parseInt(elements.marketerSelect.value);
    }

    // Validate required fields
    if (!ticketData.platform_id || !ticketData.country_id) {
        showMessage('Please fill all required fields', 'error');
        return;
    }

    // Validate category/subcategory/code (always required, even for VIP)
    if (!ticketData.category_id || !ticketData.subcategory_id || !ticketData.code_id) {
        showMessage('Please select Category > Subcategory > Code', 'error');
        return;
    }

    // Validate marketer for VIP tickets
    if (ticketData.is_vip && !ticketData.marketer_id) {
        showMessage('Please select a marketer for VIP customers', 'error');
        return;
    }

    setLoading(elements.createTicketBtn, true);

    try {
        showMessage('Creating ticket...', 'info');

        const result = await API.createTicket(ticketData);

        showMessage('Ticket created successfully! 🎉', 'success');

        // Refresh user statistics after creating ticket
        await refreshUserStats();

        // Clear the form and ticket number
        clearForm();
        elements.ticketNumberInput.value = '';
        elements.ticketDetailsSection.classList.add('hidden');
        elements.ticketFormSection.classList.add('hidden');

    } catch (error) {
        console.error('Create ticket error:', error);

        if (error.message === 'TOKEN_EXPIRED') {
            showMessage('Token expired. Please login again.', 'error');
            await handleLogout();
        } else {
            showMessage(`Failed to create ticket: ${error.message}`, 'error');
        }
    } finally {
        setLoading(elements.createTicketBtn, false);
    }
}

// Clear form
function clearForm(event) {
    // If triggered by a manual click (event exists), ask for confirmation
    if (event && event.type === 'click') {
        if (!confirm('هل أنت متأكد من مسح جميع البيانات في النموذج؟')) {
            return;
        }
    }

    elements.ticketForm?.reset();

    // Reset category code select
    if (elements.categoryCodeSelect) {
        const trigger = elements.categoryCodeSelect.querySelector('.custom-select-trigger .custom-select-value');
        if (trigger) {
            trigger.textContent = 'Select Category > Subcategory > Code';
        }
    }
    if (elements.categoryCodeValue) {
        elements.categoryCodeValue.value = '';
    }
    selectedCategoryCode = null;

    // Reset VIP switch
    if (elements.vipSwitch) {
        elements.vipSwitch.checked = false;
    }

    // Reset marketer
    elements.marketerGroup?.classList.add('hidden');
    if (elements.marketerSelect) {
        elements.marketerSelect.required = false;
        elements.marketerSelect.value = '';
    }

    currentTicket = null;
    elements.ticketDetailsSection?.classList.add('hidden');
}

// Handle close button
function handleClose() {
    window.parent.postMessage({ action: 'closePanel' }, '*');
}

// Listen for messages from content script
window.addEventListener('message', (event) => {
    const { action, ticketNumber } = event.data;

    if (action === 'searchTicket' && ticketNumber) {
        elements.ticketNumberInput.value = ticketNumber;
        handleSearchTicket();
    }
});

// Check if user is already logged in
async function checkAuth() {
    debugLog('Checking authentication...');
    const token = await Storage.getToken();
    const user = await Storage.getUser();

    debugLog('Token:', token ? 'exists' : 'not found');
    debugLog('User:', user ? user.name || user.username : 'not found');

    if (token && user) {
        // User is logged in
        debugLog('User is logged in, displaying profile...');
        displayUserProfile(user);
        await loadTicketOptions();

        if (elements.appView) {
            elements.appView.classList.remove('hidden');
            debugLog('App view shown');
        } else {
            console.error('appView element not found!');
        }

        if (elements.loginView) {
            elements.loginView.classList.add('hidden');
        }
    } else {
        // Show login
        debugLog('User not logged in, showing login view...');
        if (elements.loginView) {
            elements.loginView.classList.remove('hidden');
            debugLog('Login view shown');
        } else {
            console.error('loginView element not found!');
        }

        if (elements.appView) {
            elements.appView.classList.add('hidden');
        }
    }
}

// Handle back to search button
function handleBackToSearch() {
    elements.ticketDetailsSection?.classList.add('hidden');
    elements.ticketFormSection?.classList.add('hidden');
    elements.ticketNumberInput.value = '';
    currentTicket = null;
    clearForm();
}

// Handle quick clone button - creates a copy with the same ticket number
function handleQuickClone() {
    if (!currentTicket || !currentTicket.ticket_detail) {
        showMessage('No ticket to clone', 'error');
        return;
    }

    // Use the actual ticket_number from the API response
    // currentTicket.ticket_number = "905082116" (the real ticket number) ✅
    const ticketNumber = currentTicket.ticket_number ||
        currentTicket.ticket_detail?.name?.replace(/\D/g, '') ||
        'Unknown';

    // Show confirmation modal
    showCloneConfirmationModal(ticketNumber);
}

// Show clone confirmation modal
function showCloneConfirmationModal(ticketNumber) {
    const modal = document.getElementById('customModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalInputGroup = document.getElementById('modalInputGroup');
    const modalNotesGroup = document.getElementById('modalNotesGroup');
    const modalNotesInput = document.getElementById('modalNotesInput');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');

    if (!modal) {
        console.error('Modal not found!');
        return;
    }

    // Configure modal
    modalTitle.textContent = 'Confirm Ticket Clone';
    modalMessage.textContent = `Are you sure you want to clone ticket #${ticketNumber} with the same data?`;
    modalInputGroup.classList.add('hidden');
    modalNotesGroup.classList.remove('hidden'); // Show notes field
    if (modalNotesInput) {
        modalNotesInput.value = ''; // Clear any previous notes
    }
    modalConfirmBtn.textContent = 'Yes, Clone Ticket';

    // Show modal
    modal.classList.add('active');

    // Focus on notes input when modal opens
    if (modalNotesInput) {
        setTimeout(() => modalNotesInput.focus(), 100);
    }

    // Handle cancel - cleanup function
    const handleCancel = () => {
        modal.classList.remove('active');
        if (modalNotesInput) {
            modalNotesInput.value = ''; // Clear notes on cancel
        }
        // Remove all event listeners
        cleanup();
    };

    // Handle confirm
    const handleConfirm = async () => {
        modal.classList.remove('active');

        // Get notes from modal input
        const notes = modalNotesInput ? modalNotesInput.value.trim() : '';
        debugLog('Notes from modal:', notes);

        // Remove all event listeners
        cleanup();

        // Proceed with cloning (pass notes)
        await executeQuickClone(notes);

        // Clear notes after cloning
        if (modalNotesInput) {
            modalNotesInput.value = '';
        }
    };

    // Overlay click handler
    const handleOverlayClick = (e) => {
        if (e.target === modal) {
            handleCancel();
        }
    };

    // Cleanup function to remove all event listeners
    const cleanup = () => {
        // Clone buttons to remove all event listeners
        const newCancelBtn = modalCancelBtn.cloneNode(true);
        const newConfirmBtn = modalConfirmBtn.cloneNode(true);
        modalCancelBtn.parentNode.replaceChild(newCancelBtn, modalCancelBtn);
        modalConfirmBtn.parentNode.replaceChild(newConfirmBtn, modalConfirmBtn);

        // Remove overlay listener
        modal.removeEventListener('click', handleOverlayClick);
    };

    // Add event listeners (will be removed by cleanup)
    modalCancelBtn.addEventListener('click', handleCancel, { once: true });
    modalConfirmBtn.addEventListener('click', handleConfirm, { once: true });

    // Close on overlay click
    modal.addEventListener('click', handleOverlayClick, { once: true });
}

// Execute the actual cloning process
async function executeQuickClone(notes = '') {
    const ticket = currentTicket.ticket_detail;

    // CRITICAL: Use the actual ticket_number from the API response
    // currentTicket.ticket_number = "905082116" (the real ticket number) ✅
    // ticket.id = 102672 (database ID - WRONG!) ❌
    // ticket.name = "Ticket Detail #102672" (display name - WRONG!) ❌
    const actualTicketNumber = currentTicket.ticket_number ||
        currentTicket.ticket_detail?.name?.replace(/\D/g, '') ||
        '';

    if (!actualTicketNumber) {
        showMessage('Cannot determine ticket number for cloning', 'error');
        return;
    }

    // Prepare ticket data for cloning (same ticket number)
    // Use new notes from modal, not old notes from ticket
    const ticketData = {
        ticket_number: actualTicketNumber.replace(/\D/g, ''), // Remove non-digits only
        platform_id: parseInt(ticket.platform?.id),
        category_id: parseInt(ticket.category?.id),
        subcategory_id: parseInt(ticket.subcategory?.id),
        code_id: parseInt(ticket.code?.id),
        phone: cleanPhoneNumber(ticket.phone || ''), // Clean phone number (remove spaces)
        country_id: parseInt(ticket.country?.id),
        is_vip: ticket.is_vip || false
    };

    // Add notes from modal (always include, even if empty)
    if (notes && notes.trim()) {
        ticketData.notes = notes.trim();
    } else {
        ticketData.notes = ''; // Empty string if no notes
    }

    if (ticketData.is_vip && ticket.marketer?.id) {
        ticketData.marketer_id = parseInt(ticket.marketer.id);
    }

    debugLog('Cloning ticket with data:', ticketData);
    debugLog('Notes to be sent:', ticketData.notes);

    // Validate required fields (phone is now optional)
    if (!ticketData.platform_id || !ticketData.category_id || !ticketData.subcategory_id ||
        !ticketData.code_id || !ticketData.country_id) {
        console.error('Missing required fields:', {
            platform_id: ticketData.platform_id,
            category_id: ticketData.category_id,
            subcategory_id: ticketData.subcategory_id,
            code_id: ticketData.code_id,
            country_id: ticketData.country_id
        });
        showMessage('Ticket data is incomplete', 'error');
        return;
    }

    // Validate marketer for VIP tickets
    if (ticketData.is_vip && !ticketData.marketer_id) {
        showMessage('Please select a marketer for VIP customers', 'error');
        return;
    }

    try {
        showMessage('Cloning ticket...', 'info');

        await API.createTicket(ticketData);

        showMessage('Ticket cloned successfully! 🎉', 'success');

        // Refresh user statistics after cloning ticket
        await refreshUserStats();

        // Clear and reset
        elements.ticketNumberInput.value = '';
        currentTicket = null;
        elements.ticketDetailsSection.classList.add('hidden');
        clearForm();

    } catch (error) {
        console.error('Clone ticket error:', error);

        if (error.message === 'TOKEN_EXPIRED') {
            showMessage('Token expired. Please login again.', 'error');
            await handleLogout();
        } else {
            showMessage(`Failed to clone ticket: ${error.message}`, 'error');
        }
    }
}

// Handle cancel edit button
function handleCancelEdit() {
    if (currentTicket) {
        // If we have a current ticket, show its details
        elements.ticketFormSection?.classList.add('hidden');
    } else {
        // Otherwise, hide the form
        elements.ticketFormSection?.classList.add('hidden');
    }
}

// Set version number from manifest
function setVersionNumber() {
    try {
        // Get version from manifest.json dynamically
        const manifest = chrome.runtime?.getManifest();
        const version = manifest?.version || '1.0.0';
        const versionBadge = document.getElementById('versionBadge');

        if (versionBadge) {
            versionBadge.textContent = `v${version}`;
            // Add click event listener to show changelog
            versionBadge.addEventListener('click', showChangelog);
            debugLog('Version badge set to:', version);
        } else {
            console.warn('Version badge element not found');
        }
    } catch (error) {
        console.error('Error setting version:', error);
        // Fallback: try to set default version
        const versionBadge = document.getElementById('versionBadge');
        if (versionBadge) {
            versionBadge.textContent = 'v1.0.0';
            versionBadge.addEventListener('click', showChangelog);
        }
    }
}

// Load and display changelog
async function loadChangelog() {
    try {
        // Get the runtime URL for the changelog file
        const changelogUrl = chrome?.runtime?.getURL('CHANGELOG.json') ||
            (typeof chrome !== 'undefined' && chrome.runtime && chrome.runtime.getURL('CHANGELOG.json'));

        if (!changelogUrl) {
            console.error('Cannot get runtime URL');
            return [];
        }

        const response = await fetch(changelogUrl);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        return data.changelog || [];
    } catch (error) {
        console.error('Error loading changelog:', error);
        // Return empty array or fallback data
        return [];
    }
}

// Show changelog modal
async function showChangelog() {
    const changelogModal = document.getElementById('changelogModal');
    const changelogBody = document.getElementById('changelogBody');
    const changelogCloseBtn = document.getElementById('changelogCloseBtn');

    if (!changelogModal || !changelogBody) {
        console.error('Changelog modal elements not found');
        return;
    }

    // Load changelog data
    const changelog = await loadChangelog();

    if (changelog.length === 0) {
        changelogBody.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 20px;">لا توجد تعديلات متاحة</p>';
    } else {
        // Render changelog entries
        changelogBody.innerHTML = changelog.map(entry => `
            <div class="changelog-entry">
                <div class="changelog-entry-header">
                    <span class="changelog-version">v${entry.version}</span>
                    <span class="changelog-date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        ${entry.date} ${entry.time}
                    </span>
                </div>
                <ul class="changelog-changes">
                    ${entry.changes.map(change => `<li>${change}</li>`).join('')}
                </ul>
            </div>
        `).join('');
    }

    // Show modal
    changelogModal.classList.add('active');

    // Close handler function
    const handleClose = () => {
        changelogModal.classList.remove('active');
    };

    // Close button handler
    if (changelogCloseBtn) {
        // Remove any existing listeners to avoid duplicates
        const newCloseBtn = changelogCloseBtn.cloneNode(true);
        changelogCloseBtn.parentNode.replaceChild(newCloseBtn, changelogCloseBtn);
        newCloseBtn.addEventListener('click', handleClose);
    }

    // Close on overlay click (remove old listener first)
    const overlayClickHandler = (e) => {
        if (e.target === changelogModal) {
            handleClose();
            changelogModal.removeEventListener('click', overlayClickHandler);
        }
    };
    changelogModal.addEventListener('click', overlayClickHandler);
}

// Initialize custom category code select
function initCategoryCodeSelect() {
    if (!elements.categoryCodeSelect) return;

    const trigger = elements.categoryCodeSelect.querySelector('.custom-select-trigger');
    const searchInput = elements.categoryCodeSearch;

    // Toggle dropdown on trigger click
    if (trigger) {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            elements.categoryCodeSelect.classList.toggle('active');

            // Focus search input when opened
            if (elements.categoryCodeSelect.classList.contains('active') && searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const filterText = e.target.value;
            renderCategoryCodeOptions(filterText);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                elements.categoryCodeSelect.classList.remove('active');
                searchInput.value = '';
                renderCategoryCodeOptions();
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!elements.categoryCodeSelect.contains(e.target)) {
            elements.categoryCodeSelect.classList.remove('active');
            if (searchInput) {
                searchInput.value = '';
                renderCategoryCodeOptions();
            }
        }
    });
}

// Initialize app
async function init() {
    debugLog('=== CS Taxif Panel Initializing ===');

    // Set version number from manifest
    setVersionNumber();

    initElements();
    debugLog('Elements initialized');

    // Event listeners
    elements.loginForm.addEventListener('submit', handleLogin);
    elements.logoutBtn.addEventListener('click', handleLogout);
    elements.pasteBtn.addEventListener('click', handlePaste);
    if (elements.pastePhoneBtn) {
        elements.pastePhoneBtn.addEventListener('click', handlePastePhone);
    }
    elements.searchTicketBtn.addEventListener('click', handleSearchTicket);
    elements.platformSelect.addEventListener('change', handlePlatformChange);
    elements.vipSwitch.addEventListener('change', handleVipChange);
    elements.ticketForm.addEventListener('submit', handleCreateTicket);
    elements.clearFormBtn.addEventListener('click', clearForm);
    elements.closeBtn.addEventListener('click', handleClose);

    // Custom select event listeners
    initCategoryCodeSelect();

    // Additional buttons event listeners
    const backToSearchBtn = document.getElementById('backToSearchBtn');
    if (backToSearchBtn) {
        backToSearchBtn.addEventListener('click', handleBackToSearch);
    }

    const cancelEditBtn = document.getElementById('cancelEditBtn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', handleCancelEdit);
    }

    // Report event listeners
    if (elements.openReportBtn) {
        elements.openReportBtn.addEventListener('click', handleOpenReport);
    }
    if (elements.reportCloseBtn) {
        elements.reportCloseBtn.addEventListener('click', handleCloseReport);
    }
    if (elements.reportCancelBtn) {
        elements.reportCancelBtn.addEventListener('click', handleCloseReport);
    }
    if (elements.reportForm) {
        elements.reportForm.addEventListener('submit', handleSubmitReport);
    }

    // Support Enter key on ticket search
    elements.ticketNumberInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSearchTicket();
        }
    });

    // Check authentication status
    await checkAuth();
}

// Start app when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
