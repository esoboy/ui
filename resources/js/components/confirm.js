/*
 * wire:confirm="Delete this post?" asks through <x-ui::confirm /> when the layout has
 * one: in its alert dialog, with .destructive its destructive one, and with .popover in
 * a popover at the element. "Question?|Label" names the confirm button. Without it, and
 * for wire:confirm.prompt, Livewire's own confirm() stays.
 */

import { onLivewire } from '../support/livewire.js'

// The question and the confirm button's label, or the translated ones the server rendered.
function word(element, question, label) {
    for (const [node, text] of [[element.querySelector('[data-slot$="-title"]'), question], [element.querySelector('[value="confirm"]'), label]]) {
        node.dataset.default ??= node.textContent
        node.textContent = text || node.dataset.default
    }
}

function askInDialog(dialog) {
    return new Promise(resolve => {
        dialog.returnValue = ''
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true })
        dialog.showModal()
    })
}

// Anchored to the element while open; Escape, a click outside and Cancel decline.
function askInPopover(popover, el) {
    return new Promise(resolve => {
        let confirmed = false

        const click = ({ target }) => {
            if (target.closest('[value="confirm"]')) confirmed = true
        }

        const toggle = event => {
            if (event.newState !== 'closed') return

            popover.removeEventListener('click', click)
            popover.removeEventListener('toggle', toggle)

            // Focus was in the popover: give it back to the element that asked.
            if (! document.activeElement || document.activeElement === document.body || popover.contains(document.activeElement)) el.focus()

            resolve(confirmed)
        }

        popover.addEventListener('click', click)
        popover.addEventListener('toggle', toggle)
        popover.showPopover({ source: el })
    })
}

export function register() {
    onLivewire(Livewire => {
        // Runs after Livewire's own confirm directive, so this replaces its handler.
        Livewire.hook('directive.init', ({ el, directive }) => {
            if (directive.value !== 'confirm' || directive.modifiers.includes('prompt')) return

            const popover = directive.modifiers.includes('popover')
            const id = 'ui-confirm' + (popover ? '-popover' : '') + (directive.modifiers.includes('destructive') ? '-destructive' : '')
            const [question, label] = directive.expression.replaceAll('\\n', '\n').split('|')

            el.__livewire_confirm = (action, instead) => {
                const element = document.getElementById(id)

                if (! element) return window.confirm(question || 'Are you sure?') ? action() : instead()

                word(element, question, label)

                const asking = popover ? askInPopover(element, el) : askInDialog(element)

                asking.then(confirmed => (confirmed ? action() : instead()))
            }
        })
    })
}
