// Only presentation state lives here. Navigation and active items are rendered by PHP.
export default (options = {}) => ({
    expanded: options.open ?? true,
    mobileOpen: false,
    returnFocus: null,

    get containers() {
        return [...this.$el.querySelectorAll('[data-slot="sidebar-container"]')]
            .filter(element => element.closest('[data-slot="sidebar-wrapper"]') === this.$el)
    },

    get mobile() {
        return window.matchMedia('(width < 48rem)').matches
    },

    click(event) {
        const trigger = event.target.closest?.('[data-sidebar="trigger"], [data-sidebar="rail"]')

        if (trigger?.closest('[data-slot="sidebar-wrapper"]') !== this.$el) return
        if (trigger.matches(':disabled, [aria-disabled="true"]')) return
        this.toggle()
    },

    shortcut(event) {
        if (event.defaultPrevented || event.key.toLowerCase() !== 'b' || ! (event.metaKey || event.ctrlKey) || event.altKey || event.shiftKey) return
        if (event.target.closest?.('input, textarea, [contenteditable="true"]')) return

        const owner = event.target.closest?.('[data-slot="sidebar-wrapper"]')

        if (owner ? owner !== this.$el : document.querySelector('[data-slot="sidebar-wrapper"]') !== this.$el) return

        event.preventDefault()
        this.toggle()
    },

    toggle() {
        if (this.mobile) return this.showMobile(! this.mobileOpen)

        this.expanded = ! this.expanded
        document.cookie = `${options.cookie ?? 'sidebar_state'}=${this.expanded}; path=/; max-age=604800; samesite=lax`
    },

    showMobile(open) {
        this.mobileOpen = open

        for (const container of this.containers) {
            if (open && ! container.matches(':popover-open')) {
                this.returnFocus = document.activeElement
                container.showPopover()
                container.querySelector('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')?.focus()
            } else if (! open && container.matches(':popover-open')) {
                container.hidePopover()
            }
        }

        if (! open) {
            this.returnFocus?.focus?.()
            this.returnFocus = null
        }
    },

    outside(event) {
        if (! this.mobileOpen || event.target.closest?.('[data-sidebar="trigger"]')) return
        if (! this.containers.some(container => container.contains(event.target))) this.showMobile(false)
    },

    resize() {
        if (! this.mobile && this.mobileOpen) this.showMobile(false)
    },

    destroy() {
        this.showMobile(false)
    },
})
