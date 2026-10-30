/*
 * Clicking an Input Group addon (outside its buttons) focuses the group's input.
 *
 * <eso-input-group-count> counts the characters of its group's control as Laravel's
 * max rule does (code points, as mb_strlen), against data-max or else the control's
 * maxlength, and is data-over past it. It describes the control (aria-describedby).
 * Livewire sets the control's value without an input event, so it counts again once
 * Livewire has started and after each morph.
 */

import { define } from '../support/element.js'
import { onLivewire } from '../support/livewire.js'

let counts = 0

export class InputGroupCountElement extends HTMLElement {
    connectedCallback() {
        this.id ||= `input-group-count-${++counts}`
        this.render()
    }

    get control() {
        return this.closest('[data-slot="input-group"]')?.querySelector('[data-slot="input-group-control"]') ?? null
    }

    render() {
        const control = this.control

        if (! control) return

        const count = [...control.value].length
        const max = Number(this.dataset.max || control.getAttribute('maxlength')) || null
        const text = (max ? this.dataset.labelMax : this.dataset.label)?.replace(':count', count).replace(':max', max) ?? String(count)

        if (this.textContent !== text) this.textContent = text
        this.toggleAttribute('data-over', max !== null && count > max)

        const describedBy = (control.getAttribute('aria-describedby') ?? '').split(' ').filter(Boolean)

        if (! describedBy.includes(this.id)) control.setAttribute('aria-describedby', [...describedBy, this.id].join(' '))
    }
}

export function register() {
    define('eso-input-group-count', InputGroupCountElement)

    const countAll = () => document.querySelectorAll('eso-input-group-count').forEach(count => count.render())

    document.addEventListener('click', ({ target }) => {
        const addon = target.closest?.('[data-slot="input-group-addon"]')

        if (! addon || target.closest('button')) return

        addon.parentElement.querySelector('[data-slot="input-group-control"]')?.focus()
    })

    document.addEventListener('input', ({ target }) => {
        target.closest?.('[data-slot="input-group"]')?.querySelectorAll('eso-input-group-count').forEach(count => count.render())
    })

    onLivewire(Livewire => {
        document.addEventListener('livewire:initialized', countAll)
        Livewire.hook('morphed', () => requestAnimationFrame(countAll))
    })
}
