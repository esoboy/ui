import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerImagePreview } from '../../resources/js/components/image-preview.js'

beforeAll(() => registerImagePreview())

afterEach(() => {
    document.body.innerHTML = ''
})

const actions = ['close', 'previous', 'next', 'zoom-out', 'zoom-in', 'rotate-left', 'rotate-right', 'reset']

function mount(count = 3) {
    const triggers = Array.from({ length: count }, (_, index) => `<button data-slot="image-preview-trigger" data-src="/${index}.jpg" data-alt="Photo ${index}"></button>`).join('')

    document.body.innerHTML = `
        <eso-image-preview>
            ${triggers}
            <dialog data-slot="image-preview-content">
                <img data-slot="image-preview-image">
                ${actions.map(action => `<button data-image-preview="${action}"></button>`).join('')}
                <span data-slot="image-preview-counter"></span>
            </dialog>
        </eso-image-preview>`

    return document.querySelector('eso-image-preview')
}

const button = (element, action) => element.querySelector(`[data-image-preview="${action}"]`)
const style = (element, name) => element.image.style.getPropertyValue(`--image-preview-${name}`)
const key = (element, name) => element.image.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

describe('image preview', () => {
    it('opens at the clicked thumbnail, loading its image', () => {
        const element = mount()

        element.querySelectorAll('[data-slot="image-preview-trigger"]')[1].click()

        expect(element.dialog.open).toBe(true)
        expect(element.image.getAttribute('src')).toBe('/1.jpg')
        expect(element.image.alt).toBe('Photo 1')
        expect(element.dialog.hasAttribute('data-loading')).toBe(true)
        expect(element.querySelector('[data-slot="image-preview-counter"]').textContent).toBe('2 / 3')

        element.image.dispatchEvent(new Event('load'))
        expect(element.dialog.hasAttribute('data-loading')).toBe(false)
    })

    it('pages with the buttons and arrows, stopping at the ends', () => {
        const element = mount()

        element.open(0)
        expect(button(element, 'previous').disabled).toBe(true)

        button(element, 'next').click()
        key(element, 'ArrowRight')
        expect(element.index).toBe(2)
        expect(button(element, 'next').disabled).toBe(true)

        key(element, 'ArrowRight')
        expect(element.index).toBe(2)

        key(element, 'ArrowLeft')
        expect(element.index).toBe(1)
    })

    it('zooms within bounds, rotates and resets', () => {
        const element = mount()

        element.open(0)
        expect(button(element, 'reset').disabled).toBe(true)

        button(element, 'zoom-in').click()
        expect(style(element, 'scale')).toBe('1.5')
        expect(element.image.hasAttribute('data-zoomed')).toBe(true)

        for (let i = 0; i < 10; i++) key(element, '+')
        expect(style(element, 'scale')).toBe('5')
        expect(button(element, 'zoom-in').disabled).toBe(true)

        button(element, 'rotate-left').click()
        expect(style(element, 'rotate')).toBe('-90deg')

        key(element, '0')
        expect(style(element, 'scale')).toBe('1')
        expect(style(element, 'rotate')).toBe('0deg')
        expect(button(element, 'reset').disabled).toBe(true)

        for (let i = 0; i < 10; i++) button(element, 'zoom-out').click()
        expect(style(element, 'scale')).toBe('0.5')
        expect(button(element, 'zoom-out').disabled).toBe(true)
    })

    it('pans by dragging only when zoomed, without closing', () => {
        const element = mount()
        const pointer = (type, x, y) => element.image.dispatchEvent(new MouseEvent(type, { bubbles: true, clientX: x, clientY: y, button: 0 }))

        element.open(0)
        pointer('pointerdown', 10, 10)
        pointer('pointermove', 50, 50)
        expect(style(element, 'x')).toBe('0px')

        element.image.dispatchEvent(new MouseEvent('dblclick', { bubbles: true }))
        pointer('pointerdown', 10, 10)
        pointer('pointermove', 40, 30)
        pointer('pointerup', 40, 30)
        element.dialog.click()

        expect(style(element, 'x')).toBe('30px')
        expect(style(element, 'y')).toBe('20px')
        expect(element.dialog.open).toBe(true)
    })

    it('closes with its button and a click beside the image', () => {
        const element = mount()

        element.open(0)
        button(element, 'close').click()
        expect(element.dialog.open).toBe(false)

        element.open(0)
        element.image.click()
        expect(element.dialog.open).toBe(true)

        element.dialog.click()
        expect(element.dialog.open).toBe(false)
    })

    it('marks a single image, which has nothing to page through', () => {
        const element = mount(1)

        element.open(0)

        expect(element.dialog.hasAttribute('data-single')).toBe(true)
    })
})
