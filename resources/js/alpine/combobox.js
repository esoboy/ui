export default () => ({
    wantsOpen: false,
    stopMorph: null,
    get input() { return this.$el.querySelector('[data-combobox-input]') },
    get panel() { return this.$el.querySelector('[data-slot="combobox-content"]') },
    get items() { return [...this.$el.querySelectorAll('[data-slot="combobox-item"]:not([aria-disabled="true"])')] },
    get pending() { return !!this.input && this.input.value !== this.$el.dataset.query },
    init() {
        this.refresh()
        this.stopMorph = window.Livewire.hook('morphed', ({ el }) => {
            if (el !== this.$el) return
            this.refresh()
        })
    },
    destroy() { this.stopMorph?.() },
    refresh() {
        for (const [index, item] of this.items.entries()) item.id ||= this.$id('combobox-item', index)
        const clear = this.$el.querySelector('[data-combobox-clear]')
        if (clear) clear.hidden = !this.$wire.value?.length
        if (this.pending) this.highlight(null)
        this.syncPopover()
    },
    inputChanged() {
        this.highlight(null)
        this.open()
    },
    guardChoice(event) {
        if (!this.pending || !event.target.closest('[data-slot="combobox-item"]')) return
        event.preventDefault()
        event.stopImmediatePropagation()
    },
    open() {
        this.wantsOpen = true
        this.syncPopover()
    },
    close() {
        this.wantsOpen = false
        this.syncPopover()
    },
    syncPopover() {
        const { input, panel } = this
        panel.setAttribute('aria-busy', String(this.pending))
        const trigger = this.$el.querySelector('[data-combobox-trigger]')
        // Suggestions can arrive after the input event or after the user has dismissed the list.
        const show = this.wantsOpen && !input?.disabled && (!this.$el.hasAttribute('data-autocomplete') || this.items.length > 0)

        if (show && !panel.matches(':popover-open')) {
            panel.showPopover({ source: this.$el.querySelector('[data-combobox-anchor], [data-combobox-trigger]') ?? input })
        } else if (!show && panel.matches(':popover-open')) {
            panel.hidePopover()
        }

        panel.id ||= this.$id('combobox-list')
        for (const control of [input, trigger]) {
            control?.setAttribute('aria-expanded', String(show))
            control?.setAttribute('aria-controls', panel.id)
        }
    },
    outside(event) {
        if (this.wantsOpen && !this.$el.contains(event.target)) this.close()
    },
    click(event) {
        if (event.target.closest('[data-combobox-clear]')) { this.$wire.clear(); this.input?.focus(); return }
        if (event.target.closest('[data-combobox-trigger]')) { this.panel.matches(':popover-open') ? this.close() : this.open(); this.input?.focus(); return }
        if (event.target === this.input) this.open()
    },
    picked({ close }) { if (close) this.close(); this.input?.focus() },
    highlight(target) {
        for (const item of this.items) item.dataset.highlighted = String(item === target)
        if (target) { this.input?.setAttribute('aria-activedescendant', target.id); target.scrollIntoView({ block: 'nearest' }) }
        else this.input?.removeAttribute('aria-activedescendant')
    },
    hover(event) { const item = event.target.closest('[data-slot="combobox-item"]'); if (!this.pending && this.items.includes(item)) this.highlight(item) },
    key(event) {
        if (event.isComposing) return
        if (this.pending && ['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) {
            event.preventDefault()
            this.highlight(null)
            return
        }
        const items = this.items
        const index = items.findIndex(item => item.dataset.highlighted === 'true')
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault(); this.open(); this.highlight(items[Math.max(0, Math.min(index + (event.key === 'ArrowDown' ? 1 : -1), items.length - 1))])
        } else if (event.key === 'Enter' && this.panel.matches(':popover-open') && items[index]) { event.preventDefault(); items[index].click() }
        else if (event.key === 'Escape' || event.key === 'Tab') this.close()
        else if (event.key === 'Backspace' && this.$el.hasAttribute('data-multiple') && !this.input.value) this.$el.querySelector('[data-slot="combobox-chip"]:last-of-type [data-combobox-chip-remove]')?.click()
    },
})
