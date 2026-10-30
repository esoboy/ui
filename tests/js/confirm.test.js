import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { register as registerConfirm } from '../../resources/js/components/confirm.js'

let directiveInit

beforeAll(() => {
    window.Livewire = { hook: (name, callback) => name === 'directive.init' && (directiveInit = callback) }
    registerConfirm()
    document.dispatchEvent(new Event('livewire:init'))
})

beforeEach(() => {
    document.body.innerHTML = `
        <dialog id="ui-confirm">
            <h2 data-slot="alert-dialog-title">Are you sure?</h2>
            <button data-cancel>Cancel</button>
            <button value="confirm">Continue</button>
        </dialog>
        <dialog id="ui-confirm-destructive">
            <h2 data-slot="alert-dialog-title">Are you sure?</h2>
            <button value="confirm">Delete</button>
        </dialog>
        <div id="ui-confirm-popover" popover="auto">
            <h2 data-slot="popover-title">Are you sure?</h2>
            <button data-cancel>Cancel</button>
            <button value="confirm">Continue</button>
        </div>
        <button id="delete"></button>
    `
})

// What Livewire does for wire:confirm="...": its own directive first, then ours.
function bind(expression, modifiers = []) {
    const el = document.getElementById('delete')
    el.__livewire_confirm = vi.fn()
    directiveInit({ el, directive: { value: 'confirm', expression, modifiers } })
    return el
}

const dialog = () => document.getElementById('ui-confirm')
const title = () => dialog().querySelector('[data-slot="alert-dialog-title"]').textContent

it('asks with the alert dialog and runs the action on confirm', async () => {
    const action = vi.fn()
    const instead = vi.fn()

    bind('Delete this post?').__livewire_confirm(action, instead)

    expect(dialog().open).toBe(true)
    expect(title()).toBe('Delete this post?')

    dialog().close('confirm')
    await Promise.resolve()

    expect(action).toHaveBeenCalled()
    expect(instead).not.toHaveBeenCalled()
})

it('treats cancel and Escape as declining', async () => {
    const action = vi.fn()
    const instead = vi.fn()

    bind('Delete?').__livewire_confirm(action, instead)
    dialog().close()
    await Promise.resolve()

    expect(action).not.toHaveBeenCalled()
    expect(instead).toHaveBeenCalled()
})

it('falls back to the translated title without a message', () => {
    bind('Delete?').__livewire_confirm(vi.fn(), vi.fn())
    dialog().close()

    bind('').__livewire_confirm(vi.fn(), vi.fn())

    expect(title()).toBe('Are you sure?')
})

it('names the confirm button after the bar, and back without one', () => {
    const label = () => dialog().querySelector('[value="confirm"]').textContent

    bind('Publish this post?|Publish').__livewire_confirm(vi.fn(), vi.fn())
    expect(title()).toBe('Publish this post?')
    expect(label()).toBe('Publish')
    dialog().close()

    bind('Archive?').__livewire_confirm(vi.fn(), vi.fn())
    expect(label()).toBe('Continue')
})

it('asks with the destructive dialog for .destructive', async () => {
    const action = vi.fn()
    const destructive = document.getElementById('ui-confirm-destructive')

    bind('Delete this post?', ['destructive']).__livewire_confirm(action, vi.fn())

    expect(destructive.open).toBe(true)
    expect(dialog().open).toBe(false)
    expect(destructive.querySelector('h2').textContent).toBe('Delete this post?')

    destructive.close('confirm')
    await Promise.resolve()

    expect(action).toHaveBeenCalled()
})

it('leaves wire:confirm.prompt to Livewire', () => {
    const el = document.getElementById('delete')
    const livewire = (el.__livewire_confirm = vi.fn())

    directiveInit({ el, directive: { value: 'confirm', expression: 'Type DELETE|DELETE', modifiers: ['prompt'] } })

    expect(el.__livewire_confirm).toBe(livewire)
})

it('falls back to the browser\'s confirm, without the label, when the layout has no dialog', () => {
    const ask = vi.spyOn(window, 'confirm').mockReturnValue(true)
    const action = vi.fn()

    const el = bind('Delete?|Delete')
    dialog().remove()
    el.__livewire_confirm(action, vi.fn())

    expect(ask).toHaveBeenCalledWith('Delete?')
    expect(action).toHaveBeenCalled()
    ask.mockRestore()
})

describe('popover', () => {
    const popover = () => document.getElementById('ui-confirm-popover')

    it('asks in a popover anchored to the element, confirmed by its confirm button', async () => {
        const action = vi.fn()
        const el = bind('Remove this row?', ['popover'])
        const shown = vi.spyOn(popover(), 'showPopover')

        el.__livewire_confirm(action, vi.fn())

        expect(popover().matches(':popover-open')).toBe(true)
        expect(popover().querySelector('h2').textContent).toBe('Remove this row?')
        expect(shown).toHaveBeenCalledWith({ source: el })

        popover().querySelector('[value="confirm"]').click()
        popover().hidePopover()
        await Promise.resolve()

        expect(action).toHaveBeenCalled()
        expect(document.activeElement).toBe(el)
    })

    it('declines when it closes any other way', async () => {
        const action = vi.fn()
        const instead = vi.fn()
        const el = bind('Remove?', ['popover'])

        el.__livewire_confirm(action, instead)
        popover().querySelector('[data-cancel]').click()
        popover().hidePopover()
        await Promise.resolve()

        expect(action).not.toHaveBeenCalled()
        expect(instead).toHaveBeenCalled()
        expect(el.style.getPropertyValue('anchor-name')).toBe('')
    })
})
