import { afterEach, beforeEach, expect, it, vi } from 'vitest'
import toastProvider from '../../resources/js/alpine/toast.js'
import { cleanupControllers, provider } from './alpine-harness.js'

let root, viewport, morph, wire

beforeEach(() => {
    vi.useFakeTimers()
    document.hasFocus = () => true
    wire = { show: vi.fn(), dismiss: vi.fn() }
    window.Livewire = {
        hook: (_, callback) => { morph = callback; return () => {} },
        find: vi.fn(),
    }
    root = provider(toastProvider, '<div><div data-slot="toast-viewport" popover="manual" data-limit="3"></div></div>', { wire }).$el
    viewport = root.firstElementChild
})

afterEach(() => {
    cleanupControllers()
    delete window.Livewire
    vi.useRealTimers()
})

function render(id, duration = 0) {
    viewport.insertAdjacentHTML('beforeend', `<div data-slot="toast" data-id="${id}" data-toast-revision="1" data-toast-duration="${duration}" tabindex="0"><div><button data-toast-close>Close</button></div></div>`)
    morph({ el: root })
    return viewport.lastElementChild
}

it('forwards the public event to Livewire and measures rendered rows', async () => {
    window.dispatchEvent(new CustomEvent('ui-toast', { detail: { message: 'Saved' } }))
    await vi.advanceTimersByTimeAsync(0)
    expect(wire.show).toHaveBeenCalledWith({ message: 'Saved' })
    const first = render('first')
    const newest = render('newest')
    expect(viewport.matches(':popover-open')).toBe(true)
    expect(first.style.getPropertyValue('--toast-index')).toBe('1')
    expect(newest.style.getPropertyValue('--toast-index')).toBe('0')
})

it('pauses a dismissal during hover and requests removal after the exit animation', async () => {
    const item = render('pending', 1000)
    await vi.advanceTimersByTimeAsync(600)
    item.dispatchEvent(new MouseEvent('pointerover', { bubbles: true }))
    await vi.advanceTimersByTimeAsync(5000)
    expect(item.hasAttribute('data-expanded')).toBe(true)
    expect(wire.dismiss).not.toHaveBeenCalled()
    item.dispatchEvent(new MouseEvent('pointerout', { bubbles: true, relatedTarget: document.body }))
    await vi.advanceTimersByTimeAsync(400)
    expect(item.hasAttribute('data-ending-style')).toBe(true)
    await vi.advanceTimersByTimeAsync(500)
    expect(wire.dismiss).toHaveBeenCalledWith('pending', 1)
    item.remove()
    morph({ el: root })
    expect(viewport.matches(':popover-open')).toBe(false)
})

it('dismisses an item swiped past the threshold', async () => {
    const item = render('gesture')
    item.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true, button: 0, clientX: 0, clientY: 0 }))
    window.dispatchEvent(new MouseEvent('pointermove', { clientX: 60, clientY: 0 }))
    window.dispatchEvent(new MouseEvent('pointerup'))
    expect(item.dataset.swipeDirection).toBe('right')
    await vi.advanceTimersByTimeAsync(500)
    expect(wire.dismiss).toHaveBeenCalledWith('gesture', 1)
})
