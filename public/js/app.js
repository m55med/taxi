document.addEventListener('alpine:init', () => {
    // Start the global user activity heartbeat
    setInterval(() => {
        fetch('/taxi/api/heartbeat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).catch(error => console.error('Heartbeat failed:', error));
    }, 60000); // Send heartbeat every 60 seconds

    // Component for the driver search bar
    Alpine.data('driverSearch', () => ({
        query: '',
        results: [],
        isOpen: false,
        isLoading: false,
        highlightedIndex: -1,
        search() {
            if (this.query.length < 2) {
                this.results = [];
                this.isOpen = false;
                return;
            }
            this.isLoading = true;
            fetch(`/taxi/drivers/search?phone=${this.query}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                    this.isLoading = false;
                    this.isOpen = true;
                });
        },
        highlightNext() { if (this.highlightedIndex < this.results.length - 1) { this.highlightedIndex++; } },
        highlightPrev() { if (this.highlightedIndex > 0) { this.highlightedIndex--; } },
        selectHighlighted() {
            if (this.highlightedIndex > -1) {
                window.location.href = `/taxi/drivers/details/${this.results[this.highlightedIndex].id}`;
            }
        }
    }));
}); 