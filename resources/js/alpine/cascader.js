export default () => ({
    get panel() { return this.$el.querySelector('[data-slot="cascader-content"]') },
    get trigger() { return this.$el.querySelector('[data-cascader-trigger]') },
    items(column) { return [...(column?.querySelectorAll('[data-cascader-item]:not([aria-disabled="true"])') ?? [])] },
    toggled(event) {
        if (event.target !== this.panel) return
        this.trigger.setAttribute('aria-expanded', String(event.newState === 'open'))
        if (event.newState === 'open') this.$nextTick(() => (this.panel.querySelector('[aria-selected="true"]') ?? this.items(this.panel.firstElementChild)[0])?.focus())
    },
    picked({ leaf, depth }) {
        this.$nextTick(() => {
            if (leaf) { this.panel.hidePopover(); this.trigger.focus() }
            else this.items(this.panel.children[depth + 1])[0]?.focus()
        })
    },
    key(event) {
        const item = event.target.closest('[data-cascader-item]')
        if (!item) {
            if (event.target === this.trigger && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault(); this.panel.showPopover({ source: this.trigger })
            }
            return
        }
        const items = this.items(item.parentElement)
        const index = items.indexOf(item)
        const depth = Number(item.dataset.depth)
        let target
        if (event.key === 'ArrowDown') target = items[(index + 1) % items.length]
        else if (event.key === 'ArrowUp') target = items.at(index - 1)
        else if (event.key === 'Home') target = items[0]
        else if (event.key === 'End') target = items.at(-1)
        else if (event.key === 'ArrowLeft') target = this.panel.children[depth - 1]?.querySelector('[data-expanded]')
        else if (['Enter', ' '].includes(event.key) || event.key === 'ArrowRight' && item.hasAttribute('aria-haspopup')) { event.preventDefault(); item.click(); return }
        if (target) { event.preventDefault(); target.focus() }
    },
})
