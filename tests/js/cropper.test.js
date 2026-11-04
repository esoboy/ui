import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { CropperElement, register as registerCropper } from '../../resources/js/components/cropper.js'

/*
 * jsdom lays nothing out: the cropper is 400×300 (a 250×250 crop area at the default
 * padding) and images "load" at their size below, 1600×1000 unless a test says otherwise.
 */
let natural = { '/photo.jpg': [1600, 1000], '/portrait.jpg': [900, 1200] }

beforeAll(() => {
    registerCropper()

    Object.defineProperty(CropperElement.prototype, 'clientWidth', { get: () => 400 })
    Object.defineProperty(CropperElement.prototype, 'clientHeight', { get: () => 300 })
    CropperElement.prototype.getBoundingClientRect = () => ({ left: 0, top: 0, width: 400, height: 300 })

    window.Image = class {
        set src(value) {
            setTimeout(() => {
                if (! natural[value]) return this.onerror?.()

                ;[this.naturalWidth, this.naturalHeight] = natural[value]
                this.onload?.()
            })
        }
    }
})

afterEach(() => {
    document.body.innerHTML = ''
    vi.useRealTimers()
})

function mount(attributes = 'data-image="/photo.jpg"') {
    document.body.innerHTML = `
        <eso-cropper data-slot="cropper" tabindex="0" ${attributes}>
            <div data-slot="cropper-image-wrapper"><img data-slot="cropper-image" alt=""></div>
            <div data-slot="cropper-crop-area" hidden></div>
        </eso-cropper>`

    const cropper = document.querySelector('eso-cropper')
    const events = []

    for (const type of ['input', 'change']) cropper.addEventListener(type, () => events.push(type))
    cropper.addEventListener('zoom-change', event => events.push(`zoom ${event.detail.zoom}`))

    return [cropper, events]
}

const loaded = async (cropper, value) => vi.waitFor(() => expect(cropper.value).toEqual(value))

// jsdom has no PointerEvent: a MouseEvent with the pointer's fields.
const pointer = (target, type, { id = 1, x = 200, y = 150, kind = 'mouse' } = {}) => target.dispatchEvent(
    Object.assign(new MouseEvent(type, { bubbles: true, cancelable: true, clientX: x, clientY: y, button: 0 }), { pointerId: id, pointerType: kind }),
)

const wrapper = cropper => cropper.querySelector('[data-slot="cropper-image-wrapper"]').style
const SQUARE = { x: 300, y: 0, width: 1000, height: 1000 }

describe('framing', () => {
    it('fits the crop area, covers it with the image and reports the centred crop', async () => {
        const [cropper, events] = mount()
        const area = cropper.querySelector('[data-slot="cropper-crop-area"]')

        await loaded(cropper, SQUARE)

        expect(area.hidden).toBe(false)
        expect([area.style.width, area.style.height]).toEqual(['250px', '250px'])
        expect([wrapper(cropper).width, wrapper(cropper).height]).toEqual(['400px', '250px'])
        expect(wrapper(cropper).transform).toBe('translate3d(0px, 0px, 0) scale(1)')
        expect(events).toEqual(['input', 'change'])
    })

    it('follows the aspect ratio', async () => {
        const [cropper] = mount('data-image="/photo.jpg" data-aspect-ratio="1.6"')

        // 350×218.75 in the 400×300 box: the whole photo.
        await loaded(cropper, { x: 0, y: 0, width: 1600, height: 1000 })
    })

    it('starts from the server\'s crop without reporting it back', async () => {
        const [cropper, events] = mount(`data-image="/photo.jpg" data-value='{"x":0,"y":0,"width":500,"height":500}'`)

        await vi.waitFor(() => expect(cropper.zoom).toBe(2))

        // At twice the size the photo's top-left corner sits in the crop area.
        expect(wrapper(cropper).transform).toBe('translate3d(275px, 125px, 0) scale(2)')
        expect(cropper.value).toEqual({ x: 0, y: 0, width: 500, height: 500 })
        expect(events).toEqual(['zoom 2'])
    })

    it('has no value without an image, or with one that fails', async () => {
        const [cropper] = mount(`data-value='{"x":0,"y":0,"width":500,"height":500}'`)

        await loaded(cropper, null)

        const [broken] = mount('data-image="/missing.jpg"')

        await loaded(broken, null)
    })

    it('keeps the value while a new image loads, then reports the new crop', async () => {
        const [cropper, events] = mount()

        await loaded(cropper, SQUARE)
        cropper.setAttribute('data-image', '/portrait.jpg')

        expect(cropper.value).toEqual(SQUARE)
        expect(cropper.querySelector('[data-slot="cropper-image"]').getAttribute('src')).toBe('/portrait.jpg')

        await loaded(cropper, { x: 0, y: 150, width: 900, height: 900 })
        expect(events).toEqual(['input', 'change', 'input', 'change'])
    })
})

