export function accordion(binding = {}, multiple = false) {
    return {
        get items() { return [...this.$el.querySelectorAll('[data-slot="accordion-item"]')].filter(item => item.closest('[data-slot="accordion"]') === this.$el) },
        sync() {
            if (!binding.property) return
            const selected = [].concat(this.$wire.$get(binding.property) ?? []).map(String)
            for (const item of this.items) item.open = selected.includes(item.dataset.value)
        },
        click(event) {
            const summary = event.target.closest('summary')
            const item = summary?.parentElement
            if (!binding.property || event.defaultPrevented || !this.items.includes(item) || summary.getAttribute('aria-disabled') === 'true') return
            event.preventDefault()
            const opened = [].concat(this.$wire.$get(binding.property) ?? []).map(String)
            const selected = opened.includes(item.dataset.value)
            const value = multiple ? (selected ? opened.filter(value => value !== item.dataset.value) : [...opened, item.dataset.value]) : selected ? null : item.dataset.value
            this.$wire.$set(binding.property, value, binding.live)
            this.sync()
        },
        key(event) {
            const trigger = event.target.closest?.('[data-slot="accordion-trigger"]')
            if (! trigger || trigger.closest('[data-slot="accordion"]') !== this.$el) return
            const triggers = [...this.$el.querySelectorAll('[data-slot="accordion-trigger"]')].filter(item => item.getAttribute('aria-disabled') !== 'true' && item.closest('[data-slot="accordion"]') === this.$el)
            const index = triggers.indexOf(trigger)
            const next = { ArrowDown: triggers[(index + 1) % triggers.length], ArrowUp: triggers.at(index - 1), Home: triggers[0], End: triggers.at(-1) }[event.key]
            if (next) { event.preventDefault(); next.focus() }
        },
    }
}

export function collapsible(binding, initial = false) {
    return {
        localOpen: initial,
        get open() { return binding.property ? Boolean(this.$wire.$get(binding.property)) : this.localOpen },
        refresh() {
            const open = this.open
            const owner = '[data-slot="collapsible"], [data-slot="trace-collapsible"]'
            const panels = [...this.$el.querySelectorAll('[data-slot="collapsible-content"], [data-slot="trace-collapsible-content"]')].filter(panel => panel.closest(owner) === this.$el)
            panels.forEach((panel, index) => { panel.id ||= `${this.$id('collapsible')}-${index}` })
            for (const trigger of this.$el.querySelectorAll('[data-slot="collapsible-trigger"], [data-collapsible-trigger]')) {
                if (trigger.closest(owner) !== this.$el) continue
                trigger.toggleAttribute('data-panel-open', open)
                trigger.setAttribute('aria-expanded', String(open))
                if (panels[0]) trigger.setAttribute('aria-controls', panels[0].id)
            }
        },
        toggle(event) {
            const trigger = event.target.closest?.('[data-slot="collapsible-trigger"], [data-collapsible-trigger]')
            if (! trigger || trigger.closest('[data-slot="collapsible"], [data-slot="trace-collapsible"]') !== this.$el || trigger.disabled) return
            if (binding.property) this.$wire.$set(binding.property, ! this.open, binding.live)
            else this.localOpen = ! this.open
            this.$dispatch('input')
            this.$dispatch('change')
        },
    }
}
