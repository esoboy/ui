import { afterEach, expect, it, vi } from 'vitest'
import { Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js'
import { listen, disconnected, unlisten, emit } from '../../resources/js/alpine/interaction.js'
window.Alpine = Alpine
afterEach(() => document.body.innerHTML = '')
it('disposes Alpine element and window bindings with their owner', () => {
    document.body.innerHTML = '<div><button></button></div>'; const owner = { $el: document.body.firstElementChild }; const callback = vi.fn()
    listen(owner, owner.$el, 'click', callback); listen(owner, window, 'resize', callback)
    owner.$el.querySelector('button').click(); window.dispatchEvent(new Event('resize')); expect(callback).toHaveBeenCalledTimes(2)
    disconnected(owner); owner.$el.click(); window.dispatchEvent(new Event('resize')); expect(callback).toHaveBeenCalledTimes(2)
})
it('removes a specific binding and sends the actual native input data', () => {
    document.body.innerHTML = '<input value="42">'; const input = document.querySelector('input'); const callback = vi.fn(); const owner = { $el: input }
    listen(owner,input,'input',callback); emit(input,'input'); expect(callback.mock.calls[0][0].detail).toBe('42')
    unlisten(input,'input',callback); emit(input,'input'); expect(callback).toHaveBeenCalledOnce()
})
it('keeps nested component cleanup independent', () => {
    document.body.innerHTML = '<div><section></section></div>'
    const parent = { $el: document.querySelector('div') }, child = { $el: document.querySelector('section') }
    const first = vi.fn(), second = vi.fn()
    listen(parent,window,'resize',first); listen(child,window,'resize',second)
    disconnected(child); window.dispatchEvent(new Event('resize'))
    expect(first).toHaveBeenCalledOnce(); expect(second).not.toHaveBeenCalled(); disconnected(parent)
})
