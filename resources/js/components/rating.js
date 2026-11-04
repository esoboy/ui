/*
 * <eso-rating>
 *
 * A radio group of stars: a click picks a star, and the chosen one again clears it;
 * the arrows, Home and End move the choice. .value is the number of stars or null;
 * input/change are dispatched from the rating, so wire:model binds on it.
 */

import { announce, connected, define, disconnected, writeHidden } from '../support/element.js'

export class RatingElement extends HTMLElement {
    #value = null
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        this.#value = this.#parse(this.dataset.value)

        this.addEventListener('click', event => {
            const item = event.target.closest?.('[data-slot="rating-item"]')

            if (! item || item.disabled) return

            const value = Number(item.dataset.value)

            this.#commit(value === this.#value ? null : value)
        }, { signal })

        this.addEventListener('keydown', event => {
            const items = this.#items()
            const rtl = getComputedStyle(this).direction === 'rtl'
            const step = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1, ArrowUp: 1, ArrowDown: -1 }[event.key]
            const current = this.#value ?? 0
            const value = step ? current + step : event.key === 'Home' ? 1 : event.key === 'End' ? items.length : null

            if (value === null || ! event.target.closest?.('[data-slot="rating-item"]') || items[0]?.disabled) return

            event.preventDefault()

            const next = Math.min(items.length, Math.max(1, value))

            this.#commit(next)
            items[next - 1].focus()
        }, { signal })

        this.#render()

        // Livewire morphs put back the server's stars: show ours.
        this.#observer.observe(this, { subtree: true, attributes: true, attributeFilter: ['aria-checked', 'style', 'tabindex'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get value() {
        return this.#value
    }

    set value(value) {
        this.#value = this.#parse(value)
        this.#render()
    }

    #parse(value) {
        const number = Number(value)

        return value === null || value === undefined || value === '' || ! Number.isFinite(number) ? null : number
    }

    #items() {
        return [...this.querySelectorAll('[data-slot="rating-item"]')]
    }

    #commit(value) {
        if (value === this.#value) return

        this.#value = value
        this.#render()
        announce(this)
    }

    #render() {
        const set = (element, name, value) => element.getAttribute(name) !== value && element.setAttribute(name, value)
        const items = this.#items()
        const focusable = Math.min(items.length, Math.max(1, Math.round(this.#value ?? 1)))

        items.forEach((item, index) => {
            const star = index + 1
            const fill = this.#value !== null && star <= this.#value ? '100%' : '0%'

            if (item.style.getPropertyValue('--rating-fill') !== fill) item.style.setProperty('--rating-fill', fill)
            set(item, 'aria-checked', String(this.#value !== null && Math.round(this.#value) === star))
            set(item, 'tabindex', star === focusable ? '0' : '-1')
        })

        writeHidden(this, this.dataset.name, this.#value)
    }
}

export function register() {
    define('eso-rating', RatingElement)
}
