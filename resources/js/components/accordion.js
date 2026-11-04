/*
 * <eso-accordion>
 *
 * Items are native <details>, which open and close themselves. Unless multiple, the
 * accordion gives its items one shared name so the browser keeps a single item open.
 * Arrow keys, Home and End move between triggers, as in Base UI.
 */

import { connected, define, disconnected } from '../support/element.js'

let accordions = 0

export class AccordionElement extends HTMLElement {
    #name = `ui-accordion-${++accordions}`
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        this.addEventListener('keydown', event => this.#onKey(event), { signal })

        // A disabled item's summary never toggles it.
        this.addEventListener('click', event => {
            if (event.target.closest?.('summary[aria-disabled="true"]')) event.preventDefault()
        }, { signal })

        this.#nameItems()

        this.#observer = new MutationObserver(() => this.#nameItems())
        this.#observer.observe(this, { childList: true, subtree: true })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get items() {
        return [...this.querySelectorAll('details[data-slot="accordion-item"]')].filter(item => item.closest('eso-accordion') === this)
    }

    #nameItems() {
        if (this.hasAttribute('data-multiple')) return

        for (const item of this.items) {
            if (item.getAttribute('name') !== this.#name) item.setAttribute('name', this.#name)
        }
    }

    #onKey(event) {
        const trigger = event.target

        if (! trigger.matches?.('summary[data-slot="accordion-trigger"]') || trigger.closest('eso-accordion') !== this) return

        const triggers = this.items.map(item => item.querySelector(':scope > summary')).filter(summary => summary && summary.getAttribute('aria-disabled') !== 'true')
        const index = triggers.indexOf(trigger)
        const next = {
            ArrowDown: triggers[(index + 1) % triggers.length],
            ArrowUp: triggers.at(index - 1),
            Home: triggers[0],
            End: triggers.at(-1),
        }[event.key]

        if (! next) return

        event.preventDefault()
        next.focus()
    }
}

export function register() {
    define('eso-accordion', AccordionElement)
}
