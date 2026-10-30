/*
 * Tooltips are manual popovers: shown while their trigger is hovered or focused,
 * anchored to it, and describing it meanwhile.
 */

import { contentOf } from '../support/overlay.js'

const TRIGGER = '[data-tooltip-trigger]'

export function register() {
    const described = new WeakSet()

    const show = trigger => {
        const tooltip = contentOf(trigger, 'tooltip')

        if (! tooltip || tooltip.matches(':popover-open')) return

        tooltip.showPopover({ source: trigger })

        if ('ariaDescribedByElements' in trigger && ! trigger.hasAttribute('aria-describedby')) {
            trigger.ariaDescribedByElements = [tooltip]
            described.add(trigger)
        }
    }

    const hide = trigger => {
        const tooltip = contentOf(trigger, 'tooltip')

        if (tooltip?.matches(':popover-open')) tooltip.hidePopover()

        if (described.delete(trigger)) trigger.ariaDescribedByElements = null
    }

    // Moving between children of the trigger fires over/out pairs; only real exits hide.
    const leaves = (trigger, next) => ! trigger.contains(next)

    document.addEventListener('pointerover', ({ target }) => {
        const trigger = target.closest?.(TRIGGER)

        if (trigger) show(trigger)
    })

    document.addEventListener('pointerout', ({ target, relatedTarget }) => {
        const trigger = target.closest?.(TRIGGER)

        if (trigger && leaves(trigger, relatedTarget)) hide(trigger)
    })

    // Focus can come back to a trigger while a popover is hiding, when no popover may be
    // shown: after that, if it is still there.
    document.addEventListener('focusin', ({ target }) => {
        const trigger = target.closest?.(TRIGGER)

        if (trigger) queueMicrotask(() => trigger.contains(document.activeElement) && show(trigger))
    })

    document.addEventListener('focusout', ({ target, relatedTarget }) => {
        const trigger = target.closest?.(TRIGGER)

        if (trigger && leaves(trigger, relatedTarget)) hide(trigger)
    })

    document.addEventListener('keydown', ({ key }) => {
        if (key === 'Escape') document.querySelectorAll('[data-slot="tooltip-content"]:popover-open').forEach(tooltip => tooltip.hidePopover())
    })
}
