/*
 * Dropdown menus open, close and dismiss natively as popovers. This adds what menus
 * need on top: arrow-key navigation, hover highlighting and submenus. Context Menu and
 * Menubar share the items; they add opening at the pointer, and moving along the bar.
 */

import { contentOf, triggerOf } from '../support/overlay.js'

const ROOT_MENU = '[data-slot="dropdown-menu-content"], [data-slot="context-menu-content"], [data-slot="menubar-content"]'
const MENU = `${ROOT_MENU}, [data-slot="dropdown-menu-sub-content"]`
const ITEM = '[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]'
const ROW = '[data-slot="dropdown-menu-item"], [data-slot="dropdown-menu-checkbox-item"], [data-slot="dropdown-menu-radio-item"], [data-slot="dropdown-menu-sub-trigger"]'

export function register() {
    const itemsOf = menu => [...menu.querySelectorAll(ITEM)]
        .filter(item => item.closest('[role="menu"]') === menu && ! item.disabled && item.getAttribute('aria-disabled') !== 'true')

    // A menubar trigger's menu, or a sub trigger's submenu.
    const menuOf = trigger => contentOf(trigger, trigger.matches('[data-slot="menubar-trigger"]') ? 'menubar' : 'dropdown-menu-sub')

    const open = (popover, source) => popover && ! popover.matches(':popover-open') && popover.showPopover({ source })

    const close = popover => popover?.matches(':popover-open') && popover.hidePopover()

    // A freshly opened menu takes focus so arrow keys work right away.
    document.addEventListener('toggle', ({ target, newState }) => {
        if (newState === 'open' && target.matches?.(ROOT_MENU) && ! target.contains(document.activeElement)) {
            target.focus()
        }
    }, true)

    document.addEventListener('keydown', event => {
        if (moveAlongMenubar(event)) return

        const menu = event.target.closest?.(MENU)

        if (! menu) return

        const items = itemsOf(menu)
        const index = items.indexOf(event.target)

        const focus = position => {
            event.preventDefault()
            items.at(position)?.focus()
        }

        switch (event.key) {
            case 'ArrowDown': return focus(index < 0 ? 0 : (index + 1) % items.length)
            case 'ArrowUp': return focus(index < 0 ? -1 : (index - 1) % items.length)
            case 'Home': return focus(0)
            case 'End': return focus(-1)

            case 'ArrowRight':
                if (event.target.matches('[data-slot="dropdown-menu-sub-trigger"]')) {
                    event.preventDefault()
                    const submenu = menuOf(event.target)
                    open(submenu, event.target)
                    itemsOf(submenu)[0]?.focus()
                }
                return

            case 'ArrowLeft':
                if (menu.matches('[data-slot="dropdown-menu-sub-content"]')) {
                    event.preventDefault()
                    close(menu)
                    triggerOf(menu, 'dropdown-menu-sub')?.focus()
                }
        }
    })

    // Context menus open where the pointer is (alignOffset lower, shadcn's 4px), flipping to stay on screen.
    document.addEventListener('contextmenu', event => {
        const trigger = event.target.closest?.('[data-slot="context-menu-trigger"]')
        const menu = trigger && contentOf(trigger, 'context-menu')

        if (! menu) return

        const offset = Number(menu.dataset.alignOffset ?? 4)

        event.preventDefault()
        close(menu)
        menu.style.left = `${event.clientX}px`
        menu.style.top = `${event.clientY + offset}px`
        open(menu)

        const { width, height } = menu.getBoundingClientRect()

        if (event.clientX + width > window.innerWidth) menu.style.left = `${Math.max(0, event.clientX - width)}px`
        if (event.clientY + offset + height > window.innerHeight) menu.style.top = `${Math.max(0, event.clientY - height)}px`
    })

    // Choosing an item closes the whole menu, submenus included.
    document.addEventListener('click', event => {
        const item = event.target.closest?.('[data-slot="dropdown-menu-item"]')

        if (item && ! event.defaultPrevented && ! item.disabled && item.getAttribute('aria-disabled') !== 'true') close(item.closest(ROOT_MENU))
    })

    // Menubar: ArrowLeft/Right move between its menus, opening the next one if one was open.
    const moveAlongMenubar = event => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return false

        const menubar = event.target.closest?.('[data-slot="menubar"]')
        const menu = event.target.closest?.('[data-slot="menubar-content"]')

        // Inside a menu, Left/Right belong to sub triggers and submenus.
        if (! menubar || event.target.closest('[data-slot="dropdown-menu-sub-content"]') || (event.key === 'ArrowRight' && event.target.matches('[data-slot="dropdown-menu-sub-trigger"]'))) return false

        const triggers = [...menubar.querySelectorAll('[data-slot="menubar-trigger"]')].filter(trigger => ! trigger.disabled)
        const current = menu ? triggerOf(menu, 'menubar') : event.target
        const index = triggers.indexOf(current)

        if (index < 0) return false

        const next = triggers.at((index + (event.key === 'ArrowRight' ? 1 : -1)) % triggers.length)

        event.preventDefault()
        next.focus()

        if (menu?.matches(':popover-open')) open(menuOf(next), next)

        return true
    }

    // The highlight follows the pointer, hovering a sub trigger opens its submenu, and
    // hovering another menubar trigger while a menu is open switches to its menu.
    document.addEventListener('pointerover', ({ target }) => {
        const trigger = target.closest?.('[data-slot="menubar-trigger"]')

        if (trigger) {
            const menubar = trigger.closest('[data-slot="menubar"]')
            const anyOpen = [...menubar.querySelectorAll('[data-slot="menubar-content"]')].some(menu => menu.matches(':popover-open'))

            if (anyOpen) {
                open(menuOf(trigger), trigger)
                trigger.focus()
            }

            return
        }

        const row = target.closest?.(ROW)
        const menu = row?.closest('[role="menu"]')

        if (! menu) return

        ;(row.matches(ITEM) ? row : row.querySelector(ITEM))?.focus({ preventScroll: true })

        for (const trigger of menu.querySelectorAll('[data-slot="dropdown-menu-sub-trigger"]')) {
            if (trigger.closest('[role="menu"]') !== menu) continue

            trigger === row ? open(menuOf(trigger), trigger) : close(menuOf(trigger))
        }
    })
}
