import { afterEach, expect, it, vi } from 'vitest'
import { provider, cleanupControllers } from './alpine-harness.js'
import colorPicker, { hexToHsv, hsvToHex } from '../../resources/js/alpine/color-picker.js'

afterEach(() => {
    cleanupControllers()
    delete window.EyeDropper
})

it.each(['#2563eb', '#22c55e', '#a3a3a3', '#ec4899', '#000000'])('round trips %s through HSV', hex => {
    expect(hsvToHex(hexToHsv(hex))).toBe(hex)
})

function mount(color = '#ff0000') {
    const wire = { setColor: vi.fn().mockResolvedValue(undefined) }
    return provider(colorPicker, `<div data-value="${color}">
        <span data-slot="color-picker-value">${color}</span>
        <div data-slot="color-picker-area" tabindex="0"><span data-slot="color-picker-thumb"></span></div>
    </div>`, { wire })
}

it('paints a preview during a gesture and commits its color only once requested', () => {
    const picker = mount()
    picker.preview({ v: .9 })
    expect(picker.$wire.setColor).not.toHaveBeenCalled()
    expect(picker.$el.querySelector('[data-slot="color-picker-value"]').textContent).toBe('#ff0000')
    picker.commit()
    expect(picker.$wire.setColor).toHaveBeenCalledExactlyOnceWith('#e60000')
})

it('preserves hue for gray and black and relays the eyedropper result', async () => {
    window.EyeDropper = class { open = () => Promise.resolve({ sRGBHex: '#123456' }) }
    const picker = mount('#808080')
    picker.hue = 240
    expect(picker.hsv.h).toBe(240)
    await picker.pick()
    expect(picker.$wire.setColor).toHaveBeenCalledExactlyOnceWith('#123456')
})
