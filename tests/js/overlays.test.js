import { afterEach, expect, it, vi } from 'vitest'
import { provider, cleanupControllers } from './alpine-harness.js'
import { content, trigger, contentOf, triggerOf } from '../../resources/js/alpine/overlay.js'
import dialog from '../../resources/js/alpine/dialog.js'
import { confirmation, register } from '../../resources/js/alpine/confirm.js'

afterEach(() => { cleanupControllers(); vi.useRealTimers(); document.body.innerHTML = '' })
it('pairs only explicit PHP targets even when DOM order changes', () => {
    document.body.innerHTML = '<button id="a" data-ui-popover="pa"></button><button id="b" data-ui-popover="pb"></button><div id="pb"></div><div id="pa"></div><button id="unpaired"></button>'
    expect(contentOf(document.getElementById('a'),'popover')?.id).toBe('pa')
    expect(triggerOf(document.getElementById('pb'),'popover')?.id).toBe('b')
    expect(contentOf(document.getElementById('unpaired'),'popover')).toBeNull()
})
it('delays hover cards, cancels a leave into the card, and cleans timers', () => {
    vi.useFakeTimers(); const data = provider(trigger, '<button data-ui-hover-card="card" data-delay="200" data-close-delay="100"></button>')
    document.body.insertAdjacentHTML('beforeend','<div id="card" popover="manual"></div>'); const panel = document.getElementById('card')
    data.enter({ type:'pointerover', relatedTarget:null }); vi.advanceTimersByTime(199); expect(panel.matches(':popover-open')).toBe(false)
    vi.advanceTimersByTime(1); expect(panel.matches(':popover-open')).toBe(true)
    data.leave({ relatedTarget:panel }); vi.advanceTimersByTime(200); expect(panel.matches(':popover-open')).toBe(true)
    data.leave({ relatedTarget:null }); vi.advanceTimersByTime(100); expect(panel.matches(':popover-open')).toBe(false)
    data.enter({ type:'pointerover', relatedTarget:null }); data.destroy(); vi.advanceTimersByTime(300); expect(panel.matches(':popover-open')).toBe(false)
})
it('makes a tooltip available on focus and removes its description on leave', () => {
    vi.useFakeTimers(); const data = provider(trigger,'<button data-ui-tooltip="tip"></button>')
    document.body.insertAdjacentHTML('beforeend','<div id="tip" popover="manual"></div>')
    data.enter({type:'focusin'}); vi.advanceTimersByTime(0); expect(data.$el.getAttribute('aria-describedby')).toBe('tip')
    data.leave({relatedTarget:null}); vi.advanceTimersByTime(0); expect(data.$el.hasAttribute('aria-describedby')).toBe(false)
})
it('positions a context menu at the pointer and prevents the native menu', () => {
    const data = provider(trigger,'<button data-ui-context-menu="menu"></button>')
    document.body.insertAdjacentHTML('beforeend','<div id="menu" popover data-align-offset="4"></div>')
    const event = {clientX:20,clientY:30,preventDefault:vi.fn()}; data.context(event)
    expect(event.preventDefault).toHaveBeenCalled(); expect(document.getElementById('menu').style.top).toBe('34px')
})
it('moves over enabled menu items, wraps, and closes after an action', () => {
    const data = provider(content,'<div id="m" data-slot="dropdown-menu-content" role="menu" popover tabindex="-1"><button data-slot="dropdown-menu-item" role="menuitem">A</button><button role="menuitem" disabled>B</button><button data-slot="dropdown-menu-item" role="menuitem">C</button></div>')
    const buttons = data.$el.querySelectorAll('button'); data.$el.showPopover()
    data.key({target:buttons[0],key:'ArrowDown',preventDefault(){}}); expect(document.activeElement).toBe(buttons[2])
    data.key({target:buttons[2],key:'ArrowDown',preventDefault(){}}); expect(document.activeElement).toBe(buttons[0])
    data.click({target:buttons[0]}); expect(data.$el.matches(':popover-open')).toBe(false)
})
it('reflects native popup toggles into Livewire without event loops', () => {
    let open = false; const wire = {$get:()=>open,$set:vi.fn((_,v)=>open=v)}
    const data = provider(content,'<div data-slot="popover-content" popover></div>',{binding:{property:'open',live:true},wire})
    data.toggled({target:data.$el,newState:'open'}); data.toggled({target:data.$el,newState:'open'})
    expect(wire.$set).toHaveBeenCalledTimes(1); expect(wire.$set).toHaveBeenCalledWith('open',true,true)
})
it('opens a native dialog from the server and binds closing back', () => {
    let open = true; const wire = {$get:()=>open,$set:vi.fn((_,v)=>open=v)}
    const data = provider(dialog,'<dialog id="edit"></dialog>',{binding:{property:'editing',live:true},wire})
    expect(data.$el.open).toBe(true); data.$el.close(); data.changed(); expect(wire.$set).toHaveBeenCalledWith('editing',false,true)
    data.requested({name:'other',open:true}); expect(data.$el.open).toBe(false)
    data.requested({name:'edit',open:true}); expect(data.$el.open).toBe(true)
})
it('dismisses only dialog backdrop clicks outside the actual box', () => {
    const data = provider(dialog,'<dialog></dialog>')
    data.$el.getBoundingClientRect = ()=>({left:20,right:100,top:20,bottom:100}); data.setOpen(true)
    data.backdrop({target:data.$el,clientX:50,clientY:50}); expect(data.$el.open).toBe(true)
    data.backdrop({target:data.$el,clientX:0,clientY:0}); expect(data.$el.open).toBe(false)
    data.$el.setAttribute('role','alertdialog'); data.setOpen(true)
    data.backdrop({target:data.$el,clientX:0,clientY:0}); expect(data.$el.open).toBe(true)
})
it('resolves a rendered confirmation once and restores focus', () => {
    const data = provider(() => confirmation({}), '<dialog><h2>Delete?</h2><button value="confirm">Delete</button></dialog>')
    const action = vi.fn(), cancel = vi.fn(), source = document.createElement('button')
    document.body.append(source)
    data.ask(action, cancel, source)
    expect(data.$el.querySelector('h2').textContent).toBe('Delete?')
    data.$el.close('confirm')
    data.confirmClosed()
    data.confirmClosed()
    expect(action).toHaveBeenCalledOnce()
    expect(document.activeElement).toBe(source)
    data.ask(action, cancel, source)
    data.$el.close()
    data.confirmClosed()
    expect(cancel).toHaveBeenCalledOnce()
})

