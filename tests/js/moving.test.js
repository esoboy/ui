import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerCollapsible } from '../../resources/js/components/collapsible.js'
import { register as registerRating } from '../../resources/js/components/rating.js'
import { register as registerSelect } from '../../resources/js/components/select.js'

// A keyed morph or wire:sort moves an element: it disconnects and connects again, and
// must still handle each event once.

beforeAll(() => {
    registerCollapsible()
    registerRating()
    registerSelect()
})

afterEach(() => {
    document.body.innerHTML = ''
})

function move(element) {
    const elsewhere = document.createElement('div')

    document.body.append(elsewhere)
    elsewhere.append(element)

    return element
}

it('toggles a moved collapsible once per click', () => {
    document.body.innerHTML = `
        <eso-collapsible>
            <button data-slot="collapsible-trigger">Toggle</button>
            <div data-slot="collapsible-content" hidden>Details</div>
        </eso-collapsible>`

    const collapsible = move(document.querySelector('eso-collapsible'))

    collapsible.querySelector('button').click()

    expect(collapsible.value).toBe(true)
})

it('rates once per click after a move', () => {
    document.body.innerHTML = `
        <eso-rating>
            ${[1, 2, 3].map(star => `<button data-slot="rating-item" data-value="${star}"></button>`).join('')}
        </eso-rating>`

    const rating = move(document.querySelector('eso-rating'))

    rating.querySelector('[data-value="2"]').click()

    expect(rating.value).toBe(2)
})

it('opens a moved select once per click', () => {
    document.body.innerHTML = `
        <eso-select>
            <button data-slot="select-trigger" command="toggle-popover" commandfor=""><span data-slot="select-value" data-placeholder="Pick">Pick</span></button>
            <div data-slot="select-content" popover="auto" data-align-trigger="false">
                <div data-slot="select-item" data-value="apple" tabindex="-1"><span data-slot="select-item-text">Apple</span></div>
            </div>
        </eso-select>`

    const select = move(document.querySelector('eso-select'))

    select.trigger.click()

    expect(select.isOpen).toBe(true)
})
