import { beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { register as registerInputGroup } from '../../resources/js/components/input-group.js'

beforeAll(() => registerInputGroup())

beforeEach(() => {
    document.body.innerHTML = `
        <div data-slot="input-group">
            <input data-slot="input-group-control">
            <div data-slot="input-group-addon"><span>https://</span><button type="button">Clear</button></div>
        </div>
    `
})

const input = () => document.querySelector('input')

it('focuses the input when an addon is clicked', () => {
    document.querySelector('span').click()

    expect(document.activeElement).toBe(input())
})

it('leaves focus alone when a button in the addon is clicked', () => {
    const button = document.querySelector('button')
    button.focus()
    button.click()

    expect(document.activeElement).toBe(button)
})

describe('count', () => {
    function mount(control, count = '') {
        document.body.innerHTML = `
            <div data-slot="input-group">
                ${control}
                <div data-slot="input-group-addon"><eso-input-group-count data-label=":count characters" data-label-max=":count/:max characters" ${count}></eso-input-group-count></div>
            </div>`

        return document.querySelector('eso-input-group-count')
    }

    const type = (control, value) => {
        control.value = value
        control.dispatchEvent(new Event('input', { bubbles: true }))
    }

    it('counts characters as Laravel does, against the control\'s maxlength', () => {
        const count = mount('<textarea data-slot="input-group-control" maxlength="280" aria-describedby="hint"></textarea>')
        const control = document.querySelector('textarea')

        expect(count.textContent).toBe('0/280 characters')
        expect(control.getAttribute('aria-describedby')).toBe(`hint ${count.id}`)

        type(control, 'héllo 👋')
        expect(count.textContent).toBe('7/280 characters')
    })

    it('marks a soft limit passed, and counts without a limit', () => {
        const count = mount('<input data-slot="input-group-control" value="abcd">', 'data-max="3"')

        expect(count.textContent).toBe('4/3 characters')
        expect(count.hasAttribute('data-over')).toBe(true)

        type(document.querySelector('input'), 'abc')
        expect(count.hasAttribute('data-over')).toBe(false)

        const unlimited = mount('<input data-slot="input-group-control" value="ab">')
        expect(unlimited.textContent).toBe('2 characters')
    })

    it('describes the control again after a morph drops it', () => {
        const count = mount('<textarea data-slot="input-group-control"></textarea>')
        const control = document.querySelector('textarea')

        control.removeAttribute('aria-describedby')
        control.value = 'set by Livewire'
        count.render()

        expect(control.getAttribute('aria-describedby')).toBe(count.id)
        expect(count.textContent).toBe('15 characters')
    })
})
