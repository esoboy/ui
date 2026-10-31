/*
 * <eso-data-table>
 *
 * The select-all checkbox checks or clears every row checkbox (each announces its own
 * change, so wire:model sees them all) and shows mixed while only some are checked.
 * Rows of checked boxes get data-state="selected", as TanStack rows do in shadcn.
 *
 * Row Toggles expand a row: its Row Detail, or the rows whose data-parent is its
 * data-row-id (shown only while every ancestor is expanded). Expanded rows are kept
 * by id, so they stay expanded through morphs and pages.
 *
 * Sticky cells (data-sticky="start" / "end") get --sticky-offset, the width of the
 * sticky cells before them, and a scrolled table container says which edges have
 * more to scroll to (data-scroll-start / data-scroll-end) for their shadows.
 *
 * A merged cell (rowspan) is highlighted with every row it spans, not just its own.
 */

import { connected, define, disconnected } from '../support/element.js'

const ALL = '[data-data-table-select-all]'
const ROW = '[data-data-table-select-row]'
const TOGGLE = '[data-data-table-row-toggle]'

// Sets or removes an attribute only when it changes, so the observer isn't woken for nothing.
const set = (element, name, value) => {
    if (value === null) element.hasAttribute(name) && element.removeAttribute(name)
    else if (element.getAttribute(name) !== value) element.setAttribute(name, value)
}

export class DataTableElement extends HTMLElement {
    #expanded = new Set()
    #seen = new Set()
    #hovered = null
    #observer = new MutationObserver(() => this.#refresh())
    #resizes = new ResizeObserver(() => this.#stick())

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('change', event => {
            if (event.target.matches?.(ALL) && this.#owns(event.target)) this.#selectAll(event.target.checked)
            else if (event.target.matches?.(ROW)) this.#sync()
        }, { signal })

        this.addEventListener('click', event => {
            const toggle = event.target.closest?.(TOGGLE)
            const id = toggle && this.#owns(toggle) ? toggle.closest('[data-row-id]')?.dataset.rowId : undefined

            if (id === undefined) return

            this.#expanded.has(id) ? this.#expanded.delete(id) : this.#expanded.add(id)
            this.#expand()
        }, { signal })

        this.addEventListener('scroll', event => event.target.matches?.('[data-slot="table-container"]') && this.#edges(event.target), { capture: true, passive: true, signal })
        this.addEventListener('pointerover', event => this.#hover(event.target.closest?.('tbody > tr') ?? null), { signal })
        this.addEventListener('pointerleave', () => this.#hover(null), { signal })

        this.#refresh()

        // Livewire morphs bring new rows, the server's checked state, and reset what is set here.
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['checked', 'data-state', 'hidden', 'aria-expanded', 'data-leaf', 'style', 'data-scroll-start', 'data-scroll-end'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
        this.#resizes.disconnect()
    }

    get rows() {
        return [...this.querySelectorAll(ROW)].filter(box => this.#owns(box) && ! box.disabled)
    }

    #owns(element) {
        return element.closest('eso-data-table') === this
    }

    #refresh() {
        this.#sync()
        this.#expand()
        this.#stick()

        for (const container of this.querySelectorAll('[data-slot="table-container"]')) {
            if (this.#owns(container)) this.#edges(container)
        }
    }

    #selectAll(checked) {
        for (const box of this.rows.filter(box => box.checked !== checked)) {
            box.checked = checked
            box.dispatchEvent(new Event('input', { bubbles: true }))
            box.dispatchEvent(new Event('change', { bubbles: true }))
        }

        this.#sync()
    }

    #sync() {
        const rows = this.rows
        const checked = rows.filter(box => box.checked).length

        for (const all of [...this.querySelectorAll(ALL)].filter(box => this.#owns(box))) {
            all.checked = rows.length > 0 && checked === rows.length
            all.indeterminate = checked > 0 && checked < rows.length
        }

        for (const box of rows) {
            const row = box.closest('tr')

            if (row) set(row, 'data-state', box.checked ? 'selected' : null)
        }
    }

    #expand() {
        const rows = [...this.querySelectorAll('tr[data-row-id]')].filter(row => this.#owns(row))
        const byId = new Map(rows.map(row => [row.dataset.rowId, row]))
        const parents = new Set(rows.map(row => row.dataset.parent).filter(Boolean))
        const details = new Map([...this.querySelectorAll('tr[data-detail-for]')].filter(detail => this.#owns(detail)).map(detail => [detail.dataset.detailFor, detail]))

        for (const row of rows) {
            if (this.#seen.has(row.dataset.rowId)) continue

            this.#seen.add(row.dataset.rowId)
            if (row.hasAttribute('data-default-expanded')) this.#expanded.add(row.dataset.rowId)
        }

        const shown = (row, depth = 0) => {
            const parent = row.dataset.parent

            if (! parent || depth > 50) return true

            return this.#expanded.has(parent) && (! byId.has(parent) || shown(byId.get(parent), depth + 1))
        }

        for (const row of rows) {
            const id = row.dataset.rowId
            const visible = shown(row)
            const detail = details.get(id)
            const expandable = parents.has(id) || Boolean(detail)
            const open = expandable && this.#expanded.has(id)
            const toggle = row.querySelector(TOGGLE)

            set(row, 'hidden', visible ? null : '')
            if (detail) set(detail, 'hidden', visible && open ? null : '')

            if (toggle) {
                set(toggle, 'aria-expanded', String(open))
                set(toggle, 'data-leaf', expandable ? null : '')
            }
        }
    }

    // Each sticky cell sits after the sticky cells before it on its edge.
    #stick() {
        for (const table of this.querySelectorAll('table')) {
            if (! this.#owns(table) || ! table.querySelector('[data-sticky]')) continue

            this.#resizes.observe(table)

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
    }

    #edges(container) {
        const scrolled = Math.abs(container.scrollLeft)
        const end = container.scrollWidth - container.clientWidth

        set(container, 'data-scroll-start', scrolled > 0 ? '' : null)
        set(container, 'data-scroll-end', scrolled < end - 1 ? '' : null)
    }

    #hover(row) {
        if (row === this.#hovered) return

        this.#hovered = row

        for (const cell of this.querySelectorAll('[data-span-hover]')) cell.removeAttribute('data-span-hover')

        // The cells merged down from the rows above cover this one too.
        for (let above = row?.previousElementSibling, distance = 1; above && distance < 100; above = above.previousElementSibling, distance++) {
            for (const cell of above.cells ?? []) {
                if (cell.rowSpan > distance) cell.setAttribute('data-span-hover', '')
            }
        }
    }
}

export function register() {
    define('eso-data-table', DataTableElement)
}
