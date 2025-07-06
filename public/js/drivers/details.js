function driverSearch() {
    return {
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
            this.isOpen = true;
            fetch(`${URLROOT}/drivers/search?q=${this.query}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                    this.isLoading = false;
                    this.highlightedIndex = -1;
                })
                .catch(() => {
                    this.isLoading = false;
                    this.results = [];
                });
        },
        highlightNext() {
            if (this.highlightedIndex < this.results.length - 1) {
                this.highlightedIndex++;
            }
        },
        highlightPrev() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        selectHighlighted() {
            if (this.highlightedIndex > -1) {
                window.location.href = `${URLROOT}/drivers/details/${this.results[this.highlightedIndex].id}`;
            }
        }
    };
} 