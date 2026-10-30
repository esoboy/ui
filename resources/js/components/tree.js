/*
 * <eso-tree> and <eso-tree-select>
 *
 * A tree chooses one item (.value is its value), or, when checkable, checks items with
 * checkboxes that cascade: checking a parent checks everything below it, and a parent
 * is checked when all its children are, mixed when some are (.value is every checked
 * value). input/change are dispatched from the tree, so wire:model binds on it.
 * Keyboard as in the ARIA tree pattern: Up/Down, Right opens or goes in, Left closes
 * or goes out, Home/End, Enter or Space chooses or checks.
 *
 * Search (data-tree-search) keeps the items whose label contains it, with their
 * ancestors opened, highlighting the matches (CSS Custom Highlight API); cleared, it
 * brings back what was open. A lazy item (data-lazy) asks the Livewire method named
 * by data-load for its children when it opens; when they come, a checked item checks
 * them. With data-draggable, dragging an item (a long press on touch screens) over
 * another dispatches move from the tree: { value, target, position }, before or after
 * the target or inside it. The DOM stays as it is; the server moves the item.
 *
 * A Tree Select is a Select-style trigger opening a tree; it proxies the tree's value.
 */

import { announce, connected, define, disconnected, readValue } from '../support/element.js'
import { link } from '../support/overlay.js'

const ITEM = '[data-slot="tree-item"]'
const ROW = '[data-slot="tree-item-row"]'
const SEARCH = '[data-tree-search]'

const own = (root, selector) => [...root.querySelectorAll(selector)].filter(element => element.closest('eso-tree') === root)
const childItems = item => [...(item.querySelector(':scope > [data-slot="tree-group"]')?.children ?? [])].filter(child => child.matches(ITEM))
const isParent = item => item.hasAttribute('aria-expanded')
const isDisabled = item => item.getAttribute('aria-disabled') === 'true' || Boolean(item.parentElement?.closest(`${ITEM}[aria-disabled="true"]`))
const label = item => item.querySelector(`:scope > ${ROW} [data-slot="tree-item-label"]`)

// Every tree's search matches, painted as one highlight.
const matches = new Map()

function paint() {
    if (! window.CSS?.highlights || typeof Highlight === 'undefined') return

    const ranges = [...matches.values()].flat()

    ranges.length ? CSS.highlights.set('ui-tree-match', new Highlight(...ranges)) : CSS.highlights.delete('ui-tree-match')
}

export class TreeElement extends HTMLElement {
    #value = null
    #expanded = new Set()
    #query = ''
    #before = null
    #seen = new Set()
    #drag = null
    #dropped = false
    #observer = new MutationObserver(() => this.#refresh())

