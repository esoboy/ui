import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { register as registerRating } from '../../resources/js/components/rating.js'

beforeAll(() => registerRating())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(value = 2) {
    document.body.innerHTML = `
        <eso-rating data-value="${value}">
            ${[1, 2, 3, 4, 5].map(star => `<button data-slot="rating-item" data-value="${star}"></button>`).join('')}
            <input type="hidden" name="score">
        </eso-rating>`

    const element = document.querySelector('eso-rating')
    const seen = []

    element.addEventListener('change', event => event.target === element && seen.push(element.value))

    return [element, seen, [...element.querySelectorAll('[data-slot="rating-item"]')]]
}

const fills = items => items.map(item => item.style.getPropertyValue('--rating-fill'))

describe('rating', () => {
    it('fills up to its value with one focusable star', () => {
        const [, , items] = mount(2)

        expect(fills(items)).toEqual(['100%', '100%', '0%', '0%', '0%'])
        expect(items.map(item => item.getAttribute('aria-checked'))).toEqual(['false', 'true', 'false', 'false', 'false'])
        expect(items.map(item => item.tabIndex)).toEqual([-1, 0, -1, -1, -1])
    })

    it('picks with a click and clears with the same star', () => {
        const [element, seen, items] = mount(2)

        items[3].click()
        items[3].click()

        expect(seen).toEqual([4, null])
        expect(fills(items)).toEqual(['0%', '0%', '0%', '0%', '0%'])
        expect(items[0].tabIndex).toBe(0)
        expect(element.querySelector('input[type="hidden"]').value).toBe('')
    })

    it('moves with the arrows, Home and End', () => {
        const [element, seen, items] = mount(2)
        const key = name => items[1].dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

        key('ArrowRight')
        key('End')
        key('ArrowRight')
        key('Home')
        key('ArrowLeft')

        expect(seen).toEqual([3, 5, 1])
        expect(element.value).toBe(1)
        expect(document.activeElement).toBe(items[0])
    })

    it('ignores disabled stars', () => {
        const [, seen, items] = mount(2)

        items.forEach(item => (item.disabled = true))
        items[4].click()
        items[1].dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }))

        expect(seen).toEqual([])
    })

    it('puts its stars back after a morph', async () => {
        const [element, , items] = mount(2)

        element.value = 4
        items[3].setAttribute('aria-checked', 'false')
        items[3].style.setProperty('--rating-fill', '0%')
        await Promise.resolve()

        expect(items[3].getAttribute('aria-checked')).toBe('true')
        expect(fills(items)).toEqual(['100%', '100%', '100%', '100%', '0%'])
    })
})
