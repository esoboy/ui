/*
 * The toolbar on one line. The controls that don't fit go into More, its last button:
 * the least used first (data-priority 3, then 2, then 1; the last first among equals),
 * each group a row there, in the toolbar's order. A separator shows only between two
 * controls left in the toolbar. Laid out again whenever the toolbar's width changes.
 */

import { contentOf } from '../../support/overlay.js'

const SEPARATOR = '[data-slot="separator"]'

export function overflow(toolbar, laidOut) {
    const more = toolbar.querySelector(':scope > [data-editor-more]')
    const panel = more && contentOf(more, 'popover')

    if (! panel) return null

    // The controls in order, each with its group (the separators before it) and priority;
    // a bare popover's content stays where it is, its trigger going wherever.
    const items = []
    const separators = []

    for (const child of toolbar.children) {
        if (child === more || child === panel || child.matches('[popover]')) continue
        if (child.matches(SEPARATOR)) separators.push(child)
        else items.push({ element: child, group: separators.length, priority: Number(child.dataset.priority) || 2 })
    }

    const order = items.map((item, index) => ({ ...item, index })).sort((a, b) => b.priority - a.priority || b.index - a.index)
    const fits = () => toolbar.scrollWidth <= toolbar.clientWidth

    // Separators between controls showing, one between two groups.
    const tidy = () => {
        let shown = false
        let pending = null

        for (const child of toolbar.children) {
            if (child.matches(SEPARATOR)) {
                child.hidden = true
                if (shown) pending ??= child
            } else if (! child.hidden && child !== more && child !== panel && ! child.matches('[popover]')) {
                if (pending) pending.hidden = false
                pending = null
                shown = true
            }
        }
    }

    const layout = () => {
        // Out of sight (in a closed dialog, a hidden tab), it has no width to fit.
        if (! toolbar.clientWidth) return

        // All back in the toolbar, in order, to measure.
        items.forEach((item, index) => {
            if (index && item.group !== items[index - 1].group) separators.slice(items[index - 1].group, item.group).forEach(separator => toolbar.insertBefore(separator, more))
            item.element.hidden = false
            toolbar.insertBefore(item.element, more)
        })
        panel.replaceChildren()
        more.hidden = true
        tidy()

        const hidden = new Set()

        if (! fits()) {
            more.hidden = false

            for (const item of order) {
                item.element.hidden = true
                hidden.add(item.element)
                tidy()

                if (fits()) break
            }
        }

        const rows = new Map()

        for (const item of items.filter(({ element }) => hidden.has(element))) {
            if (! rows.has(item.group)) {
                const row = document.createElement('div')

                row.dataset.slot = 'editor-more-group'
                row.setAttribute('role', 'group')
                rows.set(item.group, row)
                panel.append(row)
            }

            item.element.hidden = false
            rows.get(item.group).append(item.element)
        }

        laidOut?.()
    }

    const observer = new ResizeObserver(layout)

    observer.observe(toolbar)
    layout()

    return { layout, disconnect: () => observer.disconnect(), panel }
}
