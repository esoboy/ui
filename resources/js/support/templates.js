/*
 * Markup a component's script adds at run time comes from its view: Blade renders it,
 * components and icons included, into <template data-template="name">, and the script
 * clones it and fills in the data. The structure lives in one place, the view.
 */

/**
 * The element's own templates by name. A template keeps working after it leaves the
 * DOM, so a component that replaces its children can read them first.
 */
export function readTemplates(element) {
    return Object.fromEntries(
        [...element.querySelectorAll(':scope > template[data-template]')].map(template => [template.dataset.template, template]),
    )
}

/**
 * A copy of a template's element, or null when the view rendered none.
 */
export function clone(template) {
    return template?.content.firstElementChild?.cloneNode(true) ?? null
}
