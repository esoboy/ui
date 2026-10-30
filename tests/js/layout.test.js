import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerCarousel } from '../../resources/js/components/carousel.js'
import { register as registerResizable } from '../../resources/js/components/resizable.js'
import { register as registerScrollArea } from '../../resources/js/components/scroll-area.js'

beforeAll(() => {
    registerCarousel()
    registerResizable()
    registerScrollArea()
})

afterEach(() => {
    document.body.innerHTML = ''
})

// jsdom does no layout: give an element the sizes a browser would.
function size(element, sizes) {
    for (const [name, value] of Object.entries(sizes)) Object.defineProperty(element, name, { configurable: true, value, writable: true })
}

describe('scroll area', () => {
    function mount() {
        document.body.innerHTML = `
            <eso-scroll-area>
                <div data-slot="scroll-area-viewport"><div>Content</div></div>
                <div data-slot="scroll-area-scrollbar" data-orientation="vertical" hidden><div data-slot="scroll-area-thumb"></div></div>
                <div data-slot="scroll-area-scrollbar" data-orientation="horizontal" hidden><div data-slot="scroll-area-thumb"></div></div>
            </eso-scroll-area>`

        return document.querySelector('eso-scroll-area')
    }

    it('shows a scrollbar for an overflowing axis and sizes its thumb', () => {
        const area = mount()
        const bar = area.scrollbar('vertical')

        size(area.viewport, { clientHeight: 286, scrollHeight: 1912, scrollTop: 200, clientWidth: 190, scrollWidth: 190 })
        size(bar, { clientHeight: 286, offsetWidth: 10 })
        bar.style.padding = '1px'
        area.update()

        expect(bar.hidden).toBe(false)
        expect(area.scrollbar('horizontal').hidden).toBe(true)
        // Base UI's numbers for the same sizes: a 42.48px thumb moved 29.71px.
        expect(parseFloat(bar.style.getPropertyValue('--scroll-area-thumb-height'))).toBeCloseTo(42.48, 2)
        expect(parseFloat(bar.firstElementChild.style.transform.slice(11))).toBeCloseTo(29.707, 2)
    })

    it('marks itself as scrolling for a moment', () => {
        const area = mount()

        area.viewport.dispatchEvent(new Event('scroll'))

        expect(area.hasAttribute('data-scrolling')).toBe(true)
    })
})

describe('resizable', () => {
    function mount(orientation = 'horizontal') {
        document.body.innerHTML = `
            <eso-resizable-panel-group data-orientation="${orientation}">
                <div data-slot="resizable-panel" data-default-size="25" data-min-size="10"></div>
                <div data-slot="resizable-handle" tabindex="0"></div>
                <div data-slot="resizable-panel" data-max-size="80"></div>
                <div data-slot="resizable-handle" tabindex="0"></div>
                <div data-slot="resizable-panel"></div>
            </eso-resizable-panel-group>`

        return document.querySelector('eso-resizable-panel-group')
    }

    const press = (handle, key) => handle.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true }))

    it('shares what sized panels leave between the others', () => {
        const group = mount()

        expect(group.sizes).toEqual([25, 37.5, 37.5])
        expect(group.panels[0].style.flex).toBe('25 1 0px')
        expect(group.querySelector('[data-slot="resizable-handle"]').getAttribute('aria-valuenow')).toBe('25')
    })

    it('moves size with the arrow keys within min and max', () => {
        const group = mount()
        const [first, second] = group.querySelectorAll('[data-slot="resizable-handle"]')

        press(first, 'ArrowRight')
        expect(group.sizes).toEqual([35, 27.5, 37.5])

        press(first, 'Home')
        expect(group.sizes).toEqual([10, 52.5, 37.5])

        press(second, 'End')
        expect(group.sizes).toEqual([10, 80, 10])
    })

    it('uses up and down when vertical', () => {
        const group = mount('vertical')

        press(group.querySelector('[data-slot="resizable-handle"]'), 'ArrowDown')

        expect(group.sizes[0]).toBe(35)
    })

    it('follows a dragged handle', () => {
        const group = mount()
        const handle = group.querySelector('[data-slot="resizable-handle"]')

        size(group, { offsetWidth: 400 })
        handle.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true, clientX: 100, button: 0 }))
        handle.dispatchEvent(new MouseEvent('pointermove', { clientX: 140 }))
        handle.dispatchEvent(new MouseEvent('pointerup', {}))

        expect(group.sizes).toEqual([35, 27.5, 37.5])
    })
})

describe('carousel', () => {
    function mount() {
        document.body.innerHTML = `
            <eso-carousel data-orientation="horizontal">
                <div data-slot="carousel-content"><div><div data-slot="carousel-item"></div><div data-slot="carousel-item"></div></div></div>
                <button data-carousel="previous" disabled></button>
                <button data-carousel="next"></button>
            </eso-carousel>`

        const carousel = document.querySelector('eso-carousel')

        size(carousel.viewport, { scrollLeft: 0, scrollWidth: 640, clientWidth: 320 })
        size(carousel.querySelector('[data-slot="carousel-item"]'), { offsetWidth: 320 })

        return carousel
    }

    const button = (carousel, name) => carousel.querySelector(`[data-carousel="${name}"]`)

    it('disables the buttons at either end', () => {
        const carousel = mount()

        carousel.update()
        expect([button(carousel, 'previous').disabled, button(carousel, 'next').disabled]).toEqual([true, false])

        carousel.viewport.scrollLeft = 320
        carousel.update()
        expect([button(carousel, 'previous').disabled, button(carousel, 'next').disabled]).toEqual([false, true])
    })

    it('scrolls one slide from the buttons and the arrow keys', () => {
        const carousel = mount()
        const calls = []

        carousel.viewport.scrollBy = options => calls.push(options.left)
        button(carousel, 'next').click()
        carousel.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true }))

        expect(calls).toEqual([320, -320])
    })
})
