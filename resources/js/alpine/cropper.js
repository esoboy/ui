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

const clamp = (value, min, max) => Math.min(Math.max(value, min), max)

// A crop as a plain { x, y, width, height } of numbers (not Livewire's proxy), or null.
const toCrop = value => (value && ['x', 'y', 'width', 'height'].every(key => Number.isFinite(Number(value[key])))
    ? { x: Number(value.x), y: Number(value.y), width: Number(value.width), height: Number(value.height) }
    : null)

import { listen, disconnected } from './interaction.js'
import boundValue from './value.js'

export default binding => {
const bridge = boundValue({ ...binding, json: true })
const value = Object.getOwnPropertyDescriptor(bridge, 'value')
return Object.defineProperties(bridge, Object.getOwnPropertyDescriptors({
    get value() { return toCrop(value.get.call(this)) },
    set value(crop) { value.set.call(this, toCrop(crop)) },

    _attributes: null,
    _ready: false,
    _natural: null,
    _area: { width: 0, height: 0 },
    _wrapper: { width: 0, height: 0 },
    _offset: { x: 0, y: 0 },
    _zoom: 1,
    _restore: null,
    _placed: false,
    _pointers: new Map(),
    _gesture: null,
    _wheel: 0,
    _loading: 0,
    _failed: false,
    _observer: null,

    init() {
        this._observer = new ResizeObserver(() => this._layout())
        if (! this._ready) {
            this._ready = true
            this._restore = toCrop(this.value)
            this._zoom = this._min

            listen(this, this.$el, 'pointerdown', event => this._press(event))
            listen(this, this.$el, 'pointermove', event => this._move(event))
            listen(this, this.$el, 'pointerup', event => this._release(event))
            listen(this, this.$el, 'pointercancel', event => this._release(event))
            listen(this, this.$el, 'wheel', event => this._scroll(event), { passive: false })
            listen(this, this.$el, 'keydown', event => this._key(event))
            listen(this, this.$el, 'keyup', event => {
                if (event.key.startsWith('Arrow')) this._commit()
            })

            this._load()
        }

        this._observer.observe(this.$el)
        if (binding.property) this.$watch('$wire.' + binding.property, value => {
            if (JSON.stringify(value) === JSON.stringify(this._crop())) return
            this._restore = toCrop(value); this._placed = false; this._layout()
        })
        this._attributes = new MutationObserver(records => records.forEach(record => this.attributeChangedCallback(record.attributeName, null, this.$el.getAttribute(record.attributeName))))
        this._attributes.observe(this.$el, { attributes: true, attributeFilter: ['data-image', 'data-aspect-ratio', 'data-zoom'] })
    },

    destroy() {
        disconnected(this)
        clearTimeout(this._wheel)
        this._loading++
        this._observer.disconnect()
        this._attributes?.disconnect()
    },

    attributeChangedCallback(name, previous, current) {
        if (! this._ready || previous === current) return

        if (name === 'data-image') {
            this._restore = null
            this._load()
        } else if (name === 'data-zoom' && current !== null) {
            this.zoom = Number(current)
        } else {
            this._layout()
        }
    },

    get zoom() {
        return this._zoom
    },

    set zoom(zoom) {
        if (! this._wrapper.width || ! Number.isFinite(zoom)) return

        this._zoomAround(zoom, 0, 0)
        this._commit()
    },

    get _min() {
        return Number(this.$el.dataset.minZoom) || 1
    },

    get _max() {
        return Number(this.$el.dataset.maxZoom) || 3
    },

    _part(slot) {
        return this.$el.querySelector(`[data-slot="${slot}"]`)
    },

    _load() {
        const src = this.$el.dataset.image
        const loading = ++this._loading
        const image = this._part('cropper-image')

        this._natural = null
        this._failed = false
        this._placed = false
        if (image && src && image.getAttribute('src') !== src) image.src = src

        if (! src) return this._layout()

        const probe = new Image()

        listen(this, probe, 'load', () => {
            if (loading !== this._loading) return

            this._natural = { width: probe.naturalWidth, height: probe.naturalHeight }
            this._layout()
        }, { once: true })
        listen(this, probe, 'error', () => {
            if (loading !== this._loading) return

            this._failed = true
            this._layout()
        }, { once: true })
        probe.src = src
    },

    // Sizes the crop area for the element and the image wrapper to cover it, then places the image.
    _layout() {
        const width = this.$el.clientWidth
        const height = this.$el.clientHeight

        if (! width || ! height) return

        const ratio = Number(this.$el.dataset.aspectRatio) || 1
        const padding = Number(this.$el.dataset.cropPadding ?? 25)
        const room = { width: Math.max(0, width - padding * 2), height: Math.max(0, height - padding * 2) }

        this._area = room.width / ratio >= room.height
            ? { width: room.height * ratio, height: room.height }
            : { width: room.width, height: room.width / ratio }

        // Still loading keeps the value; no image, or one that failed, has none.
        if (! this._natural) {
            this._wrapper = { width: 0, height: 0 }
            this._render()

            return this.$el.dataset.image && ! this._failed ? undefined : this._emit(null)
        }

        const aspect = this._natural.width / this._natural.height

        this._wrapper = aspect >= this._area.width / this._area.height
            ? { width: this._area.height * aspect, height: this._area.height }
            : { width: this._area.width, height: this._area.width / aspect }

        if (! this._placed) {
            this._placed = true
            this._place(this._restore)
        }

        this._offset = this._restrict(this._offset.x, this._offset.y, this._zoom)
        this._render()
        this._emit(this._crop())
    },

    // The server's crop, or the image centred at the starting zoom.
    _place(crop) {
        if (crop?.width > 0) {
            const scale = this._natural.width / this._wrapper.width
            const zoom = clamp(this._area.width * scale / crop.width, this._min, this._max)
            const left = -crop.x * zoom / scale
            const top = -crop.y * zoom / scale

            this._setZoom(zoom)
            this._offset = {
                x: left - (this._area.width - this._wrapper.width * zoom) / 2,
                y: top - (this._area.height - this._wrapper.height * zoom) / 2,
            }
        } else {
            this._setZoom(Number(this.$el.dataset.zoom) || this._min)
            this._offset = { x: 0, y: 0 }
        }
    },

    // How far the image may move with its edges still covering the crop area.
    _restrict(x, y, zoom) {
        const maxX = Math.max(0, (this._wrapper.width * zoom - this._area.width) / 2)
        const maxY = Math.max(0, (this._wrapper.height * zoom - this._area.height) / 2)

        return { x: clamp(x, -maxX, maxX), y: clamp(y, -maxY, maxY) }
    },

    _crop() {
        if (! this._natural || ! this._wrapper.width || ! this._area.width) return null

        const scale = this._natural.width / this._wrapper.width
        const left = this._offset.x + (this._area.width - this._wrapper.width * this._zoom) / 2
        const top = this._offset.y + (this._area.height - this._wrapper.height * this._zoom) / 2
        const x = clamp(Math.round(-left * scale / this._zoom), 0, this._natural.width)
        const y = clamp(Math.round(-top * scale / this._zoom), 0, this._natural.height)
        const width = clamp(Math.round(this._area.width * scale / this._zoom), 0, this._natural.width - x)
        const height = clamp(Math.round(this._area.height * scale / this._zoom), 0, this._natural.height - y)

        return width > 0 && height > 0 ? { x, y, width, height } : null
    },

    _render() {
        const wrapper = this._part('cropper-image-wrapper')
        const area = this._part('cropper-crop-area')

        if (wrapper) {
            Object.assign(wrapper.style, {
                width: `${this._wrapper.width}px`,
                height: `${this._wrapper.height}px`,
                left: `calc(50% - ${this._wrapper.width / 2}px)`,
                top: `calc(50% - ${this._wrapper.height / 2}px)`,
                transform: `translate3d(${this._offset.x}px, ${this._offset.y}px, 0) scale(${this._zoom})`,
            })
        }

        if (area) {
            area.hidden = ! this._area.width
            Object.assign(area.style, { width: `${this._area.width}px`, height: `${this._area.height}px` })
        }
    },

    _setZoom(zoom) {
        zoom = clamp(zoom, this._min, this._max)

        if (zoom === this._zoom) return

        this._zoom = zoom
        this.$dispatch('zoom-change', { zoom })
    },

    // Zooms keeping the image point under (x, y), measured from the centre, in place.
    _zoomAround(zoom, x, y) {
        const point = { x: (x - this._offset.x) / this._zoom, y: (y - this._offset.y) / this._zoom }

        this._setZoom(zoom)
        this._offset = this._restrict(x - point.x * this._zoom, y - point.y * this._zoom, this._zoom)
        this._render()
    },

    _fromCentre(event) {
        const rect = this.$el.getBoundingClientRect()

        return { x: event.clientX - rect.left - rect.width / 2, y: event.clientY - rect.top - rect.height / 2 }
    },

    // One pointer pans; a second one pinches.
    _press(event) {
        if (! this._wrapper.width || (event.pointerType === 'mouse' && event.button !== 0)) return

        event.preventDefault()
        this.$el.focus({ preventScroll: true })
        this.$el.setPointerCapture?.(event.pointerId)
        this._pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })
        this._start()
    },

    _start() {
        const [first, second] = [...this._pointers.values()]

        this._gesture = second
            ? { distance: Math.hypot(second.x - first.x, second.y - first.y), zoom: this._zoom }
            : first ? { point: first, offset: { ...this._offset } } : null
    },

    _move(event) {
        if (! this._pointers.has(event.pointerId) || ! this._gesture) return

        this._pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })

        const [first, second] = [...this._pointers.values()]

        if (second && this._gesture.distance) {
            const centre = this._fromCentre({ clientX: (first.x + second.x) / 2, clientY: (first.y + second.y) / 2 })
            const distance = Math.hypot(second.x - first.x, second.y - first.y)

            this._zoomAround(this._gesture.zoom * distance / this._gesture.distance, centre.x, centre.y)
        } else if (this._gesture.point) {
            this._offset = this._restrict(
                this._gesture.offset.x + first.x - this._gesture.point.x,
                this._gesture.offset.y + first.y - this._gesture.point.y,
                this._zoom,
            )
            this._render()
        }
    },

    _release(event) {
        if (! this._pointers.delete(event.pointerId)) return

        // From a pinch back to one finger: it pans from where it is.
        this._start()
        if (! this._pointers.size) this._commit()
    },

    _scroll(event) {
        if (! this._wrapper.width) return

        event.preventDefault()

        const point = this._fromCentre(event)

        this._zoomAround(this._zoom - event.deltaY * (Number(this.$el.dataset.zoomSensitivity) || 0.005), point.x, point.y)

        // The wheel has no end: the crop is reported once it rests.
        clearTimeout(this._wheel)
        this._wheel = setTimeout(() => this._commit(), 200)
    },

    _key(event) {
        const step = Number(this.$el.dataset.keyboardStep) || 10
        const move = { ArrowUp: [0, step], ArrowDown: [0, -step], ArrowLeft: [step, 0], ArrowRight: [-step, 0] }[event.key]

        if (! move || ! this._wrapper.width) return

        event.preventDefault()
        this._offset = this._restrict(this._offset.x + move[0], this._offset.y + move[1], this._zoom)
        this._render()
    },

    _commit() {
        this._emit(this._crop())
    },

    _emit(crop) {
        if (JSON.stringify(crop) === JSON.stringify(this.value)) return

        this.value = crop
        this.changed()
    },
}))
}
