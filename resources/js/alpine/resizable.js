/*
 * <eso-resizable-panel-group>
 *
 * Panels are sized in percent of the group through flex-grow, as react-resizable-panels
 * does. Dragging a handle, or its arrow keys, moves size between the panels on either
 * side of it within their min and max; Home and End push to a limit.
 */

import { disconnected, listen, unlisten } from './interaction.js'

const PANEL = '[data-slot="resizable-panel"]'
const HANDLE = '[data-slot="resizable-handle"]'
const KEY_STEP = 10

export default () => ({
    _sizes: null,
    init() {
        this._sizes = new Map()

        listen(this, this.$el, 'pointerdown', event => this._onPress(event), {})
        listen(this, this.$el, 'keydown', event => this._onKey(event), {})

        this._layout()

    },
    destroy() {
        disconnected(this)
    },
    get vertical() {
        return this.$el.dataset.orientation === 'vertical'
    },

    get panels() {
        return [...this.$el.querySelectorAll(PANEL)].filter(panel => panel.parentElement === this.$el)
    },

    get sizes() {
        return this.panels.map(panel => this._sizes.get(panel))
    },

    set sizes(sizes) {
        this.panels.forEach((panel, index) => sizes[index] !== undefined && this._size(panel, sizes[index]))
    },

    _layout() {
        const panels = this.panels
        const given = panels.filter(panel => panel.dataset.initialSize !== undefined)
        const taken = given.reduce((sum, panel) => sum + Number(panel.dataset.initialSize), 0)
        const share = (100 - taken) / Math.max(panels.length - given.length, 1)

        for (const panel of panels) this._size(panel, panel.dataset.initialSize !== undefined ? Number(panel.dataset.initialSize) : share)

        for (const handle of this.$el.querySelectorAll(`:scope > ${HANDLE}`)) {
            const before = this._around(handle)?.[0]

            if (before) handle.setAttribute('aria-valuenow', Math.round(this._sizes.get(before)))
        }
    },

    _size(panel, size) {
        this._sizes.set(panel, size)
        panel.style.flex = `${size} 1 0px`
        panel.dataset.size = size.toFixed(2)
    },

    _limits(panel) {
        return [Number(panel.dataset.minSize ?? 0), Number(panel.dataset.maxSize ?? 100)]
    },

    _around(handle) {
        const before = handle.previousElementSibling?.closest(PANEL)
        const after = handle.nextElementSibling?.closest(PANEL)

        return before && after ? [before, after] : null
    },

    _resize(handle, delta) {
        const pair = this._around(handle)

        if (! pair) return

        const [before, after] = pair
        const [beforeMin, beforeMax] = this._limits(before)
        const [afterMin, afterMax] = this._limits(after)
        const a = this._sizes.get(before)
        const b = this._sizes.get(after)
        const clamped = Math.max(Math.max(beforeMin - a, b - afterMax), Math.min(delta, Math.min(beforeMax - a, b - afterMin)))

        this._size(before, a + clamped)
        this._size(after, b - clamped)
        handle.setAttribute('aria-valuenow', Math.round(a + clamped))
        this.$dispatch('resize', { sizes: this.sizes })
    },

    _onPress(event) {
        const handle = event.target.closest?.(HANDLE)

        if (! handle || handle.parentElement !== this.$el || event.button !== 0) return

        event.preventDefault()

        const total = this.vertical ? this.$el.offsetHeight : this.$el.offsetWidth
        let last = this.vertical ? event.clientY : event.clientX

        handle.setAttribute('data-dragging', '')
        handle.setPointerCapture?.(event.pointerId)

        const move = e => {
            const point = this.vertical ? e.clientY : e.clientX
            this._resize(handle, (point - last) / total * 100)
            last = point
        }

        const stop = () => {
            handle.removeAttribute('data-dragging')
            unlisten(handle, 'pointermove', move)
            unlisten(handle, 'pointerup', stop)
            unlisten(handle, 'pointercancel', stop)
        }

        listen(this, handle, 'pointermove', move)
        listen(this, handle, 'pointerup', stop)
        listen(this, handle, 'pointercancel', stop)
    },

    _onKey(event) {
        const handle = event.target.closest?.(HANDLE)

        if (! handle || handle.parentElement !== this.$el) return

        const forward = this.vertical ? 'ArrowDown' : 'ArrowRight'
        const backward = this.vertical ? 'ArrowUp' : 'ArrowLeft'
        const delta = { [forward]: KEY_STEP, [backward]: -KEY_STEP, Home: -100, End: 100 }[event.key]

        if (delta === undefined) return

        event.preventDefault()
        this._resize(handle, delta)
    },
})
