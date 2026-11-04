/*
 * <eso-mentions>
 *
 * A prefix (@) at the start of a word opens the items whose text or value matches what
 * follows it, next to the prefix; arrows move through them, Enter or Tab inserts
 * "@value " (prefix, value, split), Escape closes. The textarea stays the control: its
 * input event carries the new text to wire:model. From the textarea, mentions-search
 * reports { prefix, query } as the query changes, for a server that loads matching
 * items itself, and mentions-select { prefix, value } a chosen item.
 */

import { connected, define, disconnected } from '../support/element.js'
import { matches } from './command.js'

const ITEM = '[data-slot="mentions-item"]'

let ids = 0

// What decides where the textarea's text wraps, copied onto the mirror.
const BOX = [
    'boxSizing', 'width', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
    'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth', 'borderStyle',
    'fontFamily', 'fontSize', 'fontStyle', 'fontVariant', 'fontWeight', 'letterSpacing', 'lineHeight',
    'tabSize', 'textIndent', 'textTransform', 'wordSpacing',
]

export class MentionsElement extends HTMLElement {
    #mention = null
    #query = null
    #observer = new MutationObserver(() => this.#mention && this.#filter())

    connectedCallback() {
        const signal = connected(this)

        const fromInput = event => event.target === this.input

        this.addEventListener('input', event => fromInput(event) && this.#update(), { signal })
        this.addEventListener('click', event => fromInput(event) && this.#update(), { signal })
        this.addEventListener('keydown', event => fromInput(event) && this.#onKey(event), { signal })
        this.addEventListener('keyup', event => fromInput(event) && ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key) && this.#update(), { signal })
        this.addEventListener('focusout', event => ! this.contains(event.relatedTarget) && this.close(), { signal })

        // Choosing keeps the focus, and the caret, in the textarea.
        this.addEventListener('pointerdown', event => {
            const item = event.target.closest?.(ITEM)

            if (! item) return

            event.preventDefault()
            this.#choose(item)
        }, { signal })

        this.addEventListener('pointermove', event => {
            const item = event.target.closest?.(ITEM)

            if (item && this.#available(item)) this.#highlight(item)
        }, { signal })

        // Livewire may render other items (a server search): filter them too.
        if (this.list) this.#observer.observe(this.list, { childList: true, subtree: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get input() {
        return this.querySelector('[data-mentions-input]')
    }

    get content() {
        return this.querySelector('[data-slot="mentions-content"]')
    }

    get list() {
        return this.querySelector('[data-slot="mentions-list"]')
    }

    get items() {
        return [...this.querySelectorAll(ITEM)]
    }

    get prefixes() {
        try {
            return JSON.parse(this.dataset.prefix || '["@"]')
        } catch {
            return ['@']
        }
    }

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    }

    close() {
        this.#mention = null
        this.#query = null

        if (this.isOpen) this.content.hidePopover()

        this.input?.setAttribute('aria-expanded', 'false')
        this.input?.removeAttribute('aria-activedescendant')
    }

    // The mention the caret ends: a prefix at the start of a word, then no space or split.
    #find() {
        const { value, selectionStart, selectionEnd } = this.input
        const split = this.dataset.split ?? ' '

        if (selectionStart !== selectionEnd) return null

        const before = value.slice(0, selectionStart)
        let found = null

        for (const prefix of this.prefixes) {
            const start = before.lastIndexOf(prefix)
            const query = before.slice(start + prefix.length)

            if (start === -1 || (start > 0 && ! /\s/.test(before[start - 1])) || /\s/.test(query) || (split && query.includes(split))) continue
            if (! found || start > found.start) found = { prefix, start, query }
        }

        return found
    }

    #update() {
        const mention = this.#find()

        if (! mention) return this.close()

        const query = `${mention.prefix}${mention.query}`

        this.#mention = mention
        this.#filter()
        this.#open()

        if (query !== this.#query) {
            this.#query = query
            this.input.dispatchEvent(new CustomEvent('mentions-search', { bubbles: true, detail: { prefix: mention.prefix, query: mention.query } }))
        }
    }

    #filter() {
        const { prefix, query } = this.#mention
        const items = this.items

        for (const item of items) {
            // An item without a prefix belongs to the first one.
            const ours = (item.dataset.prefix ?? this.prefixes[0]) === prefix
            const hidden = ! ours || (query !== '' && ! matches(query, `${item.textContent} ${item.dataset.value}`.toLowerCase()))

            if (item.hidden !== hidden) item.hidden = hidden
        }

        const available = items.filter(item => this.#available(item))
        const current = items.find(item => item.dataset.highlighted === 'true')

        this.content?.toggleAttribute('data-empty', items.every(item => item.hidden))
        this.#highlight(current && available.includes(current) ? current : available[0] ?? null)
    }

    #open() {
        if (! this.isOpen) this.content?.showPopover()
        if (this.content && 'ariaControlsElements' in this.input) this.input.ariaControlsElements = [this.content]

        this.input.setAttribute('aria-expanded', 'true')
        this.#place()
    }

