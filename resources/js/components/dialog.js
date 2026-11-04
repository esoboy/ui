/*
 * Dialog, Sheet and Drawer open and close natively through invoker commands. Two gaps remain:
 * closing on a backdrop click (Safari has no closedby="any" yet) and Livewire's
 * Ui::dialog('name')->open() / ->close(). wire:model on the root binds the open state
 * through <eso-dialog>.
 */

import { define } from '../support/element.js'
import { OpenElement } from '../support/open.js'

class DialogElement extends OpenElement {}

export function register() {
    define('eso-dialog', DialogElement)

    document.addEventListener('click', ({ target, clientX, clientY }) => {
        if (! target.matches?.('dialog[open]:is([data-slot="dialog-content"], [data-slot="sheet-content"], [data-slot="drawer-popup"])')) return

        // A click on the <dialog> itself outside its box landed on the backdrop.
        const box = target.getBoundingClientRect()

        if (clientX < box.left || clientX > box.right || clientY < box.top || clientY > box.bottom) target.close()
    })

    window.addEventListener('ui-dialog', ({ detail }) => {
        const dialog = document.getElementById(detail.name)

        if (! dialog) return

        if (detail.open) dialog.open || dialog.showModal()
        else dialog.close()
    })
}
