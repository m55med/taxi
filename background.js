// Background service worker for CS Taxif Extension

// Create context menu on installation
chrome.runtime.onInstalled.addListener(() => {
  chrome.contextMenus.create({
    id: 'searchTicket',
    title: 'البحث عن التذكرة في CS Taxif',
    contexts: ['selection']
  });
  
  console.log('CS Taxif Extension installed successfully');
});

// Handle context menu clicks
chrome.contextMenus.onClicked.addListener((info, tab) => {
  if (info.menuItemId === 'searchTicket' && info.selectionText) {
    // Extract ticket number from selection (remove any non-numeric characters)
    const ticketNumber = info.selectionText.trim().replace(/\D/g, '');
    
    // Send message to content script to open panel with ticket number
    chrome.tabs.sendMessage(tab.id, {
      action: 'openPanelWithTicket',
      ticketNumber: ticketNumber
    });
  }
});

// Handle messages from content scripts
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  if (request.action === 'getToken') {
    // Retrieve token from storage
    chrome.storage.local.get(['token'], (result) => {
      sendResponse({ token: result.token || null });
    });
    return true; // Keep channel open for async response
  }
});
