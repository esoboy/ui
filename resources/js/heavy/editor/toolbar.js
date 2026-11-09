/*
 * The toolbar on one line. The controls that don't fit go into More, its last button:
 * the least used first (data-priority 3, then 2, then 1; the last first among equals),
 * each group a row there, in the toolbar's order. A separator shows only between two
 * controls left in the toolbar. Laid out again whenever the toolbar's width changes.
 */

import { contentOf } from '../../alpine/overlay.js'

const SEPARATOR = '[data-slot="separator"]'

export function overflow(toolbar, laidOut) {
    const moved = new Map()
    const hiddenSeparators = new Set()
    const fits = () => toolbar.scrollWidth <= toolbar.clientWidth

    const restore = () => {
        for (const [element, position] of moved) {
            if (position.parentNode === toolbar) {
                const focused = element.contains(document.activeElement) ? document.activeElement : null
                position.replaceWith(element)
                focused?.focus({ preventScroll: true })
            }
        }
        moved.clear()

        for (const separator of hiddenSeparators) separator.hidden = false
        hiddenSeparators.clear()
    }

    const layout = () => {
        // A hidden editor has no width to fit.
        if (! toolbar.clientWidth) return

        restore()
        const more = toolbar.querySelector(':scope > [data-editor-more]')
        const panel = more && contentOf(more, 'popover')
        if (! panel) return

        const rows = [...panel.querySelectorAll(':scope > [data-editor-overflow-group]')]
        const children = [...toolbar.children].filter(child => child !== more && child !== panel && ! child.matches('[popover]'))
        const separators = children.filter(child => child.matches(SEPARATOR) && !child.hidden)
        let group = 0
        const items = []
        for (const child of children) {
            if (child.matches(SEPARATOR)) group++
            else if (!child.hidden) items.push({ element: child, group, priority: Number(child.dataset.priority) || 2 })
        }
        const order = [...items].reverse().sort((a, b) => b.priority - a.priority)

        // Keep only the separators between visible controls.
        const tidy = () => {
            for (const separator of separators) {
                separator.hidden = true
                hiddenSeparators.add(separator)
            }

            let shown = false
            let pending = null
            for (const child of children) {
                if (child.matches(SEPARATOR)) {
                    if (shown && hiddenSeparators.has(child)) pending ??= child
                } else if (!child.hidden) {
                    if (pending) pending.hidden = false
                    pending = null
                    shown = true
                }
            }
        }

        const focused = toolbar.contains(document.activeElement) ? document.activeElement : null
        for (const row of rows) row.hidden = true
        more.hidden = true
        tidy()

        if (!fits()) {
            more.hidden = false
            for (const { element } of order) {
                element.hidden = true
                tidy()
                if (fits()) break
            }
        }

        for (const { element, group } of items) {
            if (!element.hidden) continue
            const row = rows[group]
            element.hidden = false
            if (!row) continue

            // Restore the exact Blade position, including Livewire's conditional markers.
            const position = document.createComment('editor overflow')
            element.before(position)
            moved.set(element, position)
            row.hidden = false
            row.append(element)
        }

        if (more.hidden && panel.matches(':popover-open')) panel.hidePopover()
        if (focused && document.activeElement !== focused) {
            if (panel.contains(focused) && !panel.matches(':popover-open')) more.focus({ preventScroll: true })
            else focused.focus({ preventScroll: true })
        }

        laidOut?.()
    }

    const observer = new ResizeObserver(layout)

    observer.observe(toolbar)
    layout()

    return { toolbar, layout, restore, disconnect: () => observer.disconnect() }
}