    // Next to the prefix, below its line; above it when the list doesn't fit below.
    #place() {
        const input = this.input
        const content = this.content
        const mirror = this.querySelector('[data-slot="mentions-mirror"]')

        if (! content || ! mirror) return

        const style = getComputedStyle(input)
        const [text, caret] = mirror.children

        for (const property of BOX) mirror.style[property] = style[property]
        text.textContent = input.value.slice(0, this.#mention.start)

        const box = input.getBoundingClientRect()
        const lineHeight = parseFloat(style.lineHeight) || parseFloat(style.fontSize) * 1.2
        const line = box.top + parseFloat(style.borderTopWidth) + caret.offsetTop - input.scrollTop
        const left = box.left + parseFloat(style.borderLeftWidth) + caret.offsetLeft - input.scrollLeft
        const below = line + lineHeight + 4
        const top = below + content.offsetHeight > window.innerHeight - 8 ? line - content.offsetHeight - 4 : below

        content.style.setProperty('--mentions-top', `${Math.max(8, top)}px`)
        content.style.setProperty('--mentions-left', `${Math.max(8, Math.min(left, window.innerWidth - content.offsetWidth - 8))}px`)
    }

    #onKey(event) {
        if (! this.isOpen || event.isComposing) return

        const available = this.items.filter(item => this.#available(item))
        const index = available.findIndex(item => item.dataset.highlighted === 'true')
        const step = { ArrowDown: 1, ArrowUp: -1 }[event.key]

        if (step) {
            event.preventDefault()
            this.#highlight(available[(index + step + available.length) % available.length] ?? null)
        } else if ((event.key === 'Enter' || event.key === 'Tab') && index !== -1) {
            event.preventDefault()
            this.#choose(available[index])
        } else if (event.key === 'Escape') {
            // Before a dialog around it takes the key to close.
            event.preventDefault()
            this.close()
        }
    }

    #choose(item) {
        if (! this.#mention || ! this.#available(item)) return

        const { prefix, start } = this.#mention
        const input = this.input

        input.setRangeText(`${prefix}${item.dataset.value}${this.dataset.split ?? ' '}`, start, input.selectionStart, 'end')
        this.close()
        input.focus()
        input.dispatchEvent(new Event('input', { bubbles: true }))
        input.dispatchEvent(new CustomEvent('mentions-select', { bubbles: true, detail: { prefix, value: item.dataset.value } }))
    }

    #highlight(item) {
        for (const other of this.items) {
            if (other === item) other.dataset.highlighted = 'true'
            else if (other.hasAttribute('data-highlighted')) other.removeAttribute('data-highlighted')
        }

        if (! item) return this.input?.removeAttribute('aria-activedescendant')

        item.id ||= `ui-mentions-item-${++ids}`
        this.input.setAttribute('aria-activedescendant', item.id)
        item.scrollIntoView({ block: 'nearest' })
    }

    #available(item) {
        return ! item.hidden && item.getAttribute('aria-disabled') !== 'true'
    }
}

export function register() {
    define('eso-mentions', MentionsElement)
}
