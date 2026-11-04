import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerSelect } from '../../resources/js/components/select.js'

beforeAll(() => registerSelect())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(attributes = '') {
    document.body.innerHTML = `
        <eso-select data-name="fruit" ${attributes}>
            <button data-slot="select-trigger" command="toggle-popover" commandfor=""><span data-slot="select-value" data-placeholder="Pick">Pick</span></button>
            <div data-slot="select-content" popover="auto" data-align-trigger="false">
                <div data-slot="select-item" data-value="apple" tabindex="-1"><span data-slot="select-item-text">Apple</span></div>
                <div data-slot="select-item" data-value="banana" tabindex="-1"><span data-slot="select-item-text">Banana</span></div>
                <div data-slot="select-item" data-value="kiwi" tabindex="-1" aria-disabled="true"><span data-slot="select-item-text">Kiwi</span></div>
                <div data-slot="select-item" data-value="blueberry" tabindex="-1"><span data-slot="select-item-text">Blueberry</span></div>
            </div>
            <input type="hidden" name="fruit" value="">
        </eso-select>`

    return document.querySelector('eso-select')
}

const key = (target, name) => target.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))
const display = select => select.querySelector('[data-slot="select-value"]').textContent
const item = (select, value) => select.querySelector(`[data-value="${value}"]`)

it('shows the placeholder until something is chosen', () => {
    const select = mount()

    expect(select.value).toBeNull()
    expect(display(select)).toBe('Pick')
    expect(select.trigger.hasAttribute('data-placeholder')).toBe(true)
})

it('starts from the server value', () => {
    const select = mount('data-value="banana"')

    expect(select.value).toBe('banana')
    expect(display(select)).toBe('Banana')
    expect(item(select, 'banana').getAttribute('aria-selected')).toBe('true')
    expect(select.querySelector('input[type="hidden"]').value).toBe('banana')
})

it('opens on the trigger and chooses with the keyboard', () => {
    const select = mount('data-value="apple"')
    const seen = []

    select.addEventListener('input', event => event.target === select && seen.push(select.value))

    key(select.trigger, 'ArrowDown')
    expect(select.isOpen).toBe(true)
    expect(document.activeElement).toBe(item(select, 'apple'))

    key(document.activeElement, 'ArrowDown')
    key(document.activeElement, 'ArrowDown')
    expect(document.activeElement).toBe(item(select, 'blueberry'))

    key(document.activeElement, 'Enter')
    expect(select.value).toBe('blueberry')
    expect(display(select)).toBe('Blueberry')
    expect(select.isOpen).toBe(false)
    expect(document.activeElement).toBe(select.trigger)
    expect(seen).toEqual(['blueberry'])
})

it('jumps to typed text', () => {
    const select = mount()

    select.open()
    key(document.activeElement, 'b')
    key(document.activeElement, 'l')

    expect(document.activeElement).toBe(item(select, 'blueberry'))
})

it('chooses on click and ignores disabled items', () => {
    const select = mount()

    select.open()
    item(select, 'kiwi').click()
    expect(select.value).toBeNull()

    item(select, 'banana').click()
    expect(select.value).toBe('banana')
})

it('does not announce re-choosing the same value', () => {
    const select = mount('data-value="apple"')
    const seen = []

    select.addEventListener('input', event => event.target === select && seen.push(select.value))
    select.open()
    item(select, 'apple').click()

    expect(seen).toEqual([])
})

it('mirrors the field state onto the trigger', async () => {
    const select = mount('aria-invalid="true" aria-describedby="fruit-error"')

    expect(select.trigger.getAttribute('aria-invalid')).toBe('true')
    expect(select.trigger.getAttribute('aria-describedby')).toBe('fruit-error')

    select.removeAttribute('aria-invalid')
    await Promise.resolve()

    expect(select.trigger.hasAttribute('aria-invalid')).toBe(false)
})

it('restores its display after a morph resets it', async () => {
    const select = mount('data-value="apple"')

    select.querySelector('[data-slot="select-value"]').textContent = 'Pick'
    await Promise.resolve()

    expect(display(select)).toBe('Apple')
})
