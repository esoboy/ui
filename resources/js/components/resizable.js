/*
 * <eso-resizable-panel-group>
 *
 * Panels are sized in percent of the group through flex-grow, as react-resizable-panels
 * does. Dragging a handle, or its arrow keys, moves size between the panels on either
 * side of it within their min and max; Home and End push to a limit.
 */

import { connected, define, disconnected } from '../support/element.js'

const PANEL = '[data-slot="resizable-panel"]'
const HANDLE = '[data-slot="resizable-handle"]'
const KEY_STEP = 10

export class ResizablePanelGroupElement extends HTMLElement {
    #sizes = new Map()

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('pointerdown', event => this.#onPress(event), { signal })
        this.addEventListener('keydown', event => this.#onKey(event), { signal })

        this.#layout()
    }

    disconnectedCallback() {
        disconnected(this)
    }

    get vertical() {
        return this.dataset.orientation === 'vertical'
    }

    get panels() {
        return [...this.querySelectorAll(PANEL)].filter(panel => panel.parentElement === this)
    }

    /** Current sizes in percent, in panel order. */
    get sizes() {
        return this.panels.map(panel => this.#sizes.get(panel))
    }

    set sizes(sizes) {
        this.panels.forEach((panel, index) => sizes[index] !== undefined && this.#size(panel, sizes[index]))
    }

    // Panels without a default size share what the others leave.
    #layout() {
        const panels = this.panels
        const given = panels.filter(panel => panel.dataset.defaultSize !== undefined)
        const taken = given.reduce((sum, panel) => sum + Number(panel.dataset.defaultSize), 0)
        const share = (100 - taken) / Math.max(panels.length - given.length, 1)

        for (const panel of panels) this.#size(panel, panel.dataset.defaultSize !== undefined ? Number(panel.dataset.defaultSize) : share)

        for (const handle of this.querySelectorAll(`:scope > ${HANDLE}`)) {
            const before = this.#around(handle)?.[0]

            if (before) handle.setAttribute('aria-valuenow', Math.round(this.#sizes.get(before)))
        }
    }

    #size(panel, size) {
        this.#sizes.set(panel, size)
        panel.style.flex = `${size} 1 0px`
        panel.dataset.size = size.toFixed(2)
    }

    #limits(panel) {
        return [Number(panel.dataset.minSize ?? 0), Number(panel.dataset.maxSize ?? 100)]
    }

    #around(handle) {
        const before = handle.previousElementSibling?.closest(PANEL)
        const after = handle.nextElementSibling?.closest(PANEL)

        return before && after ? [before, after] : null
    }

    // Moves delta percent from the panel after the handle to the one before it.
    #resize(handle, delta) {
        const pair = this.#around(handle)

        if (! pair) return

        const [before, after] = pair
        const [beforeMin, beforeMax] = this.#limits(before)
        const [afterMin, afterMax] = this.#limits(after)
        const a = this.#sizes.get(before)
        const b = this.#sizes.get(after)
        const clamped = Math.max(Math.max(beforeMin - a, b - afterMax), Math.min(delta, Math.min(beforeMax - a, b - afterMin)))

        this.#size(before, a + clamped)
        this.#size(after, b - clamped)
        handle.setAttribute('aria-valuenow', Math.round(a + clamped))
        this.dispatchEvent(new CustomEvent('resize', { detail: { sizes: this.sizes } }))
    }

    #onPress(event) {
        const handle = event.target.closest?.(HANDLE)

        if (! handle || handle.parentElement !== this || event.button !== 0) return

        event.preventDefault()

        const total = this.vertical ? this.offsetHeight : this.offsetWidth
        let last = this.vertical ? event.clientY : event.clientX

        handle.setAttribute('data-dragging', '')
        handle.setPointerCapture?.(event.pointerId)

        const move = e => {
            const point = this.vertical ? e.clientY : e.clientX
            this.#resize(handle, (point - last) / total * 100)
            last = point
        }

        const stop = () => {
            handle.removeAttribute('data-dragging')
            handle.removeEventListener('pointermove', move)
            handle.removeEventListener('pointerup', stop)
            handle.removeEventListener('pointercancel', stop)
        }

        handle.addEventListener('pointermove', move)
        handle.addEventListener('pointerup', stop)
        handle.addEventListener('pointercancel', stop)
    }

    #onKey(event) {
        const handle = event.target.closest?.(HANDLE)

        if (! handle || handle.parentElement !== this) return

        const forward = this.vertical ? 'ArrowDown' : 'ArrowRight'
        const backward = this.vertical ? 'ArrowUp' : 'ArrowLeft'
        const delta = { [forward]: KEY_STEP, [backward]: -KEY_STEP, Home: -100, End: 100 }[event.key]

        if (delta === undefined) return

        event.preventDefault()
        this.#resize(handle, delta)
    }
}

export function register() {
    define('eso-resizable-panel-group', ResizablePanelGroupElement)
}
