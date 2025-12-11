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

    // Initialize extension UI
    function init() {
        // Avoid injecting into frames
        if (window.self !== window.top) return;

        createToggleButton();
        createPanel();

        console.log('CS Taxif Extension initialized');
    }

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
