/*
 * <eso-signature>
 *
 * A signature drawn with signature_pad, exposed as .value: a data URL (PNG, or SVG when
 * data-type="image/svg+xml") or null while empty. input/change come from the element at
 * the end of each stroke and on Clear, so wire:model binds on it; data-name keeps a
 * hidden input for classic forms. A value from the server is drawn fitted into the pad,
 * and new strokes add to it.
 *
 * The ink is the canvas's CSS color, black unless --signature-color says otherwise, so a
 * saved signature reads on paper; in dark mode the CSS shows the canvas inverted.
 *
 * Built into dist/signature.js; the view loads it through @assets.
 */

import SignaturePad from 'signature_pad'
import { announce, define, writeHidden } from '../support/element.js'

export class SignatureElement extends HTMLElement {
    #pad = null
    #value = null
    #image = null
    #drawing = 0
    #observer = new ResizeObserver(() => this.#resize())

    connectedCallback() {
        if (! this.canvas) return

        if (! this.#pad) {
            this.#value = this.dataset.value || null
            this.#image = this.#value
            this.#pad = new SignaturePad(this.canvas)
            this.#pad.addEventListener('beginStroke', () => {
                this.#pad.penColor = getComputedStyle(this.canvas).color
            })
            this.#pad.addEventListener('endStroke', () => this.#commit(this.#export()))

            this.addEventListener('click', event => {
                if (event.target.closest?.('[data-signature-clear]')) this.clear()
            })

            if (this.hasAttribute('data-disabled')) this.#pad.off()
        }

        this.#observer.observe(this.canvas)
        this.#resize()
    }

    disconnectedCallback() {
        this.#observer.disconnect()
    }

    get canvas() {
        return this.querySelector('[data-slot="signature-canvas"]')
    }

    get value() {
        return this.#value
    }

    // From the server (wire:model): its signature replaces what is drawn.
    set value(value) {
        value ||= null

        if (value === this.#value) return

        this.#value = value
        this.#image = value
        this.#render()
        this.#draw([])
    }

    clear() {
        this.#pad?.clear()
        this.#image = null
        this.#commit(null)
    }

    #export() {
        if (this.#pad.isEmpty()) return null

        const type = this.dataset.type || 'image/png'

        return this.#pad.toDataURL(type, type === 'image/svg+xml' ? { includeDataUrl: true } : undefined)
    }

    #commit(value) {
        if (value === this.#value) return

        this.#value = value
        this.#render()
        announce(this)
    }

    #render() {
        writeHidden(this, this.dataset.name, this.#value ?? '')
        this.querySelector('[data-slot="signature-placeholder"]')?.toggleAttribute('hidden', !! this.#value)
        this.querySelector('[data-signature-clear]')?.toggleAttribute('hidden', ! this.#value)
    }

    // The canvas holds device pixels for its CSS size; resizing it wipes it, so the drawing is put back.
    #resize() {
        const canvas = this.canvas
        const ratio = Math.max(window.devicePixelRatio || 1, 1)
        const width = Math.round(canvas.offsetWidth * ratio)
        const height = Math.round(canvas.offsetHeight * ratio)

        // Not laid out (in a closed dialog, say), or already the right size.
        if (! width || ! height || (canvas.width === width && canvas.height === height)) return

        const strokes = this.#pad.toData()

        canvas.width = width
        canvas.height = height
        canvas.getContext('2d').scale(ratio, ratio)
        this.#draw(strokes)
    }

    // The server's signature, fitted and centred, with the strokes drawn since over it.
    async #draw(strokes) {
        if (! this.#pad) return

        const drawing = ++this.#drawing

        this.#pad.clear()

        if (this.#image) {
            const fit = await this.#fit(this.#image)

            // A later draw (another resize, a new value) took over.
            if (drawing !== this.#drawing) return

            await this.#pad.fromDataURL(this.#image, fit).catch(() => {})
        }

        if (drawing === this.#drawing) this.#pad.fromData(strokes, { clear: false })
    }

    #fit(url) {
        const width = this.canvas.offsetWidth
        const height = this.canvas.offsetHeight

        return new Promise(resolve => {
            const image = new Image()

            image.onload = () => {
                const scale = Math.min(width / image.naturalWidth, height / image.naturalHeight) || 1
                const fitted = { width: image.naturalWidth * scale, height: image.naturalHeight * scale }

                resolve({ ...fitted, xOffset: (width - fitted.width) / 2, yOffset: (height - fitted.height) / 2 })
            }
            image.onerror = () => resolve({})
            image.src = url
        })
    }
}

define('eso-signature', SignatureElement)
