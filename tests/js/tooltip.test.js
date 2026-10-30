import { beforeAll, beforeEach, expect, it } from 'vitest'
import { register as registerTooltip } from '../../resources/js/components/tooltip.js'

let trigger
let tooltip

beforeAll(() => registerTooltip())

beforeEach(() => {
    document.body.innerHTML = `
        <button data-tooltip-trigger><span>Hover</span></button>
        <div id="t" popover="manual" data-slot="tooltip-content">Tip</div>
        <a id="elsewhere">x</a>
    `
    trigger = document.querySelector('[data-tooltip-trigger]')
    tooltip = document.getElementById('t')
})

const open = () => tooltip.matches(':popover-open')
const pointer = (type, target, relatedTarget = null) => target.dispatchEvent(new MouseEvent(type, { bubbles: true, relatedTarget }))

it('shows on hover and hides when the pointer leaves', () => {
    pointer('pointerover', trigger)
    expect(open()).toBe(true)

    pointer('pointerout', trigger, document.getElementById('elsewhere'))
    expect(open()).toBe(false)
})

it('stays while the pointer moves inside the trigger', () => {
    pointer('pointerover', trigger)
    pointer('pointerout', trigger, trigger.querySelector('span'))

    expect(open()).toBe(true)
})

it('shows on focus and hides on blur', async () => {
    trigger.focus()
    await Promise.resolve()
    expect(open()).toBe(true)

    trigger.dispatchEvent(new FocusEvent('focusout', { bubbles: true, relatedTarget: document.getElementById('elsewhere') }))
    expect(open()).toBe(false)
})

// Shown a moment after focus (a popover hiding gives focus back while none can be shown),
// if the focus is still there.
it('shows nothing for focus gone by then', async () => {
    trigger.focus()
    document.getElementById('elsewhere').setAttribute('tabindex', '-1')
    document.getElementById('elsewhere').focus()
    await Promise.resolve()

    expect(open()).toBe(false)
})

it('hides on Escape', () => {
    pointer('pointerover', trigger)
    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }))

    expect(open()).toBe(false)
})
