import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerInputOtp } from '../../resources/js/components/input-otp.js'

beforeAll(() => registerInputOtp())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount() {
    document.body.innerHTML = `
        <eso-input-otp>
            <div data-slot="input-otp-group">
                ${[0, 1, 2, 3].map(index => `<div data-slot="input-otp-slot" data-index="${index}"></div>`).join('')}
            </div>
            <input data-slot="input-otp-input" maxlength="4">
        </eso-input-otp>`

    return document.querySelector('[data-slot="input-otp-input"]')
}

const slots = () => [...document.querySelectorAll('[data-slot="input-otp-slot"]')]
const chars = () => slots().map(slot => slot.textContent)
const active = () => slots().map(slot => slot.dataset.active)

it('mirrors typed characters into the slots', () => {
    const input = mount()

    input.focus()
    input.value = '12'
    input.setSelectionRange(2, 2)
    input.dispatchEvent(new Event('input', { bubbles: true }))

    expect(chars()).toEqual(['1', '2', '', ''])
    expect(active()).toEqual(['false', 'false', 'true', 'false'])
    expect(slots()[2].dataset.caret).toBe('true')
})

it('keeps the last slot active once full', () => {
    const input = mount()

    input.focus()
    input.value = '1234'
    input.dispatchEvent(new Event('input', { bubbles: true }))

    expect(active()).toEqual(['false', 'false', 'false', 'true'])
    expect(slots()[3].dataset.caret).toBe('false')
})

it('shows nothing active without focus', () => {
    const input = mount()

    input.value = '9'

    expect(chars()).toEqual(['9', '', '', ''])
    expect(active()).toEqual(['false', 'false', 'false', 'false'])
})

it('follows values set by Livewire without events', () => {
    const input = mount()

    input.value = '4321'

    expect(chars()).toEqual(['4', '3', '2', '1'])
})
