import { afterEach, expect, it, vi } from 'vitest'
import tourProvider from '../../resources/js/alpine/tour.js'
import { cleanupControllers, provider } from './alpine-harness.js'

afterEach(() => cleanupControllers())

function mount(wire = {}) {
    const tour = provider(tourProvider, `<eso-tour id="onboarding" data-step="" data-tour-anchor="--ui-tour-onboarding">
        <button id="start">Start</button>
        <button id="target" style="anchor-name: --menu">Target</button>
        <div popover="manual" data-slot="tour-spotlight"></div>
        <div popover="manual" data-slot="tour-content" data-target="#target" data-step-side="left" data-step-align="start">
            <button data-tour-action="next">Next</button>
        </div>
    </eso-tour>`, { wire })
    return tour
}

it('delegates a requested starting step or close to Livewire', () => {
    const wire = { start: vi.fn(), close: vi.fn() }
    const tour = mount(wire)
    tour.requested({ name: 'other', open: true })
    tour.requested({ name: 'onboarding', open: true, step: 2 })
    tour.requested({ name: 'onboarding', open: false })
    expect(wire.start).toHaveBeenCalledExactlyOnceWith(2)
    expect(wire.close).toHaveBeenCalledOnce()
})

it('anchors the rendered panel and restores focus when the step disappears', () => {
    const tour = mount()
    const start = tour.$el.querySelector('#start')
    const target = tour.$el.querySelector('#target')
    target.scrollIntoView = vi.fn()
    start.focus()
    tour.$el.dataset.step = '0'
    tour.refresh()
    expect(tour.isOpen).toBe(true)
    expect(tour.content.dataset.side).toBe('left')
    expect(target.style.getPropertyValue('anchor-name')).toBe('--menu, --ui-tour-onboarding')
    expect(document.activeElement.dataset.tourAction).toBe('next')
    tour.$el.dataset.step = ''
    tour.refresh()
    expect(tour.isOpen).toBe(false)
    expect(target.style.getPropertyValue('anchor-name')).toBe('--menu')
    expect(document.activeElement).toBe(start)
})
