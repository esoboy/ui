/*
 * <eso-cascader>
 *
 * Picks a path through nested options, one column per level. Choosing a parent opens
 * its children; choosing a leaf (or any option, with data-change-on-select) sets the
 * value. .value is the path of values and input/change are dispatched from the
 * cascader itself, so wire:model binds on it. Keyboard: Up/Down in a column, Right
 * or Enter to open a parent, Left to go back, Enter on a leaf to choose it.
 */

import { announce, connected, define, disconnected, readValue, writeHidden } from '../support/element.js'
import { link } from '../support/overlay.js'
import { clone, readTemplates } from '../support/templates.js'

const ITEM = '[data-cascader-item]'

export class CascaderElement extends HTMLElement {
    #value = []
    #open = []
    #templates = {}
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        this.#templates = readTemplates(this)

        const value = readValue(this, [])

        this.#value = Array.isArray(value) ? value.map(String) : []

        this.trigger?.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault()
                this.open()
            }
        }, { signal })

        this.content?.addEventListener('click', event => this.#onClick(event), { signal })
        this.content?.addEventListener('keydown', event => this.#onKey(event), { signal })
        this.content?.addEventListener('toggle', event => this.trigger?.setAttribute('aria-expanded', String(event.newState === 'open')), { signal })

        // Livewire morphs put back the server's label: show ours.
        if (this.trigger) this.#observer.observe(this.trigger, { childList: true, subtree: true, characterData: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get trigger() {
        return this.querySelector('[data-cascader-trigger]')
    }

    get content() {
        return this.querySelector('[data-slot="cascader-content"]')
    }

    get options() {
        try {
            return JSON.parse(this.querySelector('[data-cascader-options]')?.textContent || '[]')
        } catch {
            return []
        }
    }

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    }

    get value() {
        return [...this.#value]
    }

    set value(value) {
        this.#value = Array.isArray(value) ? value.map(String) : []
        this.#render()
    }

    open() {
        if (! this.content || this.trigger?.disabled) return

        this.#open = [...this.#value]
        this.#renderColumns()
        this.content.showPopover({ source: this.trigger })

        const columns = this.content.querySelectorAll('[data-slot="cascader-column"]')
        const last = columns[Math.max(0, this.#value.length - 1)] ?? columns[0]

        ;(last?.querySelector(`${ITEM}[aria-selected="true"]`) ?? last?.querySelector(`${ITEM}:not([aria-disabled="true"])`))?.focus()
    }

    close() {
        if (this.isOpen) this.content.hidePopover()
        this.trigger?.focus()
    }

    // The options of each open level: the top level, then the children along the open path.
    #levels() {
        const levels = [this.options]
        let options = this.options

        for (const value of this.#open) {
            const option = options.find(option => option.value === value)

            if (! option?.children?.length) break

            levels.push(option.children)
            options = option.children
        }

        return levels
    }

    #renderColumns() {
        const { column: columnTemplate, option: optionTemplate, 'icon-parent': parentIcon } = this.#templates

        if (! columnTemplate || ! optionTemplate) return

        const columns = this.#levels().map((options, depth) => {
            const column = clone(columnTemplate)

            for (const option of options) {
                const item = clone(optionTemplate)
                const indicator = item.querySelector('[data-slot="select-item-indicator"]')
                const parent = Boolean(option.children?.length)
                const chosen = this.#value[depth] === option.value && (parent ? false : this.#value.length === depth + 1)

                item.dataset.depth = depth
                item.dataset.value = option.value
                item.setAttribute('aria-selected', String(chosen || (parent && this.#value.length === depth + 1 && this.#value[depth] === option.value)))
                item.querySelector('[data-slot="select-item-text"]').textContent = option.label
                if (option.disabled) item.setAttribute('aria-disabled', 'true')
                if (this.#open[depth] === option.value && parent) item.setAttribute('data-expanded', '')

                if (parent) {
                    item.setAttribute('aria-haspopup', 'listbox')
                    indicator.dataset.parent = ''
                    indicator.replaceChildren(clone(parentIcon) ?? '')
                }

                column.append(item)
            }

            return column
        })

        this.content.replaceChildren(...columns)
    }

    #render() {
        if (this.trigger) link(this.trigger, this.content)

        const labels = []
        let options = this.options

        for (const value of this.#value) {
            const option = options.find(option => option.value === value)

            if (! option) break

            labels.push(option.label)
            options = option.children ?? []
        }

        const display = this.trigger?.querySelector('[data-slot="select-value"]')
        const empty = labels.length === 0

        const text = empty ? this.dataset.placeholder ?? '' : labels.join(` ${this.dataset.separator ?? '/'} `)

        if (display && display.textContent !== text) display.textContent = text
        if (this.trigger && this.trigger.hasAttribute('data-placeholder') !== empty) this.trigger.toggleAttribute('data-placeholder', empty)

        writeHidden(this, this.dataset.name, empty ? [] : this.#value)

        if (this.isOpen) this.#renderColumns()
    }

    #choose(path) {
        this.#value = path
        this.#render()
        announce(this)
    }

    #activate(item) {
        if (! item || item.getAttribute('aria-disabled') === 'true') return

        const depth = Number(item.dataset.depth)
        const path = [...this.#open.slice(0, depth), item.dataset.value]

        if (item.hasAttribute('aria-haspopup')) {
            this.#open = path
            if (this.hasAttribute('data-change-on-select')) this.#choose(path)
            this.#renderColumns()

            this.content.querySelectorAll('[data-slot="cascader-column"]')[depth + 1]?.querySelector(`${ITEM}:not([aria-disabled="true"])`)?.focus()

            return
        }

        this.#choose(path)
        this.close()
    }

    #onClick(event) {
        this.#activate(event.target.closest?.(ITEM))
    }

    #onKey(event) {
        const item = event.target.closest?.(ITEM)

        if (! item) return

        const column = item.closest('[data-slot="cascader-column"]')
        const items = [...column.querySelectorAll(`${ITEM}:not([aria-disabled="true"])`)]
        const index = items.indexOf(item)
        const focus = target => {
            event.preventDefault()
            target?.focus()
        }

        switch (event.key) {
            case 'ArrowDown': return focus(items[(index + 1) % items.length])
            case 'ArrowUp': return focus(items.at(index - 1))
            case 'Home': return focus(items[0])
            case 'End': return focus(items.at(-1))
            case 'ArrowRight':
                if (item.hasAttribute('aria-haspopup')) {
                    event.preventDefault()
                    this.#activate(item)
                }
                return
            case 'ArrowLeft': {
                const depth = Number(item.dataset.depth)

                if (depth === 0) return

                event.preventDefault()
                this.#open = this.#open.slice(0, depth)
                this.#renderColumns()
                this.content.querySelectorAll('[data-slot="cascader-column"]')[depth - 1]?.querySelector('[data-expanded]')?.focus()

                return
            }
            case 'Enter':
            case ' ':
                event.preventDefault()
                this.#activate(item)
        }
    }
}

export function register() {
    define('eso-cascader', CascaderElement)
}
