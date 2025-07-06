document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="phone"]');
    const searchForm = searchInput.closest('form');
    let resultsContainer;
    let debounceTimer;

    // Create a container for search results
    function createResultsContainer() {
        resultsContainer = document.createElement('div');
        resultsContainer.className = 'absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto';
        searchInput.parentNode.style.position = 'relative'; // Ensure parent is positioned
        searchInput.parentNode.appendChild(resultsContainer);
    }
    createResultsContainer();

    // Debounce function to limit API calls
    const debounce = (callback, time) => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(callback, time);
    };

    // Fetch and display search results
    const fetchResults = async () => {
        const query = searchInput.value;

        if (query.length < 3) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`${URLROOT}/drivers/search?q=${query}`);
            if (!response.ok) throw new Error('Network response was not ok.');
            
            const drivers = await response.json();
            
            if (drivers.length > 0) {
                resultsContainer.innerHTML = drivers.map(driver => {
                    if (driver.hold == 1 && driver.held_by_username) {
                        // Driver is on hold, show special message
                        return `
                            <div class="p-3 bg-red-100 cursor-not-allowed border-b border-gray-200">
                                <p class="font-semibold text-red-800">${driver.name} - ${driver.phone}</p>
                                <p class="text-sm text-red-600">محجوز حاليًا بواسطة: ${driver.held_by_username}</p>
                            </div>
                        `;
                    } else {
                        // Driver is available
                        return `
                            <div class="p-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100" data-phone="${driver.phone}">
                                <p class="font-semibold text-gray-800">${driver.name}</p>
                                <p class="text-sm text-gray-500">${driver.phone}</p>
                            </div>
                        `;
                    }
                }).join('');
                resultsContainer.classList.remove('hidden');
            } else {
                resultsContainer.innerHTML = '<p class="p-3 text-gray-500">No drivers found.</p>';
                resultsContainer.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching search results:', error);
            resultsContainer.innerHTML = '<p class="p-3 text-red-500">Error loading results.</p>';
            resultsContainer.classList.remove('hidden');
        }
    };

    // Event listener for input
    searchInput.addEventListener('input', () => {
        debounce(fetchResults, 300); // 300ms delay
    });

    // Event listener for clicks on results
    resultsContainer.addEventListener('click', (e) => {
        const resultItem = e.target.closest('[data-phone]');
        if (resultItem) {
            searchInput.value = resultItem.dataset.phone;
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            searchForm.submit(); // Automatically submit the form
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
     // Show results when focusing on the input again if there is text
     searchInput.addEventListener('focus', () => {
        if(searchInput.value.length >= 3){
            fetchResults();
        }
    });
}); 