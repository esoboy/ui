/*
 * <eso-collapsible>
 *
 * Its trigger, or any element inside marked data-collapsible-trigger, shows and hides
 * the content (or elements marked data-collapsible-content, as Trace's). .value is the open state and input/change are dispatched from the
 * collapsible itself, so wire:model binds on it.
 */

import { announce, connected, define, disconnected } from '../support/element.js'

const TRIGGER = '[data-slot="collapsible-trigger"], [data-collapsible-trigger]'
const CONTENT = '[data-slot="collapsible-content"], [data-collapsible-content]'

let collapsibles = 0

export class CollapsibleElement extends HTMLElement {
    #id = `ui-collapsible-${++collapsibles}`
    #open = false
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        this.#open = this.hasAttribute('data-open')

        this.addEventListener('click', event => {
            const trigger = event.target.closest?.(TRIGGER)

            if (! this.#owns(trigger) || trigger.disabled || trigger.getAttribute('aria-disabled') === 'true') return

            this.value = ! this.#open
            announce(this)
        }, { signal })

        this.#sync()

        // Livewire morphs put back the server's open state: restore ours.
        this.#observer = new MutationObserver(() => this.#sync())
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['hidden', 'data-open', 'aria-expanded', 'id'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get value() {
        return this.#open
    }

    set value(open) {
        this.#open = Boolean(open)
        this.#sync()
    }

    #owns(element) {
        return element?.closest('eso-collapsible') === this
    }

    #sync() {
        const open = this.#open
        const toggle = (element, name, on) => on !== element.hasAttribute(name) && element.toggleAttribute(name, on)
        const contents = [...this.querySelectorAll(CONTENT)].filter(content => this.#owns(content))

        toggle(this, 'data-open', open)

        contents.forEach((content, index) => {
            if (! content.id) content.id = `${this.#id}-${index}`

            toggle(content, 'hidden', ! open)
            toggle(content, 'data-open', open)
        })

        for (const trigger of [...this.querySelectorAll(TRIGGER)].filter(trigger => this.#owns(trigger))) {
            if (trigger.getAttribute('aria-expanded') !== String(open)) trigger.setAttribute('aria-expanded', String(open))
            if (contents[0] && trigger.getAttribute('aria-controls') !== contents[0].id) trigger.setAttribute('aria-controls', contents[0].id)

            toggle(trigger, 'data-panel-open', open)
        }
    }
}

export function register() {
    define('eso-collapsible', CollapsibleElement)
}
