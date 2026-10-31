/*
 * <eso-color-picker>
 *
 * The area picks saturation (across) and brightness (down), the slider the hue; both
 * drag and take the arrow keys. The hex input applies on change, and an empty one
 * clears the color. Swatches and the eyedropper (where the browser has one) set it
 * outright. .value is "#rrggbb" or null; input/change are dispatched from the picker,
 * so wire:model binds on it.
 */

import { connected, define, disconnected, writeHidden } from '../support/element.js'

const clamp = (number, min = 0, max = 1) => Math.min(max, Math.max(min, number))

export function parseHex(text) {
    const hex = String(text ?? '').trim().replace(/^#/, '').toLowerCase()
    const full = hex.length === 3 ? [...hex].map(digit => digit + digit).join('') : hex

    return /^[0-9a-f]{6}$/.test(full) ? `#${full}` : null
}

export function hexToHsv(hex) {
    const number = parseInt(hex.slice(1), 16)
    const [r, g, b] = [number >> 16, number >> 8, number].map(channel => (channel & 255) / 255)
    const max = Math.max(r, g, b)
    const delta = max - Math.min(r, g, b)
    const hue = ! delta ? 0 : max === r ? ((g - b) / delta) % 6 : max === g ? (b - r) / delta + 2 : (r - g) / delta + 4

    return { h: (hue * 60 + 360) % 360, s: max ? delta / max : 0, v: max }
}

export function hsvToHex({ h, s, v }) {
    const channel = n => {
        const k = (n + h / 60) % 6

        return v - v * s * Math.max(0, Math.min(k, 4 - k, 1))
    }

    return `#${[channel(5), channel(3), channel(1)].map(c => Math.round(c * 255).toString(16).padStart(2, '0')).join('')}`
}

export class ColorPickerElement extends HTMLElement {
    #value = null
    #hsv = { h: 0, s: 0, v: 0 }
    #dragging = false
    #observer = new MutationObserver(() => this.render())

    connectedCallback() {
        const signal = connected(this)

        this.#value = parseHex(this.dataset.value)
        if (this.#value) this.#hsv = hexToHsv(this.#value)

        // The inner controls' own input/change stay inside: the picker announces the color.
        this.addEventListener('input', event => {
            if (event.target === this) return

            event.stopPropagation()
            if (event.target.matches('[data-slot="color-picker-hue"]')) this.#setHsv({ h: Number(event.target.value) }, 'input')
        }, { signal })

        this.addEventListener('change', event => {
            if (event.target === this) return

            event.stopPropagation()

            if (event.target.matches('[data-slot="color-picker-hue"]')) this.#announce('change')
            if (event.target.matches('[data-color-picker-input]')) this.#setHex(event.target.value)
        }, { signal })

        this.addEventListener('click', event => {
            const swatch = event.target.closest?.('[data-color-picker-swatch]')

            if (swatch) this.#setHex(swatch.dataset.colorPickerSwatch)
            if (event.target.closest?.('[data-color-picker-eyedropper]')) this.#pickFromScreen()
        }, { signal })

        this.addEventListener('keydown', event => {
            if (event.target.matches?.('[data-color-picker-input]') && event.key === 'Enter') {
                event.preventDefault()
                this.#setHex(event.target.value)
            }

            if (! event.target.matches?.('[data-slot="color-picker-area"]')) return

            const step = event.shiftKey ? 0.1 : 0.01
            const move = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] }[event.key]

            if (! move) return

            event.preventDefault()
            this.#setHsv({ s: clamp(this.#hsv.s + move[0]), v: clamp(this.#hsv.v + move[1]) }, 'input')
            this.#announce('change')
        }, { signal })

        this.addEventListener('pointerdown', event => {
            const area = event.target.closest?.('[data-slot="color-picker-area"]')

            if (! area || event.button !== 0) return

            event.preventDefault()
            area.focus()
            area.setPointerCapture?.(event.pointerId)
            this.#dragging = true
            this.#pointAt(area, event)
        }, { signal })

        this.addEventListener('pointermove', event => {
            const area = event.target.closest?.('[data-slot="color-picker-area"]')

            if (this.#dragging && area) this.#pointAt(area, event)
        }, { signal })

        for (const type of ['pointerup', 'pointercancel']) {
            this.addEventListener(type, () => {
                if (! this.#dragging) return

                this.#dragging = false
                this.#announce('change')
            }, { signal })
        }

        const eyedropper = this.querySelector('[data-color-picker-eyedropper]')

        if (eyedropper && 'EyeDropper' in window) eyedropper.hidden = false

        this.render()

        // Livewire morphs put back the server's trigger and inputs: show ours.
        this.#observer.observe(this, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['aria-invalid', 'aria-describedby', 'data-empty', 'aria-pressed'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get value() {
        return this.#value
    }

    set value(value) {
        this.#value = parseHex(value)
        if (this.#value) this.#hsv = hexToHsv(this.#value)
        this.render()
    }

    #pointAt(area, event) {
        const box = area.getBoundingClientRect()

        this.#setHsv({
            s: clamp((event.clientX - box.left) / (box.width || 1)),
            v: clamp(1 - (event.clientY - box.top) / (box.height || 1)),
        }, 'input')
    }

    #setHsv(changes, type) {
        this.#hsv = { ...this.#hsv, ...changes }
        this.#value = hsvToHex(this.#hsv)
        this.render()
        this.#announce(type)
    }

    #setHex(text) {
        const empty = String(text).trim() === ''
        const value = empty ? null : parseHex(text)

        // Not a color: show the current one again.
        if (! empty && ! value) return this.render(true)

        const changed = value !== this.#value

        this.#value = value
        if (value) this.#hsv = hexToHsv(value)
        this.render(true)

        if (changed) {
            this.#announce('input')
            this.#announce('change')
        }
    }

    async #pickFromScreen() {
        try {
            const { sRGBHex } = await new window.EyeDropper().open()

            this.#setHex(sRGBHex)
        } catch {
            // Dismissed.
        }
    }

    #announce(type) {
        this.dispatchEvent(new Event(type, { bubbles: true }))
    }

    render(rewriteInput = false) {
        const set = (element, name, value) => element && element.getAttribute(name) !== value && element.setAttribute(name, value)
        const value = this.#value
        const { h, s, v } = this.#hsv

        this.style.setProperty('--color-picker-hue', h)

        const area = this.querySelector('[data-slot="color-picker-area"]')
        const thumb = this.querySelector('[data-slot="color-picker-thumb"]')

        if (thumb) {
            thumb.style.left = `${s * 100}%`
            thumb.style.top = `${(1 - v) * 100}%`
            thumb.style.setProperty('--color-picker-color', hsvToHex(this.#hsv))
        }

        set(area, 'aria-valuetext', `${Math.round(s * 100)}%, ${Math.round(v * 100)}%`)

        const hue = this.querySelector('[data-slot="color-picker-hue"]')

        if (hue && Number(hue.value) !== Math.round(h)) hue.value = Math.round(h)

        const input = this.querySelector('[data-color-picker-input]')

        if (input && (rewriteInput || input !== document.activeElement) && input.value !== (value ?? '')) input.value = value ?? ''

        const trigger = this.querySelector('[data-color-picker-trigger]')
        const text = this.querySelector('[data-slot="color-picker-value"]')
        const label = value ?? this.dataset.placeholder ?? ''

        if (text && text.textContent !== label) text.textContent = label
        this.querySelector('[data-slot="color-picker-swatch"]')?.style.setProperty('--color-picker-color', value ?? 'transparent')
        set(trigger, 'data-empty', String(! value))

        for (const swatch of this.querySelectorAll('[data-color-picker-swatch]')) {
            set(swatch, 'aria-pressed', String(parseHex(swatch.dataset.colorPickerSwatch) === value))
        }

        writeHidden(this, this.dataset.name, value)

        // The picker carries the field's validation state; screen readers need it on the trigger.
        for (const name of ['aria-invalid', 'aria-describedby']) {
            const state = this.getAttribute(name)

            if (state === null) trigger?.hasAttribute(name) && trigger.removeAttribute(name)
            else set(trigger, name, state)
        }
    }
}

export function register() {
    define('eso-color-picker', ColorPickerElement)
}
