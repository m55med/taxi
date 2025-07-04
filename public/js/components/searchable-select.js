function searchableSelect(options) {
    return {
        open: false,
        searchTerm: '',
        selected: null,
        options: options,
        modelName: '',
        placeholder: 'Select an option', // Default placeholder

        init() {
            this.modelName = this.$el.dataset.modelName;
            const initialValue = this.$el.dataset.initialValue;
            
            if (this.$el.dataset.placeholder) {
                this.placeholder = this.$el.dataset.placeholder;
            }

            if (initialValue) {
                this.selected = this.options.find(opt => opt.id == initialValue) || null;
            }
            
            this.$watch('searchTerm', () => {
                if (this.open && this.searchTerm) {
                    this.$refs.search.focus();
                }
            });
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    if (this.$refs.search) {
                        this.$refs.search.focus();
                    }
                });
            }
        },

        selectOption(option) {
            this.selected = option;
            this.open = false;
            this.$dispatch('option-selected', {
                model: this.modelName,
                value: option.id,
            });
        },

        get filteredOptions() {
            if (!this.options) return [];
            if (this.searchTerm === '') {
                return this.options;
            }
            return this.options.filter(option => {
                return option.name.toLowerCase().includes(this.searchTerm.toLowerCase());
            });
        },

        get selectedLabel() {
            if (this.selected) {
                return this.selected.name;
            }
            return this.placeholder;
        },

        updateOptions(newOptions) {
            this.options = newOptions;
            this.reset();
        },
        
        reset() {
            this.selected = null;
            this.searchTerm = '';
            this.open = false;
        }
    };
} 