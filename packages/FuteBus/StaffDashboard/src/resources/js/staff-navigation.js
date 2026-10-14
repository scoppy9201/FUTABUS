window.staffNavigation = () => ({
    init() {
        this.syncViewport();
    },
    syncViewport() {
        const desktop = window.matchMedia('(min-width: 1024px)').matches;
        if (desktop && this.$el.matches(':modal')) this.$el.close();
        if (desktop && !this.$el.open) {
            const focusedElement = document.activeElement;
            this.$el.show();
            if (focusedElement === document.body) document.activeElement?.blur();
            else focusedElement?.focus({ preventScroll: true });
        }
        if (!desktop && this.$el.open && !this.$el.matches(':modal')) this.$el.close();
        this.syncExpanded();
    },
    toggle() {
        if (window.matchMedia('(min-width: 1024px)').matches) return;
        if (this.$el.open) this.close();
        else {
            this.$el.showModal();
            this.syncExpanded();
        }
    },
    close() {
        if (!this.$el.matches(':modal')) return;
        this.$el.close();
        this.syncExpanded();
    },
    syncExpanded() {
        const modal = this.$el.matches(':modal');
        document.querySelector('[data-staff-menu-toggle]')?.setAttribute('aria-expanded', String(modal));
        document.body.classList.toggle('staff-navigation-open', modal);
    },
    destroy() {
        document.body.classList.remove('staff-navigation-open');
    },
});
