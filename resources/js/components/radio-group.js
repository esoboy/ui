/*
 * <eso-radio-group> and <eso-checkbox-group>
 *
 * Expose their inputs' state as .value (one value, or an array for checkboxes) and
 * re-dispatch the inputs' input/change events from the group itself, so wire:model /
 * x-model bind on the group the way shadcn puts value on RadioGroup or ToggleGroup.
 * (Livewire v4 models only listen to events whose target is the bound element.)
 * Inputs without a name get the group's, which keeps native keyboard grouping.
 */

import { define } from '../support/element.js'

let groups = 0

class ChoiceGroupElement extends HTMLElement {
    static inputType = 'radio'

    #fallbackName = `ui-${this.constructor.inputType}-group-${++groups}`
    #observer = null

    constructor() {
        super()

        for (const type of ['input', 'change']) {
            this.addEventListener(type, event => {
                if (event.target !== this && this.inputs.includes(event.target)) this.announce(type)
            })
        }
    }

    connectedCallback() {
        this.#nameInputs()

        // Livewire morphs can add inputs or strip the names we assigned.
        this.#observer = new MutationObserver(() => this.#nameInputs())
        this.#observer.observe(this, { childList: true, subtree: true, attributes: true, attributeFilter: ['name'] })
    }

    disconnectedCallback() {
        this.#observer?.disconnect()
    }

    get inputs() {
        return [...this.querySelectorAll(`input[type="${this.constructor.inputType}"]`)]
            .filter(input => input.closest('eso-radio-group, eso-checkbox-group') === this)
    }

    announce(type) {
        this.dispatchEvent(new Event(type, { bubbles: true }))
    }

    #nameInputs() {
        const name = this.getAttribute('name') || this.#fallbackName

        this.inputs.forEach(input => input.name || (input.name = name))
    }
}

export class RadioGroupElement extends ChoiceGroupElement {
    static inputType = 'radio'

    #value = null

    constructor() {
        super()

        // A deselectable group (single Toggle Group) unchecks its pressed item when clicked again.
        this.addEventListener('click', event => {
            const radio = event.target

            if (! this.hasAttribute('data-deselectable') || ! this.inputs.includes(radio)) return

            if (radio.value === this.#value) {
                radio.checked = false
                this.#value = null
                this.announce('input')
                this.announce('change')
            }
        })

        this.addEventListener('change', () => (this.#value = this.value))
    }

    connectedCallback() {
        super.connectedCallback()
        this.#value = this.value
    }

    get value() {
        return this.inputs.find(radio => radio.checked)?.value ?? null
    }

    set value(value) {
        const target = value === null || value === undefined ? null : String(value)

        this.inputs.forEach(radio => (radio.checked = radio.value === target))
        this.#value = this.value
    }
}

export class CheckboxGroupElement extends ChoiceGroupElement {
    static inputType = 'checkbox'

    get value() {
        return this.inputs.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value)
    }

    set value(value) {
        const values = (Array.isArray(value) ? value : []).map(String)

        this.inputs.forEach(checkbox => (checkbox.checked = values.includes(checkbox.value)))
    }
}

export function register() {
    define('eso-radio-group', RadioGroupElement)
    define('eso-checkbox-group', CheckboxGroupElement)
}
