import { afterEach, expect, it, vi } from 'vitest'
import { provider, cleanupControllers } from './alpine-harness.js'
import calendar from '../../resources/js/alpine/calendar.js'
import cascader from '../../resources/js/alpine/cascader.js'
import combobox from '../../resources/js/alpine/combobox.js'
import tree from '../../resources/js/alpine/tree.js'
import questionnaire from '../../resources/js/alpine/questionnaire.js'
import emoji from '../../resources/js/alpine/emoji-picker.js'

afterEach(()=>{cleanupControllers();document.body.innerHTML=''})
it('preserves an open tree picker across a server morph and disposes its hooks', () => {
    const hooks = {}, stops = []
    window.Livewire = { hook: (name, callback) => { hooks[name] = callback; const stop = vi.fn(); stops.push(stop); return stop } }
    const data = provider(tree, '<div><button data-tree-select-trigger></button><div data-slot="tree-select-content" popover><ul data-slot="tree"><li data-slot="tree-item" data-value="a" tabindex="0"></li></ul></div></div>')
    const panel = data.$el.querySelector('[popover]')
    panel.showPopover(); data.popupOpen = true
    hooks.morph({ el: data.$el }); panel.hidePopover(); hooks.morphed({ el: data.$el })
    expect(panel.matches(':popover-open')).toBe(true)
    data.destroy(); expect(stops.every(stop => stop.mock.calls.length === 1)).toBe(true)
    delete window.Livewire
})
it('moves calendar focus by the configured start of week', async()=>{
    const data = provider(calendar,'<div data-week-starts-on="1"><button data-day="2026-11-05"></button><button data-day="2026-11-02"></button></div>')
    await data.key({target:data.$el.firstElementChild,key:'Home',preventDefault(){}}); expect(document.activeElement.dataset.day).toBe('2026-11-02')
})
it('asks PHP for the next calendar month before focusing a day', async()=>{
    const wire = {goTo:vi.fn(async()=>document.querySelector('div').insertAdjacentHTML('beforeend','<button data-day="2026-12-01"></button>'))}
    const data = provider(calendar,'<div><button data-day="2026-11-30"></button></div>',{wire})
    await data.key({target:data.$el.firstElementChild,key:'ArrowRight',preventDefault(){}})
    expect(wire.goTo).toHaveBeenCalledWith('2026-12'); expect(document.activeElement.dataset.day).toBe('2026-12-01')
})
it('navigates cascader columns and restores focus after a leaf selection',()=>{
    const data = provider(cascader,'<div><button data-cascader-trigger>Choose</button><div data-slot="cascader-content" popover><div><button data-cascader-item data-depth="0" data-expanded>A</button><button data-cascader-item aria-disabled="true">B</button></div><div><button data-cascader-item data-depth="1">A1</button></div></div></div>')
    data.panel.showPopover(); data.picked({leaf:false,depth:0}); expect(document.activeElement.textContent).toBe('A1')
    data.key({target:document.activeElement,key:'ArrowLeft',preventDefault(){}}); expect(document.activeElement.textContent).toBe('A')
    data.picked({leaf:true,depth:1}); expect(data.panel.matches(':popover-open')).toBe(false); expect(document.activeElement).toBe(data.trigger)
})
it('shows morphed combobox suggestions while the list is requested',()=>{
    let morphed; const stop = vi.fn(); window.Livewire = {hook:(_,callback)=>{morphed=callback;return stop}}
    const data = provider(combobox,'<div data-autocomplete><input data-combobox-input><div data-slot="combobox-content" popover></div></div>',{wire:{value:null}})
    data.input.focus(); data.open(); expect(data.panel.matches(':popover-open')).toBe(false)
    data.panel.innerHTML = '<button data-slot="combobox-item">Apple</button>'
    morphed({el:data.$el}); expect(data.panel.matches(':popover-open')).toBe(true)
    data.key({key:'ArrowDown',preventDefault(){}}); expect(data.input.getAttribute('aria-activedescendant')).toBe(data.items[0].id)
    data.close(); morphed({el:data.$el}); expect(data.panel.matches(':popover-open')).toBe(false)
    data.destroy(); expect(stop).toHaveBeenCalled()
})
it('delegates tree selection and expansion to PHP actions',async()=>{
    const wire = {choose:vi.fn(),expand:vi.fn()}
    const data = provider(tree,'<ul data-slot="tree"><li data-slot="tree-item" data-value="team" aria-expanded="false" tabindex="0"><span>Team</span></li></ul>',{wire})
    const item = data.$el.firstElementChild; item.getClientRects = ()=>[{}]
    await data.key({target:item,key:'Enter',preventDefault(){}}); expect(wire.choose).toHaveBeenCalledWith('team')
    await data.key({target:item,key:'ArrowRight',preventDefault(){}}); expect(wire.expand).toHaveBeenCalledWith('team')
})
it('keeps tree drag geometry local and emits only the move intent',()=>{
    const data = provider(tree,'<ul data-slot="tree" data-draggable><li data-slot="tree-item" data-value="a"><div data-slot="tree-item-row"></div></li><li data-slot="tree-item" data-value="b"><div data-slot="tree-item-row"></div></li></ul>')
    const [a,b] = data.$el.children; b.firstElementChild.getBoundingClientRect = ()=>({top:0,height:100}); document.elementFromPoint = ()=>b
    const moved = vi.fn(); data.$el.addEventListener('move',moved)
    data.press({target:a,button:0,clientX:0,clientY:0}); data.move({clientX:10,clientY:50,preventDefault(){}}); data.end(true)
    expect(moved.mock.calls[0][0].detail).toEqual({value:'a',target:'b',position:'inside'}); expect(a.hasAttribute('data-dragging')).toBe(false)
})
it('uses letter shortcuts for choices and leaves text entry alone',()=>{
    const data = provider(questionnaire,'<div data-shortcuts="letters"><fieldset data-slot="questionnaire-item"><label data-slot="questionnaire-choice" data-shortcut="A"><input type="radio"></label><input type="text"></fieldset></div>')
    const [radio,text] = data.$el.querySelectorAll('input')
    data.key({target:radio,key:'a',preventDefault(){}}); expect(radio.checked).toBe(true)
    radio.checked = false; data.key({target:text,key:'a',preventDefault(){}}); expect(radio.checked).toBe(false)
})
it('lets Enter request the next server question without copying answers',()=>{
    const wire = {next:vi.fn()}; const data = provider(questionnaire,'<div><fieldset data-slot="questionnaire-item"><input></fieldset></div>',{wire})
    data.key({target:data.$el.querySelector('input'),key:'Enter',preventDefault(){}}); expect(wire.next).toHaveBeenCalledOnce(); expect('value' in data).toBe(false)
})
it('moves emoji highlight over PHP options and requests a footer preview', async () => {
    const data = provider(emoji, '<div data-columns="2"><input data-emoji-picker-search><button data-slot="emoji-picker-emoji" aria-label="Smile">😀</button><button data-slot="emoji-picker-emoji" aria-label="Heart">❤️</button><span data-slot="emoji-picker-footer"></span></div>')
    const preview = vi.fn()
    data.$el.querySelector('[data-slot="emoji-picker-footer"]').addEventListener('emoji-preview', preview)
    data.key({ target: data.search, key: 'ArrowDown', preventDefault() {} })
    expect(data.active.textContent).toBe('❤️')
    await vi.waitFor(() => expect(preview.mock.calls[0][0].detail).toEqual({ glyph: '❤️', label: 'Heart' }))
    const picked = vi.fn()
    data.active.addEventListener('click', picked)
    data.key({ key: 'Enter', preventDefault() {} })
    expect(picked).toHaveBeenCalledOnce()
})
