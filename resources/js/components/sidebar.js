/*
 * <eso-sidebar-provider>
 *
 * Toggles the Sidebars inside it from their triggers and rails, and with Ctrl/Cmd+B.
 * From md up a toggle expands or collapses them and writes the sidebar_state cookie
 * the server reads on the next render; below md it opens or closes the sidebar's
 * container as a sheet (a popover with a backdrop, closed by Escape or a click outside).
 */

import { connected, define, disconnected } from '../support/element.js'

const COOKIE = 'sidebar_state'
const COOKIE_MAX_AGE = 60 * 60 * 24 * 7
const MOBILE = '(width < 48rem)'

export class SidebarProviderElement extends HTMLElement {
    #mobile = window.matchMedia(MOBILE)
    #returnFocus = null

    #onKey = event => {
        if (event.key === 'b' && (event.metaKey || event.ctrlKey) && ! event.altKey && ! event.shiftKey) {
            event.preventDefault()
            this.toggle()
        }
    }

    // Light dismiss for the mobile sheet; a trigger toggles it itself.
    #onOutsidePress = event => {
        if (! this.openMobile || event.target.closest?.('[data-sidebar="trigger"]')) return

        if (! this.containers.some(container => container.contains(event.target))) this.setOpenMobile(false)
    }

    #onBreakpoint = () => this.containers.forEach(container => container.matches(':popover-open') && container.hidePopover())

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('click', event => {
            const control = event.target.closest?.('[data-sidebar="trigger"], [data-sidebar="rail"]')

            if (control && control.closest('eso-sidebar-provider') === this) this.toggle()
        }, { signal })

        this.addEventListener('keydown', event => {
            if (event.key === 'Escape' && this.openMobile) {
                event.preventDefault()
                this.setOpenMobile(false)
            }
        }, { signal })

        window.addEventListener('keydown', this.#onKey, { signal })
        document.addEventListener('pointerdown', this.#onOutsidePress, { signal })
        this.#mobile.addEventListener('change', this.#onBreakpoint, { signal })
    }

    disconnectedCallback() {
        disconnected(this)
    }

    get sidebars() {
        return [...this.querySelectorAll('[data-slot="sidebar"][data-state]')].filter(sidebar => sidebar.closest('eso-sidebar-provider') === this)
    }

    get containers() {
        return this.sidebars.map(sidebar => sidebar.querySelector(':scope > [data-slot="sidebar-container"]')).filter(Boolean)
    }

    get isMobile() {
        return this.#mobile.matches
    }

    get open() {
        return this.sidebars[0]?.dataset.state !== 'collapsed'
    }

    get openMobile() {
        return this.containers.some(container => container.matches(':popover-open'))
    }

    setOpen(open) {
        for (const sidebar of this.sidebars) {
            sidebar.dataset.state = open ? 'expanded' : 'collapsed'
            sidebar.dataset.collapsible = open ? '' : sidebar.dataset.collapsibleMode
        }

        document.cookie = `${COOKIE}=${open}; path=/; max-age=${COOKIE_MAX_AGE}`
    }

    setOpenMobile(open) {
        for (const container of this.containers) {
            if (open && ! container.matches(':popover-open')) {
                this.#returnFocus = document.activeElement
                container.showPopover()
                container.querySelector('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')?.focus()
            } else if (! open && container.matches(':popover-open')) {
                container.hidePopover()
                this.#returnFocus?.focus?.()
                this.#returnFocus = null
            }
        }
    }

    toggle() {
        this.isMobile ? this.setOpenMobile(! this.openMobile) : this.setOpen(! this.open)
    }
}

export function register() {
    define('eso-sidebar-provider', SidebarProviderElement)
}