it('leaves prompt confirmations to Livewire and prepares the destructive popup', async () => {
    document.body.innerHTML = '<button></button><div data-ui-confirm-host wire:id="confirm"><div id="ui-confirm-popover-destructive"></div></div>'
    let hook, prepared
    const prepare = vi.fn(() => new Promise(resolve => { prepared = resolve }))
    const Livewire = { hook: (_, callback) => { hook = callback }, find: () => ({ prepare }) }
    const Alpine = { evaluate: vi.fn() }
    register(Livewire, Alpine)
    const el = document.querySelector('button')
    hook({ el, directive: { value: 'confirm', modifiers: ['prompt'], expression: 'Type DELETE' } })
    expect(el.__livewire_confirm).toBeUndefined()
    hook({ el, directive: { value: 'confirm', modifiers: ['popover', 'destructive'], expression: 'Delete?|Delete|Details' } })
    el.__livewire_confirm(vi.fn(), vi.fn())
    await vi.waitFor(() => expect(prepare).toHaveBeenCalledExactlyOnceWith('ui-confirm-popover-destructive', 'Delete?', 'Delete', 'Details'))
    expect(Alpine.evaluate).not.toHaveBeenCalled()
    prepared()
    await vi.waitFor(() => expect(Alpine.evaluate.mock.calls[0][0].id).toBe('ui-confirm-popover-destructive'))
})
