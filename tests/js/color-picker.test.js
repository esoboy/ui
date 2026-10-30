import { afterEach, beforeAll, describe, expect, it } from 'vitest'
import { hexToHsv, hsvToHex, parseHex, register as registerColorPicker } from '../../resources/js/components/color-picker.js'

beforeAll(() => registerColorPicker())

afterEach(() => {
    document.body.innerHTML = ''
})

describe('colors', () => {
    it('parses hex', () => {
        expect(parseHex('#2563EB')).toBe('#2563eb')
        expect(parseHex('f80')).toBe('#ff8800')
        expect(parseHex('red')).toBeNull()
        expect(parseHex(null)).toBeNull()
    })

    it('converts between hex and hsv', () => {
        expect(hexToHsv('#ff0000')).toEqual({ h: 0, s: 1, v: 1 })
        expect(hexToHsv('#000000')).toEqual({ h: 0, s: 0, v: 0 })
        expect(hsvToHex({ h: 120, s: 1, v: 1 })).toBe('#00ff00')

        for (const hex of ['#2563eb', '#22c55e', '#a3a3a3', '#ec4899']) expect(hsvToHex(hexToHsv(hex))).toBe(hex)
    })
})

describe('color picker', () => {
    function mount(value = '#2563eb') {
        document.body.innerHTML = `
            <eso-color-picker data-value="${value}" data-name="color" data-placeholder="Pick">
                <button data-color-picker-trigger><span data-slot="color-picker-swatch"></span><span data-slot="color-picker-value"></span></button>
                <div data-slot="color-picker-area" tabindex="0"><span data-slot="color-picker-thumb"></span></div>
                <input type="range" data-slot="color-picker-hue" min="0" max="360">
                <input data-color-picker-input>
                <button data-color-picker-swatch="#ef4444"></button>
                <input type="hidden" name="color">
            </eso-color-picker>`

        const element = document.querySelector('eso-color-picker')
        const seen = []

        element.addEventListener('change', event => event.target === element && seen.push([true, element.value]))

        return [element, seen]
    }

    const text = element => element.querySelector('[data-slot="color-picker-value"]').textContent

    it('shows its value on the trigger, the input and the hidden input', () => {
        const [element] = mount()

        expect(text(element)).toBe('#2563eb')
        expect(element.querySelector('[data-color-picker-input]').value).toBe('#2563eb')
        expect(element.querySelector('input[type="hidden"]').value).toBe('#2563eb')
        expect(element.querySelector('[data-color-picker-trigger]').dataset.empty).toBe('false')
    })

    it('takes swatches and announces only from itself', () => {
        const [element, seen] = mount()

        element.querySelector('[data-color-picker-swatch]').click()

        expect(element.value).toBe('#ef4444')
        expect(element.querySelector('[data-color-picker-swatch]').getAttribute('aria-pressed')).toBe('true')
        expect(seen).toEqual([[true, '#ef4444']])
    })

    it('changes the hue, keeping saturation and brightness', () => {
        const [element, seen] = mount('#ff0000')
        const hue = element.querySelector('[data-slot="color-picker-hue"]')

        hue.value = '240'
        hue.dispatchEvent(new Event('input', { bubbles: true }))
        hue.dispatchEvent(new Event('change', { bubbles: true }))

        expect(element.value).toBe('#0000ff')
        expect(seen).toEqual([[true, '#0000ff']])
    })

    it('moves through the area with the arrows', () => {
        const [element] = mount('#ff0000')
        const area = element.querySelector('[data-slot="color-picker-area"]')

        area.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', shiftKey: true, bubbles: true }))

        expect(element.value).toBe(hsvToHex({ h: 0, s: 1, v: 0.9 }))
        expect(area.getAttribute('aria-valuetext')).toBe('100%, 90%')
    })

    it('applies typed hex, restores invalid text and clears when emptied', () => {
        const [element, seen] = mount()
        const input = element.querySelector('[data-color-picker-input]')
        const type = value => {
            input.value = value
            input.dispatchEvent(new Event('change', { bubbles: true }))
        }

        type('0f0')
        expect(element.value).toBe('#00ff00')

        type('nope')
        expect(element.value).toBe('#00ff00')
        expect(input.value).toBe('#00ff00')

        type('')
        expect(element.value).toBeNull()
        expect(text(element)).toBe('Pick')
        expect(seen.map(([, value]) => value)).toEqual(['#00ff00', null])
    })

    it('takes values set by Livewire without announcing', () => {
        const [element, seen] = mount()

        element.value = '#ec4899'

        expect(text(element)).toBe('#ec4899')
        expect(seen).toEqual([])
    })
})
