import { afterEach, expect, it, vi } from 'vitest'
import { provider, cleanupControllers } from './alpine-harness.js'
import value from '../../resources/js/alpine/value.js'
import select from '../../resources/js/alpine/select.js'
import tabs from '../../resources/js/alpine/tabs.js'
import { accordion, collapsible } from '../../resources/js/alpine/disclosure.js'
import { numberInput, slider, otp, copyCode } from '../../resources/js/alpine/inputs.js'
import toggleGroup from '../../resources/js/alpine/toggle-group.js'

afterEach(() => { cleanupControllers(); document.body.innerHTML = ''; vi.useRealTimers() })
const event = (target, key) => ({ target, key, preventDefault: vi.fn() })
const bound = initial => {
    let current = initial
    return { $get: vi.fn(() => current), $set: vi.fn((_, next) => { current = next }), $commit: vi.fn() }
}
const choices = '<button data-slot="select-trigger"><span data-slot="select-value" data-placeholder="Pick">Pick</span></button><div data-slot="select-content" popover="auto"><button data-slot="select-item" data-value="a"><span data-slot="select-item-text"><b>Apple</b></span></button><button data-slot="select-item" data-value="b" aria-disabled="true"><span data-slot="select-item-text">Banana</span></button><button data-slot="select-item" data-value="c"><span data-slot="select-item-text">Cherry</span></button></div><select x-ref="input"><option value=""></option><option value="a">Apple</option><option value="c">Cherry</option></select>'

