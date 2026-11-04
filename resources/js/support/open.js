/*
 * <eso-dialog> and <eso-popover>: around a Dialog's (Sheet's, Drawer's, Alert
 * Dialog's) <dialog> or a Popover's content when wire:model binds whether it is open.
 * .value is that state; input and change are dispatched from the element as it opens
 * or closes, however that happens, so wire:model binds on it.
 */

import { announce, connected, disconnected } from './element.js'
import { kindOf, triggerOf } from './overlay.js'

export class OpenElement extends HTMLElement {
    #announced = false

    connectedCallback() {
        const signal = connected(this)

        // Livewire may set .value before this script defined the element.
        if (Object.hasOwn(this, 'value')) {
            const value = this.value

            delete this.value
            this.value = value
        }

        this.#announced = this.value

        const changed = () => {
            if (this.value === this.#announced) return

            this.#announced = this.value
            announce(this)
        }

        this.target?.addEventListener('toggle', changed, { signal })
        this.target?.addEventListener('close', changed, { signal })
    }

    disconnectedCallback() {
        disconnected(this)
    }

    get target() {
        return this.firstElementChild
    }

    get value() {
        const target = this.target

        return target instanceof HTMLDialogElement ? target.open : Boolean(target?.matches(':popover-open'))
    }

    set value(open) {
        const target = this.target

        open = Boolean(open)
        this.#announced = open

        if (! target || open === this.value) return

        if (target instanceof HTMLDialogElement) {
            open ? target.showModal() : target.close()
        } else {
            const kind = kindOf(target)

            open ? target.showPopover({ source: (kind && triggerOf(target, kind)) ?? undefined }) : target.hidePopover()
        }
    }
}
