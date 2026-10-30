/*
 * <eso-slider>
 *
 * Wraps one native range input per thumb. .value is a number for one thumb and an
 * array for several, and input/change are re-dispatched from the slider itself, so
 * wire:model binds on it like shadcn's value on Slider. Thumbs can't cross.
 */

import { define } from '../support/element.js'

export class SliderElement extends HTMLElement {
    constructor() {
        super()

        this.addEventListener('input', event => {
            if (! this.thumbs.includes(event.target)) return

            this.#keepOrder(event.target)
            this.#paint()
            this.#announce('input')
        })

        this.addEventListener('change', event => this.thumbs.includes(event.target) && this.#announce('change'))
    }

    connectedCallback() {
        const name = this.getAttribute('name')

        if (name) this.thumbs.forEach(thumb => (thumb.name = this.thumbs.length > 1 ? `${name}[]` : name))

        this.#paint()
    }

    get thumbs() {
        return [...this.querySelectorAll('input[data-slot="slider-thumb"]')]
    }

    get value() {
        const values = this.thumbs.map(thumb => Number(thumb.value))

        return values.length === 1 ? values[0] : values
    }

    set value(value) {
        const values = Array.isArray(value) ? value : [value]

        this.thumbs.forEach((thumb, index) => values[index] !== undefined && values[index] !== null && (thumb.value = values[index]))
        this.#paint()
    }

    #keepOrder(moved) {
        const thumbs = this.thumbs
        const index = thumbs.indexOf(moved)
        const previous = thumbs[index - 1]
        const next = thumbs[index + 1]

        if (previous && Number(moved.value) < Number(previous.value)) moved.value = previous.value
        if (next && Number(moved.value) > Number(next.value)) moved.value = next.value
    }

    #paint() {
        const range = this.querySelector('[data-slot="slider-range"]')
        const thumbs = this.thumbs

        if (! range || ! thumbs.length) return

        const percent = thumb => {
            const min = Number(thumb.min || 0)
            const max = Number(thumb.max || 100)

            return max === min ? 0 : ((Number(thumb.value) - min) / (max - min)) * 100
        }

        const values = thumbs.map(percent)

        range.style.setProperty('--start', values.length > 1 ? Math.min(...values) : 0)
        range.style.setProperty('--end', Math.max(...values))
    }

    #announce(type) {
        this.dispatchEvent(new Event(type, { bubbles: true }))
    }
}

export function register() {
    define('eso-slider', SliderElement)
}