it('closes and returns focus after sending an enabled choice to Livewire', () => {
    const wire = { choose: vi.fn() }; const data = provider(select, '<div>'+choices+'</div>', { wire })
    data.choose(data.items[1]); expect(wire.choose).not.toHaveBeenCalled()
    data.content.showPopover(); data.choose(data.items[2])
    expect(wire.choose).toHaveBeenCalledWith('c')
    expect(data.content.matches(':popover-open')).toBe(false)
    expect(document.activeElement).toBe(data.trigger)
})
it('navigates select options while skipping disabled items and supports typeahead', () => {
    const data = provider(select, '<div>'+choices+'</div>')
    data.content.showPopover(); data.items[0].focus()
    data.key(event(data.items[0], 'ArrowDown')); expect(document.activeElement).toBe(data.items[2])
    data.key(event(data.items[2], 'a')); expect(document.activeElement).toBe(data.items[0])
    data.key(event(data.items[0], 'Tab')); expect(data.content.matches(':popover-open')).toBe(false)
})
it('defers a blur binding until blur and reads the existing bound property', () => {
    const wire = bound('old'); const data = provider(value, '<div><input x-ref="input"></div>', { binding: { property: 'title', live: true, blur: true }, wire })
    data.value = 'new'; expect(wire.$set).toHaveBeenCalledWith('title', 'new', false)
    expect(data.value).toBe('new'); data.blur(); expect(wire.$commit).toHaveBeenCalledOnce()
})
it('delegates native model events and modifier timing to Livewire', () => {
    const wire = bound('old')
    const data = provider(value, '<div><input x-ref="input" wire:model.live.debounce.250ms="title"></div>', { binding: { property: 'title', live: true }, wire })
    const events = []
    for (const type of ['input', 'change', 'blur']) data.$refs.input.addEventListener(type, event => events.push([type, event.detail]))
    data.value = 'new'; data.changed(); data.blur()
    expect(wire.$set).toHaveBeenCalledWith('title', 'new', false)
    expect(wire.$commit).not.toHaveBeenCalled()
    expect(events).toEqual([['input', 'new'], ['change', 'new'], ['blur', 'new']])
    data.value = null
    expect(data.$refs.input.value).toBe('')
})
it('links tab panels, ignores nested groups and moves across enabled tabs', () => {
    const wire = bound('a'); const data = provider(tabs, '<div data-slot="tabs"><input x-ref="input"><button data-slot="tabs-trigger" data-value="a">A</button><button data-slot="tabs-trigger" data-value="b" disabled>B</button><button data-slot="tabs-trigger" data-value="c">C</button><div data-slot="tabs-content" data-value="a"></div><div data-slot="tabs"><button data-slot="tabs-trigger" data-value="nested">Nested</button></div></div>', { binding: { property: 'tab', live: true }, wire })
    data.refresh(); const triggers = data.$el.querySelectorAll('button')
    expect(triggers[0].getAttribute('aria-controls')).toBe(data.$el.querySelector('[data-slot="tabs-content"]').id)
    data.key(event(triggers[0], 'ArrowRight')); expect(document.activeElement).toBe(triggers[2])
    data.click({ target: triggers[2] }); expect(wire.$set).toHaveBeenCalledWith('tab', 'c', true)
    data.click({ target: triggers[3] }); expect(wire.$set).toHaveBeenCalledTimes(1)
})
it('updates a bound disclosure through Livewire and links the trigger', () => {
    const wire = bound(false); const data = provider(() => collapsible({ property: 'open', live: true }), '<div data-slot="collapsible"><button data-slot="collapsible-trigger">Open</button><div data-slot="collapsible-content"></div></div>', { wire })
    data.toggle({ target: data.$el.querySelector('button') }); expect(wire.$set).toHaveBeenCalledWith('open', true, true)
    data.refresh(); expect(data.$el.querySelector('button').getAttribute('aria-expanded')).toBe('true')
    expect(data.$el.querySelector('button').getAttribute('aria-controls')).toBeTruthy()
})
it('moves accordion focus without changing application state', () => {
    const data = provider(accordion, '<div data-slot="accordion"><button data-slot="accordion-trigger">A</button><button data-slot="accordion-trigger" aria-disabled="true">B</button><button data-slot="accordion-trigger">C</button></div>')
    const buttons = data.$el.querySelectorAll('button'); data.key(event(buttons[0], 'ArrowDown'))
    expect(document.activeElement).toBe(buttons[2]); data.key(event(buttons[2], 'ArrowDown')); expect(document.activeElement).toBe(buttons[0])
})
it('clears a selected single toggle through the owning Livewire property', () => {
    const wire = bound('a'); const data = provider(toggleGroup, '<div><input type="radio" checked value="a"></div>', { binding: { property: 'align', live: true }, wire })
    const input = data.$el.querySelector('input'); data.remember({ target: input }); const click = event(input)
    data.clear(click); expect(click.preventDefault).toHaveBeenCalled(); expect(wire.$set).toHaveBeenCalledWith('align', null, true)
    expect(input.checked).toBe(false)
})
it('stops number input repetition on destroy', async () => {
    vi.useFakeTimers()
    const step = vi.fn().mockResolvedValue(undefined)
    const data = provider(numberInput, '<div><input x-ref="input"><button data-number-input="increment"></button></div>', { wire: { step } })
    data.press({ target: data.$el.querySelector('button'), button: 0, preventDefault() {} })
    await vi.advanceTimersByTimeAsync(450)
    expect(step).toHaveBeenCalledWith(1)
    data.destroy()
    await vi.advanceTimersByTimeAsync(1000)
    expect(step).toHaveBeenCalledTimes(1)
})
it('paints a range from native thumbs and prevents crossing', () => {
    const data = provider(slider, '<div><div data-slot="slider-control"><div data-slot="slider-range"></div><input type="range" data-slot="slider-thumb" min="0" max="100" value="20"><input type="range" data-slot="slider-thumb" min="0" max="100" value="80"></div></div>')
    data.paint(); const range = data.$el.querySelector('[data-slot="slider-range"]'); expect(range.style.getPropertyValue('--start')).toBe('20'); expect(range.style.getPropertyValue('--end')).toBe('80')
    data.thumbs[0].value = '90'; data.input({ target: data.thumbs[0] }); expect(data.thumbs[0].value).toBe('80')
})
it('drags the nearest slider thumb using the native input event value', () => {
    const data = provider(slider, '<div><div data-slot="slider-control"><div data-slot="slider-range"></div><input type="range" data-slot="slider-thumb" min="0" max="100" value="20"><input type="range" data-slot="slider-thumb" min="0" max="100" value="80"></div></div>')
    const control = data.$el.firstElementChild; control.getBoundingClientRect = () => ({ left: 0, width: 100 })
    const changed = vi.fn(); data.thumbs[1].addEventListener('input', changed)
    data.press({ target: control, currentTarget: control, button: 0, clientX: 70, preventDefault() {} }); data.end()
    expect(data.thumbs[1].value).toBe('70'); expect(changed.mock.calls[0][0].detail).toBe('70')
})
it('marks the active OTP caret without changing rendered characters', () => {
    const data = provider(otp, '<div><input data-slot="input-otp-input" maxlength="4" value="12"><div data-slot="input-otp-slot" data-index="0">1</div><div data-slot="input-otp-slot" data-index="2"></div></div>')
    const input = data.$el.querySelector('input'); input.focus(); input.setSelectionRange(2,2); data.render()
    const slots = data.$el.querySelectorAll('div'); expect(slots[0].textContent).toBe('1'); expect(slots[1].dataset.caret).toBe('true')
    input.blur(); data.render(); expect(slots[1].dataset.active).toBe('false')
})
it('copies code lines, announces success and clears the status', async () => {
    vi.useFakeTimers(); const writeText = vi.fn().mockResolvedValue(); Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText } })
    const data = provider(copyCode, '<div data-slot="code-block"><span data-slot="code-block-line-content">one</span><span data-slot="code-block-line-content">two</span><button x-ref="copy" data-label-copied="Copied"></button><span data-code-block-status></span></div>')
    data.$el = data.$refs.copy; await data.copy(); expect(writeText).toHaveBeenCalledWith('one\ntwo'); expect(data.copied).toBe(true)
    vi.advanceTimersByTime(2000); expect(data.copied).toBe(false)
})
