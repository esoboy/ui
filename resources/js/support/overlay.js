/*
 * Triggers and the popovers they open, paired in the page rather than by ids. Ids
 * counted on the server shift whenever an overlay before them comes or goes, and
 * Livewire's morph matches elements by id, so it would replace an open popover.
 *
 * A trigger opens the first popover of its kind after it that no trigger in between
 * took: the order Blade writes Trigger and Content in. Several kinds can share one
 * element (a Tooltip on a Dropdown Menu trigger), each through its own marker.
 */

export const KINDS = {
    popover: { trigger: '[data-popover-trigger]', content: '[data-slot="popover-content"]', invoker: true },
    'dropdown-menu': { trigger: '[data-dropdown-menu-trigger]', content: '[data-slot="dropdown-menu-content"]', invoker: true },
    'dropdown-menu-sub': { trigger: '[data-slot="dropdown-menu-sub-trigger"]', content: '[data-slot="dropdown-menu-sub-content"]', invoker: true },
    menubar: { trigger: '[data-slot="menubar-trigger"]', content: '[data-slot="menubar-content"]', invoker: true },
    'context-menu': { trigger: '[data-slot="context-menu-trigger"]', content: '[data-slot="context-menu-content"]' },
    'navigation-menu': { trigger: '[data-slot="navigation-menu-trigger"]', content: '[data-slot="navigation-menu-content"]' },
    tooltip: { trigger: '[data-tooltip-trigger]', content: '[data-slot="tooltip-content"]' },
    'hover-card': { trigger: '[data-hover-card-trigger]', content: '[data-slot="hover-card-content"]' },
}

const both = kind => `${KINDS[kind].trigger}, ${KINDS[kind].content}`

/**
 * The popover a trigger of this kind opens, or null.
 */
export function contentOf(trigger, kind) {
    let depth = 0
    let after = false

    for (const node of trigger.getRootNode().querySelectorAll(both(kind))) {
        if (! after) {
            after = node === trigger
        } else if (node.matches(KINDS[kind].trigger)) {
            depth++
        } else if (depth-- === 0) {
            return node
        }
    }

    return null
}

/**
 * The trigger that opens this popover of this kind, or null.
 */
export function triggerOf(content, kind) {
    const nodes = [...content.getRootNode().querySelectorAll(both(kind))]

    let depth = 0

    for (let index = nodes.indexOf(content) - 1; index >= 0; index--) {
        if (! nodes[index].matches(KINDS[kind].trigger)) {
            depth++
        } else if (depth-- === 0) {
            return nodes[index]
        }
    }

    return null
}

/**
 * The kind of a popover's content, or null.
 */
export function kindOf(content) {
    return Object.keys(KINDS).find(kind => content.matches?.(KINDS[kind].content)) ?? null
}

/**
 * Points an invoker trigger (command="toggle-popover" commandfor="") at its popover, so
 * the browser opens, anchors and dismisses it natively. The server writes commandfor
 * empty and morphs keep it so, which keeps the element set here.
 */
export function link(trigger, content) {
    if (content && trigger.commandForElement !== content) trigger.commandForElement = content
    if (content && 'ariaControlsElements' in trigger) trigger.ariaControlsElements = [content]
}

/**
 * The kinds whose popover a click on this element toggles.
 */
export function invokerKinds(element) {
    return Object.keys(KINDS).filter(kind => KINDS[kind].invoker && element.matches?.(KINDS[kind].trigger))
}
