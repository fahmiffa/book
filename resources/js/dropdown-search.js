export default () => ({
    open: false,
    search: '',
    
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
    }
});
