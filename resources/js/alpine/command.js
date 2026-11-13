export default () => {
    // Keep the control's Livewire scope when an input is inside caller-owned content.
    let wire
    return {
        stopMorph: null,
        get searchValue() { return wire.query },
        set searchValue(value) { wire.query = value },
        search(event) { if (event.target === this.input) this.$wire.search(this.input.value) },
        get input() { return this.$el.querySelector('[data-command-input]') },
        get items() { return [...this.$el.querySelectorAll('[data-slot="command-item"]')] },
        get available() { return this.items.filter(item => !item.hidden && item.getAttribute('aria-disabled') !== 'true') },
        init() {
            wire = this.$wire
            this.refresh()
            this.stopMorph = window.Livewire?.hook?.('morphed', ({ el }) => {
                if (el === this.$el || el.contains(this.$el)) this.refresh()
            })
        },
        destroy() { this.stopMorph?.() },
        refresh() {
            const list = this.$el.querySelector('[data-slot="command-list"]')
            if (list && this.input) {
                list.id ||= this.$id('command-list')
                this.input.setAttribute('aria-controls', list.id)
            }
            for (const [index, item] of this.items.entries()) item.id ||= this.$id('command-item', index)
            this.highlight(this.available.find(item => item.dataset.selected === 'true') ?? this.available[0], false)
        },
        highlight(target, scroll = true) {
            for (const item of this.items) { item.dataset.selected = String(item === target); item.setAttribute('aria-selected', String(item === target)) }
            if (target) { this.input?.setAttribute('aria-activedescendant', target.id); if (scroll) target.scrollIntoView({ block: 'nearest' }) }
            else this.input?.removeAttribute('aria-activedescendant')
        },
        hover(event) {
            const item = event.target.closest('[data-slot="command-item"]')
            if (this.available.includes(item)) this.highlight(item)
        },
        async key(event) {
            if (event.isComposing) return
            const query = this.input?.value
            if (event.key === 'Enter' && query !== undefined && this.$el.hasAttribute('data-query') && query !== this.$el.dataset.query) {
                event.preventDefault()
                await this.$wire.search(query)
                if (this.input?.value !== query || this.$el.dataset.query !== query) return
                this.refresh()
            }
            const items = this.available
            const index = items.findIndex(item => item.dataset.selected === 'true')
            let next
            if (event.key === 'ArrowDown') next = Math.min(index + 1, items.length - 1)
            else if (event.key === 'ArrowUp') next = Math.max(0, index - 1)
            else if (event.key === 'Home') next = 0
            else if (event.key === 'End') next = items.length - 1
            else if (event.key === 'Enter') { event.preventDefault(); items[index]?.click(); return }
            if (next !== undefined) { event.preventDefault(); this.highlight(items[next]) }
        },
    }
}
