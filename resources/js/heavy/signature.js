/*
 * <eso-signature>
 *
 * An Alpine adapter for signature_pad. Livewire owns the data URL (PNG, or SVG when
 * data-type="image/svg+xml") or null while empty. The native hidden input forwards
 * model events after each stroke and Clear, and supports classic form submission. A value from the server is drawn fitted into the pad,
 * and new strokes add to it.
 *
 * The ink is the canvas's CSS color, black unless --signature-color says otherwise, so a
 * saved signature reads on paper; in dark mode the CSS shows the canvas inverted.
 *
 * Built into dist/signature.js; Alpine loads it on demand.
 */

import SignaturePad from 'signature_pad'
import boundValue from '../alpine/value.js'

import { listen, unlisten, emit, disconnected } from '../alpine/interaction.js'

export default binding => Object.defineProperties(boundValue(binding), Object.getOwnPropertyDescriptors({
    _pad: null,
    _image: null,
    _published: null,
    _drawing: 0,
    _observer: null,

    init() {
        this._observer = new ResizeObserver(() => this._resize())
        if (! this.canvas) return

        if (! this._pad) {
            this._image = this.value
            this._published = this.value
            this._pad = new SignaturePad(this.canvas)
            this._pad.addEventListener('beginStroke', () => {
                this._pad.penColor = getComputedStyle(this.canvas).color
            })
            this._pad.addEventListener('endStroke', () => this._commit(this._export()))

            listen(this, this.$el, 'click', event => {
                if (event.target.closest?.('[data-signature-clear]')) this.clear()
            })
            listen(this, this.$el, 'focusout', event => {
                if (!this.$el.contains(event.relatedTarget)) this.blur()
            })

            if (this.$el.hasAttribute('data-disabled')) this._pad.off()
        }

        this._observer.observe(this.canvas)
        this._resize()
        if (binding.property) this.$watch('$wire.' + binding.property, value => {
            if (value === this._published) return
            this._published = value; this._image = value; this._render(); this._draw([])
        })
        this._render()
    },

    destroy() {
        disconnected(this)
        this._pad?.off()
        this._drawing++
        this._observer.disconnect()
    },

    get canvas() {
        return this.$el.querySelector('[data-slot="signature-canvas"]')
    },

    clear() {
        this._pad?.clear()
        this._image = null
        this._commit(null)
    },

    _export() {
        if (this._pad.isEmpty()) return null

        const type = this.$el.dataset.type || 'image/png'

        return this._pad.toDataURL(type, type === 'image/svg+xml' ? { includeDataUrl: true } : undefined)
    },

    _commit(value) {
        if (value === this.value) return
        this._published = value
        this.value = value
        this._render()
        this.changed()
    },

    _render() {
        this.$el.querySelector('[data-slot="signature-placeholder"]')?.toggleAttribute('hidden', !! this.value)
        this.$el.querySelector('[data-signature-clear]')?.toggleAttribute('hidden', ! this.value)
    },

    // The canvas holds device pixels for its CSS size; resizing it wipes it, so the drawing is put back.
    _resize() {
        const canvas = this.canvas
        const ratio = Math.max(window.devicePixelRatio || 1, 1)
        const width = Math.round(canvas.offsetWidth * ratio)
        const height = Math.round(canvas.offsetHeight * ratio)

        // Not laid out (in a closed dialog, say), or already the right size.
        if (! width || ! height || (canvas.width === width && canvas.height === height)) return

        const strokes = this._pad.toData()

        canvas.width = width
        canvas.height = height
        canvas.getContext('2d').scale(ratio, ratio)
        this._draw(strokes)
    },

    // The server's signature, fitted and centred, with the strokes drawn since over it.
    async _draw(strokes) {
        if (! this._pad) return

        const drawing = ++this._drawing

        this._pad.clear()

        if (this._image) {
            const fit = await this._fit(this._image)

            // A later draw (another resize, a new value) took over.
            if (drawing !== this._drawing) return

            await this._pad.fromDataURL(this._image, fit).catch(() => {})
        }

        if (drawing === this._drawing) this._pad.fromData(strokes, { clear: false })
    },

    _fit(url) {
        const width = this.canvas.offsetWidth
        const height = this.canvas.offsetHeight

        return new Promise(resolve => {
            const image = new Image()

            listen(this, image, 'load', () => {
                const scale = Math.min(width / image.naturalWidth, height / image.naturalHeight) || 1
                const fitted = { width: image.naturalWidth * scale, height: image.naturalHeight * scale }

                resolve({ ...fitted, xOffset: (width - fitted.width) / 2, yOffset: (height - fitted.height) / 2 })
            }, { once: true })
            listen(this, image, 'error', () => resolve({}), { once: true })
            image.src = url
        })
    },
}))
