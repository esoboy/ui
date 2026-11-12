/*
 * <eso-image-preview>
 *
 * A click on a thumbnail opens the viewer at its image; the arrows page through the
 * group's other thumbnails. The image zooms (buttons, wheel, + and -, double click),
 * rotates, and pans by dragging once zoomed; 0 resets it. Esc or a click beside the
 * image closes the viewer.
 */

import { disconnected, listen } from './interaction.js'

const MIN = 0.5
const MAX = 5
const FACTOR = 1.5

export default () => ({
    _drag: null,
    _dragged: null,
    _current: null,
    _stopMorph: null,
    _rotate: null,
    _scale: null,
    _x: null,
    _y: null,
    init() {
        this._scale = 1
        this._rotate = 0
        this._x = 0
        this._y = 0
        this._drag = null
        this._dragged = false

        listen(this, this.$el, 'click', event => {
            const trigger = event.target.closest?.('[data-slot="image-preview-trigger"]')

            if (trigger && trigger.closest('eso-image-preview') === this.$el) return this.open(this._triggers().indexOf(trigger))

            const action = event.target.closest?.('[data-image-preview]')

            if (action && this.dialog.contains(action)) {
                const name = action.dataset.imagePreview
                if (name !== 'previous' && name !== 'next') this._act(name)
                return
            }

            // A click beside the image, but not the end of a drag.
            if (event.target === this.dialog && ! this._dragged) this.dialog.close()
            this._dragged = false
        }, {})

        listen(this, this.$el, 'keydown', event => {
            if (! this.dialog?.contains(event.target)) return

            const action = { ArrowLeft: 'previous', ArrowRight: 'next', '+': 'zoom-in', '=': 'zoom-in', '-': 'zoom-out', 0: 'reset' }[event.key]

            if (action) {
                event.preventDefault()
                this._act(action)
            }
        }, {})

        listen(this, this.$el, 'wheel', event => {
            if (! this.dialog?.open || ! this.dialog.contains(event.target)) return

            event.preventDefault()
            this._zoom(this._scale * Math.exp(-event.deltaY * 0.002))
        }, { passive: false })

        listen(this, this.$el, 'dblclick', event => {
            if (event.target === this.image) this._scale > 1 ? this._reset() : this._zoom(2)
        }, {})

        listen(this, this.$el, 'pointerdown', event => {
            if (event.target !== this.image || this._scale <= 1 || event.button !== 0) return

            event.preventDefault()
            this.image.setPointerCapture?.(event.pointerId)
            this._drag = { pointer: event.pointerId, x: event.clientX - this._x, y: event.clientY - this._y }
            this.image.toggleAttribute('data-dragging', true)
        }, {})

        listen(this, this.$el, 'pointermove', event => {
            if (! this._drag || this._drag.pointer !== event.pointerId) return

            this._x = event.clientX - this._drag.x
            this._y = event.clientY - this._drag.y
            this._dragged = true
            this._apply()
        }, {})

        for (const type of ['pointerup', 'pointercancel']) {
            listen(this, this.$el, type, () => {
                this._drag = null
                this.image?.removeAttribute('data-dragging')
            }, {})
        }

        // load and error don't bubble: listen while they go down.
        const loaded = event => event.target === this.image && this.dialog.removeAttribute('data-loading')

        listen(this, this.$el, 'load', loaded, { capture: true })
        listen(this, this.$el, 'error', loaded, { capture: true })
        this._stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
            if (el === this.viewer) this.sync()
        })

    },
    destroy() {
        this._stopMorph?.()
        disconnected(this)
    },
    get viewer() { return this.$el.querySelector(':scope > [data-image-preview-state]') },
    get wire() { return window.Livewire?.find(this.viewer?.getAttribute('wire:id')) },
    get dialog() { return this.viewer?.querySelector('[data-slot="image-preview-content"]') },

    get image() {
        return this.dialog?.querySelector('[data-slot="image-preview-image"]')
    },

    async open(index) {
        if (index < 0 || !this.wire) return
        await this.wire.select(index)
        if (!this.$el.isConnected || !this.image?.getAttribute('src')) return
        this.sync()
        this._reset()
        if (!this.dialog.open) this.dialog.showModal()
    },

    sync() {
        const source = this.image?.getAttribute('src')
        if (!source) {
            if (this.dialog?.open) this.dialog.close()
            return
        }

        const key = `${this.viewer.dataset.selected}:${source}`
        if (key !== this._current) {
            this._current = key
            this._reset()
        } else {
            this._apply()
        }
        this.dialog.toggleAttribute('data-loading', !this.image.complete)
    },

    _triggers() {
        return [...this.$el.querySelectorAll('[data-slot="image-preview-trigger"]')].filter(trigger => trigger.closest('eso-image-preview') === this.$el)
    },

    _button(action) {
        return this.dialog.querySelector(`[data-image-preview="${action}"]`)
    },

    _act(action) {
        if (action === 'close') this.dialog.close()
        else if (action === 'previous' || action === 'next') this.wire?.[action]()
        else if (action === 'zoom-in') this._zoom(this._scale * FACTOR)
        else if (action === 'zoom-out') this._zoom(this._scale / FACTOR)
        else if (action === 'rotate-left') this._turn(-90)
        else if (action === 'rotate-right') this._turn(90)
        else if (action === 'reset') this._reset()
    },

    _zoom(scale) {
        this._scale = Math.min(MAX, Math.max(MIN, Math.round(scale * 1000) / 1000))

        // Back at its size, the image recenters.
        if (this._scale <= 1) this._x = this._y = 0

        this._apply()
    },

    _turn(degrees) {
        this._rotate += degrees
        this._apply()
    },

    _reset() {
        this._scale = 1
        this._rotate = 0
        this._x = this._y = 0
        this._apply()
    },

    _apply() {
        const image = this.image

        image.style.setProperty('--image-preview-scale', this._scale)
        image.style.setProperty('--image-preview-rotate', `${this._rotate}deg`)
        image.style.setProperty('--image-preview-x', `${this._x}px`)
        image.style.setProperty('--image-preview-y', `${this._y}px`)
        image.toggleAttribute('data-zoomed', this._scale > 1)

        this._button('zoom-in')?.toggleAttribute('disabled', this._scale >= MAX)
        this._button('zoom-out')?.toggleAttribute('disabled', this._scale <= MIN)
        this._button('reset')?.toggleAttribute('disabled', this._scale === 1 && this._rotate === 0 && ! this._x && ! this._y)
    },
})
