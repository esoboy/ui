/*
 * Navigation Menu triggers open their content popover on hover (after 50ms, like
 * Base UI) or click. Content opened by hover closes when the pointer leaves the
 * trigger and the content; content opened by click stays until dismissed.
 */

import { contentOf, triggerOf } from '../support/overlay.js'

const DELAY = 50
const TRIGGER = '[data-slot="navigation-menu-trigger"]'

export function register() {
    const timers = new Map()

    const isOpen = content => content.matches(':popover-open')

    const show = ({ trigger, content }, by) => {
        clearTimeout(timers.get(content))
        content.dataset.openedBy = by

        if (! isOpen(content)) content.showPopover({ source: trigger })
    }

    const hideLater = content => {
        clearTimeout(timers.get(content))
        timers.set(content, setTimeout(() => isOpen(content) && content.dataset.openedBy === 'hover' && content.hidePopover(), DELAY))
    }

    // The trigger mirrors its content's state.
    document.addEventListener('toggle', ({ target, newState }) => {
        if (! target.matches?.('[data-slot="navigation-menu-content"]')) return

        triggerOf(target, 'navigation-menu')?.setAttribute('aria-expanded', String(newState === 'open'))
    }, true)

    document.addEventListener('click', ({ target }) => {
        const trigger = target.closest?.(TRIGGER)
        const content = trigger && contentOf(trigger, 'navigation-menu')

        if (! content) return

        // A click on a menu the pointer just opened keeps it open.
        if (isOpen(content) && content.dataset.openedBy === 'hover') content.dataset.openedBy = 'click'
        else if (isOpen(content)) content.hidePopover()
        else show({ trigger, content }, 'click')
    })

    const parts = element => {
        const trigger = element?.closest?.(TRIGGER)
        const content = trigger ? contentOf(trigger, 'navigation-menu') : element?.closest?.('[data-slot="navigation-menu-content"]')

        return content ? { trigger: trigger ?? triggerOf(content, 'navigation-menu'), content } : null
    }

    document.addEventListener('pointerover', ({ target, relatedTarget, pointerType }) => {
        const found = parts(target)

        if (! found || pointerType === 'touch' || parts(relatedTarget)?.content === found.content) return

        clearTimeout(timers.get(found.content))

        if (isOpen(found.content)) return

        // Moving from one open menu to another trigger switches at once.
        const nav = found.trigger?.closest('[data-slot="navigation-menu"]')
        const switching = nav && [...nav.querySelectorAll('[data-slot="navigation-menu-content"]')].some(isOpen)

        timers.set(found.content, setTimeout(() => show(found, 'hover'), switching ? 0 : DELAY))
    })

    document.addEventListener('pointerout', ({ target, relatedTarget }) => {
        const found = parts(target)

        if (found && parts(relatedTarget)?.content !== found.content) hideLater(found.content)
    })
}
