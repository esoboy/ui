import { beforeEach, expect, it } from 'vitest'
import { register as registerAvatar } from '../../resources/js/components/avatar.js'

let morphUpdated

beforeEach(() => {
    document.body.innerHTML = ''
})

function mount(complete, naturalWidth) {
    document.body.innerHTML = '<span data-slot="avatar"><img data-slot="avatar-image" src="/a.png"><span data-slot="avatar-fallback">AB</span></span>'
    const image = document.querySelector('img')
    Object.defineProperty(image, 'complete', { value: complete, configurable: true })
    Object.defineProperty(image, 'naturalWidth', { value: naturalWidth, configurable: true })
    return image
}

it('hides an image that fails to load', () => {
    registerAvatar()
    const image = mount(false, 0)

    image.dispatchEvent(new Event('error'))

    expect(image.hidden).toBe(true)
})

it('hides images that already failed before it ran', () => {
    const image = mount(true, 0)

    registerAvatar()

    expect(image.hidden).toBe(true)
})

it('keeps images that loaded', () => {
    const image = mount(true, 64)

    registerAvatar()

    expect(image.hidden).toBe(false)
})

it('hides a failed image again after a Livewire morph', () => {
    window.Livewire = { hook: (name, callback) => name === 'morph.updated' && (morphUpdated = callback) }
    registerAvatar()
    document.dispatchEvent(new Event('livewire:init'))

    const image = mount(true, 0)
    morphUpdated({ el: image })

    expect(image.hidden).toBe(true)
})

it('ignores other images', () => {
    registerAvatar()
    document.body.innerHTML = '<img src="/x.png">'
    const image = document.querySelector('img')

    image.dispatchEvent(new Event('error'))

    expect(image.hidden).toBe(false)
})
