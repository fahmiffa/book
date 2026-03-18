export default (config = {}) => ({
    open: false,
    search: '',
    items: config.items || [],
    
    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        }
    },
    
    close() {
        this.open = false;
        this.search = '';
    },
    
    select(property, value) {
        this.$wire.set(property, value);
        this.close();
    },

    get filteredItems() {
        if (!this.search) return this.items;
        return this.items.filter(item => 
            item.name.toLowerCase().includes(this.search.toLowerCase())
        );
    }
});
