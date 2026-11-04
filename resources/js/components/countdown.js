/*
 * <eso-countdown>
 *
 * Counts down from the time left the server rendered (data-remaining), and dispatches
 * finish when it reaches zero. A named one keeps its end through morphs and restarts
 * with Ui::countdown('name'). A new data-remaining (a re-render counting to a time) moves
 * the end only when it is a second or more off, so re-renders don't make it jitter.
 */

import { define } from '../support/element.js'
import { countdowns, format } from '../support/countdown.js'

export class CountdownElement extends HTMLElement {
    #end = 0
    #finished = null
    #remaining = null
    #timer = 0
    #observer = new MutationObserver(() => this.#update())

    connectedCallback() {
        const name = this.dataset.name

        this.#remaining = this.dataset.remaining
        this.#end = name && countdowns.has(name) ? countdowns.get(name) : this.#endFrom(this.#remaining)
        if (name) countdowns.set(name, this.#end)

        // Already over when it arrives: nothing finishes here.
        if (this.remaining === 0) this.#finished = this.#end

        this.#render()
        this.#observer.observe(this, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['data-remaining', 'data-format'] })
    }

    disconnectedCallback() {
        clearTimeout(this.#timer)
        this.#observer.disconnect()
    }

    get remaining() {
        return Math.max(0, this.#end - Date.now())
    }

    start(seconds = Number(this.dataset.seconds ?? Number(this.dataset.remaining) / 1000)) {
        this.#end = Date.now() + seconds * 1000
        if (this.dataset.name) countdowns.set(this.dataset.name, this.#end)
        this.#render()
    }

    #endFrom(remaining) {
        return Date.now() + Math.max(0, Number(remaining) || 0)
    }

    #update() {
        if (this.dataset.remaining !== this.#remaining) {
            this.#remaining = this.dataset.remaining

            const end = this.#endFrom(this.#remaining)

            if (Math.abs(end - this.#end) >= 1000) {
                this.#end = end
                if (this.dataset.name) countdowns.set(this.dataset.name, end)
            }
        }

        this.#render()
    }

    #render() {
        clearTimeout(this.#timer)

        const remaining = this.remaining
        const text = format(remaining, this.dataset.format ?? 'HH:mm:ss')

        if (this.textContent !== text) this.textContent = text

        if (remaining > 0) {
            this.#timer = setTimeout(() => this.#render(), remaining % 1000 || 1000)
        } else if (this.#finished !== this.#end) {
            this.#finished = this.#end
            this.dispatchEvent(new Event('finish'))
        }
    }
}

export function register() {
    define('eso-countdown', CountdownElement)

    // Ui::countdown('name', seconds) starts the countdowns of that name again.
    window.addEventListener('ui-countdown', ({ detail }) => {
        for (const element of document.querySelectorAll('eso-countdown')) {
            if (element.dataset.name === (detail?.name ?? 'verification-code')) element.start(detail?.seconds ?? undefined)
        }
    })
}
