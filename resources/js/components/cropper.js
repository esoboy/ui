/*
 * <eso-cropper>
 *
 * Origin UI's Cropper: a crop area of data-aspect-ratio fixed in the middle, and the
 * image (data-image) moved and zoomed under it. Drag or the arrow keys pan; the wheel or
 * a pinch zooms around the pointer, between data-min-zoom and data-max-zoom. .value is
 * the crop in the image's pixels, { x, y, width, height }, or null without an image;
 * input/change come from the element when an interaction ends, so wire:model binds on
 * it, and a value from the server frames the image the same way. .zoom follows the
 * zoom, which zoom-change reports.
 */

import { announce, define, readValue } from '../support/element.js'

const clamp = (value, min, max) => Math.min(Math.max(value, min), max)

// A crop as a plain { x, y, width, height } of numbers (not Livewire's proxy), or null.
const toCrop = value => (value && ['x', 'y', 'width', 'height'].every(key => Number.isFinite(Number(value[key])))
    ? { x: Number(value.x), y: Number(value.y), width: Number(value.width), height: Number(value.height) }
    : null)

export class CropperElement extends HTMLElement {
    static observedAttributes = ['data-image', 'data-aspect-ratio', 'data-zoom']

    #ready = false
    #natural = null
    #area = { width: 0, height: 0 }
    #wrapper = { width: 0, height: 0 }
    #offset = { x: 0, y: 0 }
    #zoom = 1
    #value = null
    #restore = null
    #placed = false
    #pointers = new Map()
    #gesture = null
    #wheel = 0
    #loading = 0
    #failed = false
    #observer = new ResizeObserver(() => this.#layout())