describe('moving the image', () => {
    it('pans with a drag, kept covering the crop area, and reports when it ends', async () => {
        const [cropper, events] = mount()

        await loaded(cropper, SQUARE)
        events.length = 0

        pointer(cropper, 'pointerdown', { x: 200, y: 150 })
        pointer(cropper, 'pointermove', { x: 240, y: 250 })

        // Wider than the area by 150px: 75px either way; exactly as tall: no room.
        expect(wrapper(cropper).transform).toBe('translate3d(40px, 0px, 0) scale(1)')
        expect(events).toEqual([])

        pointer(cropper, 'pointermove', { x: 400, y: 150 })
        pointer(cropper, 'pointerup', { x: 400, y: 150 })

        expect(cropper.value).toEqual({ x: 0, y: 0, width: 1000, height: 1000 })
        expect(events).toEqual(['input', 'change'])
    })

    it('pans with the arrow keys and reports on release', async () => {
        const [cropper, events] = mount()

        await loaded(cropper, SQUARE)
        events.length = 0

        const keydown = new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true, cancelable: true })

        cropper.dispatchEvent(keydown)
        cropper.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true }))
        cropper.dispatchEvent(new KeyboardEvent('keyup', { key: 'ArrowLeft', bubbles: true }))

        expect(keydown.defaultPrevented).toBe(true)
        expect(cropper.value).toEqual({ x: 220, y: 0, width: 1000, height: 1000 })
        expect(events).toEqual(['input', 'change'])
    })

    it('ignores other mouse buttons', async () => {
        const [cropper] = mount()

        await loaded(cropper, SQUARE)
        cropper.dispatchEvent(Object.assign(new MouseEvent('pointerdown', { bubbles: true, button: 2, clientX: 200, clientY: 150 }), { pointerId: 1, pointerType: 'mouse' }))
        pointer(cropper, 'pointermove', { x: 260 })

        expect(wrapper(cropper).transform).toBe('translate3d(0px, 0px, 0) scale(1)')
    })
})

describe('zooming', () => {
    it('zooms with the wheel around the pointer, up to the maximum, and reports once it rests', async () => {
        const [cropper, events] = mount()

        await loaded(cropper, SQUARE)
        vi.useFakeTimers()
        events.length = 0

        const wheel = new WheelEvent('wheel', { deltaY: -100, clientX: 200, clientY: 150, bubbles: true, cancelable: true })

        cropper.dispatchEvent(wheel)

        expect(wheel.defaultPrevented).toBe(true)
        expect(cropper.zoom).toBe(1.5)
        expect(events).toEqual(['zoom 1.5'])

        for (let i = 0; i < 6; i++) cropper.dispatchEvent(new WheelEvent('wheel', { deltaY: -100, clientX: 200, clientY: 150, bubbles: true, cancelable: true }))

        expect(cropper.zoom).toBe(3)

        vi.advanceTimersByTime(200)

        expect(cropper.value).toEqual({ x: 633, y: 333, width: 333, height: 333 })
        expect(events.slice(-2)).toEqual(['input', 'change'])
    })

    it('zooms with a pinch', async () => {
        const [cropper] = mount()

        await loaded(cropper, SQUARE)

        pointer(cropper, 'pointerdown', { id: 1, x: 150, y: 150, kind: 'touch' })
        pointer(cropper, 'pointerdown', { id: 2, x: 250, y: 150, kind: 'touch' })
        pointer(cropper, 'pointermove', { id: 2, x: 350, y: 150, kind: 'touch' })

        expect(cropper.zoom).toBe(2)

        pointer(cropper, 'pointerup', { id: 2, x: 350, y: 150, kind: 'touch' })
        pointer(cropper, 'pointerup', { id: 1, x: 150, y: 150, kind: 'touch' })

        expect(cropper.value.width).toBe(500)
    })

    it('takes a zoom from .zoom or data-zoom, centred and within the limits', async () => {
        const [cropper, events] = mount('data-image="/photo.jpg" data-max-zoom="4"')

        await loaded(cropper, SQUARE)

        cropper.zoom = 2

        expect(cropper.value).toEqual({ x: 550, y: 250, width: 500, height: 500 })

        cropper.setAttribute('data-zoom', '9')

        expect(cropper.zoom).toBe(4)
        expect(events).toContain('zoom 4')
    })
})

describe('a value from the server', () => {
    it('frames the image on it, and does nothing when it echoes ours', async () => {
        const [cropper, events] = mount()

        await loaded(cropper, SQUARE)
        events.length = 0

        cropper.value = { ...SQUARE }

        expect(events).toEqual([])

        cropper.value = { x: '1100', y: '500', width: '500', height: '500' }

        expect(cropper.value).toEqual({ x: 1100, y: 500, width: 500, height: 500 })
        expect(cropper.zoom).toBe(2)
        expect(wrapper(cropper).transform).toBe('translate3d(-275px, -125px, 0) scale(2)')
        expect(events).toEqual(['zoom 2'])
    })
})
