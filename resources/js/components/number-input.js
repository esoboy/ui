/*
 * <eso-number-input>
 *
 * − and + (held, they repeat) and the arrow keys step by data-step within data-min and
 * data-max; typed text is read on change. .value is the number, or null when empty;
 * input/change come from the element, so wire:model binds on it.
 */

import { announce, connected, define, disconnected } from '../support/element.js'

export class NumberInputElement extends HTMLElement {
    #value = null
    #repeat = 0

    connectedCallback() {
        const signal = connected(this)

        this.#value = this.#parse(this.input?.value)

        // Listeners sit on the element: a morph may replace the input inside it.
        const fromInput = event => event.target === this.input

        this.addEventListener('input', event => fromInput(event) && event.stopPropagation(), { signal })
        this.addEventListener('change', event => {
            if (! fromInput(event)) return

            event.stopPropagation()
            this.#commit(this.#parse(this.input.value))
        }, { signal })
        this.addEventListener('keydown', event => {
            if (! fromInput(event)) return

            const steps = { ArrowUp: 1, ArrowDown: -1, PageUp: 10, PageDown: -10 }[event.key]

            if (steps) {
                event.preventDefault()
                this.#stepBy(steps)
            } else if (event.key === 'Home' && this.dataset.min !== undefined) {
                event.preventDefault()
                this.#commit(Number(this.dataset.min))
            } else if (event.key === 'End' && this.dataset.max !== undefined) {
                event.preventDefault()
                this.#commit(Number(this.dataset.max))
            }
        }, { signal })

        this.addEventListener('pointerdown', event => {
            const button = event.target.closest?.('[data-number-input]')

            if (! button || button.disabled || event.button !== 0) return

            event.preventDefault()

            const direction = button.dataset.numberInput === 'increment' ? 1 : -1

            this.#stepBy(direction)

            // Held, it repeats: after a pause, then quickly.
            clearTimeout(this.#repeat)
            const again = delay => (this.#repeat = setTimeout(() => {
                this.#stepBy(direction)
                again(50)
            }, delay))

            again(400)
        }, { signal })

        for (const type of ['pointerup', 'pointerleave', 'pointercancel']) {
            this.addEventListener(type, () => clearTimeout(this.#repeat), { signal })
        }
    }

    disconnectedCallback() {
        disconnected(this)
        clearTimeout(this.#repeat)
    }

    get input() {
        return this.querySelector('[data-slot="input-group-control"]')
    }

    get value() {
        return this.#value
    }

    set value(value) {
        this.#value = this.#parse(value)
        this.#render()
    }

    #parse(value) {
        if (value === null || value === undefined || String(value).trim() === '') return null

        const number = Number(String(value).replace(',', '.'))

        return Number.isFinite(number) ? this.#clamp(number) : this.#value
    }

    #clamp(number) {
        const min = this.dataset.min !== undefined ? Number(this.dataset.min) : -Infinity
        const max = this.dataset.max !== undefined ? Number(this.dataset.max) : Infinity

        return Math.min(max, Math.max(min, number))
    }

    // Steps keep the step's decimals, so 0.1 + 0.2 shows 0.3.
    #stepBy(steps) {
        const step = Number(this.dataset.step) || 1
        const decimals = (String(step).split('.')[1] ?? '').length
        const start = this.#value ?? (this.dataset.min !== undefined ? Number(this.dataset.min) : 0)
        const next = this.#value === null ? start : start + steps * step

        this.#commit(Number(this.#clamp(next).toFixed(decimals)))
    }

    #commit(value) {
        const changed = value !== this.#value

        this.#value = value
        this.#render()

        if (changed) {
            announce(this)
        }
    }

    #render() {
        const input = this.input
        const text = this.#value === null ? '' : String(this.#value)

        if (input && input.value !== text) input.value = text
        if (input) this.#value === null ? input.removeAttribute('aria-valuenow') : input.setAttribute('aria-valuenow', text)

        const min = this.dataset.min !== undefined ? Number(this.dataset.min) : -Infinity
        const max = this.dataset.max !== undefined ? Number(this.dataset.max) : Infinity
        const disabled = input?.disabled ?? false

        for (const button of this.querySelectorAll('[data-number-input]')) {
            button.disabled = disabled || (this.#value !== null && (button.dataset.numberInput === 'increment' ? this.#value >= max : this.#value <= min))
        }
    }
}

export function register() {
    define('eso-number-input', NumberInputElement)
}
