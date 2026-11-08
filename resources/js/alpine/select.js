import { emit, notifyModel } from './interaction.js'

const ITEM = '[data-slot="select-item"]'

export default () => ({
    typed: '',
    typedAt: 0,
    positioning: null,

    notifyModel() { notifyModel(this) },
    blurModel() { emit(this.$el, 'blur') },

    get trigger() { return this.$el.querySelector('[data-slot="select-trigger"]') },
    get content() { return this.$el.querySelector('[data-slot="select-content"]') },
    get items() { return [...this.$el.querySelectorAll(ITEM)] },
    get enabled() { return this.items.filter(item => item.getAttribute('aria-disabled') !== 'true') },
    get selected() { return this.items.find(item => item.getAttribute('aria-selected') === 'true') },

    choose(item) {
        if (! item || item.getAttribute('aria-disabled') === 'true') return
        if (this.trigger.disabled) return
        if (this.content.matches(':popover-open')) this.content.hidePopover()
        this.trigger.focus()
        if (item !== this.selected) this.$wire.choose(item.dataset.value)
    },

    click(event) {
        const item = event.target.closest?.(ITEM)
        if (item && this.content?.contains(item)) this.choose(item)
    },

    toggle(event) {
        if (event.target !== this.content) return

        const open = event.newState === 'open'
        this.trigger?.setAttribute('aria-expanded', String(open))
        this.positioning?.disconnect()
        this.positioning = null

        if (! open) return
        if (this.content.dataset.alignTrigger === 'true') {
            this.positioning = new ResizeObserver(() => this.align())
            this.positioning.observe(this.trigger)
            this.align()
        }

        ;(this.selected ?? this.enabled[0])?.focus({ preventScroll: true })
    },

    align(event) {
        if (! this.content?.matches(':popover-open') || this.content.dataset.alignTrigger !== 'true') return
        if (event?.type === 'scroll' && this.content.contains(event.target)) return

        const content = this.content
        const trigger = this.trigger.getBoundingClientRect()
        const item = this.selected ?? this.enabled[0]
        const value = this.$el.querySelector('[data-slot="select-value"]')?.getBoundingClientRect() ?? trigger

        Object.assign(content.style, { positionArea: 'none', positionTryFallbacks: 'none', inset: 'auto', margin: '0', width: `${trigger.width}px` })
        let left = trigger.left + 1
        let top = trigger.top

        if (item) {
            const box = content.getBoundingClientRect()
            const itemBox = item.getBoundingClientRect()
            const text = item.querySelector('[data-slot="select-item-text"]')?.getBoundingClientRect() ?? itemBox
            left = value.left - (text.left - box.left)
            top = trigger.top + trigger.height / 2 - (itemBox.top - box.top + content.scrollTop + itemBox.height / 2)
        }

        const alignedTop = top
        top = Math.max(10, Math.min(top, window.innerHeight - content.offsetHeight - 10))
        left = Math.max(10, Math.min(left, window.innerWidth - content.offsetWidth - 10))
        Object.assign(content.style, { top: `${top}px`, left: `${left}px` })
        content.scrollTop = Math.max(0, top - alignedTop)
    },

    key(event) {
        if (event.target === this.trigger && ['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
            event.preventDefault()
            this.content.showPopover({ source: this.trigger })
            return
        }
        if (! this.content?.contains(event.target)) return

        const items = this.enabled
        const index = items.indexOf(event.target)
        const position = { ArrowDown: Math.min(index + 1, items.length - 1), ArrowUp: Math.max(index - 1, 0), Home: 0, End: items.length - 1 }[event.key]

        if (position !== undefined) {
            event.preventDefault()
            items[position]?.focus()
        } else if (['Enter', ' '].includes(event.key)) {
            event.preventDefault()
            this.choose(event.target.closest(ITEM))
        } else if (event.key === 'Tab') {
            this.content.hidePopover()
        } else if (event.key.length === 1 && ! event.ctrlKey && ! event.metaKey && ! event.altKey) {
            const now = Date.now()
            this.typed = (now - this.typedAt > 500 ? '' : this.typed) + event.key.toLowerCase()
            this.typedAt = now
            items.find(item => item.textContent.trim().toLowerCase().startsWith(this.typed))?.focus()
        }
    },

    destroy() { this.positioning?.disconnect() },
})
