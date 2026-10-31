import { afterEach, beforeAll, expect, it } from 'vitest'
import { register as registerWatermark } from '../../resources/js/components/watermark.js'

beforeAll(() => registerWatermark())

afterEach(() => {
    document.body.innerHTML = ''
})

const flush = () => new Promise(resolve => setTimeout(resolve))

function mount() {
    document.body.innerHTML = `
        <eso-watermark data-slot="watermark">
            <p>Report</p>
            <!--[if BLOCK]><![endif]--><div data-slot="watermark-layer" aria-hidden="true" style="--watermark-image: url(&quot;tile&quot;); --watermark-size: 200px 160px"></div><!--[if ENDBLOCK]><![endif]-->
        </eso-watermark>`

    return document.querySelector('eso-watermark')
}

const layers = element => [...element.querySelectorAll(':scope > [data-slot="watermark-layer"]')]

it('puts the layer back where it was when it is removed', async () => {
    const element = mount()

    layers(element)[0].remove()
    await flush()

    expect(layers(element)).toHaveLength(1)
    expect(layers(element)[0].getAttribute('style')).toContain('url("tile")')
    // Still between Livewire's markers for the @if around it.
    expect(layers(element)[0].nextSibling.textContent).toBe('[if ENDBLOCK]><![endif]')
})

it('undoes changes to the layer, in place', async () => {
    const element = mount()

    layers(element)[0].style.display = 'none'
    await flush()

    expect(layers(element)[0].style.display).toBe('')
    expect(layers(element)[0].nextSibling.textContent).toBe('[if ENDBLOCK]><![endif]')

    layers(element)[0].removeAttribute('aria-hidden')
    await flush()

    expect(layers(element)[0].getAttribute('aria-hidden')).toBe('true')
})

it('keeps a single layer', async () => {
    const element = mount()

    element.append(layers(element)[0].cloneNode(true))
    await flush()

    expect(layers(element)).toHaveLength(1)
})

it('takes what Livewire renders as the new watermark', async () => {
    const element = mount()

    // As the morph hooks do while Livewire patches the layer.
    layers(element)[0].style.setProperty('--watermark-image', 'url("next")')
    element.accept(layers(element)[0])
    await flush()

    expect(layers(element)[0].getAttribute('style')).toContain('url("next")')

    element.accept(null)
    layers(element)[0].remove()
    await flush()

    expect(layers(element)).toHaveLength(0)
})

it('leaves the content alone', async () => {
    const element = mount()
    const layer = layers(element)[0]

    element.querySelector('p').textContent = 'Updated report'
    await flush()

    expect(layers(element)[0]).toBe(layer)
})
