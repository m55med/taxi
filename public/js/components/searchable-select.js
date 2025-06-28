function searchableSelect(options) {
    return {
        open: false,
        searchTerm: '',
        selected: null,
        selectedLabel: 'Select an option',
        options: options,
        modelName: '',

        init() {
            this.modelName = this.$el.dataset.modelName;

            // If a value is already set in the form data, reflect it.
            let initialValue = this.$el.dataset.initialValue;
            if (initialValue) {
                this.selected = this.options.find(opt => opt.id == initialValue) || null;
                if (this.selected) {
                    this.selectedLabel = this.selected.name;
                }
            }
            
            this.$watch('selected', (newValue) => {
                this.selectedLabel = newValue ? newValue.name : `Select a ${this.modelName.replace('_id', '')}`;
                // Dispatch an event so the main form component can update its formData
                this.$dispatch('option-selected', { 
                    model: this.modelName, 
                    value: newValue ? newValue.id : '' 
                });
            });
        },

        get filteredOptions() {
            if (this.searchTerm.trim() === '') {
                return this.options;
            }
            return this.options.filter(option => {
                return option.name.toLowerCase().includes(this.searchTerm.toLowerCase());
            });
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.search.focus();
                });
            }
        },

        selectOption(option) {
            this.selected = option;
            this.open = false;
            this.searchTerm = '';
        }
    };
} 