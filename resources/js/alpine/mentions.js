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

const ITEM = '[data-slot="mentions-item"]'

let ids = 0

// What decides where the textarea's text wraps, copied onto the mirror.
const BOX = [
    'boxSizing', 'width', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
    'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth', 'borderStyle',
    'fontFamily', 'fontSize', 'fontStyle', 'fontVariant', 'fontWeight', 'letterSpacing', 'lineHeight',
    'tabSize', 'textIndent', 'textTransform', 'wordSpacing',
]

import { listen, emit, disconnected } from './interaction.js'

export default (binding = {}) => ({
    _mention: null,
    _query: null,
    _remoteResult: null,
    _observer: null,

    init() {
        this._observer = new MutationObserver(() => this._mention && this._refresh())

        const fromInput = event => event.target === this.input

        listen(this, this.$el, 'input', event => fromInput(event) && this._update())
        listen(this, this.$el, 'click', event => fromInput(event) && this._update())
        listen(this, this.$el, 'keydown', event => fromInput(event) && this._onKey(event))
        listen(this, this.$el, 'keyup', event => fromInput(event) && ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key) && this._update())
        listen(this, this.$el, 'focusout', event => ! this.$el.contains(event.relatedTarget) && this.close())

        // Choosing keeps the focus, and the caret, in the textarea.
        listen(this, this.$el, 'pointerdown', event => {
            const item = event.target.closest?.(ITEM)

            if (! item) return

            event.preventDefault()
            this._choose(item)
        })

        listen(this, this.$el, 'pointermove', event => {
            const item = event.target.closest?.(ITEM)

            if (item && this._available(item)) this._highlight(item)
        })

        // Livewire may render other items (a server search): filter them too.
        const results = this.results ?? this.list
        if (results) this._observer.observe(results, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['hidden', 'data-results-prefix', 'data-results-query'],
        })
    },

    destroy() {
        disconnected(this)
        this._observer.disconnect()
    },

    get input() {
        return this.$el.querySelector('[data-mentions-input]')
    },

    get content() {
        return this.$el.querySelector('[data-slot="mentions-content"]')
    },

    get list() {
        return this.$el.querySelector('[data-slot="mentions-list"]')
    },

    get results() {
        return this.$el.querySelector('[data-slot="mentions-results"]')
    },

    get items() {
        return [...this.$el.querySelectorAll(ITEM)]
    },

    get prefixes() {
        try {
            return JSON.parse(this.$el.dataset.prefix || '["@"]')
        } catch {
            return ['@']
        }
    },

    get isOpen() {
        return this.content?.matches(':popover-open') ?? false
    },

    close() {
        this._mention = null
        this._query = null
        this._remoteResult = null

        if (this.isOpen) this.content.hidePopover()

        this.input?.setAttribute('aria-expanded', 'false')
        this.input?.removeAttribute('aria-activedescendant')
    },

    // The mention the caret ends: a prefix at the start of a word, then no space or split.
    _find() {
        const { value, selectionStart, selectionEnd } = this.input
        const split = this.$el.dataset.split ?? ' '

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
    },

    _update() {
        const mention = this._find()

        if (! mention) return this.close()

        const query = JSON.stringify([mention.prefix, mention.query])

        this._mention = mention
        if (query !== this._query) {
            this._query = query
            const detail = { prefix: mention.prefix, query: mention.query }
            if (this.$el.hasAttribute('data-local-catalogue')) {
                emit(this.list, 'ui-mentions-query', detail)
            }
            if (this.$el.hasAttribute('data-remote-catalogue')) {
                Object.defineProperty(detail, 'livewire', { value: {
                    // Livewire invokes this after morphing the caller's Blade results.
                    interceptAction: ({ onSuccess }) => onSuccess(() => {
                        if (this._query !== query) return
                        this._remoteResult = query
                        this._refresh()
                        this._place()
                    }),
                } })
            }
            emit(this.input, 'mentions-search', detail)
        }
        this._refresh()
        this._open()
    },

    _refresh() {
        if (this.results) {
            this.results.toggleAttribute('inert', ! this._currentResults())
        }
        const available = this.items.filter(item => this._available(item))
        const current = available.find(item => item.dataset.highlighted === 'true')
        this._highlight(current ?? available[0] ?? null)
    },

    _open() {
        if (! this.isOpen) this.content?.showPopover()
        if (this.content && 'ariaControlsElements' in this.input) this.input.ariaControlsElements = [this.content]

        this.input.setAttribute('aria-expanded', 'true')
        this._place()
    },

    // Next to the prefix, below its line; above it when the list doesn't fit below.
    _place() {
        const input = this.input
        const content = this.content
        const mirror = this.$el.querySelector('[data-slot="mentions-mirror"]')

        if (! content || ! mirror) return

        const style = getComputedStyle(input)
        const [text, caret] = mirror.children

        for (const property of BOX) mirror.style[property] = style[property]
        text.textContent = input.value.slice(0, this._mention.start)

        const box = input.getBoundingClientRect()
        const lineHeight = parseFloat(style.lineHeight) || parseFloat(style.fontSize) * 1.2
        const line = box.top + parseFloat(style.borderTopWidth) + caret.offsetTop - input.scrollTop
        const left = box.left + parseFloat(style.borderLeftWidth) + caret.offsetLeft - input.scrollLeft
        const below = line + lineHeight + 4
        const top = below + content.offsetHeight > window.innerHeight - 8 ? line - content.offsetHeight - 4 : below

        content.style.setProperty('--mentions-top', `${Math.max(8, top)}px`)
        content.style.setProperty('--mentions-left', `${Math.max(8, Math.min(left, window.innerWidth - content.offsetWidth - 8))}px`)
    },

    _onKey(event) {
        if (! this.isOpen || event.isComposing) return

        const available = this.items.filter(item => this._available(item))
        const index = available.findIndex(item => item.dataset.highlighted === 'true')
        const step = { ArrowDown: 1, ArrowUp: -1 }[event.key]

        if (step) {
            event.preventDefault()
            this._highlight(available[(index + step + available.length) % available.length] ?? null)
        } else if ((event.key === 'Enter' || event.key === 'Tab') && index !== -1) {
            event.preventDefault()
            this._choose(available[index])
        } else if (event.key === 'Escape') {
            // Before a dialog around it takes the key to close.
            event.preventDefault()
            this.close()
        }
    },

    _choose(item) {
        if (! this._mention || ! this._available(item)) return

        const { prefix, start } = this._mention
        const input = this.input

        input.setRangeText(`${prefix}${item.dataset.value}${this.$el.dataset.split ?? ' '}`, start, input.selectionStart, 'end')
        this.close()
        input.focus()
        emit(input, 'input')
        emit(input, 'mentions-select', { prefix, value: item.dataset.value })
    },

    _highlight(item) {
        for (const other of this.items) {
            if (other === item) other.dataset.highlighted = 'true'
            else if (other.hasAttribute('data-highlighted')) other.removeAttribute('data-highlighted')
        }

        if (! item) return this.input?.removeAttribute('aria-activedescendant')

        item.id ||= `ui-mentions-item-${++ids}`
        this.input.setAttribute('aria-activedescendant', item.id)
        item.scrollIntoView({ block: 'nearest' })
    },

    _available(item) {
        return this._currentResults() && ! item.hidden && item.getAttribute('aria-disabled') !== 'true'
    },

    _currentResults() {
        if (this.$el.hasAttribute('data-remote-catalogue')) return this._query !== null && this._remoteResult === this._query
        if (! this.$el.hasAttribute('data-local-catalogue')) return true
        return this.results?.dataset.resultsPrefix === this._mention?.prefix
            && this.results?.dataset.resultsQuery === this._mention?.query
    },
})
