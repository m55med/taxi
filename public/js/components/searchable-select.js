function searchableSelect(options) {
    return {
        open: false,
        searchTerm: '',
        options: [],
        selected: null,
        modelName: '',
        placeholder: 'Select an option...',

        init() {
            this.options = Array.isArray(options) ? options : [];
            this.modelName = this.$el.dataset.modelName;
            this.placeholder = this.$el.dataset.placeholder;
        },

        get selectedLabel() {
            if (this.selected) {
                return this.selected.name;
            }
            return this.placeholder;
        },

        get filteredOptions() {
            if (this.searchTerm === '') {
                return this.options;
            }
            return this.options.filter(option => {
                return option.name.toLowerCase().includes(this.searchTerm.toLowerCase());
            });
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.search.focus());
            }
        },

        selectOption(option) {
            if (this.selected && this.selected.id === option.id) {
                this.selected = null;
            } else {
                this.selected = option;
            }
            this.open = false;
            
            this.$dispatch('option-selected', {
                value: this.selected ? this.selected.id : null,
                model: this.modelName
            });
        },
        
        reset() {
            this.selected = null;
            this.searchTerm = '';
        },
        
        updateOptions(newOptions) {
            this.options = Array.isArray(newOptions) ? newOptions : [];
            this.reset();
        }
    };
} 