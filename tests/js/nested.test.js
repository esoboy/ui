import { afterEach, expect, it } from 'vitest'
import { Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js'
import resizable from '../../resources/js/alpine/resizable.js'

window.Alpine = Alpine
afterEach(() => { Alpine.destroyTree(document.body); document.body.innerHTML = '' })
it('keeps nested resizable panel measurements in their owning Alpine scope', () => {
    Alpine.data('testResizable', resizable)
    document.body.innerHTML = '<div x-data="testResizable" data-slot="resizable-panel-group"><section data-slot="resizable-panel" data-initial-size="50"></section><button data-slot="resizable-handle"></button><section data-slot="resizable-panel" data-initial-size="50"><div x-data="testResizable" data-slot="resizable-panel-group"><section data-slot="resizable-panel" data-initial-size="25"></section><button data-slot="resizable-handle"></button><section data-slot="resizable-panel"></section></div></section></div>'
    Alpine.initTree(document.body)
    const [parent, child] = document.querySelectorAll('[data-slot="resizable-panel-group"]')
    expect(Alpine.$data(parent)._sizes).not.toBe(Alpine.$data(child)._sizes)
    parent.querySelector('button').dispatchEvent(new KeyboardEvent('keydown', { key:'ArrowRight', bubbles:true }))
    expect(Alpine.$data(parent).sizes).toEqual([60,40])
    expect(Alpine.$data(child).sizes).toEqual([25,75])
})
