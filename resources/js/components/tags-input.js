/*
 * <eso-tags-input>
 *
 * Typed text becomes a tag on Enter, a comma or leaving the field (pasted lists split
 * on commas and new lines); Backspace in the empty input removes the last tag;
 * duplicates and tags past data-max are ignored. .value is the list; input/change
 * come from the element, so wire:model binds on it.
 */

import { announce, connected, define, disconnected, readValue, writeHidden } from '../support/element.js'

const SEPARATORS = /[,，\n]/

export class TagsInputElement extends HTMLElement {
    #tags = []
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        const value = readValue(this, [])

        this.#tags = Array.isArray(value) ? value.map(String) : []

        // Listeners sit on the element: a morph may replace the input inside it.
        const fromInput = event => event.target === this.input

        this.addEventListener('keydown', event => {
            const input = this.input

            if (! fromInput(event) || event.isComposing) return

            if (event.key === 'Enter' || event.key === ',') {
                if (input.value.trim() === '' && event.key === 'Enter') return

                event.preventDefault()
                this.#add(input.value)
            } else if (event.key === 'Backspace' && input.value === '' && this.#tags.length) {
                this.#set(this.#tags.slice(0, -1))
            }
        }, { signal })

        this.addEventListener('input', event => {
            if (! fromInput(event)) return

            event.stopPropagation()
            if (SEPARATORS.test(this.input.value)) this.#add(this.input.value)
        }, { signal })

        this.addEventListener('focusout', event => fromInput(event) && this.input.value.trim() && this.#add(this.input.value), { signal })

        this.addEventListener('click', event => {
            const remove = event.target.closest?.('[data-tags-input-remove]')

            if (remove) {
                const tag = remove.closest('[data-slot="tags-input-tag"]')
                const index = [...this.querySelectorAll(':scope > [data-slot="tags-input-tag"]')].indexOf(tag)

                this.#set(this.#tags.filter((_, position) => position !== index))
                this.input?.focus()
            } else if (event.target === this) {
                this.input?.focus()
            }
        }, { signal })

        this.#render()

        // Livewire morphs put back the server's tags: show ours.
        this.#observer.observe(this, { childList: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get input() {
        return this.querySelector('[data-slot="tags-input-input"]')
    }

    get value() {
        return [...this.#tags]
    }

    set value(value) {
        this.#tags = Array.isArray(value) ? value.map(String) : []
        this.#render()
    }

    #add(text) {
        const max = Number(this.dataset.max) || Infinity
        const tags = [...this.#tags]

        for (const part of text.split(SEPARATORS).map(part => part.trim()).filter(Boolean)) {
            if (! tags.includes(part) && tags.length < max) tags.push(part)
        }

        if (this.input) this.input.value = ''
        if (tags.length !== this.#tags.length) this.#set(tags)
    }

    #set(tags) {
        this.#tags = tags
        this.#render()
        announce(this)
    }

    #render() {
        const template = this.querySelector(':scope > template[data-tags-input-template]')
        const current = [...this.querySelectorAll(':scope > [data-slot="tags-input-tag"]')]
        const same = current.length === this.#tags.length
            && current.every((tag, index) => tag.querySelector('[data-tags-input-remove]') && tag.querySelector('[data-tags-input-label]')?.textContent === this.#tags[index])

        if (! same && template) {
            this.#observer.disconnect()

            current.forEach(tag => tag.remove())

            for (const text of this.#tags) {
                const tag = template.content.firstElementChild.cloneNode(true)

                tag.querySelector('[data-tags-input-label]').textContent = text
                this.insertBefore(tag, this.input)
            }

            writeHidden(this, this.dataset.name, this.#tags)
            this.#observer.observe(this, { childList: true })
        }

        const full = this.#tags.length >= (Number(this.dataset.max) || Infinity)

        if (this.input) this.input.disabled = this.hasAttribute('data-disabled') || full
    }
}

export function register() {
    define('eso-tags-input', TagsInputElement)
}
