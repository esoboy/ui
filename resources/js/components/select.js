/*
 * <eso-select>
 *
 * shadcn's Select on native pieces: the list is a popover, items are focusable
 * options. .value is the chosen item's value and input/change are dispatched from the
 * select itself, so wire:model binds on it. By default the list opens over the trigger
 * with the chosen item lined up on it, as Base UI's alignItemWithTrigger does.
 */

import { announce, connected, define, disconnected, writeHidden } from '../support/element.js'
import { link } from '../support/overlay.js'

const ITEM = '[data-slot="select-item"]'

export class SelectElement extends HTMLElement {
    #value = null
    #typed = ''
    #typedAt = 0
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        this.#value = this.dataset.value ?? null

        this.trigger?.addEventListener('keydown', event => this.#onTriggerKey(event), { signal })
        this.content?.addEventListener('keydown', event => this.#onListKey(event), { signal })
        this.content?.addEventListener('click', event => this.#onItemClick(event), { signal })
        this.content?.addEventListener('pointermove', event => event.target.closest?.(ITEM)?.focus({ preventScroll: true }), { signal })
        this.content?.addEventListener('toggle', event => this.#onToggle(event), { signal })

        this.#sync()

        // Livewire morphs reset the trigger text and item states to the server's: restore them.
        this.#observer = new MutationObserver(() => this.#sync())
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['aria-selected', 'aria-invalid', 'aria-describedby', 'data-placeholder'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get trigger() {
        return this.querySelector('[data-slot="select-trigger"]')
    }

    get content() {
        return this.querySelector('[data-slot="select-content"]')
    }

    get items() {
        return [...this.querySelectorAll(ITEM)]
    }

    get enabledItems() {
        return this.items.filter(item => item.getAttribute('aria-disabled') !== 'true')
    }

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    }

    get value() {
        return this.#value
    }

    set value(value) {
        this.#value = value === null || value === undefined ? null : String(value)
        this.#sync()
    }

    open() {
        if (! this.content || this.trigger?.disabled) return

        this.content.showPopover({ source: this.trigger })
    }

    close() {
        if (this.isOpen) this.content.hidePopover()
    }

    choose(item) {
        if (! item || item.getAttribute('aria-disabled') === 'true') return

        const changed = item.dataset.value !== this.#value

        this.#value = item.dataset.value
        this.#sync()
        this.close()
        this.trigger?.focus()

        if (changed) {
            announce(this)
        }
    }

    get #selected() {
        return this.items.find(item => item.dataset.value === this.#value) ?? null
    }

    #sync() {
        const selected = this.#selected

        if (this.trigger) link(this.trigger, this.content)

        for (const item of this.items) {
            const state = String(item === selected)

            if (item.getAttribute('aria-selected') !== state) item.setAttribute('aria-selected', state)
        }

        const display = this.querySelector('[data-slot="select-value"]')
        const html = selected ? selected.querySelector('[data-slot="select-item-text"]')?.innerHTML ?? '' : display?.dataset.placeholder ?? ''

        if (display && display.innerHTML !== html) display.innerHTML = html

        const trigger = this.trigger

        if (trigger) {
            trigger.toggleAttribute('data-placeholder', ! selected)

            // The select carries the field's validation state; screen readers need it on the trigger.
            for (const name of ['aria-invalid', 'aria-describedby']) {
                const value = this.getAttribute(name)

                if (value === null) trigger.removeAttribute(name)
                else if (trigger.getAttribute(name) !== value) trigger.setAttribute(name, value)
            }
        }

        writeHidden(this, this.dataset.name, this.#value)
    }

    #onToggle({ newState }) {
        const open = newState === 'open'

        this.trigger?.setAttribute('aria-expanded', String(open))

        if (! open) return

        if (this.content.dataset.alignTrigger === 'true') this.#alignWithTrigger()

        ;(this.#selected ?? this.enabledItems[0])?.focus({ preventScroll: true })
    }

    // Base UI's alignItemWithTrigger: the list covers the trigger, the chosen item's text
    // sits exactly where the trigger shows it, then it is kept inside the viewport.
    #alignWithTrigger() {
        const content = this.content
        const trigger = this.trigger.getBoundingClientRect()
        const item = this.#selected ?? this.enabledItems[0]
        const value = this.querySelector('[data-slot="select-value"]')?.getBoundingClientRect() ?? trigger

        content.style.width = `${trigger.width}px`

        let left = trigger.left + 1
        let top = trigger.top

        if (item) {
            const box = content.getBoundingClientRect()
            const itemBox = item.getBoundingClientRect()
            const text = item.querySelector('[data-slot="select-item-text"]')?.getBoundingClientRect() ?? itemBox

            left = value.left - (text.left - box.left)
            top = trigger.top + trigger.height / 2 - (itemBox.top - box.top + content.scrollTop + itemBox.height / 2)
        }

        const margin = 10
        const height = content.offsetHeight

        top = Math.min(Math.max(top, margin), window.innerHeight - height - margin)

        Object.assign(content.style, { positionArea: 'none', inset: 'auto', margin: '0', top: `${top}px`, left: `${left}px` })
    }

    #onTriggerKey(event) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
            event.preventDefault()
            this.open()
        }
    }

    #onListKey(event) {
        const items = this.enabledItems
        const index = items.indexOf(event.target)

        const focus = position => {
            event.preventDefault()
            items.at(position)?.focus()
        }

        switch (event.key) {
            case 'ArrowDown': return focus(Math.min(index + 1, items.length - 1))
            case 'ArrowUp': return focus(Math.max(index - 1, 0))
            case 'Home': return focus(0)
            case 'End': return focus(-1)
            case 'Enter':
            case ' ':
                event.preventDefault()
                return this.choose(event.target.closest(ITEM))
            case 'Tab':
                return this.close()
        }

        // Typeahead: jump to the first item starting with what was typed.
        if (event.key.length === 1 && ! event.ctrlKey && ! event.metaKey && ! event.altKey) {
            const now = Date.now()

            this.#typed = (now - this.#typedAt > 500 ? '' : this.#typed) + event.key.toLowerCase()
            this.#typedAt = now

            items.find(item => item.textContent.trim().toLowerCase().startsWith(this.#typed))?.focus()
        }
    }

    #onItemClick(event) {
        this.choose(event.target.closest(ITEM))
    }
}

export function register() {
    define('eso-select', SelectElement)
}
