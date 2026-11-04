/*
 * <eso-combobox>
 *
 * Base UI's Combobox on the platform: type to filter, arrows move the highlight while
 * focus stays in the input, Enter or a click chooses. .value is the chosen value, or an
 * array of values with data-multiple (shown as chips), and input/change are dispatched
 * from the combobox itself, so wire:model binds on it.
 *
 * With data-autocomplete (Base UI's Autocomplete) .value is the input's text: typing
 * dispatches input, and change follows once it is settled (a suggestion chosen, Enter,
 * leaving the input). The items only suggest; the popover shows when some match, also
 * when a morph brings new ones from the server while typing.
 */

import { announce, connected, define, disconnected, readValue, writeHidden } from '../support/element.js'
import { matches } from './command.js'

const ITEM = '[data-slot="combobox-item"]'

let ids = 0

export class ComboboxElement extends HTMLElement {
    #value = null
    #committed = null
    #observer = null
    #onOutside = event => ! this.contains(event.target) && ! this.content?.contains(event.target) && this.close()

    connectedCallback() {
        const signal = connected(this)

        const initial = readValue(this)

        this.#value = this.multiple ? (Array.isArray(initial) ? initial.map(String) : []) : this.autocomplete ? String(initial ?? '') : initial === null ? null : String(initial)
        this.#committed = this.#value

        const input = this.input

        input?.addEventListener('input', () => {
            if (this.autocomplete) return this.#type(input.value)

            this.open()
            this.filter(input.value)
        }, { signal })
        input?.addEventListener('click', () => this.open(), { signal })
        input?.addEventListener('keydown', event => this.#onKey(event), { signal })
        input?.addEventListener('blur', () => queueMicrotask(() => ! this.contains(document.activeElement) && (this.autocomplete ? this.#change() : this.#restoreText())), { signal })

        this.addEventListener('click', event => {
            if (event.target.closest('[data-combobox-trigger]')) return this.isOpen ? this.close() : (this.open(), input?.focus())
            if (event.target.closest('[data-combobox-clear]')) return this.#commit(this.multiple ? [] : this.autocomplete ? '' : null), input?.focus()

            const remove = event.target.closest('[data-combobox-chip-remove]')

            if (remove) this.#commit(this.#value.filter(value => value !== remove.closest('[data-slot="combobox-chip"]').dataset.value))
        }, { signal })

        this.content?.addEventListener('click', event => this.choose(event.target.closest(ITEM)), { signal })
        this.content?.addEventListener('mousedown', event => event.target !== this.input && event.preventDefault(), { signal })
        this.content?.addEventListener('pointermove', event => {
            const item = event.target.closest?.(ITEM)

            if (item && this.#isAvailable(item)) this.highlight(item)
        }, { signal })

        this.#sync()
        this.#restoreText()

        // Livewire morphs reset chips, item states and validation attributes: restore them.
        this.#observer = new MutationObserver(records => {
            if (! records.some(record => record.type === 'childList' || record.target === this)) return

            this.#sync()

            if (this.autocomplete && this.ownerDocument.activeElement === this.input) this.#suggest()
        })
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['aria-invalid', 'aria-describedby'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
        document.removeEventListener('pointerdown', this.#onOutside)
    }

    get multiple() {
        return this.hasAttribute('data-multiple')
    }

    get autocomplete() {
        return this.hasAttribute('data-autocomplete')
    }

    get input() {
        return this.querySelector('[data-combobox-input]')
    }

    get content() {
        return this.querySelector('[data-slot="combobox-content"]')
    }

    get items() {
        return [...this.querySelectorAll(ITEM)]
    }

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    }

    get value() {
        return this.multiple ? [...this.#value] : this.#value
    }

    set value(value) {
        this.#value = this.multiple
            ? (Array.isArray(value) ? value.map(String) : [])
            : this.autocomplete ? String(value ?? '') : value === null || value === undefined || value === '' ? null : String(value)

        if (this.autocomplete) this.#committed = this.#value

        this.#sync()

        if (this.ownerDocument.activeElement !== this.input) this.#restoreText()
    }

    open() {
        if (this.isOpen || ! this.content || this.input?.disabled) return

        const query = this.multiple || (! this.autocomplete && this.#isShowingSelection()) ? '' : this.input?.value ?? ''

        // An autocomplete has nothing to show without a suggestion.
        if (this.autocomplete && (this.filter(query), ! this.#hasSuggestions())) return

        this.content.showPopover({ source: this.querySelector('[data-combobox-anchor]') ?? this.querySelector('[data-combobox-trigger]') ?? this.input })
        this.input?.setAttribute('aria-expanded', 'true')
        if (this.input && 'ariaControlsElements' in this.input) this.input.ariaControlsElements = [this.content]
        this.querySelector('[data-combobox-trigger]')?.setAttribute('aria-expanded', 'true')
        document.addEventListener('pointerdown', this.#onOutside)

        this.filter(query)
    }

    close() {
        if (! this.isOpen) return

        const focused = this.content.contains(document.activeElement)

        this.content.hidePopover()
        this.input?.setAttribute('aria-expanded', 'false')

        // Searching inside the list, focus goes back to the trigger.
        if (focused && this.#searching) this.querySelector('[data-combobox-trigger]')?.focus()
        this.querySelector('[data-combobox-trigger]')?.setAttribute('aria-expanded', 'false')
        document.removeEventListener('pointerdown', this.#onOutside)

        this.#restoreText()
    }

    filter(query) {
        const filtering = this.dataset.shouldFilter !== 'false' && query.trim() !== ''

        for (const item of this.items) {
            item.id ||= `ui-combobox-item-${++ids}`
            item.hidden = filtering && ! matches(query.trim(), item.textContent.toLowerCase())
        }

        for (const group of this.querySelectorAll('[data-slot="combobox-group"]')) {
            group.hidden = ! [...group.querySelectorAll(ITEM)].some(item => ! item.hidden)
        }

        this.content?.toggleAttribute('data-empty', ! this.items.some(item => ! item.hidden))

        // Like Base UI (autoHighlight off): only the chosen item starts highlighted, arrows do the rest.
        this.highlight(filtering ? null : this.items.find(item => this.#isAvailable(item) && this.#isChosen(item)) ?? null)
    }

    highlight(target) {
        for (const item of this.items) {
            const highlighted = String(item === target)

            if (item.dataset.highlighted !== highlighted) item.dataset.highlighted = highlighted
        }

        if (target) {
            this.input?.setAttribute('aria-activedescendant', target.id)
            target.scrollIntoView({ block: 'nearest' })
        } else {
            this.input?.removeAttribute('aria-activedescendant')
        }
    }

    choose(item) {
        if (! item || ! this.#isAvailable(item)) return

        const value = item.dataset.value

        if (this.multiple) {
            this.#commit(this.#value.includes(value) ? this.#value.filter(chosen => chosen !== value) : [...this.#value, value])

            if (this.input) this.input.value = ''

            this.filter('')
            this.highlight(item)
        } else {
            this.#commit(value)
            this.close()
        }
    }

    #commit(value) {
        const before = JSON.stringify(this.#value)

        this.#value = value
        this.#sync()

        if (! this.multiple) this.#restoreText()

        if (this.autocomplete) {
            if (JSON.stringify(this.#value) !== before) this.dispatchEvent(new Event('input', { bubbles: true }))

            return this.#change()
        }

        if (JSON.stringify(this.#value) !== before) {
            announce(this)
        }
    }

    // Autocomplete: the typed text is the value; the suggestions follow it.
    #type(text) {
        this.#value = text
        this.#sync()
        this.dispatchEvent(new Event('input', { bubbles: true }))
        this.#suggest()
    }

    #suggest() {
        if (! this.isOpen) return this.open()

        this.filter(this.input?.value ?? '')

        if (! this.#hasSuggestions()) this.close()
    }

    #change() {
        if (this.#value === this.#committed) return

        this.#committed = this.#value
        this.dispatchEvent(new Event('change', { bubbles: true }))
    }

    #hasSuggestions() {
        return this.items.some(item => this.#isAvailable(item))
    }

    #isChosen(item) {
        return this.multiple ? this.#value.includes(item.dataset.value) : item.dataset.value === this.#value
    }

    #isAvailable(item) {
        return ! item.hidden && item.getAttribute('aria-disabled') !== 'true'
    }

    // An item's text, without its check indicator.
    #label(value) {
        const item = this.items.find(item => item.dataset.value === value)

        if (! item) return value

        const copy = item.cloneNode(true)

        copy.querySelector('[data-slot="combobox-item-indicator"]')?.remove()

        return copy.textContent.trim()
    }

    #isShowingSelection() {
        return ! this.multiple && this.#value !== null && this.input?.value === this.#label(this.#value)
    }

    // With a Combobox Trigger, the input inside the list only searches.
    get #searching() {
        return Boolean(this.input && this.content?.contains(this.input))
    }

    // Single: when not typing, the input shows the chosen item's text; a search field, nothing.
    #restoreText() {
        if (this.multiple || ! this.input) return

        const text = this.#searching ? '' : this.autocomplete ? this.#value : this.#value === null ? '' : this.#label(this.#value)

        if (this.input.value !== text) this.input.value = text
    }

    #sync() {
        for (const item of this.items) {
            const chosen = String(this.#isChosen(item))

            if (item.getAttribute('aria-selected') !== chosen) item.setAttribute('aria-selected', chosen)
        }

        this.querySelectorAll('[data-combobox-clear]').forEach(clear => (clear.hidden = this.multiple ? ! this.#value.length : this.#value === null))

        const chosen = this.multiple ? this.#value.map(value => this.#label(value)).join(', ') : this.#value === null ? '' : this.#label(this.#value)

        for (const display of this.querySelectorAll('[data-slot="combobox-value"]')) {
            const text = chosen || display.dataset.placeholder || ''

            if (display.textContent !== text) display.textContent = text
        }

        this.#renderChips()
        this.#mirrorValidation()
        writeHidden(this, this.dataset.name, this.#value)
    }

    #renderChips() {
        const chips = this.querySelector('[data-slot="combobox-chips"]')
        const template = chips?.querySelector('template[data-combobox-chip]')

        if (! chips || ! template || ! this.multiple) return

        const current = [...chips.querySelectorAll(':scope > [data-slot="combobox-chip"]')]

        if (current.map(chip => chip.dataset.value).join('\u0000') === this.#value.join('\u0000')) return

        current.forEach(chip => chip.remove())

        for (const value of this.#value) {
            const chip = template.content.firstElementChild.cloneNode(true)

            chip.dataset.value = value
            chip.querySelector('[data-combobox-chip-label]').textContent = this.#label(value)
            chips.insertBefore(chip, this.input)
        }
    }

    // The combobox carries the field's validation state; the input needs it too.
    #mirrorValidation() {
        const input = this.input

        if (! input) return

        for (const name of ['aria-invalid', 'aria-describedby']) {
            const value = this.getAttribute(name)

            if (value === null) input.removeAttribute(name)
            else if (input.getAttribute(name) !== value) input.setAttribute(name, value)
        }
    }

    #onKey(event) {
        const available = this.items.filter(item => this.#isAvailable(item))
        const index = available.findIndex(item => item.dataset.highlighted === 'true')

        const move = position => {
            event.preventDefault()

            if (! this.isOpen) return this.open()
            if (available.length) this.highlight(available[Math.max(0, Math.min(position, available.length - 1))])
        }

        switch (event.key) {
            case 'ArrowDown': return move(index + 1)
            case 'ArrowUp': return move(index - 1)
            case 'Enter':
                // Autocomplete: Enter takes a highlighted suggestion, or else settles the text as typed and goes on (to submit a form).
                if (this.autocomplete && ! (this.isOpen && index >= 0)) {
                    this.close()

                    return this.#change()
                }

                if (this.isOpen) {
                    event.preventDefault()
                    this.choose(available[index])
                }
                return
            case 'Escape':
                if (this.isOpen) {
                    event.preventDefault()
                    this.close()
                }
                return
            case 'Tab':
                return this.close()
            case 'Backspace':
                if (this.multiple && this.input.value === '' && this.#value.length) this.#commit(this.#value.slice(0, -1))
        }
    }
}

export function register() {
    define('eso-combobox', ComboboxElement)
}
