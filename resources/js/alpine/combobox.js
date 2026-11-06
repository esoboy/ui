export default () => ({
    stopMorph: null,
    get input() { return this.$el.querySelector('[data-combobox-input]') },
    get panel() { return this.$el.querySelector('[data-slot="combobox-content"]') },
    get items() { return [...this.$el.querySelectorAll('[data-slot="combobox-item"]:not([aria-disabled="true"])')] },
    init() {
        this.refresh()
        this.stopMorph = window.Livewire.hook('morphed', ({ el }) => {
            if (el !== this.$el) return
            this.refresh()
            if (document.activeElement === this.input) this.open()
        })
    },
    destroy() { this.stopMorph?.() },
    refresh() {
        for (const [index, item] of this.items.entries()) item.id ||= this.$id('combobox-item', index)
        const clear = this.$el.querySelector('[data-combobox-clear]')
        if (clear) clear.hidden = !this.$wire.value?.length
        if (this.$el.hasAttribute('data-autocomplete') && document.activeElement === this.input) {
            this.items.length ? this.open() : this.close()
        }
    },
    open() {
        if (this.input?.disabled || this.panel.matches(':popover-open') || this.$el.hasAttribute('data-autocomplete') && !this.items.length) return
        this.panel.showPopover({ source: this.$el.querySelector('[data-combobox-anchor], [data-combobox-trigger]') ?? this.input })
        this.input?.setAttribute('aria-expanded', 'true')
        this.panel.id ||= this.$id('combobox-list')
        this.input?.setAttribute('aria-controls', this.panel.id)
    },
    close() {
        if (this.panel.matches(':popover-open')) this.panel.hidePopover()
        this.input?.setAttribute('aria-expanded', 'false')
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
    hover(event) { const item = event.target.closest('[data-slot="combobox-item"]'); if (this.items.includes(item)) this.highlight(item) },
    key(event) {
        if (event.isComposing) return
        const items = this.items
        const index = items.findIndex(item => item.dataset.highlighted === 'true')
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault(); this.open(); this.highlight(items[Math.max(0, Math.min(index + (event.key === 'ArrowDown' ? 1 : -1), items.length - 1))])
        } else if (event.key === 'Enter' && this.panel.matches(':popover-open') && items[index]) { event.preventDefault(); items[index].click() }
        else if (event.key === 'Escape' || event.key === 'Tab') this.close()
        else if (event.key === 'Backspace' && this.$el.hasAttribute('data-multiple') && !this.input.value) this.$el.querySelector('[data-slot="combobox-chip"]:last-of-type [data-combobox-chip-remove]')?.click()
    },
})
