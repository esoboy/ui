/*
 * What the components' custom elements share.
 */

/**
 * Defines a custom element. A name taken by another script (another UI library) is an
 * error, not a silent skip: the page would otherwise run that script's element.
 */
export function define(name, element) {
    const existing = window.customElements.get(name)

    if (existing === element) return
    if (existing) throw new Error(`esoboy/ui: <${name}> is already defined by another script.`)

    window.customElements.define(name, element)
}

const connections = new WeakMap()

/**
 * The signal for the listeners an element adds when it connects. A moved element (a
 * keyed morph, wire:sort) disconnects and connects again: with disconnected() in its
 * disconnectedCallback, the listeners of the last connection go, not doubled.
 */
export function connected(element) {
    disconnected(element)

    const controller = new AbortController()

    connections.set(element, controller)

    return controller.signal
}

/**
 * Removes the listeners added with connected()'s signal.
 */
export function disconnected(element) {
    connections.get(element)?.abort()
    connections.delete(element)
}

/**
 * A control's value changed through the user: input and change come from the element
 * itself, which is what Livewire's wire:model (.self in v4) listens to.
 */
export function announce(element) {
    element.dispatchEvent(new Event('input', { bubbles: true }))
    element.dispatchEvent(new Event('change', { bubbles: true }))
}

/**
 * The value the server rendered into data-value as JSON, or the fallback.
 */
export function readValue(element, fallback = null) {
    try {
        return element.dataset.value ? JSON.parse(element.dataset.value) : fallback
    } catch {
        return fallback
    }
}

/**
 * Keeps hidden inputs for classic form posts in step with a value: one `name` input,
 * or one `name[]` input per item of a list. They are rewritten only when they differ,
 * so the element's own MutationObserver isn't woken for nothing.
 */
export function writeHidden(element, name, value) {
    if (! name) return

    const field = Array.isArray(value) ? `${name}[]` : name
    const values = (Array.isArray(value) ? value : [value]).map(item => String(item ?? ''))
    const inputs = [...element.querySelectorAll(':scope > input[type="hidden"]')]

    if (inputs.length === values.length && inputs.every((input, index) => input.name === field && input.value === values[index])) return

    inputs.forEach(input => input.remove())
    element.append(...values.map(item => Object.assign(element.ownerDocument.createElement('input'), { type: 'hidden', name: field, value: item })))
}
