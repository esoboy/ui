import { confirmation } from './confirm.js'

// PHP gives every instance stable targets. Alpine handles focus, hover timing and
// browser positioning; it never searches document order to guess a pair.
const timers = new WeakMap()
const openedBy = new WeakMap()
const kinds = ['popover', 'dropdown-menu', 'dropdown-menu-sub', 'menubar', 'context-menu', 'navigation-menu', 'tooltip', 'hover-card']
const items = panel => [...panel.querySelectorAll('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]')].filter(item => item.closest('[role="menu"]') === panel && ! item.disabled && item.getAttribute('aria-disabled') !== 'true')

export function contentOf(trigger, kind) {
    const id = trigger?.getAttribute(`data-ui-${kind}`)
    return id ? trigger.ownerDocument.getElementById(id) : null
}

export function triggerOf(panel, kind) {
    return panel?.id ? panel.ownerDocument.querySelector(`[data-ui-${kind}="${panel.id}"]`) : null
}

function hide(panel) {
    if (panel?.matches(':popover-open')) panel.hidePopover()
}

function show(panel, trigger, by = 'click') {
    if (! panel) return
    clearTimeout(timers.get(panel))
    openedBy.set(panel, by)
    if (! panel.matches(':popover-open')) panel.showPopover({ source: trigger ?? undefined })
}

function later(panel, callback, delay) {
    if (! panel) return
    clearTimeout(timers.get(panel))
    timers.set(panel, setTimeout(callback, delay))
}

function hoverKinds(element) {
    return ['tooltip', 'hover-card', 'navigation-menu'].filter(kind => element.hasAttribute(`data-ui-${kind}`))
}

export function registerTrigger(Alpine) {
    Alpine.directive('ui-overlay-trigger', (element, _, { cleanup }) => {
        const control = Object.assign(trigger(), { $el: element, $nextTick: Alpine.nextTick })
        const unbind = Alpine.bind(element, {
            'x-on:pointerover': event => control.enter(event),
            'x-on:pointerout': event => control.leave(event),
            'x-on:focusin': event => control.enter(event),
            'x-on:focusout': event => control.leave(event),
            'x-on:click': event => control.click(event),
            'x-on:keydown': event => control.key(event),
        })
        control.init()
        cleanup(() => { unbind(); control.destroy() })
    })
}

function moveBar(event, trigger) {
    if (! ['ArrowLeft', 'ArrowRight'].includes(event.key)) return false
    const bar = trigger?.closest('[data-slot="menubar"]')
    if (! bar) return false
    const triggers = [...bar.querySelectorAll('[data-slot="menubar-trigger"]')].filter(item => ! item.disabled)
    const index = triggers.indexOf(trigger)
    if (index < 0) return false
    const next = triggers.at((index + (event.key === 'ArrowRight' ? 1 : -1)) % triggers.length)
    event.preventDefault()
    next.focus()
    if ([...bar.querySelectorAll('[data-slot="menubar-content"]')].some(panel => panel.matches(':popover-open'))) show(contentOf(next, 'menubar'), next)
    return true
}

