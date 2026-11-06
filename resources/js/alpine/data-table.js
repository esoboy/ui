/*
 * <eso-data-table>
 *
 * The select-all checkbox updates the visible, enabled rows in one Livewire action
 * and shows mixed while only some of those rows are checked.
 * Selected rows are rendered by Blade.
 *
 * Tree and detail toggles keep independent expansion state by row ID.
 *
 * Sticky cells (data-sticky="start" / "end") get --sticky-offset, the width of the
 * sticky cells before them, and a scrolled table container says which edges have
 * more to scroll to (data-scroll-start / data-scroll-end) for their shadows.
 *
 * A merged cell (rowspan) is highlighted with every row it spans, not just its own.
 */

const ALL = '[data-data-table-select-all]'
const ROW = '[data-data-table-select-row]'
const TOGGLE = '[data-data-table-row-toggle]'
const SELECTION_BAR = '[data-slot="data-table-selection-toolbar"]'
const SELECTION_OFFSET = '--ui-selection-offset'

function stackSelectionBars() {
    let offset = 0

    for (const bar of document.querySelectorAll(SELECTION_BAR)) {
        if (! bar.offsetHeight) continue
        const position = `${offset}px`
        if (bar.style.getPropertyValue(SELECTION_OFFSET) !== position) bar.style.setProperty(SELECTION_OFFSET, position)
        offset += bar.offsetHeight + 8
    }
}

// Sets or removes an attribute only when it changes, so the observer isn't woken for nothing.
const set = (element, name, value) => {
    if (value === null) element.hasAttribute(name) && element.removeAttribute(name)
    else if (element.getAttribute(name) !== value) element.setAttribute(name, value)
}

import { listen, disconnected } from './interaction.js'

