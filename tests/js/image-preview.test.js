import { provider, cleanupControllers } from './alpine-harness.js'
import { afterEach, describe, expect, it, vi } from 'vitest'
import createImagePreview from '../../resources/js/alpine/image-preview.js'


afterEach(() => {
    cleanupControllers()
    delete window.Livewire
    document.body.innerHTML = ''
})

const actions = ['close', 'previous', 'next', 'zoom-out', 'zoom-in', 'rotate-left', 'rotate-right', 'reset']

function mount(count = 3) {
    const triggers = Array.from({ length: count }, (_, index) => `<button data-slot="image-preview-trigger" data-src="/${index}.jpg" data-alt="Photo ${index}"></button>`).join('')

    const data = provider(createImagePreview, `
        <eso-image-preview>
            ${triggers}
            <div data-image-preview-state data-selected="0" wire:id="viewer">
            <dialog data-slot="image-preview-content">
                <img data-slot="image-preview-image" src="/0.jpg">
                ${actions.map(action => `<button data-image-preview="${action}"></button>`).join('')}
                <span data-slot="image-preview-counter"></span>
            </dialog>
            </div>
        </eso-image-preview>`)

    const wire = { select: vi.fn().mockResolvedValue(undefined), previous: vi.fn(), next: vi.fn() }
    window.Livewire = { find: () => wire }
    data.sync()
    data.dialog.showModal()
    return data
}

const button = (element, action) => element.$el.querySelector(`[data-image-preview="${action}"]`)
const style = (element, name) => element.image.style.getPropertyValue(`--image-preview-${name}`)
const key = (element, name) => element.image.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

describe('image preview', () => {
    it('asks the server to open the selected item and navigate by keyboard', async () => {
        const element = mount()
        const trigger = element.$el.querySelectorAll('[data-slot="image-preview-trigger"]')[1]
        trigger.click()
        expect(element.wire.select).toHaveBeenCalledExactlyOnceWith(1)
        await Promise.resolve()
        key(element, 'ArrowRight')
        expect(element.wire.next).toHaveBeenCalledOnce()
    })

    it('zooms within bounds, rotates and resets', () => {
        const element = mount()

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

        button(element, 'close').click()
        expect(element.dialog.open).toBe(false)

        element.dialog.showModal()
        element.image.click()
        expect(element.dialog.open).toBe(true)

        element.dialog.click()
        expect(element.dialog.open).toBe(false)
    })

})
