document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ticket-search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');
    let debounceTimer;

    if (!searchInput || !suggestionsContainer) {
        return; // Exit if the required elements aren't on this page
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const term = searchInput.value.trim();

            if (term.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.classList.add('hidden');
                return;
            }

            try {
                // The BASE_URL variable must be available globally in the HTML page.
                const response = await fetch(`${BASE_URL}/tickets/ajaxSearch?term=${encodeURIComponent(term)}`);
                const suggestions = await response.json();

                suggestionsContainer.innerHTML = '';
                if (suggestions.length > 0) {
                    suggestions.forEach(s => {
                        const item = document.createElement('a');
                        item.href = `${BASE_URL}/tickets/view/${s.id}`;
                        item.className = 'block px-4 py-3 hover:bg-gray-100 border-b last:border-b-0';
                        
                        const ticketNumber = document.createElement('span');
                        ticketNumber.className = 'font-semibold text-gray-800';
                        ticketNumber.textContent = s.ticket_number;
                        
                        const phone = document.createElement('span');
                        phone.className = 'text-sm text-gray-500 ml-2';
                        phone.textContent = `(${s.phone || 'No phone'})`;

                        item.appendChild(ticketNumber);
                        item.appendChild(phone);
                        suggestionsContainer.appendChild(item);
                    });
                    suggestionsContainer.classList.remove('hidden');
                } else {
                    suggestionsContainer.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error fetching search suggestions:', error);
                suggestionsContainer.classList.add('hidden');
            }
        }, 300); // 300ms debounce delay
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        // Check if the click is outside the search input and the suggestions container
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.classList.add('hidden');
        }
    });
}); 