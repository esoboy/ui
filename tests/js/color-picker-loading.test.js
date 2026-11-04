import { afterEach, expect, it } from 'vitest'
import { register as registerColorPicker } from '../../resources/js/components/color-picker.js'
import { register as registerSlider } from '../../resources/js/components/slider.js'

afterEach(() => { document.body.innerHTML = '' })

it('keeps the hue in sync when extras load before the core Slider', async () => {
    registerColorPicker()
    document.body.innerHTML = `
        <div><eso-color-picker data-value="#0000ff">
            <eso-slider data-color-picker-hue>
                <div data-slot="slider-control">
                    <div data-slot="slider-range"></div>
                    <input type="range" data-slot="slider-thumb" min="0" max="360">
                </div>
            </eso-slider>
        </eso-color-picker></div>`

    const picker = document.querySelector('eso-color-picker')
    const slider = picker.querySelector('eso-slider')

    expect(Object.hasOwn(slider, 'value')).toBe(false)

    registerSlider()
    await customElements.whenDefined('eso-slider')
    await Promise.resolve()
    expect(slider.value).toBe(240)

    const changes = []
    picker.parentElement.addEventListener('change', event => changes.push([event.target, picker.value]))
    slider.thumbs[0].value = '120'
    slider.thumbs[0].dispatchEvent(new Event('input', { bubbles: true }))
    slider.thumbs[0].dispatchEvent(new Event('change', { bubbles: true }))

    expect(picker.value).toBe('#00ff00')
    expect(changes).toEqual([[picker, '#00ff00']])
})
