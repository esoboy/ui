import { listen, disconnected } from './interaction.js'

export default () => ({
    _target: null,
    _returnFocus: null,
    _step: '',
    _stopMorph: null,

    init() {
        const host = this.$el.closest('eso-tour')
        listen(this, host, 'command', event => {
            if (event.command === '--start') this.$wire.start(0)
            if (event.command === '--close') this.$wire.close()
        })
        this._stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
            if (el === this.$el || this.isOpen) this.refresh()
        })
        this.refresh()
    },

    destroy() {
        this._stopMorph?.()
        disconnected(this)
        this._point(null)
    },

    requested(detail) {
        if (detail.name !== this.$el.closest('eso-tour')?.id) return
        detail.open ? this.$wire.start(detail.step ?? 0) : this.$wire.close()
    },

    escape(event) {
        if (event.defaultPrevented || !this.isOpen || document.querySelector('[popover]:not([popover="manual"]):popover-open, dialog:modal')) return
        event.preventDefault()
        this.$wire.close()
    },

    get content() { return this.$el.querySelector('[data-slot="tour-content"]') },
    get spotlight() { return this.$el.querySelector('[data-slot="tour-spotlight"]') },
    get isOpen() { return this.content?.matches(':popover-open') ?? false },
    get anchor() { return this.$el.dataset.tourAnchor },

    refresh() {
        const step = this.$el.dataset.step
        const content = this.content
        if (!content || !step) return this.hide()

        const changed = this._step !== step
        this._step = step
        let target = null
        if (content.dataset.target) {
            try { target = document.querySelector(content.dataset.target) } catch {}
        }
        this._point(target, { scroll: changed })
        if (target) {
            content.dataset.side = content.dataset.stepSide
            content.dataset.align = content.dataset.stepAlign
        } else {
            delete content.dataset.side
            delete content.dataset.align
        }
        this.spotlight?.toggleAttribute('data-empty', !target)

        if (!this.isOpen) {
            this._returnFocus = document.activeElement
            this.spotlight?.showPopover()
            content.showPopover()
        }
        if (changed) content.querySelector('[data-tour-action="next"], [data-tour-action="finish"]')?.focus({ preventScroll: true })
    },

    hide() {
        this._point(null)
        if (this.isOpen) this.content.hidePopover()
        if (this.spotlight?.matches(':popover-open')) this.spotlight.hidePopover()
        this._returnFocus?.focus?.({ preventScroll: true })
        this._returnFocus = null
        this._step = ''
    },

    // The target keeps its own anchor names (a Popover trigger's, say) beside ours.
    _point(target, { scroll = false } = {}) {
        if (this._target && this._target !== target) {
            const names = this._names(this._target)
            this._target.style.setProperty('anchor-name', names.join(', ') || null)
            this._target.toggleAttribute('data-tour-target', names.some(name => name.startsWith('--ui-tour-')))
        }

        this._target = target

        if (! target) return

        target.style.setProperty('anchor-name', [...this._names(target), this.anchor].join(', '))
        target.setAttribute('data-tour-target', '')

        // At once, so the panel picks its place with the target in view (as Chrome keeps the place that fit last).
        if (scroll) target.scrollIntoView({ block: 'center' })
    },

    _names(element) {
        return (element.style.getPropertyValue('anchor-name') || '').split(',').map(name => name.trim()).filter(name => name && name !== 'none' && name !== this.anchor)
    },

})
