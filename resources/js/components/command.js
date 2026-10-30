/*
 * <eso-command>
 *
 * cmdk on the platform: typing filters the items (letters in order, like cmdk's fuzzy
 * match), arrows move the highlight while focus stays in the input, Enter clicks the
 * highlighted item, so its wire:click (or any click handler) runs.
 */

import { connected, define, disconnected } from '../support/element.js'

const ITEM = '[data-slot="command-item"]'

let ids = 0

export function matches(query, text) {
    let position = 0

    for (const character of query.toLowerCase().replace(/\s+/g, '')) {
        position = text.indexOf(character, position)

        if (position === -1) return false

        position++
    }

    return true
}

export class CommandElement extends HTMLElement {
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        this.input?.addEventListener('input', () => this.refresh(), { signal })
        this.input?.addEventListener('keydown', event => this.#onKey(event), { signal })
        this.addEventListener('pointermove', event => {
            const item = event.target.closest?.(ITEM)

            if (item && this.#isAvailable(item)) this.highlight(item)
        }, { signal })

        this.refresh()

        // Items added or replaced by Livewire (server-side search) are filtered and highlighted too.
        this.#observer = new MutationObserver(records => records.some(record => record.type === 'childList') && this.refresh())
        this.#observer.observe(this.list ?? this, { childList: true, subtree: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get input() {
        return this.querySelector('[data-slot="command-input"]')
    }

    get list() {
        return this.querySelector('[data-slot="command-list"]')
    }

    get items() {
        return [...this.querySelectorAll(ITEM)]
    }

    get visibleItems() {
        return this.items.filter(item => this.#isAvailable(item))
    }

    refresh() {
        const query = this.input?.value.trim() ?? ''
        const filter = this.dataset.shouldFilter !== 'false' && query !== ''

        for (const item of this.items) {
            item.id ||= `ui-command-item-${++ids}`

            const text = `${item.dataset.value ?? ''} ${item.textContent} ${item.dataset.keywords ?? ''}`.toLowerCase()

            item.hidden = filter && ! matches(query, text)
        }

        for (const group of this.querySelectorAll('[data-slot="command-group"]')) {
            group.hidden = ! [...group.querySelectorAll(ITEM)].some(item => ! item.hidden)
        }

        for (const separator of this.querySelectorAll('[data-slot="command-separator"]')) {
            separator.hidden = filter
        }

        this.toggleAttribute('data-empty', ! this.items.some(item => ! item.hidden))

        const current = this.items.find(item => item.dataset.selected === 'true')

        this.highlight(current && this.#isAvailable(current) ? current : this.visibleItems[0] ?? null)
    }

    highlight(target) {
        for (const item of this.items) {
            const selected = String(item === target)

            if (item.dataset.selected !== selected) {
                item.dataset.selected = selected
                item.setAttribute('aria-selected', selected)
            }
        }

        if (target) {
            this.input?.setAttribute('aria-activedescendant', target.id)
            target.scrollIntoView({ block: 'nearest' })
        } else {
            this.input?.removeAttribute('aria-activedescendant')
        }
    }

    #isAvailable(item) {
        return ! item.hidden && item.getAttribute('aria-disabled') !== 'true'
    }

    #onKey(event) {
        const items = this.visibleItems
        const index = items.findIndex(item => item.dataset.selected === 'true')

        const move = position => {
            event.preventDefault()

            if (items.length) this.highlight(items[Math.max(0, Math.min(position, items.length - 1))])
        }

        switch (event.key) {
            case 'ArrowDown': return move(index + 1)
            case 'ArrowUp': return move(index - 1)
            case 'Home': return move(0)
            case 'End': return move(items.length - 1)
            case 'Enter':
                event.preventDefault()
                items[index]?.click()
        }
    }
}

export function register() {
    define('eso-command', CommandElement)
}
