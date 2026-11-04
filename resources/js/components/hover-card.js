/*
 * Hover cards are manual popovers opened by hovering or focusing their trigger, after
 * its data-delay, and closed data-close-delay after the pointer leaves both the
 * trigger and the card, so the pointer can travel into the card.
 */

import { contentOf, triggerOf } from '../support/overlay.js'

const TRIGGER = '[data-hover-card-trigger]'

export function register() {
    const timers = new Map()

    const cardOf = trigger => contentOf(trigger, 'hover-card')

    const schedule = (card, trigger, open, delay) => {
        clearTimeout(timers.get(card))
        timers.set(card, setTimeout(() => {
            if (open === card.matches(':popover-open')) return

            open ? card.showPopover({ source: trigger ?? triggerOf(card, 'hover-card') }) : card.hidePopover()
        }, delay))
    }

    const enter = trigger => {
        const card = cardOf(trigger)

        if (card) schedule(card, trigger, true, Number(trigger.dataset.delay ?? 600))
    }

    const leave = (trigger, card) => {
        if (card) schedule(card, trigger, false, Number(trigger?.dataset.closeDelay ?? 300))
    }

    const leaveFrom = node => node.matches(TRIGGER) ? leave(node, cardOf(node)) : leave(triggerOf(node, 'hover-card'), node)

    // Moving between children fires over/out pairs; only real exits count.
    const target = element => element?.closest?.(`${TRIGGER}, [data-slot="hover-card-content"]`)

    document.addEventListener('pointerover', ({ target: element, relatedTarget }) => {
        const node = target(element)

        if (! node || node.contains(relatedTarget)) return

        if (node.matches(TRIGGER)) enter(node)
        else schedule(node, null, true, 0)
    })

    document.addEventListener('pointerout', ({ target: element, relatedTarget }) => {
        const node = target(element)

        if (node && ! node.contains(relatedTarget)) leaveFrom(node)
    })

    document.addEventListener('focusin', ({ target: element }) => {
        const trigger = element.closest?.(TRIGGER)

        if (trigger) enter(trigger)
    })

    document.addEventListener('focusout', ({ target: element, relatedTarget }) => {
        const node = target(element)

        if (node && ! node.contains(relatedTarget) && ! target(relatedTarget)) leaveFrom(node)
    })

    document.addEventListener('keydown', ({ key }) => {
        if (key === 'Escape') document.querySelectorAll('[data-slot="hover-card-content"]:popover-open').forEach(card => card.hidePopover())
    })
}
