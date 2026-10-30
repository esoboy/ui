/*
 * <eso-input-otp>
 *
 * One real input sits invisibly over the slots and does all the typing, pasting and
 * autofill; this mirrors its characters, the active slot and the caret into the slots.
 * wire:model binds on that input directly.
 */

import { define } from '../support/element.js'

export class InputOtpElement extends HTMLElement {
    #input = null
    #render = () => this.render()

    connectedCallback() {
        this.#input = this.querySelector('input[data-slot="input-otp-input"]')

        if (! this.#input) return

        // Livewire and Alpine set .value directly, without events: watch the setter too.
        const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')
        const render = this.#render

        Object.defineProperty(this.#input, 'value', {
            configurable: true,
            get() { return descriptor.get.call(this) },
            set(value) { descriptor.set.call(this, value); render() },
        })

        for (const type of ['input', 'focus', 'blur', 'select', 'keyup', 'click']) this.#input.addEventListener(type, this.#render)
        this.ownerDocument.addEventListener('selectionchange', this.#render)

        this.render()
    }

    disconnectedCallback() {
        this.ownerDocument.removeEventListener('selectionchange', this.#render)
    }

    render() {
        const input = this.#input

        if (! input) return

        const value = input.value
        const max = Number(input.maxLength > 0 ? input.maxLength : value.length)
        const focused = this.ownerDocument.activeElement === input
        const active = Math.min(input.selectionStart ?? value.length, max - 1)

        this.querySelectorAll('[data-slot="input-otp-slot"]').forEach(slot => {
            const index = Number(slot.dataset.index)
            const isActive = focused && index === active

            slot.textContent = value[index] ?? ''
            slot.dataset.active = String(isActive)
            slot.dataset.caret = String(isActive && value[index] === undefined)
        })
    }
}

export function register() {
    define('eso-input-otp', InputOtpElement)
}
