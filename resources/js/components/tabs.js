/*
 * <eso-tabs>
 *
 * .value is the active tab and input/change are dispatched from the tabs themselves,
 * so wire:model binds on them. Without a value the first enabled tab is active, as
 * in Base UI. Arrow keys move focus along the list; Enter or Space activates.
 */

import { announce, connected, define, disconnected } from '../support/element.js'

const TRIGGER = '[data-slot="tabs-trigger"]'
const CONTENT = '[data-slot="tabs-content"]'

let count = 0

export class TabsElement extends HTMLElement {
    #id = `ui-tabs-${++count}`
    #value = null
    #observer = null

    connectedCallback() {
        const signal = connected(this)

        this.#value = this.dataset.value ?? null

        this.addEventListener('click', event => {
            const trigger = event.target.closest?.(TRIGGER)

            if (this.#owns(trigger)) this.#activate(trigger)
        }, { signal })
        this.addEventListener('keydown', event => this.#onKey(event), { signal })

        this.#sync()

        // Livewire morphs put back the server's states and drop our ids: restore them.
        this.#observer = new MutationObserver(() => this.#sync())
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['data-active', 'hidden', 'id', 'tabindex', 'aria-selected'] })
    }

    disconnectedCallback() {
        disconnected(this)
        this.#observer?.disconnect()
    }

    get triggers() {
        return [...this.querySelectorAll(TRIGGER)].filter(trigger => this.#owns(trigger))
    }

    get panels() {
        return [...this.querySelectorAll(CONTENT)].filter(panel => this.#owns(panel))
    }

    get value() {
        return this.#value ?? this.#enabled()[0]?.dataset.value ?? null
    }

    set value(value) {
        this.#value = value === null || value === undefined ? null : String(value)
        this.#sync()
    }

    #owns(element) {
        return element?.closest('eso-tabs') === this
    }

    #enabled() {
        return this.triggers.filter(trigger => ! trigger.disabled && trigger.getAttribute('aria-disabled') !== 'true')
    }

    #activate(trigger) {
        if (trigger.disabled || trigger.getAttribute('aria-disabled') === 'true' || trigger.dataset.value === this.value) return

        this.value = trigger.dataset.value
        announce(this)
    }

    #onKey(event) {
        const trigger = event.target.closest?.(TRIGGER)

        if (! this.#owns(trigger)) return

        const vertical = this.dataset.orientation === 'vertical'
        const rtl = getComputedStyle(this).direction === 'rtl'
        const enabled = this.#enabled()
        const index = enabled.indexOf(trigger)
        const step = {
            [vertical ? 'ArrowDown' : rtl ? 'ArrowLeft' : 'ArrowRight']: 1,
            [vertical ? 'ArrowUp' : rtl ? 'ArrowRight' : 'ArrowLeft']: -1,
        }[event.key]

        let next = null

        if (step) next = enabled[(index + step + enabled.length) % enabled.length]
        else if (event.key === 'Home') next = enabled[0]
        else if (event.key === 'End') next = enabled.at(-1)
        else return

        event.preventDefault()
        next?.focus()
    }

    #sync() {
        const value = this.value
        const set = (element, name, wanted) => {
            if (wanted === null) element.hasAttribute(name) && element.removeAttribute(name)
            else if (element.getAttribute(name) !== wanted) element.setAttribute(name, wanted)
        }

        for (const trigger of this.triggers) {
            const active = trigger.dataset.value === value

            set(trigger, 'id', trigger.id || `${this.#id}-tab-${trigger.dataset.value}`)
            set(trigger, 'data-active', active ? '' : null)
            set(trigger, 'aria-selected', String(active))
            set(trigger, 'tabindex', active ? '0' : '-1')
        }

        for (const panel of this.panels) {
            const active = panel.dataset.value === value
            const trigger = this.triggers.find(trigger => trigger.dataset.value === panel.dataset.value)

            set(panel, 'id', panel.id || `${this.#id}-panel-${panel.dataset.value}`)
            set(panel, 'hidden', active ? null : '')

            if (trigger) {
                set(trigger, 'aria-controls', panel.id)
                set(panel, 'aria-labelledby', trigger.id)
            }
        }
    }
}

export function register() {
    define('eso-tabs', TabsElement)
}
