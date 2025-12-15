// Content script - Injects CS Taxif panel into pages

(function () {
    'use strict';

    let panelIframe = null;
    let toggleButton = null;
    let isOpen = false;

    // Create toggle button
    function createToggleButton() {
        if (document.getElementById('cs-taxif-extension-toggle-btn')) return;

        toggleButton = document.createElement('div');
        toggleButton.id = 'cs-taxif-extension-toggle-btn';
        toggleButton.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"></polyline>
      </svg>
    `;
        toggleButton.title = 'Open CS Taxif';

        toggleButton.addEventListener('click', togglePanel);
        document.body.appendChild(toggleButton);
    }

    // Create panel iframe
    function createPanel() {
        if (document.getElementById('cs-taxif-extension-iframe-panel')) return;

        panelIframe = document.createElement('iframe');
        panelIframe.id = 'cs-taxif-extension-iframe-panel';
        panelIframe.src = chrome.runtime.getURL('content/panel.html');
        // Styles are handled by content.css class

        document.body.appendChild(panelIframe);

        // Listen for messages from panel
        window.addEventListener('message', handlePanelMessage);
    }

    // Toggle panel visibility
    function togglePanel() {
        isOpen = !isOpen;

        if (isOpen) {
            panelIframe.classList.add('open');
            toggleButton.classList.add('active');
            toggleButton.title = 'Close CS Taxif';
        } else {
            panelIframe.classList.remove('open');
            toggleButton.classList.remove('active');
            toggleButton.title = 'Open CS Taxif';
        }
    }

    // Handle messages from panel iframe
    function handlePanelMessage(event) {
        // Verify message is from our extension
        if (event.source !== panelIframe?.contentWindow) return;

        const { action, data } = event.data;

        switch (action) {
            case 'closePanel':
                if (isOpen) togglePanel();
                break;

            case 'resize':
                // Optional: handle panel resize requests
                break;

            default:
                console.log('Unknown action:', action);
        }
    }

    // Listen for messages from background script
    chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
        if (request.action === 'openPanelWithTicket') {
            // Open panel if closed
            if (!isOpen) {
                togglePanel();
            }

            // Wait a bit for iframe to load, then send ticket number
            setTimeout(() => {
                panelIframe?.contentWindow?.postMessage({
                    action: 'searchTicket',
                    ticketNumber: request.ticketNumber
                }, '*');
            }, 300);

            sendResponse({ success: true });
        }
    });

    // Trengo integration: Add icon next to ticket number
    function initTrengoIntegration() {
        // Check if we're on Trengo
        if (!window.location.hostname.includes('trengo.com')) return;

        // Function to extract ticket number from element
        function extractTicketNumber(element) {
            const text = element?.textContent?.trim() || '';
            // Match pattern like "# 909299631" or "#909299631"
            const match = text.match(/#\s*(\d+)/);
            return match ? match[1] : null;
        }

        // Function to create the icon button
        function createTicketIcon(ticketNumber) {
            const icon = document.createElement('button');
            icon.className = 'cs-taxif-trengo-icon';
            icon.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
            `;
            icon.title = `Search ticket #${ticketNumber} in CS Taxif`;
            icon.setAttribute('data-ticket-number', ticketNumber);
            
            icon.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Open panel if closed
                if (!isOpen) {
                    togglePanel();
                }

                // Wait a bit for iframe to load, then send ticket number
                setTimeout(() => {
                    panelIframe?.contentWindow?.postMessage({
                        action: 'searchTicket',
                        ticketNumber: ticketNumber
                    }, '*');
                }, 300);
            });

            return icon;
        }

        // Function to add icon to ticket number element
        function addIconToTicketElement(ticketElement) {
            // Check if icon already exists in the same container
            const parentContainer = ticketElement.parentElement;
            if (parentContainer?.querySelector('.cs-taxif-trengo-icon')) return;

            const ticketNumber = extractTicketNumber(ticketElement);
            if (!ticketNumber) return;

            // Find the immediate parent container (should be div.flex.items-center.gap-2)
            // If not found, use the closest flex container
            let container = parentContainer;
            if (!container?.classList?.contains('flex')) {
                container = ticketElement.closest('.flex.items-center');
            }
            
            if (!container) {
                // Fallback: insert after the h5 element
                container = ticketElement.parentNode;
            }

            // Create icon
            const icon = createTicketIcon(ticketNumber);
            
            // Insert icon right after the h5 element
            if (ticketElement.nextSibling) {
                container.insertBefore(icon, ticketElement.nextSibling);
            } else {
                container.appendChild(icon);
            }
        }

        // Function to observe and add icons
        function observeTicketNumbers() {
            // Use MutationObserver to watch for dynamic changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            // Check if the added node is the ticket element
                            const ticketElement = node.querySelector?.('[data-test="sidebar-ticket-id"]') || 
                                                  (node.matches?.('[data-test="sidebar-ticket-id"]') ? node : null);
                            
                            if (ticketElement) {
                                addIconToTicketElement(ticketElement);
                            }

                            // Also check for ticket elements inside the added node
                            const ticketElements = node.querySelectorAll?.('[data-test="sidebar-ticket-id"]') || [];
                            ticketElements.forEach(addIconToTicketElement);
                        }
                    });
                });
            });

            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Also check for existing ticket elements
            const checkExisting = () => {
                const ticketElements = document.querySelectorAll('[data-test="sidebar-ticket-id"]');
                ticketElements.forEach(addIconToTicketElement);
            };

            // Check immediately and after a delay (for dynamic content)
            checkExisting();
            setTimeout(checkExisting, 1000);
            setTimeout(checkExisting, 3000);
        }

        // Initialize Trengo integration
        observeTicketNumbers();
        console.log('CS Taxif Trengo integration initialized');
    }

    // Initialize extension UI
    function init() {
        // Avoid injecting into frames
        if (window.self !== window.top) return;

        createToggleButton();
        createPanel();

        // Initialize Trengo integration
        initTrengoIntegration();

        console.log('CS Taxif Extension initialized');
    }

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