    connectedCallback() {
        if (! this.#ready) {
            this.#ready = true
            this.#value = toCrop(readValue(this))
            this.#restore = this.#value
            this.#zoom = this.#min

            this.addEventListener('pointerdown', event => this.#press(event))
            this.addEventListener('pointermove', event => this.#move(event))
            this.addEventListener('pointerup', event => this.#release(event))
            this.addEventListener('pointercancel', event => this.#release(event))
            this.addEventListener('wheel', event => this.#scroll(event), { passive: false })
            this.addEventListener('keydown', event => this.#key(event))
            this.addEventListener('keyup', event => {
                if (event.key.startsWith('Arrow')) this.#commit()
            })

            this.#load()
        }

        this.#observer.observe(this)
    }

    disconnectedCallback() {
        this.#observer.disconnect()
    }

    attributeChangedCallback(name, previous, current) {
        if (! this.#ready || previous === current) return

        if (name === 'data-image') {
            this.#restore = null
            this.#load()
        } else if (name === 'data-zoom' && current !== null) {
            this.zoom = Number(current)
        } else {
            this.#layout()
        }
    }

    get value() {
        return this.#value
    }

    // From the server (wire:model): frame the image on that crop.
    set value(value) {
        value = toCrop(value)

        if (JSON.stringify(value) === JSON.stringify(this.#value)) return

        this.#value = value
        this.#restore = value
        this.#placed = false
        this.#layout()
    }

    get zoom() {
        return this.#zoom
    }

    set zoom(zoom) {
        if (! this.#wrapper.width || ! Number.isFinite(zoom)) return

        this.#zoomAround(zoom, 0, 0)
        this.#commit()
    }

    get #min() {
        return Number(this.dataset.minZoom) || 1
    }

    get #max() {
        return Number(this.dataset.maxZoom) || 3
    }

    #part(slot) {
        return this.querySelector(`[data-slot="${slot}"]`)
    }

    #load() {
        const src = this.dataset.image
        const loading = ++this.#loading
        const image = this.#part('cropper-image')

        this.#natural = null
        this.#failed = false
        this.#placed = false
        if (image && src && image.getAttribute('src') !== src) image.src = src

        if (! src) return this.#layout()

        const probe = new Image()

        probe.onload = () => {
            if (loading !== this.#loading) return

            this.#natural = { width: probe.naturalWidth, height: probe.naturalHeight }
            this.#layout()
        }
        probe.onerror = () => {
            if (loading !== this.#loading) return

            this.#failed = true
            this.#layout()
        }
        probe.src = src
    }

    // Sizes the crop area for the element and the image wrapper to cover it, then places the image.
    #layout() {
        const width = this.clientWidth
        const height = this.clientHeight

        if (! width || ! height) return

        const ratio = Number(this.dataset.aspectRatio) || 1
        const padding = Number(this.dataset.cropPadding ?? 25)
        const room = { width: Math.max(0, width - padding * 2), height: Math.max(0, height - padding * 2) }

        this.#area = room.width / ratio >= room.height
            ? { width: room.height * ratio, height: room.height }
            : { width: room.width, height: room.width / ratio }

        // Still loading keeps the value; no image, or one that failed, has none.
        if (! this.#natural) {
            this.#wrapper = { width: 0, height: 0 }
            this.#render()

            return this.dataset.image && ! this.#failed ? undefined : this.#emit(null)
        }

        const aspect = this.#natural.width / this.#natural.height

        this.#wrapper = aspect >= this.#area.width / this.#area.height
            ? { width: this.#area.height * aspect, height: this.#area.height }
            : { width: this.#area.width, height: this.#area.width / aspect }

        if (! this.#placed) {
            this.#placed = true
            this.#place(this.#restore)
        }

        this.#offset = this.#restrict(this.#offset.x, this.#offset.y, this.#zoom)
        this.#render()
        this.#emit(this.#crop())
    }

    // The server's crop, or the image centred at the starting zoom.
    #place(crop) {
        if (crop?.width > 0) {
            const scale = this.#natural.width / this.#wrapper.width
            const zoom = clamp(this.#area.width * scale / crop.width, this.#min, this.#max)
            const left = -crop.x * zoom / scale
            const top = -crop.y * zoom / scale

            this.#setZoom(zoom)
            this.#offset = {
                x: left - (this.#area.width - this.#wrapper.width * zoom) / 2,
                y: top - (this.#area.height - this.#wrapper.height * zoom) / 2,
            }
        } else {
            this.#setZoom(Number(this.dataset.zoom) || this.#min)
            this.#offset = { x: 0, y: 0 }
        }
    }

    // How far the image may move with its edges still covering the crop area.
    #restrict(x, y, zoom) {
        const maxX = Math.max(0, (this.#wrapper.width * zoom - this.#area.width) / 2)
        const maxY = Math.max(0, (this.#wrapper.height * zoom - this.#area.height) / 2)

        return { x: clamp(x, -maxX, maxX), y: clamp(y, -maxY, maxY) }
    }

    #crop() {
        if (! this.#natural || ! this.#wrapper.width || ! this.#area.width) return null

        const scale = this.#natural.width / this.#wrapper.width
        const left = this.#offset.x + (this.#area.width - this.#wrapper.width * this.#zoom) / 2
        const top = this.#offset.y + (this.#area.height - this.#wrapper.height * this.#zoom) / 2
        const x = clamp(Math.round(-left * scale / this.#zoom), 0, this.#natural.width)
        const y = clamp(Math.round(-top * scale / this.#zoom), 0, this.#natural.height)
        const width = clamp(Math.round(this.#area.width * scale / this.#zoom), 0, this.#natural.width - x)
        const height = clamp(Math.round(this.#area.height * scale / this.#zoom), 0, this.#natural.height - y)

        return width > 0 && height > 0 ? { x, y, width, height } : null
    }

    #render() {
        const wrapper = this.#part('cropper-image-wrapper')
        const area = this.#part('cropper-crop-area')

        if (wrapper) {
            Object.assign(wrapper.style, {
                width: `${this.#wrapper.width}px`,
                height: `${this.#wrapper.height}px`,
                left: `calc(50% - ${this.#wrapper.width / 2}px)`,
                top: `calc(50% - ${this.#wrapper.height / 2}px)`,
                transform: `translate3d(${this.#offset.x}px, ${this.#offset.y}px, 0) scale(${this.#zoom})`,
            })
        }

        if (area) {
            area.hidden = ! this.#area.width
            Object.assign(area.style, { width: `${this.#area.width}px`, height: `${this.#area.height}px` })
        }
    }

    #setZoom(zoom) {
        zoom = clamp(zoom, this.#min, this.#max)

        if (zoom === this.#zoom) return

        this.#zoom = zoom
        this.dispatchEvent(new CustomEvent('zoom-change', { bubbles: true, detail: { zoom } }))
    }

    // Zooms keeping the image point under (x, y), measured from the centre, in place.
    #zoomAround(zoom, x, y) {
        const point = { x: (x - this.#offset.x) / this.#zoom, y: (y - this.#offset.y) / this.#zoom }

        this.#setZoom(zoom)
        this.#offset = this.#restrict(x - point.x * this.#zoom, y - point.y * this.#zoom, this.#zoom)
        this.#render()
    }

    #fromCentre(event) {
        const rect = this.getBoundingClientRect()

        return { x: event.clientX - rect.left - rect.width / 2, y: event.clientY - rect.top - rect.height / 2 }
    }

    // One pointer pans; a second one pinches.
    #press(event) {
        if (! this.#wrapper.width || (event.pointerType === 'mouse' && event.button !== 0)) return

        event.preventDefault()
        this.focus({ preventScroll: true })
        this.setPointerCapture?.(event.pointerId)
        this.#pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })
        this.#start()
    }

    #start() {
        const [first, second] = [...this.#pointers.values()]

        this.#gesture = second
            ? { distance: Math.hypot(second.x - first.x, second.y - first.y), zoom: this.#zoom }
            : first ? { point: first, offset: { ...this.#offset } } : null
    }

    #move(event) {
        if (! this.#pointers.has(event.pointerId) || ! this.#gesture) return

        this.#pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })

        const [first, second] = [...this.#pointers.values()]

        if (second && this.#gesture.distance) {
            const centre = this.#fromCentre({ clientX: (first.x + second.x) / 2, clientY: (first.y + second.y) / 2 })
            const distance = Math.hypot(second.x - first.x, second.y - first.y)

            this.#zoomAround(this.#gesture.zoom * distance / this.#gesture.distance, centre.x, centre.y)
        } else if (this.#gesture.point) {
            this.#offset = this.#restrict(
                this.#gesture.offset.x + first.x - this.#gesture.point.x,
                this.#gesture.offset.y + first.y - this.#gesture.point.y,
                this.#zoom,
            )
            this.#render()
        }
    }

    #release(event) {
        if (! this.#pointers.delete(event.pointerId)) return

        // From a pinch back to one finger: it pans from where it is.
        this.#start()
        if (! this.#pointers.size) this.#commit()
    }

    #scroll(event) {
        if (! this.#wrapper.width) return

        event.preventDefault()

        const point = this.#fromCentre(event)

        this.#zoomAround(this.#zoom - event.deltaY * (Number(this.dataset.zoomSensitivity) || 0.005), point.x, point.y)

        // The wheel has no end: the crop is reported once it rests.
        clearTimeout(this.#wheel)
        this.#wheel = setTimeout(() => this.#commit(), 200)
    }

    #key(event) {
        const step = Number(this.dataset.keyboardStep) || 10
        const move = { ArrowUp: [0, step], ArrowDown: [0, -step], ArrowLeft: [step, 0], ArrowRight: [-step, 0] }[event.key]

        if (! move || ! this.#wrapper.width) return

        event.preventDefault()
        this.#offset = this.#restrict(this.#offset.x + move[0], this.#offset.y + move[1], this.#zoom)
        this.#render()
    }

    #commit() {
        this.#emit(this.#crop())
    }

    #emit(crop) {
        if (JSON.stringify(crop) === JSON.stringify(this.#value)) return

        this.#value = crop
        announce(this)
    }
}

export function register() {
    define('eso-cropper', CropperElement)
}
