/*
 * <eso-verification-code-button>
 *
 * Ui::countdown('name') makes the buttons of that name count down, ignoring clicks,
 * then offer to resend.
 */

import { countdowns } from '../support/countdown.js'
import { connected, define, disconnected } from '../support/element.js'

export class VerificationCodeButtonElement extends HTMLElement {
    #timer = 0
    #observer = new MutationObserver(() => this.#render())

    connectedCallback() {
        const signal = connected(this)

        // While counting, clicks stop here, before the button's wire:click.
        this.addEventListener('click', event => {
            if (this.remaining > 0) {
                event.preventDefault()
                event.stopPropagation()
            }
        }, { capture: true, signal })

        this.#render()
        this.#observer.observe(this, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['aria-disabled'] })
    }

    disconnectedCallback() {
        disconnected(this)
        clearTimeout(this.#timer)
        this.#observer.disconnect()
    }

    get remaining() {
        const end = countdowns.get(this.dataset.name)

        return end ? Math.max(0, Math.ceil((end - Date.now()) / 1000)) : 0
    }

    start(seconds = Number(this.dataset.seconds) || 60) {
        countdowns.set(this.dataset.name, Date.now() + seconds * 1000)
        this.#render()
    }

    #render() {
        clearTimeout(this.#timer)

        const button = this.querySelector('[data-slot="button"]')
        const label = this.querySelector('[data-verification-code-label]')
        const remaining = this.remaining

        if (! button || ! label || ! countdowns.has(this.dataset.name)) return

        const text = remaining > 0
            ? (this.dataset.labelCountdown ?? ':seconds').replace(':seconds', remaining)
            : this.dataset.labelResend ?? label.textContent

        if (label.textContent !== text) label.textContent = text

        if (remaining > 0) {
            button.getAttribute('aria-disabled') !== 'true' && button.setAttribute('aria-disabled', 'true')
            this.#timer = setTimeout(() => this.#render(), (countdowns.get(this.dataset.name) - Date.now()) % 1000 || 1000)
        } else {
            button.hasAttribute('aria-disabled') && button.removeAttribute('aria-disabled')
        }
    }
}

export function register() {
    define('eso-verification-code-button', VerificationCodeButtonElement)

    // Ui::countdown('name') starts the buttons of that name.
    window.addEventListener('ui-countdown', ({ detail }) => {
        for (const element of document.querySelectorAll('eso-verification-code-button')) {
            if (element.dataset.name === (detail?.name ?? 'verification-code')) element.start(detail?.seconds ?? undefined)
        }
    })
}