export function trigger() {
    return {
        stopMorph: null,
        init() {
            this.$nextTick(() => this.refresh())
            this.stopMorph = window.Livewire?.hook('morph.updated', ({ el }) => { if (el === this.$el) this.refresh() })
        },
        refresh() {
            for (const kind of kinds.filter(kind => kind !== 'tooltip' && (kind !== 'context-menu' || this.$el.matches('button, [role="button"]')))) {
                const panel = contentOf(this.$el, kind)
                if (panel) this.$el.setAttribute('aria-expanded', String(panel.matches(':popover-open')))
            }
        },
        enter(event) {
            if (this.$el.matches(':disabled, [aria-disabled="true"]')) return
            if (event.type.startsWith('pointer') && this.$el.contains(event.relatedTarget)) return
            for (const kind of hoverKinds(this.$el)) {
                const panel = contentOf(this.$el, kind)
                if (! panel || (kind === 'navigation-menu' && event.pointerType === 'touch')) continue
                const delay = kind === 'tooltip' ? 0 : kind === 'navigation-menu' ? 50 : Number(this.$el.dataset.delay ?? 600)
                later(panel, () => {
                    show(panel, this.$el, 'hover')
                    if (kind === 'tooltip' && ! this.$el.hasAttribute('aria-describedby')) this.$el.setAttribute('aria-describedby', panel.id)
                }, delay)
            }
            const submenu = contentOf(this.$el, 'dropdown-menu-sub')
            if (submenu) show(submenu, this.$el, 'hover')
            const bar = this.$el.closest('[data-slot="menubar"]')
            if (bar && [...bar.querySelectorAll('[data-slot="menubar-content"]')].some(panel => panel.matches(':popover-open'))) show(contentOf(this.$el, 'menubar'), this.$el, 'hover')
        },
        leave(event) {
            if (this.$el.contains(event.relatedTarget)) return
            for (const kind of hoverKinds(this.$el)) {
                const panel = contentOf(this.$el, kind)
                if (! panel || panel.contains(event.relatedTarget)) continue
                const delay = kind === 'tooltip' ? 0 : kind === 'navigation-menu' ? 50 : Number(this.$el.dataset.closeDelay ?? 300)
                later(panel, () => {
                    if (kind !== 'navigation-menu' || openedBy.get(panel) === 'hover') hide(panel)
                    if (kind === 'tooltip' && this.$el.getAttribute('aria-describedby') === panel.id) this.$el.removeAttribute('aria-describedby')
                }, delay)
            }
        },
        click(event) {
            if (this.$el.matches(':disabled, [aria-disabled="true"]')) {
                event.preventDefault()
                return
            }
            if (! this.$el.matches('button')) {
                const panel = contentOf(this.$el, 'popover') ?? contentOf(this.$el, 'dropdown-menu')
                if (panel) {
                    event.preventDefault()
                    panel.matches(':popover-open') ? hide(panel) : show(panel, this.$el)
                }
            }
            const nav = contentOf(this.$el, 'navigation-menu')
            if (nav) {
                if (nav.matches(':popover-open') && openedBy.get(nav) === 'hover') openedBy.set(nav, 'click')
                else if (nav.matches(':popover-open')) hide(nav)
                else show(nav, this.$el)
            }
            for (const kind of ['menubar', 'dropdown-menu-sub']) {
                const panel = contentOf(this.$el, kind)
                if (panel?.matches(':popover-open') && openedBy.get(panel) === 'hover') {
                    event.preventDefault()
                    openedBy.set(panel, 'click')
                }
            }
        },
        key(event) {
            if (event.key === ' ' && this.$el.matches('[role="button"]:not(button)')) {
                event.preventDefault()
                this.$el.click()
            }
            if (this.$el.matches('[data-slot="menubar-trigger"]')) moveBar(event, this.$el)
        },
        context(event) {
            const panel = contentOf(this.$el, 'context-menu')
            if (! panel) return
            event.preventDefault()
            hide(panel)
            const offset = Number(panel.dataset.alignOffset ?? 4)
            panel.style.left = `${event.clientX}px`
            panel.style.top = `${event.clientY + offset}px`
            show(panel)
            const rect = panel.getBoundingClientRect()
            if (event.clientX + rect.width > innerWidth) panel.style.left = `${Math.max(0, event.clientX - rect.width)}px`
            if (event.clientY + offset + rect.height > innerHeight) panel.style.top = `${Math.max(0, event.clientY - rect.height)}px`
        },
        destroy() {
            this.stopMorph?.()
            for (const kind of kinds) clearTimeout(timers.get(contentOf(this.$el, kind)))
        },
    }
}

