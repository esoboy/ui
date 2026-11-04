import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerSlider } from '../../resources/js/components/slider.js'

beforeAll(() => registerSlider())

afterEach(() => {
    document.body.innerHTML = ''
})

function mount(values, name = '') {
    document.body.innerHTML = `
        <eso-slider ${name ? `name="${name}"` : ''}>
            <div data-slot="slider-control">
                <div data-slot="slider-track"><div data-slot="slider-range"></div></div>
                ${values.map(value => `<input type="range" data-slot="slider-thumb" min="0" max="200" step="1" value="${value}">`).join('')}
            </div>
        </eso-slider>`

    return document.querySelector('eso-slider')
}

const range = slider => slider.querySelector('[data-slot="slider-range"]').style
const move = (thumb, value) => {
    thumb.value = value
    thumb.dispatchEvent(new Event('input', { bubbles: true }))
}

it('is a number for one thumb and fills from the start', () => {
    const slider = mount([50])

    expect(slider.value).toBe(50)
    expect(range(slider).getPropertyValue('--start')).toBe('0')
    expect(range(slider).getPropertyValue('--end')).toBe('25')
})

it('is an array for several thumbs and fills between them', () => {
    const slider = mount([20, 100])

    expect(slider.value).toEqual([20, 100])
    expect(range(slider).getPropertyValue('--start')).toBe('10')
    expect(range(slider).getPropertyValue('--end')).toBe('50')
})

it('re-dispatches input from the slider with the new value', () => {
    const slider = mount([50])
    const seen = []

    slider.addEventListener('input', event => event.target === slider && seen.push(slider.value))
    move(slider.querySelector('input'), 120)

    expect(seen).toEqual([120])
    expect(range(slider).getPropertyValue('--end')).toBe('60')
})

it('keeps thumbs from crossing', () => {
    const slider = mount([20, 100])
    const [low, high] = slider.querySelectorAll('input')

    move(low, 150)
    expect(slider.value).toEqual([100, 100])

    move(high, 10)
    expect(slider.value).toEqual([100, 100])
})

it('takes values set by Livewire', () => {
    const slider = mount([20, 100])

    slider.value = [40, 160]

    expect(slider.value).toEqual([40, 160])
    expect(range(slider).getPropertyValue('--end')).toBe('80')
})

it('names its inputs for form posts', () => {
    expect([...mount([1], 'volume').querySelectorAll('input')].map(input => input.name)).toEqual(['volume'])
    expect([...mount([1, 2], 'price').querySelectorAll('input')].map(input => input.name)).toEqual(['price[]', 'price[]'])
})

// A 200px track from x = 0, so a pointer's x is its value on 0–200.
const press = (slider, clientX, type = 'pointerdown') => {
    const control = slider.querySelector('[data-slot="slider-control"]')

    control.getBoundingClientRect = () => ({ left: 0, right: 200, top: 0, bottom: 10, width: 200, height: 10 })
    control.setPointerCapture = () => {}
    ;(type === 'pointerdown' ? slider.querySelector('[data-slot="slider-track"]') : control).dispatchEvent(new MouseEvent(type, { bubbles: true, cancelable: true, button: 0, clientX }))
}

it('moves the thumb to a press on the track and drags it until release', () => {
    const slider = mount([50])
    const events = []
    // wire:model listens to the slider's own events (.self).
    slider.addEventListener('input', event => event.target === slider && events.push('input'))
    slider.addEventListener('change', event => event.target === slider && events.push('change'))

    press(slider, 150)
    expect(slider.value).toBe(150)
    expect(document.activeElement).toBe(slider.thumbs[0])

    press(slider, 170, 'pointermove')
    press(slider, 170, 'pointerup')
    press(slider, 20, 'pointermove')

    expect(slider.value).toBe(170)
    expect(events).toEqual(['input', 'input', 'change'])
})

it('moves the nearest of several thumbs', () => {
    const slider = mount([20, 100])

    press(slider, 90)
    expect(slider.value).toEqual([20, 90])

    press(slider, 0, 'pointerup')
    press(slider, 30)
    expect(slider.value).toEqual([30, 90])
})
