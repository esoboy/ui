import { afterEach, beforeEach, expect, it, vi } from 'vitest'
import countdown from '../../resources/js/alpine/countdown.js'
import { cleanupControllers, provider } from './alpine-harness.js'

let visible, morph

beforeEach(() => {
    vi.useFakeTimers()
    vi.stubGlobal('IntersectionObserver', class {
        constructor(callback) { visible = value => callback([{ isIntersecting: value }]) }
        observe() {}
        disconnect() {}
    })
    window.Livewire = { hook: (_, callback) => { morph = callback; return () => {} } }
})

afterEach(() => {
    cleanupControllers()
    delete window.Livewire
    vi.unstubAllGlobals()
    vi.useRealTimers()
})

it('only requests the next visible clock update and leaves the rendered time intact', async () => {
    const wire = { tick: vi.fn() }
    const clock = provider(countdown, '<span data-delay="1000" data-running="true">05</span>', { wire })
    await vi.advanceTimersByTimeAsync(1000)
    expect(wire.tick).not.toHaveBeenCalled()
    visible(true)
    await vi.advanceTimersByTimeAsync(1)
    expect(wire.tick).toHaveBeenCalledOnce()
    expect(clock.$el.textContent).toBe('05')
})

it('restores a named deadline on the next page and restarts only the requested name', async () => {
    const wire = { restore: vi.fn(), start: vi.fn() }
    const first = provider(countdown, '<span data-name="sale" data-deadline="10000" data-delay="5000"></span>')
    morph({ el: first.$el })
    const next = provider(countdown, '<span data-name="sale" data-deadline="20000"></span>', { wire })
    next.start({ name: 'other', seconds: 5 })
    next.start({ name: 'sale', seconds: 3 })
    await vi.advanceTimersByTimeAsync(0)
    expect(wire.restore).toHaveBeenCalledExactlyOnceWith(10000)
    expect(wire.start).toHaveBeenCalledExactlyOnceWith(3)
})