    connectedCallback() {
        const signal = connected(this)

        this.#value = readValue(this)

        if (this.checkable) this.#value = this.#cascade(Array.isArray(this.#value) ? this.#value : [])

        for (const item of this.items) {
            this.#seen.add(item.dataset.value)
            if (item.getAttribute('aria-expanded') === 'true') this.#expanded.add(item.dataset.value)
        }

        // A drag ends without the click that would choose the item dropped.
        this.addEventListener('click', event => {
            if (! this.#dropped) return

            event.stopPropagation()
            this.#dropped = false
        }, { capture: true, signal })

        this.addEventListener('click', event => this.#onClick(event), { signal })
        this.addEventListener('keydown', event => this.#onKey(event), { signal })
        this.addEventListener('input', event => event.target.matches?.(SEARCH) && this.search(event.target.value), { signal })
        this.addEventListener('pointerdown', event => this.#onPointerDown(event), { signal })

        this.#sync()

        // Livewire morphs put back the server's open and checked states, and bring loaded children: restore ours.
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['aria-expanded', 'aria-selected', 'aria-checked', 'hidden'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
        matches.delete(this)
        paint()
    }

    get checkable() {
        return this.hasAttribute('data-checkable')
    }

    get items() {
        return own(this, ITEM)
    }

    get value() {
        return this.checkable ? [...this.#value] : this.#value
    }

    set value(value) {
        this.#value = this.checkable
            ? this.#cascade(Array.isArray(value) ? value : [])
            : (value === null || value === undefined || value === '' ? null : String(value))
        this.#sync()
    }

    /** Labels of the chosen or checked items, in tree order. */
    get labels() {
        return this.items.filter(item => item.getAttribute(this.checkable ? 'aria-checked' : 'aria-selected') === 'true').map(item => item.querySelector(':scope > [data-slot="tree-item-row"] [data-slot="tree-item-label"]')?.textContent ?? '')
    }

    expand(item, open = ! this.#expanded.has(item.dataset.value)) {
        if (! isParent(item)) return

        open ? this.#expanded.add(item.dataset.value) : this.#expanded.delete(item.dataset.value)
        this.#sync()

        if (open) this.#load(item)
    }

    search(query) {
        const text = query.trim().toLowerCase()

        if (text && this.#before === null) this.#before = new Set(this.#expanded)

        if (! text && this.#before !== null) {
            this.#expanded = this.#before
            this.#before = null
        }

        this.#query = text
        this.#sync()
    }

    #announce() {
        announce(this)
    }

    #refresh() {
        // Children a lazy item loaded: a checked item checks them too.
        const fresh = this.items.filter(item => ! this.#seen.has(item.dataset.value))
        const before = JSON.stringify(this.#value)

        fresh.forEach(item => this.#seen.add(item.dataset.value))
        if (fresh.length && this.checkable) this.#value = this.#cascade(this.#value)

        this.#sync()

        if (JSON.stringify(this.#value) !== before) this.#announce()
    }

    async #load(item) {
        const value = item.dataset.value
        const id = this.closest('[wire\\:id]')?.getAttribute('wire:id')
        const component = id && window.Livewire?.find(id)

        if (! this.dataset.load || ! item.hasAttribute('data-lazy') || item.getAttribute('aria-busy') === 'true' || childItems(item).length || ! component) return

        item.setAttribute('aria-busy', 'true')

        try {
            await component.call(this.dataset.load, value)
        } finally {
            this.items.find(other => other.dataset.value === value)?.removeAttribute('aria-busy')
        }
    }

    // Search: the matches and their ancestors stay, opened; the rest hide.
    #filter() {
        const keep = new Set()
        const ranges = []

        if (this.#query) {
            for (const item of this.items) {
                const text = label(item)?.firstChild
                const index = text?.nodeType === Node.TEXT_NODE ? text.textContent.toLowerCase().indexOf(this.#query) : -1

                if (index < 0) continue

                keep.add(item)

                for (let node = item.parentElement?.closest(ITEM); node && node.closest('eso-tree') === this; node = node.parentElement?.closest(ITEM)) {
                    keep.add(node)
                    this.#expanded.add(node.dataset.value)
                }

                const range = document.createRange()

                range.setStart(text, index)
                range.setEnd(text, index + this.#query.length)
                ranges.push(range)
            }
        }

        for (const item of this.items) {
            const hidden = Boolean(this.#query) && ! keep.has(item)

            if (item.hidden !== hidden) item.hidden = hidden
        }

        const empty = this.querySelector('[data-slot="tree-empty"]')
        const nothing = Boolean(this.#query) && keep.size === 0

        // Written only when it changes: every write wakes the observer, which filters again.
        if (empty && empty.hidden === nothing) empty.hidden = ! nothing

        ranges.length ? matches.set(this, ranges) : matches.delete(this)
        paint()
    }

    #choose(item) {
        if (isDisabled(item)) return

        if (this.checkable) {
            this.#check(item, item.getAttribute('aria-checked') !== 'true')
        } else {
            this.#value = item.dataset.value
            this.#sync()
        }

        this.#announce()
    }

    // Checking cascades down to every enabled descendant; parents follow from their leaves.
    #check(item, checked) {
        const values = new Set(this.#value)
        const walk = node => {
            if (isDisabled(node)) return

            checked ? values.add(node.dataset.value) : values.delete(node.dataset.value)
            childItems(node).forEach(walk)
        }

        walk(item)
        this.#value = this.#settle(values)
        this.#sync()
    }

    // A checked parent checks everything below it; then parents follow their children.
    #cascade(values) {
        const set = new Set(values.map(String))

        for (const item of this.items) {
            if (set.has(item.dataset.value)) item.querySelectorAll(ITEM).forEach(child => ! isDisabled(child) && set.add(child.dataset.value))
        }

        return this.#settle(set)
    }

    // A parent is checked exactly when all its enabled children are.
    #settle(values) {
        const visit = item => {
            const children = childItems(item).filter(child => ! isDisabled(child))

            if (! children.length) return values.has(item.dataset.value)

            const states = children.map(visit)
            const all = states.every(Boolean)

            all ? values.add(item.dataset.value) : values.delete(item.dataset.value)

            return all
        }

        this.items.filter(item => item.parentElement === this || item.parentElement?.closest(ITEM) === null).forEach(visit)

        return this.items.map(item => item.dataset.value).filter(value => values.has(value))
    }

    #sync() {
        const set = (element, name, value) => {
            if (value === null) element.hasAttribute(name) && element.removeAttribute(name)
            else if (element.getAttribute(name) !== value) element.setAttribute(name, value)
        }

        this.#filter()

        const checked = new Set(this.checkable ? this.#value : [])
        const visible = this.#visible()
        const focusable = this.items.find(item => item.tabIndex === 0 && visible.includes(item))
            ?? visible.find(item => item.getAttribute('aria-selected') === 'true')
            ?? visible[0]

        for (const item of this.items) {
            const level = (() => {
                let depth = 1
                for (let node = item.parentElement?.closest(ITEM); node && node.closest('eso-tree') === this; node = node.parentElement?.closest(ITEM)) depth++
                return depth
            })()

            set(item, 'aria-level', String(level))

            if (isParent(item)) {
                const open = this.#expanded.has(item.dataset.value)
                const group = item.querySelector(':scope > [data-slot="tree-group"]')

                set(item, 'aria-expanded', String(open))
                if (group && group.hidden === open) group.hidden = ! open
            }

            if (this.checkable) {
                const children = item.querySelectorAll(ITEM)
                const some = [...children].some(child => checked.has(child.dataset.value))
                const state = checked.has(item.dataset.value) ? 'true' : some ? 'mixed' : 'false'
                const box = item.querySelector(':scope > [data-slot="tree-item-row"] [data-tree-check]')

                set(item, 'aria-checked', state)
                if (box) {
                    box.checked = state === 'true'
                    box.indeterminate = state === 'mixed'
                }
            } else {
                set(item, 'aria-selected', String(item.dataset.value === this.#value))
            }

            item.tabIndex = item === focusable ? 0 : -1
        }
    }

    // Items the search keeps whose ancestors are all open, in order.
    #visible() {
        return this.items.filter(item => {
            if (item.hidden) return false

            for (let node = item.parentElement?.closest(ITEM); node && node.closest('eso-tree') === this; node = node.parentElement?.closest(ITEM)) {
                if (! this.#expanded.has(node.dataset.value)) return false
            }

            return true
        })
    }

    #focus(item) {
        if (! item) return

        this.items.forEach(other => (other.tabIndex = other === item ? 0 : -1))
        item.focus()
    }

    #onClick(event) {
        const item = event.target.closest?.(ITEM)

        if (! item || item.closest('eso-tree') !== this || ! event.target.closest('[data-slot="tree-item-row"]')) return

        if (event.target.closest('[data-slot="tree-item-toggle"]')) {
            this.expand(item)
            return
        }

        // The checkbox toggles itself natively; the tree decides its state.
        if (event.target.matches('[data-tree-check]')) event.preventDefault()

        if (! this.checkable && isParent(item)) this.expand(item)
        this.#choose(item)
        this.#focus(item)
    }

    #onKey(event) {
        const search = this.querySelector(SEARCH)

        // Down from the search goes into the tree, Up from its first item back.
        if (event.target === search) {
            if (event.key === 'ArrowDown') {
                event.preventDefault()
                this.#focus(this.#visible()[0])
            }

            return
        }

        const item = event.target.closest?.(ITEM)

        if (! item || item.closest('eso-tree') !== this) return

        const visible = this.#visible()
        const index = visible.indexOf(item)
        const go = target => {
            event.preventDefault()
            this.#focus(target)
        }

        switch (event.key) {
            case 'ArrowDown': return go(visible[index + 1])
            case 'ArrowUp': return index === 0 && search ? (event.preventDefault(), search.focus()) : go(visible[index - 1])
            case 'Home': return go(visible[0])
            case 'End': return go(visible.at(-1))
            case 'ArrowRight':
                event.preventDefault()
                if (! isParent(item)) return
                if (! this.#expanded.has(item.dataset.value)) return this.expand(item, true)
                return this.#focus(childItems(item)[0])
            case 'ArrowLeft': {
                event.preventDefault()
                if (isParent(item) && this.#expanded.has(item.dataset.value)) return this.expand(item, false)

                const parent = item.parentElement?.closest(ITEM)
                if (parent && parent.closest('eso-tree') === this) this.#focus(parent)
                return
            }
            case 'Enter':
            case ' ':
                event.preventDefault()
                this.#choose(item)
        }
    }

    // A drag starts once the pointer moves, or on touch screens after a long press (so a swipe still scrolls).
    #onPointerDown(event) {
        const row = event.target.closest?.(ROW)
        const item = row?.closest(ITEM)

        if (! this.hasAttribute('data-draggable') || event.button !== 0 || ! item || item.closest('eso-tree') !== this || isDisabled(item)) return
        if (event.target.closest('[data-slot="tree-item-toggle"], [data-tree-check]')) return

        const touch = event.pointerType === 'touch'
        const start = { x: event.clientX, y: event.clientY }
        const timer = touch ? setTimeout(() => this.#startDrag(item), 400) : 0

        const move = moved => {
            if (! this.#drag) {
                if (Math.hypot(moved.clientX - start.x, moved.clientY - start.y) < 5) return
                if (touch) return stop()

                this.#startDrag(item)
            }

            this.#dragOver(moved.clientX, moved.clientY)
        }

        // While dragging, the page doesn't scroll under the finger.
        const hold = held => this.#drag && held.preventDefault()
        const escape = pressed => pressed.key === 'Escape' && stop()
        const drop = () => stop(true)
        const cancel = () => stop()

        const stop = (dropping = false) => {
            clearTimeout(timer)
            document.removeEventListener('pointermove', move)
            document.removeEventListener('pointerup', drop)
            document.removeEventListener('pointercancel', cancel)
            document.removeEventListener('keydown', escape)
            document.removeEventListener('touchmove', hold)

            if (this.#drag) this.#endDrag(dropping)
        }

        document.addEventListener('pointermove', move)
        document.addEventListener('pointerup', drop)
        document.addEventListener('pointercancel', cancel)
        document.addEventListener('keydown', escape)
        document.addEventListener('touchmove', hold, { passive: false })
    }

    #startDrag(item) {
        this.#drag = { item, target: null, position: null }
        item.toggleAttribute('data-dragging', true)
        this.toggleAttribute('data-dragging', true)
    }

    // Over a row: its top quarter drops before it, its bottom quarter after, the rest inside (not into a disabled item).
    #dragOver(x, y) {
        const drag = this.#drag
        const row = document.elementFromPoint(x, y)?.closest(ROW)
        const target = row?.closest(ITEM)
        let position = null

        if (target && target.closest('eso-tree') === this && ! drag.item.contains(target)) {
            const box = row.getBoundingClientRect()
            const at = (y - box.top) / box.height

            position = at < 0.25 ? 'before' : at > 0.75 ? 'after' : 'inside'
            if (position === 'inside' && isDisabled(target)) position = at < 0.5 ? 'before' : 'after'
        }

        if (drag.target && (drag.target !== target || drag.position !== position)) drag.target.removeAttribute('data-drop')

        drag.target = position ? target : null
        drag.position = position

        if (drag.target) drag.target.setAttribute('data-drop', position)
    }

    #endDrag(dropping) {
        const { item, target, position } = this.#drag

        this.#drag = null
        item.removeAttribute('data-dragging')
        target?.removeAttribute('data-drop')
        this.removeAttribute('data-dragging')

        // The click that follows the pointer going up isn't a choice.
        this.#dropped = true
        setTimeout(() => (this.#dropped = false))

        if (! dropping || ! target) return

        // Dropped inside, the target opens, to show the item there once the server has moved it.
        if (position === 'inside') {
            this.#expanded.add(target.dataset.value)
            this.#sync()
        }

        this.dispatchEvent(new CustomEvent('move', { detail: { value: item.dataset.value, target: target.dataset.value, position } }))
    }
}

export class TreeSelectElement extends HTMLElement {
    #observer = new MutationObserver(() => this.render())

    connectedCallback() {
        const signal = connected(this)

        this.content?.addEventListener('toggle', event => {
            this.trigger?.setAttribute('aria-expanded', String(event.newState === 'open'))

            if (event.newState === 'open') (this.tree?.querySelector(SEARCH) ?? this.tree?.querySelector(`${ITEM}[tabindex="0"]`))?.focus()
        }, { signal })

        // The tree's events become the picker's; one choice closes a single picker.
        for (const type of ['input', 'change']) {
            this.addEventListener(type, event => {
                if (event.target !== this.tree) return

                event.stopPropagation()
                this.render()
                if (type === 'change' && ! this.hasAttribute('data-checkable') && this.content?.matches(':popover-open')) {
                    this.content.hidePopover()
                    this.trigger?.focus()
                }
                this.dispatchEvent(new Event(type, { bubbles: true }))
            }, { signal })
        }

        if (this.trigger) link(this.trigger, this.content)

        // Livewire morphs put back the server's label: show ours.
        if (this.trigger) this.#observer.observe(this.trigger, { childList: true, subtree: true, characterData: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get trigger() {
        return this.querySelector('[data-tree-select-trigger]')
    }

    get content() {
        return this.querySelector('[data-slot="tree-select-content"]')
    }

    get tree() {
        return this.querySelector('eso-tree')
    }

    get value() {
        return this.tree?.value ?? null
    }

    set value(value) {
        if (this.tree) this.tree.value = value
        this.render()
    }

    render() {
        const display = this.trigger?.querySelector('[data-slot="select-value"]')
        const labels = this.tree?.labels ?? []
        const text = labels.length ? labels.join(', ') : this.dataset.placeholder ?? ''

        if (display && display.textContent !== text) display.textContent = text
        if (this.trigger && this.trigger.hasAttribute('data-placeholder') !== (labels.length === 0)) this.trigger.toggleAttribute('data-placeholder', labels.length === 0)
    }
}

export function register() {
    define('eso-tree', TreeElement)
    define('eso-tree-select', TreeSelectElement)
}
