/*
 * Toasts, as shadcn's Toast on Base UI: a stack with the newest in front and older ones
 * peeking behind, spread out while hovered or focused. Each closes after its timeout
 * (paused while the stack is spread or the window is in the background), by its close
 * button, or by a swipe down or right. Past the limit, the oldest leave.
 *
 * Livewire: Ui::toast('Saved', type: 'success').
 * Blade: wire:click="$dispatch('ui-toast', { message: 'Saved' })".
 * A toast with the id of a shown one updates it in place (e.g. loading, then success).
 */

import { clone, readTemplates } from '../support/templates.js'

const SWIPE_THRESHOLD = 40
const LEAVE_DURATION = 500

let ids = 0

export function register() {
    const timers = new Map()
    let expanded = false

    const viewport = () => document.querySelector('[data-slot="toast-viewport"]')
    const live = () => [...(viewport()?.querySelectorAll(':scope > [data-slot="toast"]:not([data-ending-style])') ?? [])].reverse()

    // --- timers: paused while spread out or while the page is in the background ---

    const paused = () => expanded || document.hidden || ! document.hasFocus()

    const schedule = (toast, remaining) => {
        window.clearTimeout(timers.get(toast)?.id)

        if (! remaining) return timers.delete(toast)

        timers.set(toast, { remaining, started: Date.now(), id: paused() ? null : window.setTimeout(() => close(toast), remaining) })
    }

    const pause = () => timers.forEach(timer => {
        if (timer.id === null) return

        window.clearTimeout(timer.id)
        timer.id = null
        timer.remaining -= Date.now() - timer.started
    })

    const resume = () => {
        if (! paused()) timers.forEach((timer, toast) => timer.id === null && schedule(toast, timer.remaining))
    }

    // --- layout: the CSS stacks toasts from these variables ---

    const layout = () => {
        const toasts = live()
        const limit = Number(viewport()?.dataset.limit ?? 3)
        let offset = 0

        toasts.forEach((toast, index) => {
            // Natural height, measured without the stack's fixed height.
            toast.style.height = 'auto'
            const height = toast.offsetHeight
            toast.style.height = ''

            toast.style.setProperty('--toast-index', index)
            toast.style.setProperty('--toast-offset-y', `${offset}px`)
            toast.style.setProperty('--toast-height', `${height}px`)
            toast.toggleAttribute('data-expanded', expanded)
            toast.firstElementChild?.toggleAttribute('data-expanded', expanded)
            toast.firstElementChild?.toggleAttribute('data-behind', index > 0 && ! expanded)

            if (index === 0) viewport().style.setProperty('--toast-frontmost-height', `${height}px`)
            if (index >= limit) close(toast, { limited: true })

            offset += height
        })
    }

    // --- one toast, cloned from the toaster's template ---

    const fill = (toast, templates, { message, description = null, type = 'default', action = null }) => {
        const id = toast.dataset.id
        const content = clone(templates.toast).querySelector('[data-slot="toast-content"]')
        const icon = clone(templates[`icon-${type}`])
        const title = content.querySelector('[data-slot="toast-title"]')
        const text = content.querySelector('[data-slot="toast-description"]')
        const run = content.querySelector('[data-toast-action]')

        type === 'default' ? toast.removeAttribute('data-type') : toast.setAttribute('data-type', type)
        toast.setAttribute('role', type === 'error' ? 'alertdialog' : 'dialog')

        icon ? content.querySelector('[data-slot="toast-icon"]').append(icon) : content.querySelector('[data-slot="toast-icon"]').remove()
        message ? Object.assign(title, { id: `${id}-title`, textContent: message }) : title.remove()
        description ? Object.assign(text, { id: `${id}-description`, textContent: description }) : text.remove()

        message ? toast.setAttribute('aria-labelledby', `${id}-title`) : toast.removeAttribute('aria-labelledby')
        description ? toast.setAttribute('aria-describedby', `${id}-description`) : toast.removeAttribute('aria-describedby')

        if (action?.label) {
            run.textContent = action.label
            run.addEventListener('click', () => {
                if (action.component && action.method) window.Livewire?.find(action.component)?.call(action.method, ...(action.params ?? []))
                close(toast)
            })
        } else {
            run.remove()
        }

        content.querySelector('[data-toast-close]').addEventListener('click', () => close(toast))
        toast.replaceChildren(content)
    }

    const show = detail => {
        const root = viewport()
        const templates = root ? readTemplates(root) : {}

        if (! templates.toast || ! (detail.message || detail.description)) return

        const existing = detail.id && live().find(toast => toast.dataset.id === String(detail.id))
        const toast = existing || clone(templates.toast)

        if (! existing) toast.dataset.id = detail.id || `ui-toast-${++ids}`

        fill(toast, templates, detail)

        if (! existing) {
            for (const name of ['--toast-swipe-movement-x', '--toast-swipe-movement-y']) toast.style.setProperty(name, '0px')

            root.append(toast)
        }

        // Keep toasts above a modal dialog opened after the viewport.
        if (root.matches(':popover-open') && document.querySelector('dialog:modal')) root.hidePopover()
        if (! root.matches(':popover-open')) root.showPopover()

        const duration = detail.duration ?? (detail.type === 'loading' ? 0 : Number(root.dataset.timeout ?? 5000))

        schedule(toast, duration)
        layout()
    }

    const close = (toast, { limited = false, swipe = null } = {}) => {
        if (toast.hasAttribute('data-ending-style')) return

        window.clearTimeout(timers.get(toast)?.id)
        timers.delete(toast)

        if (limited) toast.setAttribute('data-limited', '')
        if (swipe) toast.setAttribute('data-swipe-direction', swipe)
        toast.setAttribute('data-ending-style', '')

        layout()

        window.setTimeout(() => {
            const root = toast.parentElement

            toast.remove()

            // The viewport also holds the templates.
            if (root && ! root.querySelector(':scope > [data-slot="toast"]')) {
                expanded = false
                if (root.matches(':popover-open')) root.hidePopover()
            }
        }, LEAVE_DURATION)
    }

    // --- spreading out on hover and focus ---

    const setExpanded = value => {
        if (expanded === value) return

        expanded = value
        value ? pause() : resume()
        layout()
    }

    document.addEventListener('pointerover', ({ target }) => target.closest?.('[data-slot="toast-viewport"]') && setExpanded(true))

    document.addEventListener('pointerout', ({ target, relatedTarget }) => {
        const root = target.closest?.('[data-slot="toast-viewport"]')

        if (root && ! root.contains(relatedTarget) && ! root.contains(document.activeElement)) setExpanded(false)
    })

    document.addEventListener('focusin', ({ target }) => target.closest?.('[data-slot="toast-viewport"]') && setExpanded(true))

    document.addEventListener('focusout', ({ target, relatedTarget }) => {
        const root = target.closest?.('[data-slot="toast-viewport"]')

        if (root && ! root.contains(relatedTarget) && ! root.matches(':hover')) setExpanded(false)
    })

    // F6 moves focus to the newest toast, as in Base UI.
    document.addEventListener('keydown', event => {
        if (event.key === 'F6' && live()[0]) {
            event.preventDefault()
            live()[0].focus()
        }

        if (event.key === 'Escape' && event.target.closest?.('[data-slot="toast"]')) close(event.target.closest('[data-slot="toast"]'))
    })

    window.addEventListener('blur', pause)
    window.addEventListener('focus', resume)
    document.addEventListener('visibilitychange', () => (document.hidden ? pause() : resume()))

    // --- swiping down or right dismisses ---

    document.addEventListener('pointerdown', event => {
        const toast = event.target.closest?.('[data-slot="toast"]')

        if (! toast || event.button !== 0 || event.target.closest('button, a, input')) return

        const start = { x: event.clientX, y: event.clientY }
        let movement = { x: 0, y: 0 }

        // Down and right follow the pointer; up and left resist.
        const damp = value => (value > 0 ? value : value / 8)

        const move = e => {
            movement = { x: damp(e.clientX - start.x), y: damp(e.clientY - start.y) }
            toast.style.setProperty('--toast-swipe-movement-x', `${movement.x}px`)
            toast.style.setProperty('--toast-swipe-movement-y', `${movement.y}px`)
        }

        const end = () => {
            window.removeEventListener('pointermove', move)
            window.removeEventListener('pointerup', end)
            window.removeEventListener('pointercancel', end)

            const direction = movement.y > SWIPE_THRESHOLD ? 'down' : movement.x > SWIPE_THRESHOLD ? 'right' : null

            if (direction) return close(toast, { swipe: direction })

            toast.style.setProperty('--toast-swipe-movement-x', '0px')
            toast.style.setProperty('--toast-swipe-movement-y', '0px')
        }

        window.addEventListener('pointermove', move)
        window.addEventListener('pointerup', end)
        window.addEventListener('pointercancel', end)
    })

    window.addEventListener('ui-toast', ({ detail }) => show(detail ?? {}))

    // The toaster is persisted across wire:navigate; moving it into the new page closes its popover.
    document.addEventListener('livewire:navigated', () => {
        const root = viewport()

        if (! root || ! live().length) return

        if (! root.matches(':popover-open')) root.showPopover()
        layout()
    })
}
