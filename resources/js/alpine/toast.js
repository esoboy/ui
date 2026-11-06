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

import { listen, disconnected } from './interaction.js'

const SWIPE_THRESHOLD = 40
const LEAVE_DURATION = 500

export default () => ({
    stopTimers: null,
    stopMorph: null,
    init() {
    const timers = new Map()
    const revisions = new Set()
    const leaving = new Map()
    let requests = Promise.resolve()
    const request = (method, ...args) => {
        requests = requests.catch(() => {}).then(() => this.$el.isConnected && this.$wire[method](...args))
        return requests
    }
    this.stopTimers = () => {
        timers.forEach(timer => clearTimeout(timer.id))
        leaving.forEach(({ timer }) => clearTimeout(timer))
    }
    let expanded = false

    const viewport = () => this.$el.querySelector('[data-slot="toast-viewport"]')
    const all = () => [...(viewport()?.querySelectorAll(':scope > [data-slot="toast"]') ?? [])]
    const live = () => all().filter(toast => ! leaving.has(toast)).reverse()

    // --- timers: paused while spread out or while the page is in the background ---

    const paused = () => expanded || document.hidden || ! document.hasFocus()

    const schedule = (toast, remaining) => {
        window.clearTimeout(timers.get(toast)?.id)

        if (remaining <= 0) return timers.delete(toast)

        timers.set(toast, { remaining, started: Date.now(), id: paused() ? null : window.setTimeout(() => close(toast), remaining) })
    }

    const pause = () => timers.forEach(timer => {
        if (timer.id === null) return

        window.clearTimeout(timer.id)
        timer.id = null
        timer.remaining -= Date.now() - timer.started
    })

    const resume = () => {
        if (! paused()) timers.forEach((timer, toast) => timer.id === null && schedule(toast, Math.max(1, timer.remaining)))
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

    // Livewire owns the list and its text. Measure and time the Blade rows after morph.
    const sync = () => {
        const root = viewport()
        if (! root) return

        for (const toast of revisions) {
            if (root.contains(toast)) continue
            clearTimeout(timers.get(toast)?.id)
            clearTimeout(leaving.get(toast)?.timer)
            timers.delete(toast)
            leaving.delete(toast)
            revisions.delete(toast)
        }

        const rows = all()
        if (! rows.length) {
            expanded = false
            if (root.matches(':popover-open')) root.hidePopover()
            return
        }

        const added = rows.filter(toast => ! revisions.has(toast))
        if (added.length && root.matches(':popover-open') && document.querySelector('dialog:modal')) root.hidePopover()
        if (! root.matches(':popover-open')) root.showPopover()

        for (const toast of rows) {
            const closing = leaving.get(toast)
            if (closing) markClosing(toast, closing)
            if (revisions.has(toast)) continue
            revisions.add(toast)
            schedule(toast, Number(toast.dataset.toastDuration))
        }
        layout()
    }

    const markClosing = (toast, { limited, swipe }) => {
        if (limited) toast.setAttribute('data-limited', '')
        if (swipe) toast.setAttribute('data-swipe-direction', swipe)
        toast.setAttribute('data-ending-style', '')
    }

    const close = (toast, { limited = false, swipe = null } = {}) => {
        if (leaving.has(toast)) return
        window.clearTimeout(timers.get(toast)?.id)
        timers.delete(toast)

        const timer = window.setTimeout(() => request('dismiss', toast.dataset.id, Number(toast.dataset.toastRevision)), LEAVE_DURATION)
        const closing = { limited, swipe, timer }
        leaving.set(toast, closing)
        markClosing(toast, closing)
        layout()
    }

    listen(this, this.$el, 'click', ({ target }) => {
        const control = target.closest?.('[data-toast-close], [data-toast-action]')
        const toast = control?.closest('[data-slot="toast"]')
        if (! toast || leaving.has(toast) || control.matches(':disabled,[aria-disabled="true"]')) return

        if (control.hasAttribute('data-toast-action')) {
            const action = JSON.parse(toast.dataset.toastCallback ?? 'null')
            if (action?.component && action.method) window.Livewire?.find(action.component)?.call(action.method, ...action.params)
        }
        close(toast)
    })

    // --- spreading out on hover and focus ---

    const setExpanded = value => {
        if (expanded === value) return

        expanded = value
        value ? pause() : resume()
        layout()
    }

    listen(this, document, 'pointerover', ({ target }) => target.closest?.('[data-slot="toast-viewport"]') && setExpanded(true))

    listen(this, document, 'pointerout', ({ target, relatedTarget }) => {
        const root = target.closest?.('[data-slot="toast-viewport"]')

        if (root && ! root.contains(relatedTarget) && ! root.contains(document.activeElement)) setExpanded(false)
    })

    listen(this, document, 'focusin', ({ target }) => target.closest?.('[data-slot="toast-viewport"]') && setExpanded(true))

    listen(this, document, 'focusout', ({ target, relatedTarget }) => {
        const root = target.closest?.('[data-slot="toast-viewport"]')

        if (root && ! root.contains(relatedTarget) && ! root.matches(':hover')) setExpanded(false)
    })

    // F6 moves focus to the newest toast, as in Base UI.
    listen(this, document, 'keydown', event => {
        if (event.key === 'F6' && live()[0]) {
            event.preventDefault()
            live()[0].focus()
        }

        if (event.key === 'Escape' && event.target.closest?.('[data-slot="toast"]')) close(event.target.closest('[data-slot="toast"]'))
    })

    listen(this, window, 'blur', pause)
    listen(this, window, 'focus', resume)
    listen(this, document, 'visibilitychange', () => (document.hidden ? pause() : resume()))

    // --- swiping down or right dismisses ---

    listen(this, document, 'pointerdown', event => {
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
            stopMove()
            stopEnd()
            stopCancel()

            const direction = movement.y > SWIPE_THRESHOLD ? 'down' : movement.x > SWIPE_THRESHOLD ? 'right' : null

            if (direction) return close(toast, { swipe: direction })

            toast.style.removeProperty('--toast-swipe-movement-x')
            toast.style.removeProperty('--toast-swipe-movement-y')
        }

        const stopMove = listen(this, window, 'pointermove', move)
        const stopEnd = listen(this, window, 'pointerup', end)
        const stopCancel = listen(this, window, 'pointercancel', end)
    })

    listen(this, window, 'ui-toast', ({ detail }) => request('show', detail ?? {}))
    listen(this, document, 'livewire:navigated', sync)
    this.stopMorph = window.Livewire?.hook('morphed', ({ el }) => {
        if (el === this.$el) sync()
    })
    sync()
    },
    destroy() { this.stopTimers?.(); this.stopMorph?.(); disconnected(this) },
})