export function content(binding = null) {
    return confirmation({
        init() {
            if (binding?.property) {
                this.$watch(() => this.$wire.$get(binding.property), open => open ? show(this.$el, this.trigger) : hide(this.$el))
                this.$nextTick(() => { if (this.$wire.$get(binding.property)) show(this.$el, this.trigger) })
            }
        },
        get kind() { return kinds.find(kind => this.$el.dataset.slot === `${kind}-content`) },
        get trigger() { return this.kind ? triggerOf(this.$el, this.kind) : null },
        toggled(event) {
            if (event.newState === 'closed') this.confirmClosed()
            if (event.target !== this.$el) return
            const open = event.newState === 'open'
            if (this.kind !== 'context-menu' || this.trigger?.matches('button, [role="button"]')) this.trigger?.setAttribute('aria-expanded', String(open))
            if (binding?.property && Boolean(this.$wire.$get(binding.property)) !== open) this.$wire.$set(binding.property, open, binding.live)
            if (open && this.$el.getAttribute('role') === 'menu' && ! this.$el.contains(document.activeElement)) this.$el.focus()
        },
        enter() {
            if (['hover-card', 'navigation-menu'].includes(this.kind)) clearTimeout(timers.get(this.$el))
        },
        leave(event) {
            if (this.$el.contains(event.relatedTarget) || this.trigger?.contains(event.relatedTarget)) return
            if (this.kind === 'hover-card') later(this.$el, () => hide(this.$el), Number(this.trigger?.dataset.closeDelay ?? 300))
            if (this.kind === 'navigation-menu' && openedBy.get(this.$el) === 'hover') later(this.$el, () => hide(this.$el), 50)
        },
        hover(event) {
            if (this.$el.getAttribute('role') !== 'menu') return
            const row = event.target.closest?.('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"], [data-slot="dropdown-menu-checkbox-item"], [data-slot="dropdown-menu-radio-item"]')
            if (! row || row.closest('[role="menu"]') !== this.$el) return
            const target = row.matches('input, button, a') ? row : row.querySelector('input, button, a')
            if (! target?.disabled) target?.focus({ preventScroll: true })
            for (const trigger of this.$el.querySelectorAll('[data-slot="dropdown-menu-sub-trigger"]')) {
                if (trigger.closest('[role="menu"]') === this.$el && trigger !== row) hide(contentOf(trigger, 'dropdown-menu-sub'))
            }
        },
        click(event) {
            this.confirmClick(event)
            const item = event.target.closest?.('[data-slot="dropdown-menu-item"]')
            if (! item || event.defaultPrevented || item.disabled || item.getAttribute('aria-disabled') === 'true') return
            hide(item.closest('[data-slot="dropdown-menu-content"], [data-slot="context-menu-content"], [data-slot="menubar-content"]'))
        },
        key(event) {
            const panel = event.target.closest?.('[role="menu"]')
            if (panel !== this.$el) return
            if (this.kind === 'menubar' && ! event.target.matches('[data-slot="dropdown-menu-sub-trigger"]') && moveBar(event, this.trigger)) return
            const available = items(panel)
            const index = available.indexOf(event.target)
            const position = { ArrowDown: index < 0 ? 0 : (index + 1) % available.length, ArrowUp: index < 0 ? -1 : (index - 1 + available.length) % available.length, Home: 0, End: -1 }[event.key]
            if (position !== undefined) { event.preventDefault(); available.at(position)?.focus(); return }
            if (event.key === 'ArrowRight' && event.target.matches('[data-slot="dropdown-menu-sub-trigger"]')) {
                event.preventDefault()
                const child = contentOf(event.target, 'dropdown-menu-sub')
                show(child, event.target)
                if (child) items(child)[0]?.focus()
            }
            if (event.key === 'ArrowLeft' && this.kind === 'dropdown-menu-sub') { event.preventDefault(); hide(panel); this.trigger?.focus() }
        },
        escape() { if (['tooltip', 'hover-card'].includes(this.kind)) hide(this.$el) },
        destroy() { clearTimeout(timers.get(this.$el)) },
    })
}
