/*
 * <eso-slider>
 *
 * Wraps one native range input per thumb. .value is a number for one thumb and an
 * array for several, and input/change are re-dispatched from the slider itself, so
 * wire:model binds on it like shadcn's value on Slider. Thumbs can't cross. The
 * inputs only take presses on their thumbs, so a press on the track moves the
 * nearest thumb there and drags it, as Base UI's Slider does.
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

        this.addEventListener('pointerdown', event => this.#press(event))
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

    #press(event) {
        const control = this.querySelector('[data-slot="slider-control"]') ?? this
        const thumbs = this.thumbs.filter(thumb => ! thumb.disabled)

        if (event.button !== 0 || ! thumbs.length || this.thumbs.includes(event.target)) return

        const vertical = thumbs[0].getAttribute('aria-orientation') === 'vertical'

        const valueAt = ({ clientX, clientY }) => {
            const rect = control.getBoundingClientRect()
            const ratio = Math.min(1, Math.max(0, vertical ? (rect.bottom - clientY) / rect.height : (clientX - rect.left) / rect.width))
            const min = Number(thumbs[0].min || 0)
            const max = Number(thumbs[0].max || 100)

            return min + ratio * (max - min)
        }

        // The nearest thumb; of thumbs on the same value, the one on the side pressed.
        const target = valueAt(event)
        const thumb = thumbs.reduce((nearest, candidate) => {
            const distance = Math.abs(Number(candidate.value) - target) - Math.abs(Number(nearest.value) - target)

            return distance < 0 || (distance === 0 && target > Number(candidate.value)) ? candidate : nearest
        })

        // The range input snaps what it is given to its step and bounds.
        const move = pointer => {
            const before = thumb.value

            thumb.value = String(valueAt(pointer))

            if (thumb.value !== before) thumb.dispatchEvent(new Event('input', { bubbles: true }))
        }

        const start = thumb.value

        event.preventDefault()
        thumb.focus()
        move(event)
        control.setPointerCapture?.(event.pointerId)

        const drag = new AbortController()
        const end = () => {
            drag.abort()

            if (thumb.value !== start) thumb.dispatchEvent(new Event('change', { bubbles: true }))
        }

        control.addEventListener('pointermove', move, { signal: drag.signal })
        control.addEventListener('pointerup', end, { signal: drag.signal })
        control.addEventListener('pointercancel', end, { signal: drag.signal })
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
