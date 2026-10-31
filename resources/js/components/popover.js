/*
 * Popover, Dropdown Menu, its submenus and Menubar open from invoker triggers
 * (command="toggle-popover"), so the browser opens, anchors and dismisses them. This
 * points each trigger at its popover as it is about to be used (pressed, reached with
 * the keyboard, or clicked by a script), and keeps its aria-expanded, which shadcn's
 * Button styles.
 */

import { define } from '../support/element.js'
import { onLivewire } from '../support/livewire.js'
import { OpenElement } from '../support/open.js'
import { contentOf, invokerKinds, KINDS, kindOf, link, triggerOf } from '../support/overlay.js'

// wire:model on a Popover binds its open state through <eso-popover>.
class PopoverElement extends OpenElement {}

const INVOKERS = Object.values(KINDS).filter(kind => kind.invoker).map(kind => kind.trigger).join(', ')

export function register() {
    define('eso-popover', PopoverElement)

    const prepare = ({ target }) => {
        const trigger = target.closest?.(INVOKERS)

        if (trigger) for (const kind of invokerKinds(trigger)) link(trigger, contentOf(trigger, kind))
    }

    for (const type of ['pointerdown', 'keydown', 'focusin', 'click']) document.addEventListener(type, prepare, true)

    document.addEventListener('toggle', ({ target, newState }) => {
        const kind = kindOf(target)

        if (kind && KINDS[kind].invoker) triggerOf(target, kind)?.setAttribute('aria-expanded', String(newState === 'open'))
    }, true)

    // Morphs drop aria-expanded, which the server doesn't write: put back an open one's.
    onLivewire(Livewire => Livewire.hook('morph.updated', ({ el }) => {
        if (el.commandForElement?.matches?.(':popover-open')) el.setAttribute('aria-expanded', 'true')
    }))
}