export default () => ({
    _expanded: new Set(),
    _details: new Set(),
    _serverExpanded: new Map(),
    _hovered: null,
    _observer: null,
    _resizes: null,
    _selectionBar: null,

    init() {
        this._observer = new MutationObserver(() => this._refresh())
        this._resizes = new ResizeObserver(() => {
            this._stick()
            if (this._selectionBar) stackSelectionBars()
        })

        listen(this, this.$el, 'change', event => {
            if (event.target.matches?.(ALL) && this._owns(event.target)) this._selectAll(event.target.checked)
            else if (event.target.matches?.(ROW)) this._sync()
        })

        listen(this, this.$el, 'click', event => {
            const toggle = event.target.closest?.(TOGGLE)
            const id = toggle && this._owns(toggle) ? toggle.closest('[data-row-id]')?.dataset.rowId : undefined

            if (id === undefined) return

            const expanded = toggle.dataset.dataTableRowToggle === 'detail' ? this._details : this._expanded
            expanded.has(id) ? expanded.delete(id) : expanded.add(id)
            this._expand()
        })

        listen(this, this.$el, 'scroll', event => event.target.matches?.('[data-slot="table-container"]') && this._edges(event.target), { capture: true, passive: true })
        listen(this, this.$el, 'pointerover', event => this._hover(event.target.closest?.('tbody > tr') ?? null))
        listen(this, this.$el, 'pointerleave', () => this._hover(null))

        this._refresh()

        // Livewire morphs bring new rows, the server's checked state, and reset what is set here.
        this._observer.observe(this.$el, { childList: true, subtree: true, attributes: true, attributeFilter: ['checked', 'disabled', 'class', 'data-state', 'hidden', 'aria-expanded', 'data-expanded', 'style', 'data-scroll-start', 'data-scroll-end'] })
    },

    destroy() {
        disconnected(this)
        this._observer.disconnect()
        this._resizes.disconnect()
        if (this._selectionBar) queueMicrotask(stackSelectionBars)
    },

    get rows() {
        return [...this.$el.querySelectorAll(ROW)].filter(box => this._owns(box))
    },

    _selectable(rows) {
        return rows.filter(box => ! box.matches(':disabled') && box.checkVisibility({ visibilityProperty: true }))
    },

    _owns(element) {
        return element.closest('eso-data-table') === this.$el
    },

    _refresh() {
        this._expand()
        this._sync()
        this._stick()
        this._observeSelectionBar()

        for (const container of this.$el.querySelectorAll('[data-slot="table-container"]')) {
            if (this._owns(container)) this._edges(container)
        }
    },

    _observeSelectionBar() {
        const bar = [...this.$el.querySelectorAll(SELECTION_BAR)].find(element => this._owns(element)) ?? null
        if (bar === this._selectionBar) {
            if (bar && ! bar.style.getPropertyValue(SELECTION_OFFSET)) stackSelectionBars()
            return
        }

        if (this._selectionBar) this._resizes.unobserve(this._selectionBar)
        this._selectionBar = bar
        if (bar) this._resizes.observe(bar)
        stackSelectionBars()
    },

    _selectAll(checked) {
        const values = this._selectable(this.rows).map(box => box.value)
        if (values.length === 0) {
            this._sync()
            return
        }

        this.$wire.selectRows(this.$el.dataset.selection, values, checked)
    },

    _sync() {
        const rows = this.rows
        const headers = [...this.$el.querySelectorAll(ALL)].filter(box => this._owns(box))
        if (! rows.length && ! headers.length) return

        const selectable = this._selectable(rows)
        const selected = new Set((this.$wire[this.$el.dataset.selection] ?? []).map(String))
        const checked = selectable.filter(box => selected.has(box.value)).length

        for (const all of headers) {
            all.checked = selectable.length > 0 && checked === selectable.length
            all.indeterminate = checked > 0 && checked < selectable.length
        }

    },

    _expand() {
        const rows = [...this.$el.querySelectorAll('tr[data-row-id]')].filter(row => this._owns(row))
        const details = new Map([...this.$el.querySelectorAll('tr[data-detail-for]')].filter(detail => this._owns(detail)).map(detail => [detail.dataset.detailFor, detail]))
        const visibleRows = new Set()

        for (const row of rows) {
            const id = row.dataset.rowId
            const expanded = row.getAttribute('data-expanded')
            if (this._serverExpanded.get(id) === expanded) continue

            this._serverExpanded.set(id, expanded)
            if (expanded === null) continue
            expanded === 'false' ? this._expanded.delete(id) : this._expanded.add(id)
        }

        for (const row of rows) {
            const { rowId: id, parent } = row.dataset
            // The Blade iterator renders each parent before its children.
            const visible = parent === undefined || (visibleRows.has(parent) && this._expanded.has(parent))
            if (visible) visibleRows.add(id)

            const detail = details.get(id)
            const treeToggle = row.querySelector('[data-data-table-row-toggle="tree"]')
            const detailToggle = row.querySelector('[data-data-table-row-toggle="detail"]')

            set(row, 'hidden', visible ? null : '')
            if (detail) set(detail, 'hidden', visible && this._details.has(id) ? null : '')

            if (treeToggle) set(treeToggle, 'aria-expanded', String(this._expanded.has(id)))
            if (detailToggle) set(detailToggle, 'aria-expanded', String(this._details.has(id)))
        }
    },

    // Each sticky cell sits after the sticky cells before it on its edge.
    _stick() {
        for (const table of this.$el.querySelectorAll('table')) {
            if (! this._owns(table) || ! table.querySelector('[data-sticky]')) continue

            this._resizes.observe(table)

            for (const row of table.rows) {
                const cells = [...row.cells]

                for (const [edge, order] of [['start', cells], ['end', [...cells].reverse()]]) {
                    let offset = 0

                    for (const cell of order.filter(cell => cell.dataset.sticky === edge)) {
                        if (cell.style.getPropertyValue('--sticky-offset') !== `${offset}px`) cell.style.setProperty('--sticky-offset', `${offset}px`)
                        offset += cell.getBoundingClientRect().width
                    }
                }
            }
        }
    },

    _edges(container) {
        const scrolled = Math.abs(container.scrollLeft)
        const end = container.scrollWidth - container.clientWidth

        set(container, 'data-scroll-start', scrolled > 0 ? '' : null)
        set(container, 'data-scroll-end', scrolled < end - 1 ? '' : null)
    },

    _hover(row) {
        if (row === this._hovered) return

        this._hovered = row

        for (const cell of this.$el.querySelectorAll('[data-span-hover]')) cell.removeAttribute('data-span-hover')

        // The cells merged down from the rows above cover this one too.
        for (let above = row?.previousElementSibling, distance = 1; above && distance < 100; above = above.previousElementSibling, distance++) {
            for (const cell of above.cells ?? []) {
                if (cell.rowSpan > distance) cell.setAttribute('data-span-hover', '')
            }
        }
    },
})
