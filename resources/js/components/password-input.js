/*
 * <eso-password-input>
 *
 * The eye toggles the input between password and text.
 */

import { connected, define, disconnected } from '../support/element.js'

// The eye shows or hides the password; morphs put back type="password", so it is reapplied.
export class PasswordInputElement extends HTMLElement {
    #visible = false
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('click', event => {
            if (! event.target.closest?.('[data-password-toggle]')) return

            this.#visible = ! this.#visible
            this.#render()
        }, { signal })

        this.#render()
        this.#observer.observe(this, { subtree: true, childList: true, attributes: true, attributeFilter: ['type', 'aria-pressed'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer.disconnect()
    }

    get visible() {
        return this.#visible
    }

    #render() {
        const input = this.querySelector('[data-slot="input-group-control"]')
        const toggle = this.querySelector('[data-password-toggle]')
        const type = this.#visible ? 'text' : 'password'

        if (input && input.getAttribute('type') !== type) input.setAttribute('type', type)
        if (toggle && toggle.getAttribute('aria-pressed') !== String(this.#visible)) toggle.setAttribute('aria-pressed', String(this.#visible))
    }
}

export function register() {
    define('eso-password-input', PasswordInputElement)
}
