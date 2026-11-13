import boundValue from './value.js'

export default binding => Object.defineProperties(boundValue(binding), Object.getOwnPropertyDescriptors({
    refresh() {
        this.value
        const root = this.$id('tabs')
        for (const trigger of this.$el.querySelectorAll('[data-slot="tabs-trigger"]')) {
            if (trigger.closest('[data-slot="tabs"]') !== this.$el) continue
            const panel = [...this.$el.querySelectorAll('[data-slot="tabs-content"]')].find(panel => panel.dataset.value === trigger.dataset.value && panel.closest('[data-slot="tabs"]') === this.$el)
            trigger.id ||= `${root}-tab-${trigger.dataset.value}`
            if (panel) {
                panel.id ||= `${root}-panel-${trigger.dataset.value}`
                trigger.setAttribute('aria-controls', panel.id)
                panel.setAttribute('aria-labelledby', trigger.id)
            }
        }
    },
    click(event) {
        const trigger = event.target.closest?.('[data-slot="tabs-trigger"]')
        if (! trigger || trigger.closest('[data-slot="tabs"]') !== this.$el || trigger.disabled) return
        this.value = trigger.dataset.value
        this.changed()
    },

    key(event) {
        const trigger = event.target.closest?.('[data-slot="tabs-trigger"]')
        if (! trigger || trigger.closest('[data-slot="tabs"]') !== this.$el) return
        const triggers = [...this.$el.querySelectorAll('[data-slot="tabs-trigger"]')].filter(item => ! item.disabled && item.closest('[data-slot="tabs"]') === this.$el)
        const index = triggers.indexOf(trigger)
        const vertical = this.$el.dataset.orientation === 'vertical'
        const rtl = getComputedStyle(this.$el).direction === 'rtl'
        const step = { [vertical ? 'ArrowDown' : rtl ? 'ArrowLeft' : 'ArrowRight']: 1, [vertical ? 'ArrowUp' : rtl ? 'ArrowRight' : 'ArrowLeft']: -1 }[event.key]
        const next = step ? triggers[(index + step + triggers.length) % triggers.length] : event.key === 'Home' ? triggers[0] : event.key === 'End' ? triggers.at(-1) : null
        if (next) { event.preventDefault(); next.focus() }
    },
}))
